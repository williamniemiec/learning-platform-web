<?php 
declare (strict_types=1);

namespace models;

use DateTime;
use models\enum\GenreEnum;


/**
 * Responsible for representing users. An user can be a student or an admin.
 * 
 * @author		William Niemiec &lt; williamniemiec@hotmail.com &gt;
 * @version		1.0.0
 * @since		1.0.0
 */
abstract class User
{
    //-------------------------------------------------------------------------
    //        Attributes
    //-------------------------------------------------------------------------
    protected $id;
    protected $name;
    protected $genre;
    protected $birthdate;
    protected $email;
    
    
    //-------------------------------------------------------------------------
    //        Getters
    //-------------------------------------------------------------------------
    /**
     * Gets user id.
     * 
     * @return      int User id
     */
    public function getId() : int
    {
        return $this->id;
    }
    
    /**
     * Gets user name.
     * 
     * @return      string name
     */
    public function getName() : string
    {
        return $this->name;
    }
    
    /**
     * Gets user genre
     * 
     * @return      GenreEnum User genre
     */
    public function getGenre() : GenreEnum
    {
        return $this->genre;
    }
    
    /**
     * Gets user birthdate.
     * 
     * @return      DateTime User birthdate
     */
    public function getBirthdate() : DateTime
    {
        return $this->birthdate;
    }
    
    /**
     * Gets user email.
     *
     * @return      string User email
     */
    public function getEmail() : string
    {
        return $this->email;
    }
}