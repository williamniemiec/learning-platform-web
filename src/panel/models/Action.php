<?php 
declare (strict_types=1);

namespace models;


/**
 * Responsible for generating action messages. There are three types:
 * <ul>
 *  <li>[ADD] - Adding something new</li>
 *  <li>[UPD] - Updating something</li>
 *  <li>[DEL] - Deleting something</li>
 * </ul>
 * 
 * An action should be generated in the following events:
 * <ul>
 *  <li>Adding, updating and removing bundles</li>
 *  <li>Adding, updating and removing courses</li>
 *  <li>Adding, updating and removing modules</li>
 *  <li>Adding, updating and removing classes</li>
 *  <li>Adding, updating and removing administrators</li>
 *  <li>Updating and removing students</li>
 *  <li>Response to support topics</li>
 *  <li>Opening and closing support topics</li>
 * </ul>
 *
 *
 * @author		William Niemiec &lt; williamniemiec@hotmail.com &gt;
 * @version		1.0.0
 * @since		1.0.0
 */
class Action
{
    //-------------------------------------------------------------------------
    //        Attributes
    //-------------------------------------------------------------------------
    private $action;
    
    
    //-------------------------------------------------------------------------
    //        Methods
    //-------------------------------------------------------------------------
    /**
     * Generates an action message regarding adding a course.
     * 
     * @param       int $id_course New course id
     * 
     * @throws      \InvalidArgumentException If course id is null or less than
     * or equal to zero
     */
    public function addCourse(int $id_course) : void
    {
        if (empty($id_course) || $id_course <= 0)
            throw new \InvalidArgumentException("Course id cannot be null or". 
                "less than or equal to zero");
        
        $this->action = $this->add("Course - id_course=".$id_course);
    }
    
    /**
     * Generates an action message regarding updating a course.
     *
     * @param       int $id_course Course id
     *
     * @throws      \InvalidArgumentException If course id is null or less than
     * or equal to zero
     */
    public function editCourse(int $id_course) : void
    {
        if (empty($id_course) || $id_course <= 0)
            throw new \InvalidArgumentException("Course id cannot be null or".
                "less than or equal to zero");
        
        $this->action = $this->update("Course - id_course=".$id_course);
    }
    
    /**
     * Generates an action message regarding removing a course.
     *
     * @param       int $id_course Course id
     *
     * @throws      \InvalidArgumentException If course id is null or less than
     * or equal to zero
     */
    public function deleteCourse(int $id_course) : void
    {
        if (empty($id_course) || $id_course <= 0)
            throw new \InvalidArgumentException("Course id cannot be null or".
                "less than or equal to zero");
            
        $this->action = $this->delete("Course - id_course=".$id_course);
    }
    
    /**
     * Generates an action message regarding adding a bundle.
     *
     * @param       int $id_bundle New bundle id
     *
     * @throws      \InvalidArgumentException If bundle id is null or less than
     * or equal to zero
     */
    public function addBundle(int $id_bundle) : void
    {
        if (empty($id_bundle) || $id_bundle <= 0)
            throw new \InvalidArgumentException("Bundle id cannot be null or".
                "less than or equal to zero");
            
        $this->action = $this->add("Bundle - id_bundle=".$id_bundle);
    }
    
    /**
     * Generates an action message regarding updating a bundle.
     *
     * @param       int $id_bundle Bundle id
     *
     * @throws      \InvalidArgumentException If bundle id is null or less than
     * or equal to zero
     */
    public function editBundle(int $id_bundle) : void
    {
        if (empty($id_bundle) || $id_bundle <= 0)
            throw new \InvalidArgumentException("Bundle id cannot be null or".
                "less than or equal to zero");
            
        $this->action = $this->update("Bundle - id_bundle=".$id_bundle);
    }
    
    /**
     * Generates an action message regarding removing a bundle.
     *
     * @param       int $id_bundle Bundle id
     *
     * @throws      \InvalidArgumentException If bundle id is null or less than
     * or equal to zero
     */
    public function deleteBundle(int $id_bundle) : void
    {
        if (empty($id_bundle) || $id_bundle <= 0)
            throw new \InvalidArgumentException("Bundle id cannot be null or".
                "less than or equal to zero");
            
        $this->action = $this->delete("Bundle - id_bundle=".$id_bundle);
    }
    
    /**
     * Generates an action message regarding adding a module.
     *
     * @param       int $id_module New module id
     *
     * @throws      \InvalidArgumentException If module id is null or less than
     * or equal to zero
     */
    public function addModule(int $id_module) : void
    {
        if (empty($id_module) || $id_module <= 0)
            throw new \InvalidArgumentException("Module id cannot be null or".
                "less than or equal to zero");
            
        $this->action = $this->add("Module - id_module=".$id_module);
    }
    
    /**
     * Generates an action message regarding updating a module.
     *
     * @param       int $id_module Module id
     *
     * @throws      \InvalidArgumentException If module id is null or less than
     * or equal to zero
     */
    public function editModule(int $id_module) : void
    {
        if (empty($id_module) || $id_module <= 0)
            throw new \InvalidArgumentException("Module id cannot be null or".
                "less than or equal to zero");
            
        $this->action = $this->update("Module - id_module=".$id_module);
    }
    
    /**
     * Generates an action message regarding removing a module.
     *
     * @param       int $id_module Module id
     *
     * @throws      \InvalidArgumentException If module id is null or less than
     * or equal to zero
     */
    public function deleteModule(int $id_module) : void
    {
        if (empty($id_module) || $id_module <= 0)
            throw new \InvalidArgumentException("Module id cannot be null or".
                "less than or equal to zero");
            
        $this->action = $this->delete("Module - id_module=".$$id_module);
    }
    
    /**
     * Generates an action message regarding adding a class.
     *
     * @param       int $id_module Module id to which the new class belongs
     * @param       int $class_order Class order in the module 
     *
     * @throws      \InvalidArgumentException If module id or class order are 
     * null or less than or equal to zero
     */
    public function addClass(int $id_module, int $class_order) : void
    {
        if (empty($id_module) || $id_module <= 0)
            throw new \InvalidArgumentException("Module id cannot be null or".
                "less than or equal to zero");
            
        if (empty($class_order) || $class_order <= 0)
            throw new \InvalidArgumentException("Class order cannot be null or".
                "less than or equal to zero");
            
        $this->action = $this->add("Class - id_module=".$id_module.", class_order=".$class_order);
    }
    
    /**
     * Generates an action message regarding updating a class.
     *
     * @param       int $id_module Module id to which the class belongs
     * @param       int $class_order Class order in the module
     *
     * @throws      \InvalidArgumentException If module id or class order are
     * null or less than or equal to zero
     */
    public function editClass(int $id_module, int $class_order) : void
    {
        if (empty($id_module) || $id_module <= 0)
            throw new \InvalidArgumentException("Module id cannot be null or".
                "less than or equal to zero");

        if (empty($class_order) || $class_order <= 0)
            throw new \InvalidArgumentException("Class order cannot be null or".
                "less than or equal to zero");
            
        $this->action = $this->edit("Class - id_module=".$id_module.", class_order=".$class_order);
    }
    
    /**
     * Generates an action message regarding removing a class.
     *
     * @param       int $id_module Module id to which the class belongs
     * @param       int $class_order Class order in the module
     *
     * @throws      \InvalidArgumentException If module id or class order are
     * null or less than or equal to zero
     */
    public function deleteClass(int $id_module, int $class_order) : void
    {
        if (empty($id_module) || $id_module <= 0)
            throw new \InvalidArgumentException("Module id cannot be null or".
                "less than or equal to zero");
            
        if (empty($class_order) || $class_order <= 0)
            throw new \InvalidArgumentException("Class order cannot be null or".
                "less than or equal to zero");
            
        $this->action = $this->delete("Class - id_module=".$id_module.", class_order=".$class_order);
    }
    
    /**
     * Generates an action message regarding adding an administrator.
     *
     * @param       int $id_admin New admin id
     *
     * @throws      \InvalidArgumentException If admin id is null or less than
     * or equal to zero
     */
    public function addAdmin(int $id_admin) : void
    {
        if (empty($id_admin) || $id_admin <= 0)
            throw new \InvalidArgumentException("Admin id cannot be null or".
                "less than or equal to zero");
        
        $this->action = $this->add("Admin - id_admin=".$id_admin);
    }
    
    /**
     * Generates an action message regarding updating an administrator.
     *
     * @param       int $id_admin Admin id
     *
     * @throws      \InvalidArgumentException If admin id is null or less than
     * or equal to zero
     */
    public function editAdmin(int $id_admin) : void
    {
        if (empty($id_admin) || $id_admin <= 0)
            throw new \InvalidArgumentException("Admin id cannot be null or".
                "less than or equal to zero");
            
        $this->action = $this->edit("Admin - id_admin=".$id_admin);
    }
    
    /**
     * Generates an action message regarding removing an administrator.
     *
     * @param       int $id_admin Admin id
     *
     * @throws      \InvalidArgumentException If admin id is null or less than
     * or equal to zero
     */
    public function deleteAdmin(int $id_admin) : void
    {
        if (empty($id_admin) || $id_admin <= 0)
            throw new \InvalidArgumentException("Admin id cannot be null or".
                "less than or equal to zero");
            
        $this->action = $this->delete("Admin - id_admin=".$id_admin);
    }
    
    /**
     * Generates an action message regarding updating a student.
     *
     * @param       int $id_student Student id
     *
     * @throws      \InvalidArgumentException If student id is null or less than
     * or equal to zero
     */
    public function editStudent(int $id_student) : void
    {
        if (empty($id_student) || $id_student <= 0)
            throw new \InvalidArgumentException("Student id cannot be null or".
                "less than or equal to zero");
            
        $this->action = $this->edit("Student - id_student=".$id_student);
    }
    
    /**
     * Generates an action message regarding removing a student.
     *
     * @param       int $id_student Student id
     *
     * @throws      \InvalidArgumentException If student id is null or less than
     * or equal to zero
     */
    public function deleteStudent(int $id_student) : void
    {
        if (empty($id_student) || $id_student <= 0)
            throw new \InvalidArgumentException("Student id cannot be null or".
                "less than or equal to zero");
            
        $this->action = $this->delete("Student - id_student=".$id_student);
    }
    
    /**
     * Generates an action message regarding the response to a support topic.
     *
     * @param       int $id_topic Support topic id
     *
     * @throws      \InvalidArgumentException If topic id is null or less than
     * or equal to zero
     */
    public function answerTopic(int $id_topic) : void
    {
        if (empty($id_topic) || $id_topic <= 0)
            throw new \InvalidArgumentException("Topic id cannot be null or".
                "less than or equal to zero");
            
        $this->action = $this->edit("Topic answered - id_topic=".$id_topic);
    }
    
    /**
     * Generates an action message regarding opening a support topic.
     *
     * @param       int $id_topic Support topic id
     *
     * @throws      \InvalidArgumentException If topic id is null or less than
     * or equal to zero
     */
    public function openTopic(int $id_topic) : void
    {
        if (empty($id_topic) || $id_topic <= 0)
            throw new \InvalidArgumentException("Topic id cannot be null or".
                "less than or equal to zero");
            
        $this->action = $this->update("Topic opened - id_topic=".$id_topic);
    }
    
    /**
     * Generates an action message regarding closing a support topic.
     *
     * @param       int $id_topic Support topic id
     *
     * @throws      \InvalidArgumentException If topic id is null or less than
     * or equal to zero
     */
    public function closeTopic(int $id_topic) : void
    {
        if (empty($id_topic) || $id_topic <= 0)
            throw new \InvalidArgumentException("Topic id cannot be null or".
                "less than or equal to zero");
            
        $this->action = $this->update("Topic closed - id_topic=".$id_topic);
    }
    
    /**
     * Generates addiction action message.
     *
     * @param       string $content Message content
     *
     * @return      string Add action message with the given text
     *
     * @throws      \InvalidArgumentException If content is null
     */
    private function add(string $content) : string
    {
        if (empty($content))
            throw new \InvalidArgumentException("Content cannot be null");
            
        return "[ADD] ".$content;
    }
    
    /**
     * Generates update action message.
     *
     * @param       string $content Message content
     *
     * @return      string Add action message with the given text
     *
     * @throws      \InvalidArgumentException If text is null
     */
    private function update(string $content) : string
    {
        if (empty($content))
            throw new \InvalidArgumentException("Content cannot be null");
        
        return "[UPD] ".$content;
    }
    
    /**
     * Generates delete action message.
     *
     * @param       string $content Message content
     *
     * @return      string Add action message with the given text
     *
     * @throws      \InvalidArgumentException If text is null
     */
    private function delete(string $content) : string
    {
        if (empty($content))
            throw new \InvalidArgumentException("Content cannot be null");
        
        return "[DEL] ".$content;
    }
    
    
    //-------------------------------------------------------------------------
    //        Getters
    //-------------------------------------------------------------------------
    /**
     * Gets selected action.
     * 
     * @return      string Selected action or empty string if no action was 
     * selected
     */
    public function get() : string
    {
        return empty($this->action) ? "" : $this->action;
    }
}