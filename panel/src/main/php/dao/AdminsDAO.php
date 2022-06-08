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
        $this->withQuery("
            SELECT  *, 
                    admins.name AS name_admin, 
                    authorization.name AS name_authorization
            FROM    admins JOIN authorization USING (id_authorization) 
            WHERE   email = ? AND password = ?
        ");
        $this->runQueryWithArguments($email, md5($pass));
        
        return $this->parseGetResponseQuery();
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

    private function parseGetResponseQuery()
    {
        if (!$this->hasResponseQuery()) {
            return null;
        }

        $adminRaw = $this->getResponseQuery();
        
        return new Admin(
            (int) $adminRaw['id_admin'], 
            new Authorization(
                (int)$adminRaw['id_authorization'], 
                $adminRaw['name_authorization'], 
                (int)$adminRaw['level']
            ),
            $adminRaw['name_admin'],
            new GenreEnum((int) $adminRaw['genre']),
            new \DateTime($adminRaw['birthdate']),
            $adminRaw['email']
        );
    }
    
    /**
     * Adds a new admin.
     *
     * @param       Admin $admin Information about the admin to be added
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
        $this->withQuery("
            INSERT INTO admins
            (id_authorization, name, genre, birthdate, email, password)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $this->runQueryWithArguments(
            $admin->getAuthorization()->getId(),
            $admin->getName(),
            $admin->getGenre()->get() == 1,
            $admin->getBirthdate()->format("Y-m-d"),
            $admin->getEmail(),
            md5($password)
        );

        return $this->parseNewResponseQuery();
    }

    private function validateAdmin($admin)
    {
        if (empty($admin)) {
            throw new \InvalidArgumentException("Admin cannot be empty");
        }
    }

    private function parseNewResponseQuery()
    {
        if (!$this->hasResponseQuery()) {
            return -1;
        }

        $newId = $this->db->lastInsertId();
        $action = new Action();
        $action->addAdmin($newId);
        $adminsDao = new AdminsDAO($this->db, Admin::getLoggedIn($this->db));
        $adminsDao->newAction($action);
        
        return $newId;
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
    public function updateAdmin(
        int $idAdmin, 
        int $newAuthorization, 
        string $newEmail,
        string $newPassword=''
    ) : bool
    {
        $this->validateLoggedAdmin();
        $this->validateAuthorization(0);
        $this->validateAdminId($idAdmin);
        $this->validateAuthorization($newAuthorization);
        $this->validateEmail($newEmail);
            
        if (!empty($newPassword)) {
            $this->changePassword($newPassword, $idAdmin);
        }
        
        $this->withQuery($this->buildUpdateAdminQuery($idAdmin, $newEmail));
        $this->runQueryWithArguments($this->buildUpdateAdminQueryArguments(
            $idAdmin, 
            $newEmail, 
            $newAuthorization
        ));
        
        return $this->parseUpdateResponseQuery($idAdmin);
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

    private function buildUpdateAdminQuery($adminId, $newEmail)
    {
        $query = "";
        $targetAdmin = $this->get($adminId);
        
        if ($targetAdmin->getEmail() == $newEmail) {
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
        }
        
        return $query;
    }

    private function buildUpdateAdminQueryArguments($adminId, $newEmail, $newAuthorization)
    {
        $targetAdmin = $this->get($adminId);
        $bindParams = array($newAuthorization);

        if ($targetAdmin->getEmail() != $newEmail) {
            $bindParams[] = $newEmail;
        }
        
        $bindParams[] = $adminId;

        return $bindParams;
    }

    private function parseUpdateResponseQuery($adminId)
    {
        if (!$this->hasResponseQuery()) {
            return false;
        }

        $action = new Action();
        $action->addAdmin($adminId);
        $adminsDao = new AdminsDAO($this->db, Admin::getLoggedIn($this->db));
        $adminsDao->newAction($action);
        
        return true;
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
        $this->withQuery("
            UPDATE  admins
            SET     name = ?,
                    genre = ?,
                    birthdate = ?
            WHERE id_admin = ?
        ");
        $this->runQueryWithArguments(
            $name, 
            $genre->get() == 1, 
            $birthdate,
            $this->admin->getId()
        );
        
        return $this->hasResponseQuery();
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
        $id = $idAdmin;

        if ($id <= 0) {
            $this->validateLoggedAdmin();
            $id = $this->admin->getId();
        }

        if ($id != $this->admin->getId()) {
            $this->validateAuthorization(0);
        }
            
        $this->validatePassword($newPassword);
        $this->withQuery("
            UPDATE  admins
            SET     password = ?
            WHERE   id_admin = ?
        ");
        $this->runQueryWithArguments(md5($newPassword), $id);
        
        return $this->parseUpdateResponseQuery($id);
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
        $this->runQueryWithArguments($this->admin->getId(), $action->get());
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
        $id = $idAdmin;

        if ($id <= 0) {
            $this->validateLoggedAdmin();
            $id = $this->admin->getId();
        }
                
        $this->withQuery("
            SELECT  *, 
                    admins.name AS name_admin, 
                    authorization.name AS name_authorization
            FROM    admins JOIN authorization USING (id_authorization)
            WHERE   id_admin = ".$id 
        );
        $this->runQueryWithoutArguments();
        
        return $this->parseGetResponseQuery();
    }
    
    /**
     * Gets all registered admins (not include admin provided in the 
     * constructor).
     *
     * @return      Admin[] Admins
     */
    public function getAll() : array
    {
        $this->withQuery("
            SELECT  *,
                    admins.name AS name_admin,
                    authorization.name AS name_authorization
            FROM    admins JOIN authorization USING (id_authorization)
        ");
        $this->runQueryWithoutArguments();
        
        return $this->parseGetAllResponseQuery();
    }

    private function parseGetAllResponseQuery()
    {
        if (!$this->hasResponseQuery()) {
            return array();
        }

        $admins = array();
        
        foreach ($this->getAllResponseQuery() as $admin) {
            $admins[] = new Admin(
                (int) $admin['id_admin'],
                new Authorization(
                    (int) $admin['id_authorization'],
                    $admin['name_authorization'],
                    (int) $admin['level']
                ),
                $admin['name_admin'],
                new GenreEnum((int) $admin['genre']),
                new \DateTime($admin['birthdate']),
                $admin['email']
            );
        }

        return $admins;
    }
}