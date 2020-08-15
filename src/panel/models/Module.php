<?php
declare (strict_types=1);

namespace models;


use models\dao\VideosDAO;
use models\dao\QuestionnairesDAO;
use models\util\IllegalStateException;
use database\Database;


/**
 * Responsible for representing modules.
 *
 * @author		William Niemiec &lt; williamniemiec@hotmail.com &gt;
 * @version		1.0.0
 * @since		1.0.0
 */
class Module implements \JsonSerializable
{
    //-------------------------------------------------------------------------
    //        Attributes
    //-------------------------------------------------------------------------
    private $id_module;
    private $name;
    private $order;
    private $totalClasses;
    private $db;
    
    
    //-------------------------------------------------------------------------
    //        Constructor
    //-------------------------------------------------------------------------
    /**
     * Creates a representation of a module.
     *
     * @param       int $id_module Module id
     * @param       string $name Module name
     */
    public function __construct(int $id_module, string $name)
    {
        $this->id_module = $id_module;
        $this->name = $name;
        $this->order = -1;
    }
    
    
    //-------------------------------------------------------------------------
    //        Getters & Setters
    //-------------------------------------------------------------------------
    /**
     * Gets Module id.
     *
     * @return      int Module id
     */
    public function getId() : int
    {
        return $this->id_module;
    }
    
    /**
     * Gets module name.
     *
     * @return      string Module name
     */
    public function getName() : string
    {
        return $this->name;
    }
    
    /**
     * Gets module order in a course.
     * 
     * @return      int Module order
     */
    public function getOrder() : int
    {
        return $this->order;
    }
    
    /**
     * Sets module order in a course.
     * 
     * @param       int $order Module order
     * 
     * @return      Module Itself to allow chained calls
     */
    public function setOrder(int $order) : Module
    {
        if ($order > 0)
            $this->order = $order;
        
        return $this;
    }
    
    /**
     * Gets all classes that belongs to the module.
     * 
     * @param       Database $db [Optional] Database (it is necessary to 
     * provide a database, either by argument or by the method 'setDatabase')
     * 
     * @return      array Classes that belongs to this module. The returned
     * array is empty if there are no classes; otherwise, it has the following
     * format:
     * <ul>
     *  <li><b>Key</b>: Class order in module</li>
     *  <li><b>Value</b>: Class</li>
     * </ul>
     * 
     * @throws      IllegalStateException If no database has been set
     * 
     * @implNote    Lazy initialization
     */
    public function getClasses(?Database $db = null) : array
    {
        if (empty($db) && empty($this->db))
            throw new IllegalStateException("No database has been set");
        
        // Sets database
        if (empty($db))
            $db = $this->db;
            
        if (empty($this->classes)) {
            $this->classes = array();
            $videos = new VideosDAO($db);
            $questionnaires = new QuestionnairesDAO($db);
            
            $classes_video = $videos->getAllFromModule($this->id_module);
            $classes_questionnaire = $questionnaires->getAllFromModule($this->id_module);
            
            foreach ($classes_video as $class) {
                $this->classes[$class->getClassOrder()] = $class;
            }
            
            foreach ($classes_questionnaire as $class) {
                $this->classes[$class->getClassOrder()] = $class;
            }
        }
        
        return $this->classes;
    }
    
    public function setDatabase(Database $db)
    {
        $this->db = $db;
    }

    /**
     * Gets total classes that the module has.
     * 
     * @return      int Total classes
     */
    public function getTotalClasses() : int
    {
        return $this->totalClasses;
    }
    
    /**
     * Sets total classes.
     * 
     * @param       int $totalClasses Total classes
     * 
     * @return      Module Itself to allow chained calls
     */
    public function setTotalClasses(int $totalClasses) : Module
    {
        if ($totalClasses >= 0)
            $this->totalClasses = $totalClasses;
        
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
            'id_module' => $this->id_module,
            'name' => $this->name,
            'order' => $this->order,
            'totalClasses' => $this->totalClasses
        );
    }
}