<?php
declare (strict_types=1);

namespace domain;


use dao\VideosDAO;
use dao\QuestionnairesDAO;
use domain\util\IllegalStateException;
use repositories\Database;


/**
 * Responsible for representing modules.
 */
class Module implements \JsonSerializable
{
    //-------------------------------------------------------------------------
    //        Attributes
    //-------------------------------------------------------------------------
    private $idModule;
    private $name;
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
        $this->idModule = $id_module;
        $this->name = $name;
    }
    
    
    //-------------------------------------------------------------------------
    //        Getters & Setters
    //-------------------------------------------------------------------------
    /**
     * Gets Module id.
     *
     * @return      int Module id
     */
    public function getCourseId() : int
    {
        return $this->idModule;
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
    public function get_classes(?Database $db = null) : array
    {
        if (empty($db) && empty($this->db)) {
            throw new IllegalStateException("No database has been set");
        }
        
        // Sets database 
        if (empty($db)) {
            $db = $this->db;
        }
            
        if (empty($this->classes)) {
            $this->classes = array();
            $videos = new VideosDAO($db);
            $questionnaires = new QuestionnairesDAO($db);
            
            $classes_video = $videos->getAllFromModule($this->idModule);
            $classes_questionnaire = $questionnaires->getAllFromModule($this->idModule);
            
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
            'id_module' => $this->idModule,
            'name' => $this->name
        );
    }
}