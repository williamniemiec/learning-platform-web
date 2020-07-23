<?php
namespace models\obj;

use models\Courses;


/**
 * Responsible for representing bundles.
 *
 * @author		William Niemiec &lt; williamniemiec@hotmail.com &gt;
 * @version		1.0.0
 * @since		1.0.0
 */
class Bundle
{
    //-------------------------------------------------------------------------
    //        Attributes
    //-------------------------------------------------------------------------
    private $id_bundle;
    private $name;
    private $price;
    private $description;
    private $courses;
    
    
    //-------------------------------------------------------------------------
    //        Constructor
    //-------------------------------------------------------------------------
    /**
     * Creates a representation of a bundle.
     *
     * @param       int $id_bundle Bundle id
     * @param       string $name Bundle name
     * @param       float $price Bundle price
     * @param       string $description [Optional] Bundle description
     */
    public function __construct($id_bundle, $name, $price, $description = '')
    {
        $this->id_bundle = $id_bundle;
        $this->name = $name;
        $this->price = $price;
        $this->description = $description;
    }
    
    
    //-------------------------------------------------------------------------
    //        Getters
    //-------------------------------------------------------------------------
    /**
     * Gets bundle id.
     *
     * @return      int Bundle id
     */
    public function getBundleId()
    {
        return $this->id_bundle;
    }
    
    /**
     * Gets bundle name.
     *
     * @return      string Bundle name
     */
    public function getName()
    {
        return $this->name;
    }
    
    /**
     * Gets bundle price.
     *
     * @return      float Bundle price
     */
    public function getPrice()
    {
        return $this->price;
    }
    
    /**
     * Gets bundle description.
     *
     * @return      string Bundle description or empty string if
     * bundle does not have a description
     */
    public function getDescription()
    {
        return $this->description;
    }
    
    public function getCourses()
    {
        if (empty($this->courses)) {
            $this->courses = Courses::getFromBundle($this->id_bundle);
        }
        
        return $this->courses;
    }
}