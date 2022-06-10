<?php
declare (strict_types=1);

namespace domain;


use dao\VideosDAO;
use dao\QuestionnairesDAO;
use util\IllegalStateException;
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
     * @param       int idModule Module id
     * @param       string $name Module name
     */
    public function __construct(int $idModule, string $name)
    {
        $this->idModule = $idModule;
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
    public function getClasses(?Database $db = null) : array
    {
        if (empty($this->classes)) {
            $this->classes = $this->fetchClasses($db);
        }
        
        return $this->classes;
    }

    private function fetchClasses($db)
    {
        if (empty($this->db) && empty($db)) {
            throw new \InvalidArgumentException("Database cannot be empty");
        }
        
        if (empty($db)) {
            $db = $this->db;       
        }

        $classes = array();
        $videosDao = new VideosDAO($db);
        $questionnairesDao = new QuestionnairesDAO($db);
        
        $classesVideo = $videosDao->getAllFromModule($this->idModule);
        $classesQuestionnaire = $questionnairesDao->getAllFromModule($this->idModule);
        
        foreach ($classesVideo as $class) {
            $classes[$class->getClassOrder()] = $class;
        }
        
        foreach ($classesQuestionnaire as $class) {
            $classes[$class->getClassOrder()] = $class;
        }

        return $classes;
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
        return array(
            'id_module' => $this->idModule,
            'name' => $this->name
        );
    }
}