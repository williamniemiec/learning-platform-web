<?php 
namespace models\obj;


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
     * @param       int $id_authorization Authorization id that the
     * administrator has
     * @param       string $name Administrator name
     * @param       int $genre Administrator genre
     * @param       string $birthdate Administrator birthdate
     * @param       string $email Administrator email
     */
    public function __construct($id, $id_authorization, $name, $genre, $birthdate, $email)
    {
        $this->id = $id;
        $this->id_authorization = $id_authorization;
        $this->name = $name;
        $this->genre = $genre;
        $this->birthdate = $birthdate;
        $this->email = $email;
    }
    
    
    //-------------------------------------------------------------------------
    //        Getters
    //-------------------------------------------------------------------------
    /**
     * Gets authorization id that the administrator has.
     * 
     * @return      int Authorization id
     */
    public function getAuthorizationId()
    {
        return $this->id_authorization;
    }
}