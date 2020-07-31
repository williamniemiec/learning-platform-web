<?php
declare (strict_types=1);

namespace models\dao;


use database\Database;
use models\Admin;
use models\Authorization;
use models\enum\GenreEnum;
use models\util\IllegalAccessException;


/**
 * Responsible for managing 'admins' table.
 * 
 * @author		William Niemiec &lt; williamniemiec@hotmail.com &gt;
 * @version		1.0.0
 * @since		1.0.0
 */
class AdminsDAO
{
    //-------------------------------------------------------------------------
    //        Attributes
    //-------------------------------------------------------------------------
    private $db;
    private $id_admin;
    
    
    //-------------------------------------------------------------------------
    //        Constructor
    //-------------------------------------------------------------------------
    /**
     * Creates 'admins' table manager.
     *
     * @param       mixed $db Database
     * @param       int $id_user [Optional] Admin id
     *
     * @apiNote     It will connect to the database when it is instantiated
     */
    public function __construct(Database $db, int $id_admin = -1)
    {
        $this->db = $db->getConnection();
        $this->id_admin = $id_admin;
    }
    
    
    //-------------------------------------------------------------------------
    //        Methods
    //-------------------------------------------------------------------------
    /**
     * Checks whether an admin is logged.
     *
     * @return      bool If admin is logged or not
     */
    public static function isLogged() : bool
    {
        return !empty($_SESSION['a_login']);
    }
    
    /**
     * Checks whether the supplied credentials are valid.
     *
     * @param       string $email Student email
     * @param       string $pass Student password
     *
     * @return      bool If credentials are correct
     * 
     * @throws      \InvalidArgumentException If any argument is invalid 
     */
    public function login(string $email, string $pass) : bool
    {
        if (empty($email))
            throw new \InvalidArgumentException("Email cannot be empty");
            
        if (empty($pass))
            throw new \InvalidArgumentException("Password cannot be empty");
        
        $response = false;
        
        // Query construction
        $sql = $this->db->prepare("
            SELECT  id_admin 
            FROM    admins 
            WHERE   email = ? AND password = ?
        ");
        
        // Executes query
        $sql->execute(array($email, md5($pass)));
        
        // Parses results
        if ($sql && $sql->rowCount() > 0) {
            $_SESSION['a_login'] = $sql->fetch()['id'];
            $this->id_admin = $sql->fetch()['id'];
            $response = true;
        }
        
        return $response;
    }
    
    /**
     * Adds a new admin.
     *
     * @param       Admin $admin Informations about the admin to be added
     * @param       string $password Admin password
     *
     * @return      int Admin id or -1 if the admin has not been added
     *
     * @throws      IllegalAccessException If current admin does not have root
     * authorization
     * @throws      \InvalidArgumentException If admin or password are empty
     */
    public function new(Admin $admin, string $password)
    {
        if ($this->getAuthorization()->getLevel() != 0)
            throw new IllegalAccessException("Current admin does not have root authorization");
        
        if (empty($admin))
            throw new \InvalidArgumentException("Admin cannot be empty");
            
        if (empty($password))
            throw new \InvalidArgumentException("Password cannot be empty");

        $response = -1;
            
        // Query construction
        $sql = $this->db->prepare("
            INSERT INTO admins
            (id_authorization, name, genre, birthdate, email, password)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        // Executes query
        $sql->execute(array(
            $admin->getName(),
            $admin->getGenre(),
            $admin->getBirthdate(),
            $admin->getEmail(),
            md5($password)
        ));
        
        if ($sql && $sql->rowCount() > 0)
            $response = $this->db->lastInsertId();
        
        return $response;
    }
    
    /**
     * Edits an admin.
     * 
     * @param       int $id_admin Admin id to be edited
     * @param       int $id_authorization New authorization
     * @param       string $email New email
     * @param       string $password [Optional] New password
     * 
     * @return      bool If admin was successfully edited
     * 
     * @throws      IllegalAccessException If current admin does not have root
     * authorization
     * @throws      \InvalidArgumentException If any argument is invalid
     */
    public function edit(int $id_admin, int $id_authorization, string $email,
        string $password='') : bool
    {
        if ($this->getAuthorization()->getLevel() != 0)
            throw new IllegalAccessException("Admin does not have root authorization");
        
        if (empty($this->id_admin) || $this->id_admin <= 0)
            throw new \InvalidArgumentException("Invalid admin id");
            
        if (empty($id_admin)  || ($id_admin <= 0))
            throw new \InvalidArgumentException("Invalid admin id");
            
        if (empty($id_authorization) || ($id_authorization <= 0))
            throw new \InvalidArgumentException("Invalid authorization id");
            
        if (empty($email))
            throw new \InvalidArgumentException("Email cannot be empty");
            
        if (!empty($password))
            $this->changePassword($id_admin, $password);
        
        // Query construction
        $sql = $this->db->prepare("
            UPDATE  admins
            (id_authorization, email)
            VALUES (?, ?)
            WHERE id_admin = ?
        ");

        // Executes query
        $sql->execute(array($id_authorization, $email, $id_admin));

        return $sql && $sql->rowCount() > 0;
    }

    /**
     * Removes an admin.
     * 
     * @param       int $id_admin Admin to be removed
     * 
     * @return      bool If admin was successfully removed
     * 
     * @throws      IllegalAccessException If current admin does not have root
     * authorization
     * @throws      \InvalidArgumentException If admin id is invalid
     */
    public function remove(int $id_admin) : bool
    {
        if ($this->getAuthorization()->getLevel() != 0)
            throw new IllegalAccessException("Admin does not have root authorization");
        
        if (empty($id_admin)  || ($id_admin <= 0))
            throw new \InvalidArgumentException("Invalid admin id");
        
        // Query construction
        $sql = $this->db->prepare("
            DELETE FROM admins
            WHERE id_admin = ?
        ");
        
        // Executes query
        $sql->execute(array($id_admin));
        
        return $sql && $sql->rowCount() > 0;
    }

    /**
     * Updates current admin information.
     *
     * @param       string $name New name
     * @param       GenreEnum $genre New genre
     * @param       string $birthdate New birthdate
     *
     * @return      bool If student information was successfully updated
     *
     * @throws      \InvalidArgumentException If any argument is invalid
     */
    public function update(string $name, GenreEnum $genre, string $birthdate) : bool
    {
        if (empty($this->id_admin) || $this->id_admin <= 0)
            throw new \InvalidArgumentException("Invalid admin id");
            
        if (empty($name))
            throw new \InvalidArgumentException("Name cannot be empty");
            
        if (empty($genre) || ($genre != 0 && $genre != 1))
            throw new \InvalidArgumentException("Invalid genre - must be 0 or 1");
            
        if (empty($birthdate))
            throw new \InvalidArgumentException("Birthdate cannot be empty");
        
        // Query construction
        $sql = $this->db->prepare("
            UPDATE  admins
            (id_authorization, name, genre, birthdate)
            VALUES (?, ?, ?, ?)
            WHERE id_admin = ?
        ");

        // Executes query
        $sql->execute(array(
            $name, 
            $genre->get(), 
            $birthdate,
            $this->id_admin
        ));
        
        return $sql && $sql->rowCount() > 0;
    }

    /**
     * Deletes current admin.
     *
     * @return      bool If admin was successfully deleted
     *
     * @throws      \InvalidArgumentException If admin id is invalid
     */
    public function delete() : bool
    {
        if (empty($this->id_admin) || $this->id_admin <= 0)
            throw new \InvalidArgumentException("Invalid admin id");
            
        // Query construction
        $sql = $this->db->query("
            DELETE FROM admins
            WHERE id_admin = ?
        ");
            
        // Executes query
        $sql->execute(array($this->id_admin));
        
        return $sql && $sql->rowCount() > 0;
    }
    
    /**
     * Changes admin password.
     * 
     * @param       int $id_admin Admin id to be updated
     * @param       string $newPassword New password
     * 
     * @return      bool If password was successfully updated
     * 
     * @throws      \InvalidArgumentException If admin id is invalid or 
     * password is empty
     */
    public function changePassword(int $id_admin, string $newPassword) : bool
    {
        if (empty($this->id_admin) || $this->id_admin <= 0)
            throw new \InvalidArgumentException("Invalid admin id");
            
        if (empty($newPassword))
            throw new \InvalidArgumentException("Password cannot be empty");
            
        // Query construction
        $sql = $this->db->prepare("
            UPDATE  admins
            SET     password = ?
            WHERE   id_admin = ?
        ");

        // Executes query
        $sql->execute(array(md5($newPassword), $id_admin));
        
        return $sql && $sql->rowCount() > 0;
    }

    /**
     * Gets admin authorization.
     * 
     * @return      \models\Authorization Admin authorization
     */
    public function getAuthorization() : Authorization
    {
        $authorization = new Authorization();
        
        return $authorization->getAuthorization($this->id_admin);
    }
    
    /**
     * Gets information about an admin.
     * 
     * @param       int $id_admin Admin id
     * 
     * @return      Admin Admin with the given id or null if there us no admin
     * with the given id
     * 
     * @throws      \InvalidArgumentException If admin id is invalid
     */
    public function get($id_admin) : Admin
    {
        if (empty($id_admin) || $id_admin <= 0)
            throw new \InvalidArgumentException("Invalid admin id");
        
        $response = NULL;
        $sql = $this->db->setAttribute(\PDO::ATTR_FETCH_TABLE_NAMES, true);
        
        // Query construction
        $sql = $this->db->prepare("
            SELECT  *
            FROM    admins JOIN authorization USING (id_authorization)
            WHERE   id_admin = ?
        ");
        
        // Executes query
        $sql->execute(array($id_admin));
        
        // Parses result
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