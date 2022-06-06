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
        $this->db = $db->getConnection();
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
        if (empty($idModule) || $idModule <= 0) {
            throw new \InvalidArgumentException("Module id cannot be empty ".
                "or less than or equal to zero");
        }
            
        if (empty($classOrder) || $classOrder <= 0) {
            throw new \InvalidArgumentException("Class order cannot be empty ".
                "or less than or equal to zero");
        }
            
        $response = null;
        
        // Query construction
        $sql = $this->db->prepare("
            SELECT  * 
            FROM    videos 
            WHERE   id_module = ? AND class_order = ?
        ");
        
        // Executes query
        $sql->execute(array($idModule, $classOrder));
        
        // Parses results
        if ($sql->rowCount() > 0) {
            $class = $sql->fetch();
            
            $response = new Video(
                (int) $class['id_module'],
                (int) $class['class_order'],
                $class['title'],
                $class['videoID'],
                (int) $class['length'],
                $class['description']
            ); 
        }
        
        return $response; 
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
        if (empty($idModule) || $idModule <= 0) {
            throw new \InvalidArgumentException("Module id cannot be empty ".
                "or less than or equal to zero");
        }
            
        $response = array();
        
        $sql = $this->db->prepare("
            SELECT  *
            FROM    videos
            WHERE   id_module = ?
        ");
        
        $sql->execute(array($idModule));
        
        if ($sql->rowCount() > 0) {
            $classes =  $sql->fetchAll();
            
            foreach ($classes as $class) {
                $response[] = new Video(
                    (int) $class['id_module'], 
                    (int) $class['class_order'], 
                    $class['title'], 
                    $class['videoID'], 
                    (int) $class['length'],
                    $class['description']
                );
            }
        }
        
        return $response;
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
        $sql = $this->db->prepare("
            SELECT  COUNT(*) AS was_watched
            FROM    student_historic
            WHERE   class_type = 0 AND 
                    id_student = ? AND 
                    id_module = ? AND 
                    class_order = ?
        ");
        
        $sql->execute(array($idStudent, $idModule, $classOrder));
        
        return $sql->fetch()['was_watched'] > 0;
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