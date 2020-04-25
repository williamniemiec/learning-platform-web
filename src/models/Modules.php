<?php
namespace models;

use core\Model;


/**
 *
 */
class Modules extends Model
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
    public function getModules($id_course)
    {
        if (empty($id_course) || $id_course <= 0) { return array(); }
        
        $response = array();
        
        $sql = $this->db->prepare("SELECT * FROM modules WHERE id_course = ?");
        $sql->execute(array($id_course));
        
        if ($sql->rowCount() > 0) {
            $response = $sql->fetchAll(\PDO::FETCH_ASSOC);
            
            $classes = new Classes();
            
            //foreach ($response as $key => $value) {
            for ($i=0; $i<count($response); $i++) {
                $response[$i]['classes'] = $classes->getClassesFromModule($response[$i]['id']);
            }
        }
        
        return $response;
    }
}