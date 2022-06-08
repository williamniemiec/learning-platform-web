<?php
declare (strict_types=1);

namespace dao;


use repositories\Database;
use domain\enum\ClassTypeEnum;
use domain\Video;


/**
 * Responsible for managing 'videos' table.
 */
class VideosDAO extends ClassesDAO
{
    //-------------------------------------------------------------------------
    //        Constructor
    //-------------------------------------------------------------------------
    /**
     * Creates 'videos' table manager.
     *
     * @param       Database $db Database
     */
    public function __construct(Database $db)
    {
        parent::__construct($db);
    }
    
    
    //-------------------------------------------------------------------------
    //        Methods
    //-------------------------------------------------------------------------
    /**
     * Gets video from a class.
     *
     * @param       int idModule Module id that the class belongs to
     * @param       int classOrder Class order inside the module that it 
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
            FROM    videos 
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
            (int) $rawClass['id_module'],
            (int) $rawClass['class_order'],
            $rawClass['title'],
            $rawClass['videoID'],
            (int) $rawClass['length'],
            $rawClass['description']
        ); 
    }
    
    /**
     * Gets all video classes from a module.
     * 
     * @param       int idModule Module id
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
            FROM    videos
            WHERE   id_module = ?
        ");
        $this->runQueryWithArguments($idModule);
        
        return $this->parseGetAllResponseQuery();
    }

    private function parseGetAllResponseQuery()
    {
        if (!$this->hasResponseQuery()) {
            return array();
        }
        
        $classes = array();
            
        foreach ($this->getAllResponseQuery() as $class) {
            $classes[] = new Video(
                (int) $class['id_module'], 
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
     * {@inheritdoc}
     * @Override
     */
    public function totalLength() : int
    {
        $this->withQuery("
            SELECT  SUM(length) AS total_length
            FROM    videos
        ");
        $this->runQueryWithoutArguments();
        
        return $this->getResponseQuery()['total_length'];
    }
    
    /**
     * {@inheritdoc}
     * @Override
     */
    public function wasWatched(int $idStudent, int $idModule, int $classOrder) : bool
    {
        $this->withQuery("
            SELECT  COUNT(*) AS was_watched
            FROM    student_historic
            WHERE   class_type = 0 AND 
                    id_student = ? AND 
                    id_module = ? AND 
                    class_order = ?
        ");
        $this->runQueryWithArguments($idStudent, $idModule, $classOrder);
        
        return ($this->getResponseQuery()['was_watched'] > 0);
    }
    
    /**
     * Marks a class as watched by a student.
     * 
     * @param       int $idStudent Student id
     * @param       int $idModule Module id
     * @param       int $classOrder Class order
     * 
     * @return      bool If class has been successfully added to student history
     * 
     * @throws      \InvalidArgumentException If any argument is invalid 
     * 
     * @Override
     */
    public function markAsWatched(int $idStudent, int $idModule, int $classOrder) : bool
    {
        return $this->_MarkAsWatched(
            $idStudent, 
            $idModule, 
            $classOrder,
            new ClassTypeEnum(ClassTypeEnum::VIDEO)
        );
    }
}