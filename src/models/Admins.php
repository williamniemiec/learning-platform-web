<?php
namespace models;

use core\Model;


/**
 *
 */
class Admins extends Model
{
    //-----------------------------------------------------------------------
    //        Attributes
    //-----------------------------------------------------------------------
    private $id_user;
    
    
    //-----------------------------------------------------------------------
    //        Constructor
    //-----------------------------------------------------------------------
    public function __construct($id_user = -1)
    {
        parent::__construct();
        $this->id_user = $id_user;
    }
    
    
    //-----------------------------------------------------------------------
    //        Methods
    //-----------------------------------------------------------------------
    public static function isLogged()
    {
        if (empty($_SESSION['s_login'])) {
            return false;
        }
        
        return true;
    }
    
    public function login($email, $pass)
    {
        if (empty($email) || empty($pass)) { return false; }
        
        $sql = $this->db->prepare("SELECT id FROM admins WHERE email = ? AND password = ?");
        $sql->execute(array($email, md5($pass)));
        
        if ($sql->rowCount() == 0) { return false; }
        
        $_SESSION['a_login'] = $sql->fetch()['id'];
        $this->id_user = $sql->fetch()['id'];
        
        return true;
    }
}