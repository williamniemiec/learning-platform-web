<?php
declare (strict_types=1);

namespace panel\domain;


use panel\repositories\Database;
use panel\dao\CoursesDAO;
use panel\dao\BundlesDAO;


/**
 * Responsible for representing bundles.
 */
class Bundle implements \JsonSerializable
{
    //-------------------------------------------------------------------------
    //        Attributes
    //-------------------------------------------------------------------------
    private $idBundle;
    private $name;
    private $price;
    private $logo;
    private $description;
    private $courses;
    private $totalClasses;
    private $totalLength;
    
    
    //-------------------------------------------------------------------------
    //        Constructor
    //-------------------------------------------------------------------------
    /**
     * Creates a representation of a bundle.
     *
     * @param       int $id_bundle Bundle id
     * @param       string $name Bundle name
     * @param       float $price Bundle price
     * @param       string $logo [Optional] Bundle logo
     * @param       string $description [Optional] Bundle description
     */
    public function __construct(
        int $id_bundle,
        string $name, 
        float $price, 
        ?string $logo = '', 
        ?string $description = ''
    )
    {
        $this->idBundle = $id_bundle;
        $this->name = $name;
        $this->price = $price;
        $this->logo = empty($logo) ? '' : $logo;
        $this->description = empty($description) ? '' : $description;
    }


    //-------------------------------------------------------------------------
    //        Methods
    //-------------------------------------------------------------------------
    private function fetchCourses($db)
    {
        $coursesDao = new CoursesDAO($db);
            
        return $coursesDao->getFromBundle($this->idBundle);
    }

    private function fetchTotal($db)
    {
        $bundlesDao = new BundlesDAO($db);
        
        return $bundlesDao->countTotalClasses($this->idBundle);
    }
    
    
    //-------------------------------------------------------------------------
    //        Getters
    //-------------------------------------------------------------------------
    /**
     * Gets bundle id.
     *
     * @return      int Bundle id
     */
    public function getId() : int
    {
        return $this->idBundle;
    }
    
    /**
     * Gets bundle name.
     *
     * @return      string Bundle name
     */
    public function getName() : string
    {
        return $this->name;
    }
    
    /**
     * Gets bundle price.
     *
     * @return      float Bundle price
     */
    public function getPrice() : float
    {
        return $this->price;
    }
    
    /**
     * Gets bundle logo.
     * 
     * @return      string Bundle logo file name or empty string if the bundle
     * does not have a logo
     */
    public function getLogo() : string
    {
        return $this->logo;
    }
    
    /**
     * Gets bundle description.
     *
     * @return      string Bundle description or empty string if
     * bundle does not have a description
     */
    public function getDescription() : string
    {
        return $this->description;
    }
    
    /**
     * Gets courses that belongs to the bundle.
     * 
     * @param       Database $db Database
     * 
     * @return      Course[] Courses that belongs to the bundle or
     * empty array if there are no courses in the bundle
     * 
     * @implNote    Lazy initialization
     */
    public function getCourses(Database $db) : array
    {
        if (empty($this->courses)) {
            $this->courses = $this->fetchCourses($db);
        }
        
        return $this->courses;
    }
    
    /**
     * Gets the total length of the bundle.
     * 
     * @param       Database $db Database
     * 
     * @return      int Total length of the bundle
     * 
     * @implNote    Lazy initialization
     */
    public function getTotalLength(Database $db) : int
    {
        if (empty($this->totalLength)) {
            $total = $this->fetchTotal($db);
            
            $this->totalLength = $total['total_length'];
            $this->totalClasses = $total['total_classes'];
        }
        
        return $this->totalLength;
    }
    
    /**
     * Gets the total classes of the bundle.
     * 
     * @param       Database $db Database
     * 
     * @return      int Total classes of the bundle
     *
     * @implNote    Lazy initialization
     */
    public function getTotalClasses(Database $db) : int
    {
        if (empty($this->totalClasses)) {
            $total = $this->fetchTotal($db);
            
            $this->totalLength = $total['total_length'];
            $this->totalClasses = $total['total_classes'];
        }
        
        return $this->totalClasses;
    }

    /**
     * Gets total students who have this bundle.
     * 
     * @return      int Total students
     */
    public function getTotalStudents() : int
    {
        return $this->totalStudents;
    }
    
    /**
     * Sets total students who have this bundle.
     * 
     * @param       int $totalStudents Total students who have this bundle
     * 
     * @return      Bundle Itself to allow chained calls
     */
    public function setTotalStudents(int $totalStudents) : Bundle
    {
        if ($totalStudents >= 0) {
            $this->totalStudents = $totalStudents;
        }
        
        return $this;
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
    public function jsonSerialize()
    {
        return array(
            'id' => $this->id_bundle,
            'name' => $this->name,
            'price' => $this->price,
            'logo' => $this->logo,
            'description' => $this->description,
            'courses' => $this->courses,
            'totalClasses' => $this->totalClasses,
            'totalLength' => $this->totalLength,
            'totalStudents' => $this->totalStudents
        );
    }
}
