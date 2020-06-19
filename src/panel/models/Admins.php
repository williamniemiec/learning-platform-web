<?php
namespace models;

use core\Model;


/**
 * Responsible for managing admins.
 * 
 * @author		William Niemiec &lt; williamniemiec@hotmail.com &gt;
 * @version		1.0
 * @since		1.0
 */
class Admins extends Model
{
    //-------------------------------------------------------------------------
    //        Attributes
    //-------------------------------------------------------------------------
    private $id_user;
    
    
    //-------------------------------------------------------------------------
    //        Constructor
    //-------------------------------------------------------------------------
    /**
     * Creates admins manager.
     * 
     * @param       int $id_user [Optional] Admin id
     * 
     * @apiNote     It will connect to the database when it is instantiated
     */
    public function __construct($id_user = -1)
    {
        parent::__construct();
        $this->id_user = $id_user;
    }
    
    
    //-------------------------------------------------------------------------
    //        Methods
    //-------------------------------------------------------------------------
    /**
     * Checks whether current user is logged.
     * 
     * @return      boolean Whether current user is logged
     */
    public static function isLogged()
    {
        return !empty($_SESSION['a_login']);
    }
    
    /**
     * Checks whether the supplied credentials are valid.
     * 
     * @param       string $email Admin email
     * @param       string $pass Admin password
     * 
     * @return      boolean If credentials are correct
     */
    public function login($email, $pass)
    {
        if (empty($email) || empty($pass))
            return false;
        
        $response = false;
        
        
        $sql = $this->db->prepare("SELECT id FROM admins WHERE email = ? AND password = ?");
        $sql->execute(array($email, md5($pass)));
        
        if ($sql->rowCount() >= 0) {
            $_SESSION['a_login'] = $sql->fetch()['id'];
            $this->id_user = $sql->fetch()['id'];
            $response = true;
        }
        
        return $response;
    }
    
    /**
     * Gets name from logged admin.
     * 
     * @return      string Admin name
     */
    public function getName()
    {
        $sql = $this->db->query("SELECT name FROM admins WHERE id = $this->id_user");
        
        return $sql->fetch()['name'];
    }
}