<?php
namespace models\obj;


/**
 * Responsible for representing student-type users.
 *
 * @author		William Niemiec &lt; williamniemiec@hotmail.com &gt;
 * @version		1.0.0
 * @since		1.0.0
 */
class Student extends User
{
    //-----------------------------------------------------------------------
    //        Attributes
    //-----------------------------------------------------------------------
    private $photo;
    

    //-----------------------------------------------------------------------
    //        Constructor
    //-----------------------------------------------------------------------
    /**
     * Creates a representation of a student-type user.
     *
     * @param       int $id Student id
     * @param       string $name Student name
     * @param       int $genre Student genre
     * @param       string $birthdate Student birthdate
     * @param       string $email Student email
     * @param       string $photo [Optional] Name of the student photo file
     */
    public function __construct($id, $name, $genre, $birthdate, $email, $photo = '')
    {
        $this->id = $id;
        $this->name = $name;
        $this->genre = $genre;
        $this->birthdate = $birthdate;
        $this->email = $email;
        $this->photo = $photo;
    }

    
    //-----------------------------------------------------------------------
    //        Getters
    //-----------------------------------------------------------------------
    /**
     * Gets the name of the student photo file.
     * 
     * @return      string Name of the student photo file or empty string if
     * the student does not have a registered photo.
     */
    public function getPhoto()
    {
        return $this->photo;
    }
}