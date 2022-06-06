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
            
        $sql = $this->buildGetQuery();
        $this->runQueryWithArguments($sql, $idModule, $classOrder);
        
        return $this->parseGetQueryResponse($sql);
    }

    private function buildGetQuery()
    {
        return $this->db->prepare("
            SELECT  * 
            FROM    videos 
            WHERE   id_module = ? AND class_order = ?
        ");
    }

    private function parseGetQueryResponse($sql)
    {
        if (!$sql || $sql->rowCount() <= 0) {
            return array();
        }
        
        $rawClass = $sql->fetch();
        
        return new Video(
            (int) $rawClass['id_module'],
            (int) $rawClass['class_order'],
            $rawClass['title'],
            $rawClass['videoID'],
            (int) $rawClass['length'],
            $rawClass['description']
        ); 
    }

    private function validateModuleId($id)
    {
        if (empty($id) || $id <= 0) {
            throw new \InvalidArgumentException("Module id cannot be empty or ".
                                                "less than or equal to zero");
        }
    }

    private function validateClassOrder($order)
    {
        if (empty($order) || $order <= 0) {
            throw new \InvalidArgumentException("Class order cannot be empty ".
                                                "or less than or equal to zero");
        }
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

        $sql = $this->buildGetAllQuery();
        $this->runQueryWithArguments($sql, $idModule);
        
        return $this->parseGetAllQueryResponse($sql);
    }

    private function buildGetAllQuery()
    {
        return $this->db->prepare("
            SELECT  *
            FROM    videos
            WHERE   id_module = ?
        ");
    }

    private function parseGetAllQueryResponse($sql)
    {
        if (!$sql || $sql->rowCount() <= 0) {
            return array();
        }
        
        $classes = array();
        $rawClasses = $sql->fetchAll();
            
        foreach ($rawClasses as $class) {
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
        return $this->db->query("
            SELECT  SUM(length) AS total_length
            FROM    videos
        ")->fetch()['total_length'];
    }
    
    /**
     * {@inheritdoc}
     * @Override
     */
    public function wasWatched(int $idStudent, int $idModule, int $classOrder) : bool
    {
        $sql = $this->buildWasWatchedQuery();
        $this->runQueryWithArguments($sql, $idStudent, $idModule, $classOrder);
        
        return $this->parseWasWatchedQueryResponse($sql);
    }

    private function buildWasWatchedQuery()
    {
        return $this->db->prepare("
            SELECT  COUNT(*) AS was_watched
            FROM    student_historic
            WHERE   class_type = 0 AND 
                    id_student = ? AND 
                    id_module = ? AND 
                    class_order = ?
        ");
    }

    private function parseWasWatchedQueryResponse($sql)
    {
        return ($sql->fetch()['was_watched'] > 0);
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
        return $this->_MarkAsWatched($idStudent, $idModule, $classOrder,
            new ClassTypeEnum(ClassTypeEnum::VIDEO));
    }
}