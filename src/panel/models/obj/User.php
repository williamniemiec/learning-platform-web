<?php 
namespace models\obj;


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
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * Gets user name.
     * 
     * @return      string name
     */
    public function getName()
    {
        return $this->name;
    }
    
    /**
     * Gets user genre (0 for man and 1 for woman).
     * 
     * @return      int User genre
     */
    public function getGenre()
    {
        return $this->genre;
    }
    
    /**
     * Gets user birthdate.
     * 
     * @return      string User birthdate
     */
    public function getBirthdate()
    {
        return $this->birthdate;
    }
    
    /**
     * Gets user email.
     *
     * @return      string User email
     */
    public function getEmail()
    {
        return $this->email;
    }
}