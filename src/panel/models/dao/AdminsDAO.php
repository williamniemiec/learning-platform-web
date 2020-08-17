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
    private $admin;
    
    
    //-------------------------------------------------------------------------
    //        Constructor
    //-------------------------------------------------------------------------
    /**
     * Creates 'admins' table manager.
     *
     * @param       Database $db Database
     * @param       Admin $admin [Optional] Admin logged in
     *
     * @apiNote     It will connect to the database when it is instantiated
     */
    public function __construct(Database $db, Admin $admin = null)
    {
        $this->db = $db->getConnection();
        $this->admin = $admin;
    }
    
    
    //-------------------------------------------------------------------------
    //        Methods
    //-------------------------------------------------------------------------
    /**
     * Checks whether the supplied credentials are valid.
     *
     * @param       string $email Admin email
     * @param       string $pass Admin password
     *
     * @return      Admin Information about admin logged in or null if 
     * credentials provided are incorrect
     * 
     * @throws      \InvalidArgumentException If email or password is empty
     */
    public function login(string $email, string $pass) : ?Admin
    {
        if (empty($email))
            throw new \InvalidArgumentException("Email cannot be empty");
            
        if (empty($pass))
            throw new \InvalidArgumentException("Password cannot be empty");
        
        $response = null;
        
        // Query construction
        $sql = $this->db->prepare("
            SELECT  *, 
                    admins.name AS name_admin, 
                    authorization.name AS name_authorization
            FROM    admins JOIN authorization USING (id_authorization) 
            WHERE   email = ? AND password = ?
        ");
        
        // Executes query
        $sql->execute(array($email, md5($pass)));
        
        // Parses results
        if ($sql && $sql->rowCount() > 0) {
            $admin = $sql->fetch();
            
            $response = new Admin(
                (int)$admin['id_admin'], 
                new Authorization(
                    (int)$admin['id_authorization'], 
                    $admin['name_authorization'], 
                    (int)$admin['level']
                ),
                $admin['name_admin'],
                new GenreEnum((int)$admin['genre']),
                new \DateTime($admin['birthdate']),
                $admin['email']
            );
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
     * @throws      IllegalAccessException If current admin does not have 
     * authorization to add new admins
     * @throws      \InvalidArgumentException If admin or password is empty or
     * if admin provided in the constructor is empty
     */
    public function new(Admin $admin, string $password)
    {
        if (empty($this->admin) || $this->admin->getId() <= 0)
            throw new \InvalidArgumentException("Admin logged in must be ".
                "provided in the constructor");
            
        if ($this->admin->getAuthorization()->getLevel() != 0)
            throw new IllegalAccessException("Current admin does not have ".
                "authorization to perform this action");
        
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
            $admin->getAuthorization()->getId(),
            $admin->getName(),
            $admin->getGenre()->get() == 1,
            $admin->getBirthdate()->format("Y-m-d"),
            $admin->getEmail(),
            md5($password)
        ));
        
        if ($sql && $sql->rowCount() > 0)
            $response = $this->db->lastInsertId();
        
        return $response;
    }
    
    /**
     * Updates an admin.
     * 
     * @param       int $id_admin Admin id to be edited
     * @param       int $newId_authorization New authorization
     * @param       string $newEmail New email
     * @param       string $newPassword [Optional] New password
     * 
     * @return      bool If admin has been successfully edited
     * 
     * @throws      IllegalAccessException If current admin does not have 
     * authorization to update admins
     * @throws      \InvalidArgumentException If any argument is invalid
     */
    public function updateAdmin(int $id_admin, int $newId_authorization, string $newEmail,
        string $newPassword='') : bool
    {
        if (empty($this->admin) || $this->admin->getId() <= 0)
            throw new \InvalidArgumentException("Admin logged in must be ".
                "provided in the constructor");
        
        if ($this->admin->getAuthorization()->getLevel() != 0)
            throw new IllegalAccessException("Current admin does not have ".
                "authorization to perform this action");
            
        if (empty($id_admin)  || ($id_admin <= 0))
            throw new \InvalidArgumentException("Admin id cannot be empty ".
                "or less than or equal to zero");
            
        if (empty($newId_authorization) || ($newId_authorization <= 0))
            throw new \InvalidArgumentException("Authorization id cannot be ".
                "empty or less than or equal to zero");
            
        if (empty($newEmail))
            throw new \InvalidArgumentException("Email cannot be empty");
            
        if (!empty($newPassword))
            $this->changePassword($id_admin, $newPassword);
        
        // Query construction
        $sql = $this->db->prepare("
            UPDATE  admins
            SET     id_authorization = ?,
                    email = ?
            WHERE id_admin = ?
        ");

        // Executes query
        $sql->execute(array($newId_authorization, $newEmail, $id_admin));

        return $sql && $sql->rowCount() > 0;
    }

    /**
     * Updates admin provided in the constructor.
     *
     * @param       string $name New name
     * @param       GenreEnum $genre New genre
     * @param       string $birthdate New birthdate
     *
     * @return      bool If admin has been successfully updated
     *
     * @throws      \InvalidArgumentException If any argument is empty or admin
     * provided in the constructor is empty
     */
    public function update(string $name, GenreEnum $genre, string $birthdate) : bool
    {
        if (empty($this->admin) || $this->admin->getId() <= 0)
            throw new \InvalidArgumentException("Admin logged in must be ".
                "provided in the constructor");
            
        if (empty($name))
            throw new \InvalidArgumentException("Name cannot be empty");
            
        if (empty($genre) || (empty($genre->get()) && $genre->get() != 0))
            throw new \InvalidArgumentException("Genre cannot be empty");
            
        if (empty($birthdate))
            throw new \InvalidArgumentException("Birthdate cannot be empty");
        
        // Query construction
        $sql = $this->db->prepare("
            UPDATE  admins
            SET     name = ?,
                    genre = ?,
                    birthdate = ?
            WHERE id_admin = ?
        ");

        // Executes query
        $sql->execute(array(
            $name, 
            $genre->get() == 1, 
            $birthdate,
            $this->admin->getId()
        ));
        
        return $sql && $sql->rowCount() > 0;
    }
    
    /**
     * Changes admin password.
     * 
     * @param       string $newPassword New password
     * @param       int $id_admin [Optional] Admin id to be updated. If it is
     * not provided, it will change admin provided in the constructor
     * 
     * @return      bool If password has been successfully updated
     * 
     * @throws      IllegalAccessException If current admin does not have 
     * authorization to update admins and he is not updating itself
     * @throws      \InvalidArgumentException If password is empty or admin 
     * provided in the constructor and admin id are empty 
     * empty
     */
    public function changePassword(string $newPassword, int $id_admin = -1) : bool
    {
        if ($id_admin <= 0 && (empty($this->admin) || $this->admin->getId() <= 0))
            throw new \InvalidArgumentException("Admin logged in must be ".
                "provided in the constructor");

        if ($this->admin->getAuthorization()->getLevel() != 0 && $id_admin != $this->admin->getId())
            throw new IllegalAccessException("Current admin does not have ".
                "authorization to perform this action");
            
        if (empty($newPassword))
            throw new \InvalidArgumentException("Password cannot be empty");
        
        if ($id_admin <= 0)
            $id_admin = $this->admin->getId();
            
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
     * Gets information about an admin or about logged in admin.
     * 
     * @param       int $id_admin [Optional] Admin id
     * 
     * @return      Admin Admin with the given id or null if there is no admin
     * with the given id
     * 
     * @throws      \InvalidArgumentException If admin id is empty, less than
     * or equal to zero and admin provided in the constructor is empty
     */
    public function get(int $id_admin = -1) : ?Admin
    {
        if ($id_admin <= 0 && (empty($this->admin) || $this->admin->getId() <= 0))
            throw new \InvalidArgumentException("Admin logged in must be ".
                "provided in the constructor");
            
        $response = null;
        
        if ($id_admin <= 0)
            $id_admin = $this->admin->getId();
        
        // Query construction
        $sql = $this->db->query("
            SELECT  *, 
                    admins.name AS name_admin, 
                    authorization.name AS name_authorization
            FROM    admins JOIN authorization USING (id_authorization)
            WHERE   id_admin = ".$id_admin 
        );
        
        // Parses result
        if ($sql->rowCount() > 0) {
            $admin = $sql->fetch();
            
            $response = new Admin(
                (int)$admin['id_admin'], 
                new Authorization(
                    (int)$admin['id_authorization'], 
                    $admin['name_authorization'], 
                    (int)$admin['level']
                ), 
                $admin['name_admin'], 
                new GenreEnum((int)$admin['genre']),
                new \DateTime($admin['birthdate']),
                $admin['email']
            );
        }
        
        return $response;
    }
    
    /**
     * Gets all registered admins (not include admin provided in the 
     * constructor).
     *
     * @return      Admin[] Admins
     */
    public function getAll() : array
    {
        $response = array();
            
        // Query construction
        $sql = $this->db->query("
            SELECT  *,
                    admins.name AS name_admin,
                    authorization.name AS name_authorization
            FROM    admins JOIN authorization USING (id_authorization)
        ");
            
        // Parses result
        if (!empty($sql) && $sql->rowCount() > 0) {
            foreach ($sql->fetchAll() as $admin) {
                $response[] = new Admin(
                    (int)$admin['id_admin'],
                    new Authorization(
                        (int)$admin['id_authorization'],
                        $admin['name_authorization'],
                        (int)$admin['level']
                    ),
                    $admin['name_admin'],
                    new GenreEnum((int)$admin['genre']),
                    new \DateTime($admin['birthdate']),
                    $admin['email']
                );
            }
        }
        
        return $response;
    }
}