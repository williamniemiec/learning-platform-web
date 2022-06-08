<?php
declare (strict_types=1);

namespace panel\dao;


use panel\repositories\Database;
use panel\domain\Admin;
use panel\domain\Video;
use panel\domain\Module;
use panel\domain\Action;
use panel\util\IllegalAccessException;


/**
 * Responsible for managing 'videos' table.
 */
class VideosDAO extends ClassesDAO
{
    //-------------------------------------------------------------------------
    //        Attributes
    //-------------------------------------------------------------------------
    private $admin;
    
    
    //-------------------------------------------------------------------------
    //        Constructor
    //-------------------------------------------------------------------------
    /**
     * Creates 'videos' table manager.
     *
     * @param       Database $db Database
     * @param       Admin $admin [Optional] Admin logged in
     */
    public function __construct(Database $db, Admin $admin = null)
    {
        parent::__construct($db);
        $this->admin = $admin;
    }
    
    
    //-------------------------------------------------------------------------
    //        Methods
    //-------------------------------------------------------------------------
    /**
     * Gets video from a class.
     *
     * @param       int $idModule Module id that the class belongs to
     * @param       int $classOrder Class order inside the module that it 
     * belongs to
     *
     * @return      Video Video class or null if class does not exist
     * 
     * @throws      \InvalidArgumentException If module id or class order is 
     * empty or less than or equal to zero
     */
    public function get(int $idModule, int $classOrder) : ?Video
    {
        $this->validateModuleId($idModule);
        $this->validateClassOrder($classOrder);
        $this->withQuery("
            SELECT  * 
            FROM    videos  NATURAL JOIN modules
            WHERE   id_module = ? AND class_order = ?
        ");
        $this->runQueryWithArguments($idModule, $classOrder);

        return $this->parseGetResponseQuery(); 
    }

    private function parseGetResponseQuery()
    {
        if (!$this->hasResponseQuery()) {
            return null;
        }
        
        $rawClass = $this->getResponseQuery();
        
        return new Video(
            new Module((int) $rawClass['id_module'], $rawClass['name']),
            (int) $rawClass['class_order'],
            $rawClass['title'],
            $rawClass['videoID'],
            (int) $rawClass['length'],
            $rawClass['description']
        );
    }
    
    /**
     * Gets all registered video classes.
     * 
     * @param       int $limit [Optional] Maximum classes returned
     * @param       int $offset [Optional] Ignores first results from the return
     * 
     * @return      Video[] Registered video classes or empty array if there are
     * no registered video classes
     */
    public function getAll(int $limit = -1, int $offset = -1) : array
    {
        $this->withQuery($this->buildGetAllQuery($limit, $offset));
        $this->runQueryWithoutArguments();

        return $this->parseGetAllResponseQuery();
    }

    private function buildGetAllQuery($limit, $offset)
    {
        $query = "
            SELECT      *
            FROM        videos NATURAL JOIN modules
            ORDER BY    title
        ";

        if ($limit > 0) {
            if ($offset > 0) {
                $query .= " LIMIT ".$offset.",".$limit;
            }
            else {
                $query .= " LIMIT ".$limit;
            }
        }

        return $query;
    }

    private function parseGetAllResponseQuery()
    {
        if (!$this->hasResponseQuery()) {
            return array();
        }

        $classes = array();
        
        foreach ($this->getAllResponseQuery() as $class) {
            $classes[] = new Video(
                new Module((int) $class['id_module'], $class['name']),
                (int) $class['class_order'],
                $class['title'],
                $class['videoID'],
                (int) $class['length'],
                $class['description']
            );
        }

        return $classes;
    }
    
    /**
     * Gets all video classes from a module.
     *
     * @param       int $idModule Module id
     *
     * @return      Video[] Classes that belongs to the module
     *
     * @throws      \InvalidArgumentException If module id is empty or less
     * than or equal to zero
     *
     * @Override
     */
    public function getAllFromModule(int $idModule) : array
    {
        $this->validateModuleId($idModule);
        $this->withQuery("
            SELECT  *
            FROM    videos NATURAL JOIN modules
            WHERE   id_module = ?
        ");
        $this->runQueryWithArguments($idModule);
        
        return $this->parseGetAllResponseQuery();
    }
    
    /**
     * Adds a new video class.
     * 
     * @param       Video $video Video to be added
     * 
     * @return      bool If class was successfully added
     * 
     * @throws      IllegalAccessException If current admin does not have
     * authorization to create new classes
     * @throws      \InvalidArgumentException If video or admin provided in the
     * constructor is empty
     */
    public function add(Video $video) : bool
    {
        $this->validateLoggedAdmin();
        $this->validateAuthorization(0, 1);
        $this->validateVideo($video);
        $this->withQuery($this->buildNewQuery($video));
        $this->runQueryWithArguments($this->buildNewQueryArguments($video));

        return $this->parseAddResponseQuery($video);
    }

    private function validateVideo($video)
    {
        if (empty($video)) {
            throw new \InvalidArgumentException("Video cannot be empty");
        }
    }

    private function buildNewQuery($video)
    {
        $query = "";

        if (empty($video->getDescription())) {
            $query = "
                INSERT INTO videos
                (id_module, class_order, title, videoID, length)
                VALUES (?, ?, ?, ?, ?)
            ";
        }
        else {
            $query = "
                INSERT INTO videos
                (id_module, class_order, title, videoID, length, description)
                VALUES (?, ?, ?, ?, ?, ?)
            ";
        }

        return $query;
    }

    private function buildNewQueryArguments($video)
    {
        $bindArguments = array(
            $video->getModuleId(), 
            $video->getClassOrder(), 
            $video->getTitle(), 
            $video->getVideoId(), 
            $video->getLength()
        );

        if (!empty($video->getDescription())) {
            $bindArguments[] = $video->getDescription();
        }

        return $bindArguments;
    }

    private function parseAddResponseQuery($video)
    {
        if (!$this->hasResponseQuery()) {
            return false;
        }

        $action = new Action();
        $action->addClass($video->getModuleId(), $video->getClassOrder());
        $adminsDao = new AdminsDAO($this->db, Admin::getLoggedIn($this->db));
        $adminsDao->newAction($action);
        
        return true;
    }
    
    /**
     * Updates a video class.
     * 
     * @param       Video $video Video to be added
     * 
     * @return      bool If class has been successfully updated
     * 
     * @throws      IllegalAccessException If current admin does not have
     * authorization to update classes
     * @throws      \InvalidArgumentException If video or admin provided in the
     * constructor is empty
     */
    public function update(Video $video) : bool
    {
        $this->validateLoggedAdmin();
        $this->validateAuthorization(0, 1);
        $this->validateVideo($video);
        $this->withQuery($this->buildUpdateQuery($video));
        $this->runQueryWithArguments($this->buildUpdateQueryArguments($video));

        return $this->parseUpdateResponseQuery($video);
    }

    private function buildUpdateQuery($video)
    {
        $query = "";

        if (empty($video->getDescription())) {
            $query = "
                UPDATE  videos
                SET     title = ?, videoID = ?, length = ?
                WHERE   id_module = ? AND class_order = ?
            ";
        }
        else {
            $query = "
                UPDATE  videos
                SET     title = ?, videoID = ?, length = ?, description = ?
                WHERE   id_module = ? AND class_order = ?
            ";
        }

        return $query;
    }

    private function buildUpdateQueryArguments($video)
    {
        $bindArguments = array(
            $video->getTitle(), 
            $video->getVideoId(), 
            $video->getLength()
        );

        if (!empty($video->getDescription())) {
            $bindArguments[] = $video->getDescription();
        }

        $bindArguments[] = $video->getModuleId();
        $bindArguments[] = $video->getClassOrder();

        return $bindArguments;
    }

    private function parseUpdateResponseQuery($video)
    {
        if (!$this->hasResponseQuery()) {
            return false;
        }

        $action = new Action();
        $action->updateClass($video->getModuleId(), $video->getClassOrder());
        $adminsDao = new AdminsDAO($this->db, Admin::getLoggedIn($this->db));
        $adminsDao->newAction($action);
        
        return true;
    }
    
    /**
     * Removes a video class.
     *
     * @param       int $idModule Module id to which the class belongs
     * @param       int $classOrder Class order in the module
     *
     * @return      bool If class has been successfully removed
     *
     * @throws      IllegalAccessException If current admin does not have
     * authorization to delete classes
     * @throws      \InvalidArgumentException If module id or class order is 
     * empty, less than or equal to zero or if admin id provided in the 
     * constructor is empty
     */
    public function delete(int $idModule, int $classOrder) : bool
    {
        $this->validateLoggedAdmin();
        $this->validateAuthorization(0, 1);
        $this->validateModuleId($idModule);
        $this->validateClassOrder($classOrder);
        $this->withQuery("
            DELETE FROM videos
            WHERE id_module = ? AND class_order = ?
        ");
        $this->runQueryWithArguments($idModule, $classOrder);
        
        return $this->parseDeleteResponseQuery($idModule, $classOrder);
    }

    private function parseDeleteResponseQuery($idModule, $classOrder)
    {
        if (!$this->hasResponseQuery()) {
            return false;
        }

        $action = new Action();
        $action->deleteClass($idModule, $classOrder);
        $adminsDao = new AdminsDAO($this->db, Admin::getLoggedIn($this->db));
        $adminsDao->newAction($action);
        
        return true;
    }
    
    /**
     * Changes module ans class order of a class.
     * 
     * @param       Video $video Class to be updated
     * @param       int $newIdModule New module id
     * @param       int $newClassOrder New class order
     * 
     * @return      bool If class has been successfully updated
     * 
     * @throws      IllegalAccessException If current admin does not have
     * authorization to update classes
     * @throws      \InvalidArgumentException If video or admin provided in the
     * constructor is empty or if module id or class order is empty or less 
     * than or equal to zero
     */
    public function updateModule(Video $video, int $newIdModule, int $newClassOrder) : bool
    {
        $this->validateLoggedAdmin();
        $this->validateAuthorization(0, 1);
        $this->validateModuleId($newIdModule);
        $this->validateClassOrder($newClassOrder);
        $this->validateVideo($video);

        // Sets class_order = 0 temporary to avoid constraint error
        $this->withQuery("
            UPDATE  videos
            SET     class_order = 0
            WHERE   id_module = ? AND class_order = ?
        ");
        $this->runQueryWithArguments($video->getModuleId(), $video->getClassOrder());
        
        // Moves class to new module
        $this->withQuery("
            UPDATE  videos
            SET     id_module = ?
            WHERE   id_module = ? AND class_order = 0
        ");
        $this->runQueryWithArguments($newIdModule, $video->getModuleId());
        
        // Sets class order
        $this->withQuery("
            UPDATE  videos
            SET     class_order = ?
            WHERE   id_module = ? AND class_order = 0
        ");
        $this->runQueryWithArguments($newClassOrder, $newIdModule);
        
        return $this->parseUpdateResponseQuery($video);
    }
}