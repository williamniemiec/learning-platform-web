<?php
namespace models;

use core\Model;
use models\obj\Admin;
use models\obj\Authorization;


/**
 * Responsible for managing admins table.
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
    private $id_admin;
    
    
    //-------------------------------------------------------------------------
    //        Constructor
    //-------------------------------------------------------------------------
    /**
     * Creates admins table manager.
     *
     * @param       int $id_user [Optional] Student id
     *
     * @apiNote     It will connect to the database when it is instantiated
     */
    public function __construct($id_admin = -1)
    {
        parent::__construct();
        $this->id_admin = $id_admin;
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
        return !empty($_SESSION['s_login']);
    }
    
    /**
     * Checks whether the supplied credentials are valid.
     *
     * @param       string $email Student email
     * @param       string $pass Student password
     *
     * @return      boolean If credentials are correct
     */
    public function login($email, $pass)
    {
        if (empty($email) || empty($pass))
            return false;
        
        $response = false;
        
        
        $sql = $this->db->prepare("
            SELECT id 
            FROM admins 
            WHERE email = ? AND password = ?
        ");
        $sql->execute(array($email, md5($pass)));
        
        if ($sql->rowCount() > 0) {
            $_SESSION['a_login'] = $sql->fetch()['id'];
            $this->id_admin = $sql->fetch()['id'];
            $response = true;
        }
        
        return $response;
    }
    
    public function new($id_authorization, $name, $genre, $birthdate, $email, $password)
    {
        if ($this->getAuthorization()->getLevel() != 0) {
            throw new IllegalAccessException("Admin does not have root authorization");
        }

        $sql = $this->db->prepare("
            INSERT INTO admins
            (id_authorization, name, genre, birthdate, email, password)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        $sql->execute(array($id_authorization, $name, $genre, $birthdate, $email, md5($password)));
        
        return $sql->rowCount() > 0;
    }
    
    public function edit($id_admin, $newId_authorization, $newEmail, $newPassword='')
    {
        if ($this->getAuthorization()->getLevel() != 0) {
            throw new IllegalAccessException("Admin does not have root authorization");
        }

        if (!empty($newPassword)) {
            $this->changePassword($id_admin, $newPassword);
        }
        $sql = $this->db->prepare("
            UPDATE  admins
            (id_authorization, email)
            VALUES (?, ?)
            WHERE id_admin = ?
        ");

        $sql->execute(array($newId_authorization, $newEmail, $id_admin));

        return $sql->rowCount() > 0;
    }

    public function remove($id_admin)
    {
        if ($this->getAuthorization()->getLevel() != 0) {
            throw new IllegalAccessException("Admin does not have root authorization");
        }
    }

    public function editMyself($id_admin, $newName, $newGenre, $newBirthdate)
    {
        $sql = $this->db->prepare("
            UPDATE  admins
            (id_authorization, name, genre, birthdate)
            VALUES (?, ?, ?, ?)
            WHERE id_admin = ?
        ");

        $sql->execute(array($id_authorization, $name, $genre, $birthdate, $id_admin));
    }

    public function changePassword($id_admin, $newPassword)
    {
        $sql = $this->db->prepare("
            UPDATE  admins
            SET     password = ?
            WHERE   id_admin = ?
        ");

        $sql->execute(array(md5($newPassword), $id_admin));
    }

    /**
     * Gets admin authorization.
     * 
     * @return      \models\Authorization Admin authorization
     */
    public function getAuthorization()
    {
        $authorization = new Authorization();
        
        return $authorization->getAuthorization($this->id_admin);
    }
    
    public function get($id)
    {
        $response = NULL;
        $sql = $this->db->setAttribute(\PDO::ATTR_FETCH_TABLE_NAMES, true);
        $sql = $this->db->prepare("
            SELECT  *
            FROM    admins JOIN authorization USING (id_authorization)
            WHERE   id_admin = ?
        ");
        
        $sql->execute(array($id));
        
        if ($sql->rowCount() > 0) {
            $admin = $sql->fetch(\PDO::FETCH_ASSOC);
            $response = new Admin(
                $admin['admins.id_admin'], 
                new Authorization($admin['authorization.name'], $admin['authorization.level']), 
                $admin['admins.name'], 
                $admin['admins.genre'], 
                $admin['admins.birthdate'], 
                $admin['admins.email']
            );
        }
        
        return $response;
    }
}