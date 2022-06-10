<?php
declare (strict_types=1);

namespace panel\domain;


use \DateTime;
use panel\domain\enum\GenreEnum;
use panel\dao\StudentsDAO;
use panel\repositories\Database;


/**
 * Responsible for representing student-type users.
 */
class Student extends User
{
    //-------------------------------------------------------------------------
    //        Attributes
    //-------------------------------------------------------------------------
    private $photo;
    private $bundles;
    private $db;
    

    //-------------------------------------------------------------------------
    //        Constructor
    //-------------------------------------------------------------------------
    /**
     * Creates a representation of a student-type user.
     *
     * @param       int $id Student id
     * @param       string $name Student name
     * @param       GenreEnum $genre Student genre
     * @param       DateTime $birthdate Student birthdate
     * @param       string $email Student email
     * @param       string $photo [Optional] Name of the student photo file
     */
    public function __construct(
        int $id, 
        string $name, 
        GenreEnum $genre, 
        DateTime $birthdate, 
        string $email, 
        ?string $photo = ''
    )
    {
        $this->id = $id;
        $this->name = $name;
        $this->genre = $genre;
        $this->birthdate = $birthdate;
        $this->email = $email;
        $this->photo = empty($photo) ? "" : $photo;
    }

    
    //-------------------------------------------------------------------------
    //        Methods
    //-------------------------------------------------------------------------
    /**
     * Checks whether a student is logged.
     *
     * @return      bool If student is logged
     */
    public static function isLogged() : bool
    {
        return !empty($_SESSION['s_login']);
    }

    
    //-------------------------------------------------------------------------
    //        Getters & Setters
    //-------------------------------------------------------------------------
    /**
     * Gets the name of the student photo file.
     * 
     * @return      string Name of the student photo file or empty string if
     * the student does not have a registered photo.
     */
    public function getPhoto() : string
    {
        return $this->photo;
    }
    
    /**
     * Gets bundles that the student has.
     *
     * @param       Database $db [Optional] Database to get all purchases from
     * the student
     *
     * @return      Bundle[] Bundles that the student has
     *
     * @throws      \InvalidArgumentException If bundles has not yet been
     * set and a database is not provided to obtain this information
     *
     * @implNote    Lazy initialization
     */
    public function getBundles(?Database $db = null)
    {
        if (empty($this->bundles)) {
            $this->bundles = $this->fetchBundles($db);
        }
        
        return $this->bundles;
    }

    private function fetchBundles($db)
    {
        if (empty($this->db) && empty($db)) {
            throw new \InvalidArgumentException("Database cannot be empty");
        }
        
        if (empty($db)) {
            $db = $this->db;       
        }

        $studentsDao = new StudentsDAO($db);
        
        return $studentsDao->getBundles($this->id);
    }
    
    public function setDatabase(Database $db)
    {
        $this->db = $db;
    }
    
    
    //-------------------------------------------------------------------------
    //        Serialization
    //-------------------------------------------------------------------------
    /**
     * {@inheritDoc}
     *  @see \JsonSerializable::jsonSerialize()
     *
     *  @Override
     */
    public function jsonSerialize(): array
    {
        $json = parent::jsonSerialize();
        $json['photo'] = $this->photo;
        
        return $json;
    }
}