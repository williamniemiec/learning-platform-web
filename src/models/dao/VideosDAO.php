<?php
declare (strict_types=1);

namespace models\dao;


use database\Database;
use models\enum\ClassTypeEnum;
use models\Video;


/**
 * Responsible for managing 'videos' table.
 * 
 * @author		William Niemiec &lt; williamniemiec@hotmail.com &gt;
 * @version		1.0.0
 * @since		1.0.0
 */
class VideosDAO
{
    //-------------------------------------------------------------------------
    //        Attributes
    //-------------------------------------------------------------------------
    private $db;
    
    
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
     * @param       int $id_module Module id that the class belongs to
     * @param       int $class_order Class order inside the module that it 
     * belongs to
     *
     * @return      Video Video class or null if class does not exist
     * 
     * @throws      \InvalidArgumentException If any argument is invalid 
     */
    public function get(int $id_module, int $class_order) : Video
    {
        if (empty($id_module) || $id_module <= 0)
            throw new \InvalidArgumentException("Invalid module id");
            
        if (empty($class_order) || $class_order <= 0)
            throw new \InvalidArgumentException("Invalid class order");
            
        $response = null;
        
        // Query construction
        $sql = $this->db->prepare("
            SELECT  * 
            FROM    videos 
            WHERE   id_module = ? AND class_order = ?
        ");
        
        // Executes query
        $sql->execute(array($id_module, $class_order));
        
        // Parses results
        if ($sql->rowCount() > 0) {
            $class = $sql->fetch(\PDO::FETCH_ASSOC);
            
            $response = new Video(
                $class['id_module'],
                $class['class_order'],
                $class['title'],
                $class['videoID'],
                $class['length'],
                $class['description']
            ); 
        }
        
        return $response; 
    }
    
    /**
     * Gets all video classes from a module.
     * 
     * @param       int $id_module Module id
     * 
     * @return      Video[] Classes that belongs to the module
     * 
     * @throws      \InvalidArgumentException If any argument is invalid
     * 
     * @Override
     */
    public function getAllFromModule(int $id_module) : array
    {
        $response = array();
        
        $sql = $this->db->prepare("
            SELECT  *
            FROM    videos
            WHERE   id_module = ?
        ");
        
        $sql->execute(array($id_module));
        
        if ($sql->rowCount() > 0) {
            $classes =  $sql->fetchAll(\PDO::FETCH_ASSOC);
            
            foreach ($classes as $class) {
                $response[] = new Video(
                    $class['id_module'], 
                    $class['class_order'], 
                    $class['title'], 
                    $class['videoID'], 
                    $class['length'],
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
     * Marks a class as watched by a student.
     * 
     * @param       int $id_student Student id
     * @param       int $id_module Module id
     * @param       int $class_order Class order
     * 
     * @throws      \InvalidArgumentException If any argument is invalid 
     * 
     * @Override
     */
    public function markAsWatched(int $id_student, int $id_module, int $class_order) : void
    {
        $this->markAsWatched($id_student, $id_module, $class_order,
            new ClassTypeEnum(ClassTypeEnum::VIDEO));
    }
}