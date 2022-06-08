<?php
declare (strict_types=1);

namespace dao;


use repositories\Database;
use domain\Admin;
use domain\Authorization;
use domain\Action;
use domain\enum\GenreEnum;
use util\IllegalAccessException;


/**
 * Responsible for managing 'admins' table.
 */
class AdminsDAO extends DAO
{
    //-------------------------------------------------------------------------
    //        Attributes
    //-------------------------------------------------------------------------
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
        parent::__construct($db);
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
        $this->validateEmail($email);
        $this->validatePassword($pass);

        $response = null;
        
        // Query construction
        $this->withQuery("
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

    private function validateEmail($email)
    {
        if (empty($email)) {
            throw new \InvalidArgumentException("Email cannot be empty");
        }
    }

    private function validatePassword($password)
    {
        if (empty($password)) {
            throw new \InvalidArgumentException("Password cannot be empty");
        }
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
        $this->validateLoggedAdmin();
        $this->validateAuthorization(0);
        $this->validateAdmin($admin);
        $this->validatePassword($password);

        $response = -1;
            
        // Query construction
        $this->withQuery("
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
        
        if ($sql && $sql->rowCount() > 0) {
            $response = $this->db->lastInsertId();
            $action = new Action();
            $action->addAdmin($response);
            $this->newAction($action);
        }
        
        return $response;
    }

    private function validateAdmin($admin)
    {
        if (empty($admin)) {
            throw new \InvalidArgumentException("Admin cannot be empty");
        }
    }
    
    /**
     * Updates an admin.
     * 
     * @param       int $idAdmin Admin id to be edited
     * @param       int newAuthorization New authorization
     * @param       string $newEmail New email
     * @param       string $newPassword [Optional] New password
     * 
     * @return      bool If admin has been successfully edited
     * 
     * @throws      IllegalAccessException If current admin does not have 
     * authorization to update admins
     * @throws      \InvalidArgumentException If any argument is invalid
     */
    public function updateAdmin(int $idAdmin, int $newAuthorization, string $newEmail,
        string $newPassword='') : bool
    {
        $this->validateLoggedAdmin();
        $this->validateAuthorization(0);
        $this->validateAdminId($idAdmin);
        $this->validateAuthorization($newAuthorization);
        $this->validateEmail($newEmail);
            
        if (!empty($newPassword))
            $this->changePassword($idAdmin, $newPassword);
        
        $admin = $this->get($idAdmin);
        $bindParams = array($newAuthorization);
        $response = false;
        
        // Query construction
        if ($admin->getEmail() == $newEmail) {
            $query = "
                UPDATE  admins
                SET     id_authorization = ?
                WHERE   id_admin = ?
            ";
        }
        else {
            $query = "
                UPDATE  admins
                SET     id_authorization = ?,
                        email = ?
                WHERE id_admin = ?
            ";
            $bindParams[] = $newEmail;
        }
        
        $bindParams[] = $idAdmin;
        $this->withQuery($query);
        
        // Executes query
        $sql->execute($bindParams);
        
        if (!empty($sql) && $sql->rowCount() > 0) {
            $action = new Action();
            $action->updateAdmin($idAdmin);
            $this->newAction($action);
            $response = true;
        }
        
        return $response;
    }

    private function validateAdminId($id)
    {
        if (empty($id) || $id <= 0) {
            throw new \InvalidArgumentException("Admin id cannot be empty or ".
                                                "less than or equal to zero");
        }
    }

    private function validateAuthorization($value)
    {
        if (empty($value) || $value <= 0) {
            throw new \InvalidArgumentException("Authorization cannot be empty ".
                                                "or less than or equal to zero");
        }
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
        $this->validateLoggedAdmin();
        $this->validateName($name);
        $this->validateGenre($genre);
        $this->validateBirthdate($birthdate);

        $response = false;
            
        // Query construction
        $this->withQuery("
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
        
        return !empty($sql) && $sql->rowCount() > 0;
    }

    private function validateName($name)
    {
        if (empty($name)) {
            throw new \InvalidArgumentException("Name cannot be empty");
        }
    }

    private function validateGenre($genre)
    {
        if (empty($genre) || (empty($genre->get()) && $genre->get() != 0)) {
            throw new \InvalidArgumentException("Genre cannot be empty");
        }
    }

    private function validateBirthdate($birthdate)
    {
        if (empty($birthdate)) {
            throw new \InvalidArgumentException("Birthdate cannot be empty");
        }
    }
    
    /**
     * Changes admin password.
     * 
     * @param       string $newPassword New password
     * @param       int idAdmin [Optional] Admin id to be updated. If it is
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
    public function changePassword(string $newPassword, int $idAdmin = -1) : bool
    {
        if ($idAdmin <= 0) {
            $this->validateLoggedAdmin();
        }

        if ($idAdmin != $this->admin->getId()) {
            $this->validateAuthorization(0);
        }
            
        $this->validatePassword($newPassword);
        
        if ($idAdmin <= 0)
            $idAdmin = $this->admin->getId();
            
        $response = false;
            
        // Query construction
        $this->withQuery("
            UPDATE  admins
            SET     password = ?
            WHERE   id_admin = ?
        ");

        // Executes query
        $sql->execute(array(md5($newPassword), $idAdmin));
        
        if (!empty($sql) && $sql->rowCount() > 0) {
            $response = true;
            
            if ($idAdmin > 0) {
                $action = new Action();
                $action->updateAdmin($idAdmin);
                $this->newAction($action);
            }
        }
        
        return $response;
    }
    
    /**
     * Register an action.
     * 
     * @param       Action $action Action to be registered
     * 
     * @throws      \InvalidArgumentException If action is null or no action 
     * has been selected
     */
    public function newAction(Action $action)
    {
        $this->validateAction($action);
        
        $this->withQuery("
            INSERT INTO actions
            (id_admin, date, description)
            VALUES (?, NOW(), ?)
        ");
        
        $sql->execute(array($this->admin->getId(), $action->get()));
    }

    private function validateAction($action)
    {
        if (empty($action)) {
            throw new \InvalidArgumentException("Action cannot be empty");
        }
    }
    
    /**
     * Gets information about an admin or about logged in admin.
     * 
     * @param       int $idAdmin [Optional] Admin id
     * 
     * @return      Admin Admin with the given id or null if there is no admin
     * with the given id
     * 
     * @throws      \InvalidArgumentException If admin id is empty, less than
     * or equal to zero and admin provided in the constructor is empty
     */
    public function get(int $idAdmin = -1) : ?Admin
    {
        if ($idAdmin <= 0) {
            $this->validateLoggedAdmin();
        }
            
        $response = null;
        
        if ($idAdmin <= 0)
            $idAdmin = $this->admin->getId();
        
        // Query construction
        $sql = $this->db->query("
            SELECT  *, 
                    admins.name AS name_admin, 
                    authorization.name AS name_authorization
            FROM    admins JOIN authorization USING (id_authorization)
            WHERE   id_admin = ".$idAdmin 
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