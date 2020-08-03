<?php 
declare (strict_types=1);

namespace models;


use DateTime;
use models\enum\GenreEnum;

/**
 * Responsible for representing admin-type users.
 *
 * @author		William Niemiec &lt; williamniemiec@hotmail.com &gt;
 * @version		1.0.0
 * @since		1.0.0
 */
class Admin extends User
{
    //-------------------------------------------------------------------------
    //        Attributes
    //-------------------------------------------------------------------------
    private $id_authorization;
    
    
    //-------------------------------------------------------------------------
    //        Constructor
    //-------------------------------------------------------------------------
    /**
     * Creates a representation of a admin-type user.
     * 
     * @param       int $id Administrator id
     * @param       Authorization $authorization Authorization that the
     * administrator has
     * @param       string $name Administrator name
     * @param       GenreEnum $genre Administrator genre
     * @param       DateTime $birthdate Administrator birthdate
     * @param       string $email Administrator email
     */
    public function __construct(int $id, Authorization $authorization, string $name, 
        GenreEnum $genre, DateTime $birthdate, string $email)
    {
        $this->id = $id;
        $this->authorization = $authorization;
        $this->name = $name;
        $this->genre = $genre;
        $this->birthdate = $birthdate;
        $this->email = $email;
    }
    
    
    //-------------------------------------------------------------------------
    //        Getters
    //-------------------------------------------------------------------------
    /**
     * Gets authorization that the administrator has.
     * 
     * @return      Authorization Authorization that the administrator has
     */
    public function getAuthorization() : Authorization
    {
        return $this->authorization;
    }
}