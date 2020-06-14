<?php
namespace models;

use core\Model;


/**
 *
 */
class Classes extends Model
{
    //-----------------------------------------------------------------------
    //        Constructor
    //-----------------------------------------------------------------------
    public function __construct()
    {
        parent::__construct();
    }
    
    
    //-----------------------------------------------------------------------
    //        Methods
    //-----------------------------------------------------------------------
    public function countClasses($id_course)
    {
        if (empty($id_course) || $id_course <= 0) { return 0; }
        
        $sql = $this->db->query("SELECT COUNT(*) as count FROM classes WHERE id_course = $id_course");
        
        return $sql->fetch()['count'];
    }
    
    public function getClassesFromModule($id_module)
    {
        if (empty($id_module) || $id_module <= 0) { return array(); }
        
        $response = array();
       // $id_student = $_SESSION['s_login'];
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
    
    public function getCourseId($id_class)
    {
        if (empty($id_class) || $id_class <= 0) { return -1; }

        $sql = $this->db->prepare("SELECT id_course FROM classes WHERE id = ?");
        $sql->execute(array($id_class));
        
        return $sql->fetch(\PDO::FETCH_ASSOC)['id_course'];
    }
    
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
    
    public function getClass($id_class, $id_student)
    {
        if (empty($id_class) || $id_class <= 0) { return -1; }
        
        $response = array();
        
        $sql = $this->db->prepare("
            SELECT 
                *,
                (select count(*) from historic where historic.id_class = classes.id and historic.id_student = $id_student) as watched
            FROM classes 
            WHERE id = ?
        ");
        $sql->execute(array($id_class));
        
        if ($sql->rowCount() > 0) {
            $response = $sql->fetch(\PDO::FETCH_ASSOC);
            
            if ($response['type'] == 'video') {
                $videos = new Videos();
                $response['video'] = $videos->getVideoFromClass($id_class);
            } else {
                $quests = new Questionnaires();
                $response['quest'] = $quests->getQuestFromClass($id_class);
            }
        }
        
        return $response; 
        
    }
    
    public function exist($id_class)
    {
        if (empty($id_class) || $id_class <= 0) { return false; }
        
        $sql = $this->db->prepare("SELECT COUNT(*) AS count FROM classes WHERE id = ?");
        $sql->execute(array($id_class));
        
        return $sql->fetch()['count'] > 0;
    }
    
    public function markAsWatched($id_student,$id_class)
    {
        if (empty($id_class) || $id_class <= 0) { return; }
        
        if ($this->alreadyMarkedAsWatched($id_student,$id_class)) { return; }
        
        $sql = $this->db->prepare("INSERT INTO historic (id_student,id_class,date_watched) VALUES (?,?,NOW())");
        $sql->execute(array($id_student,$id_class));   
    }
    
    public function removeWatched($id_student,$id_class)
    {
        if (empty($id_class) || $id_class <= 0) { return; }
        
        $sql = $this->db->prepare("DELETE FROM historic WHERE id_student = ? AND id_class = ?");
        $sql->execute(array($id_student,$id_class));
    }
    
    public function getClassesInCourse($id_course)
    {
        if (empty($id_course) || $id_course <= 0) { return; }
        
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
    
    private function getLastOrder($id_module, $id_course)
    {
        $response = 0;
        
        $sql = $this->db->prepare("SELECT classes.order FROM classes WHERE id_module = ? AND id_course = ? ORDER BY classes.order DESC LIMIT 1");
        $sql->execute(array($id_module, $id_course));
        
        if ($sql->rowCount() > 0) {
            $response = $sql->fetch()['order'];
        }

        return $response;
    }
    
    private function alreadyMarkedAsWatched($id_class, $id_student)
    {
        if (empty($id_class) || $id_class <= 0)     { return true; }
        if (empty($id_student) || $id_student <= 0) { return true; }
        
        $sql = $this->db->prepare("SELECT COUNT(*) as count FROM historic WHERE id_student = ? AND id_class = ?");
        $sql->execute(array($id_student,$id_class));
        
        return $sql->fetch()["count"] > 0;
    }
}