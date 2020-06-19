<?php
namespace models;

use core\Model;


/**
 * Responsible for managing classes.
 * 
 * @author		William Niemiec &lt; williamniemiec@hotmail.com &gt;
 * @version		1.0
 * @since		1.0
 */
class Classes extends Model
{
    //-------------------------------------------------------------------------
    //        Constructor
    //-------------------------------------------------------------------------
    /**
     * Creates classes manager.
     *
     * @apiNote     It will connect to the database when it is instantiated
     */
    public function __construct()
    {
        parent::__construct();
    }
    
    
    //-------------------------------------------------------------------------
    //        Methods
    //-------------------------------------------------------------------------
    /**
     * Gets total of classes from a course.
     * 
     * @param       int $id_course Course id
     * 
     * @return      int Total course classes
     */
    public function countClasses($id_course)
    {
        if (empty($id_course) || $id_course <= 0) { return 0; }
        
        $sql = $this->db->query("SELECT COUNT(*) as count FROM classes WHERE id_course = $id_course");
        
        return $sql->fetch()['count'];
    }
    
    /**
     * Gets informations about all classes from a module.
     * 
     * @param       int $id_module Module id
     * 
     * @return      array Informations about all classes from the module
     */
    public function getClassesFromModule($id_module)
    {
        if (empty($id_module) || $id_module <= 0) { return array(); }
        
        $response = array();
        
        $sql = $this->db->prepare("
            SELECT 
                * 
            FROM classes 
            WHERE id_module = ? 
            ORDER BY classes.order
        ");
        
        $sql->execute(array($id_module));
        
        if ($sql->rowCount() > 0) {
            $response = $sql->fetchAll(\PDO::FETCH_ASSOC);
            
            foreach ($response as $key => $value) {
                if ($value['type'] == 'video') {
                    $videos = new Videos();
                    $response[$key]['video'] = $videos->getVideoFromClass($value['id']); 
                } else if ($value['type'] == 'quest') {
                    $quests = new Questionnaires();
                    $response[$key]['quest'] = $quests->getQuestFromClass($value['id']);
                }
            }
        }
        
        return $response;
    }
    
    /**
     * Gets course id from a class.
     * 
     * @param       int $id_class
     * 
     * @return      number Course id of -1 if class id is invalid
     */
    public function getCourseId($id_class)
    {
        if (empty($id_class) || $id_class <= 0) { return -1; }

        $sql = $this->db->prepare("SELECT id_course FROM classes WHERE id = ?");
        $sql->execute(array($id_class));
        
        return $sql->fetch(\PDO::FETCH_ASSOC)['id_course'];
    }
    
    /**
     * Gets information about the first class from the first module from a
     * course.
     * 
     * @param       int $id_course Course id
     * 
     * @return      array Informations about the first class from the first
     * module from a course
     */
    public function getFirstClassFromFirstModule($id_course)
    {
        $response = array();
        
        $sql = $this->db->prepare("
            SELECT * 
            FROM classes 
            WHERE 
                classes.order = 1 AND 
                id_course = ? 
            ORDER BY id_module ASC 
            LIMIT 1
        ");
        
        $sql->execute(array($id_course));
        
        if ($sql->rowCount() > 0) {
            $response = $sql->fetch(\PDO::FETCH_ASSOC);
            
            if ($response['type'] == 'video') {
                $videos = new Videos();
                $response['video'] = $videos->getVideoFromClass($response['id']);
            } else {
                $quests = new Questionnaires();
                $response['quest'] = $quests->getQuestFromClass($response['id']);
            }
        }
        
        return $response;
    }
    
    /**
     * Checks whether there is a class with the given id.
     * 
     * @param       int $id_class Class id
     * 
     * @return      boolean If there is a class with the given id
     */
    public function exist($id_class)
    {
        if (empty($id_class) || $id_class <= 0) { return false; }
        
        $sql = $this->db->prepare("SELECT COUNT(*) AS count FROM classes WHERE id = ?");
        $sql->execute(array($id_class));
        
        return $sql->fetch()['count'] > 0;
    }
    
    /**
     * Gets all classes from a course.
     * 
     * @param       int $id_course Course id
     * 
     * @return      array Informations about all classes from a course
     */
    public function getClassesInCourse($id_course)
    {
        if (empty($id_course) || $id_course <= 0) { return array(); }
        
        $response = array();
        
        $sql = $this->db->prepare("SELECT id FROM classes WHERE id_course = ?");
        $sql->execute(array($id_course));
        
        if ($sql->rowCount() > 0) {
            foreach ($sql->fetchAll() as $class) {
                $response[] = $class['id'];
            }
        }
        
        return $response;
    }
    
    /**
     * Deletes a class from a course.
     * 
     * @param       int $id_class Class id
     */
    public function delete($id_class)
    {
        if (empty($id_class) || $id_class <= 0) { return; }
        
        // Deletes class
        $sql = $this->db->prepare("DELETE FROM classes WHERE id = ?");
        $sql->execute(array($id_class));
        
        // Deletes historic
        $sql = $this->db->prepare("DELETE FROM historic WHERE id_class = ?");
        $sql->execute(array($id_class));
        
        // Deletes doubts
        $sql = $this->db->prepare("DELETE FROM doubts WHERE id_class = ?");
        $sql->execute(array($id_class));
        
        // Deletes videos
        $sql = $this->db->prepare("DELETE FROM videos WHERE id_class = ?");
        $sql->execute(array($id_class));
        
        // Deletes questionnaires
        $sql = $this->db->prepare("DELETE FROM questionnaries WHERE id_class = ?");
        $sql->execute(array($id_class));
    }
    
    /**
     * Adds a class to a course
     * 
     * @param       int $id_module Module id
     * @param       int $id_course Course id
     * @param       int $type Class type ('video' or 'quest')
     * @param       int $order [Optional] Class order within a course module
     * 
     * @return      int Id of added class or -1 if any argument is invalid
     */
    public function add($id_module, $id_course, $type, $order=0)
    {
        if (empty($id_module) || $id_module <= 0) { return -1; }
        if (empty($id_course) || $id_course <= 0) { return -1; }
        if (empty($type)) { return -1; }
        if ($order < 0) { return -1; }
        
        $response = -1;
        
        if ($order == 0) {
            $lastOrder = $this->getLastOrder($id_module, $id_course);
            $lastOrder == 0 ? 1 : $lastOrder;
            $lastOrder++;
        }
        
        $sql = $this->db->prepare("INSERT INTO classes (id_module, id_course, classes.order, classes.type) VALUES (?,?,?,?)");
        $sql->execute(array($id_module, $id_course, $lastOrder, $type));
        
        if ($sql->rowCount() > 0) {
            $response = $this->db->lastInsertId();
        }

        return $response;
    }
    
    /**
     * Gets class with higher order.
     * 
     * @param       int $id_module Module id
     * @param       int $id_course Course id
     * 
     * @return      int Class id with higher order or zero if there is no class
     * registered.
     */
    private function getLastOrder($id_module, $id_course)
    {
        $response = 0;
        
        $sql = $this->db->prepare("
            SELECT classes.order 
            FROM classes 
            WHERE id_module = ? AND id_course = ? 
            ORDER BY classes.order DESC 
            LIMIT 1
        ");
        
        $sql->execute(array($id_module, $id_course));
        
        if ($sql->rowCount() > 0) {
            $response = $sql->fetch()['order'];
        }

        return $response;
    }
}