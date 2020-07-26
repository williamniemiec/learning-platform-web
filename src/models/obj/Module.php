<?php
declare (strict_types=1);

namespace models\obj;


use models\Modules;
use models\Classes;
use models\Videos;
use models\Questionnaires;

/**
 * Responsible for representing modules.
 *
 * @author		William Niemiec &lt; williamniemiec@hotmail.com &gt;
 * @version		1.0.0
 * @since		1.0.0
 */
class Module
{
    //-------------------------------------------------------------------------
    //        Attributes
    //-------------------------------------------------------------------------
    private $id_module;
    private $name;
    
    
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
    }
    
    
    //-------------------------------------------------------------------------
    //        Getters
    //-------------------------------------------------------------------------
    /**
     * Gets Module id.
     *
     * @return      int Module id
     */
    public function getCourseId() : int
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
     * Gets all classes that belongs to the module.
     * 
     * @return      array Classes that belongs to this module. The returned
     * array is empty if there are no classes; otherwise, it has the following
     * format:
     * <ul>
     *  <li><b>Key</b>: Class order in module</li>
     *  <li><b>Value</b>: Class</li>
     * </ul>
     * 
     * @implNote    Lazy initialization
     */
    public function getClasses() : array
    {
        if (empty($this->classes)) {
            $this->classes = array();
            $videos = new Videos();
            $questionnaires = new Questionnaires();
            
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
}