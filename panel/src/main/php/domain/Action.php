<?php 
declare (strict_types=1);

namespace panel\domain;


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
 *  <li>Updating students</li>
 *  <li>Response to support topics</li>
 *  <li>Opening and closing support topics</li>
 * </ul>
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
     * @param       int $idCourse New course id
     * 
     * @throws      \InvalidArgumentException If course id is null or less than
     * or equal to zero
     */
    public function addCourse(int $idCourse) : void
    {
        $this->validateCourseId($idCourse);
        $this->action = $this->add("Course - id_course=".$idCourse);
    }

    private function validateCourseId($id)
    {
        if (empty($id) || $id <= 0) {
            throw new \InvalidArgumentException("Course id cannot be null or". 
                                                "less than or equal to zero");
        }
    }
    
    /**
     * Generates an action message regarding updating a course.
     *
     * @param       int $idCourse Course id
     *
     * @throws      \InvalidArgumentException If course id is null or less than
     * or equal to zero
     */
    public function updateCourse(int $idCourse) : void
    {
        $this->validateCourseId($idCourse);
        $this->action = $this->update("Course - id_course=".$idCourse);
    }
    
    /**
     * Generates an action message regarding removing a course.
     *
     * @param       int $idCourse Course id
     *
     * @throws      \InvalidArgumentException If course id is null or less than
     * or equal to zero
     */
    public function deleteCourse(int $idCourse) : void
    {
        $this->validateCourseId($idCourse);
        $this->action = $this->delete("Course - id_course=".$idCourse);
    }
    
    /**
     * Generates an action message regarding adding a bundle.
     *
     * @param       int $idBundle New bundle id
     *
     * @throws      \InvalidArgumentException If bundle id is null or less than
     * or equal to zero
     */
    public function addBundle(int $idBundle) : void
    {
        $this->validateBundleId($idBundle);
        $this->action = $this->add("Bundle - id_bundle=".$idBundle);
    }

    private function validateBundleId($id)
    {
        if (empty($id) || $id <= 0) {
            throw new \InvalidArgumentException("Bundle id cannot be null or". 
                                                "less than or equal to zero");
        }
    }
    
    /**
     * Generates an action message regarding updating a bundle.
     *
     * @param       int $idBundle Bundle id
     *
     * @throws      \InvalidArgumentException If bundle id is null or less than
     * or equal to zero
     */
    public function updateBundle(int $idBundle) : void
    {
        $this->validateBundleId($idBundle);
        $this->action = $this->update("Bundle - id_bundle=".$idBundle);
    }
    
    /**
     * Generates an action message regarding removing a bundle.
     *
     * @param       int $idBundle Bundle id
     *
     * @throws      \InvalidArgumentException If bundle id is null or less than
     * or equal to zero
     */
    public function deleteBundle(int $idBundle) : void
    {
        $this->validateBundleId($idBundle);
        $this->action = $this->delete("Bundle - id_bundle=".$idBundle);
    }
    
    /**
     * Generates an action message regarding adding a module.
     *
     * @param       int idModule New module id
     *
     * @throws      \InvalidArgumentException If module id is null or less than
     * or equal to zero
     */
    public function addModule(int $idModule) : void
    {
        $this->validateModuleId($idModule);
        $this->action = $this->add("Module - id_module=".$idModule);
    }

    private function validateModuleId($id)
    {
        if (empty($id) || $id <= 0) {
            throw new \InvalidArgumentException("BundModulele id cannot be null ". 
                                                "or less than or equal to zero");
        }
    }
    
    /**
     * Generates an action message regarding updating a module.
     *
     * @param       int $idModule Module id
     *
     * @throws      \InvalidArgumentException If module id is null or less than
     * or equal to zero
     */
    public function updateModule(int $idModule) : void
    {
        $this->validateModuleId($idModule);
        $this->action = $this->update("Module - id_module=".$idModule);
    }
    
    /**
     * Generates an action message regarding removing a module.
     *
     * @param       int $idModule Module id
     *
     * @throws      \InvalidArgumentException If module id is null or less than
     * or equal to zero
     */
    public function deleteModule(int $idModule) : void
    {
        $this->validateModuleId($idModule);
        $this->action = $this->delete("Module - id_module=".$idModule);
    }
    
    /**
     * Generates an action message regarding adding a class.
     *
     * @param       int $idModule Module id to which the new class belongs
     * @param       int $classOrder Class order in the module 
     *
     * @throws      \InvalidArgumentException If module id or class order are 
     * null or less than or equal to zero
     */
    public function addClass(int $idModule, int $classOrder) : void
    {
        $this->validateModuleId($idModule);
        $this->validateClassOrder($classOrder);
        $this->action = $this->add("Class - id_module=".$idModule.", class_order=".$classOrder);
    }

    private function validateClassOrder($order)
    {
        if (empty($order) || $order <= 0) {
            throw new \InvalidArgumentException("Class order cannot be null or".
                                                "less than or equal to zero");
        }
    }
    
    /**
     * Generates an action message regarding updating a class.
     *
     * @param       int $idModule Module id to which the class belongs
     * @param       int $classOrder Class order in the module
     *
     * @throws      \InvalidArgumentException If module id or class order are
     * null or less than or equal to zero
     */
    public function updateClass(int $idModule, int $classOrder) : void
    {
        $this->validateModuleId($idModule);
        $this->validateClassOrder($classOrder);
        $this->action = $this->update("Class - id_module=".$idModule.", class_order=".$classOrder);
    }
    
    /**
     * Generates an action message regarding removing a class.
     *
     * @param       int $idModule Module id to which the class belongs
     * @param       int $classOrder Class order in the module
     *
     * @throws      \InvalidArgumentException If module id or class order are
     * null or less than or equal to zero
     */
    public function deleteClass(int $idModule, int $classOrder) : void
    {
        $this->validateModuleId($idModule);
        $this->validateClassOrder($classOrder);
        $this->action = $this->delete("Class - id_module=".$idModule.", class_order=".$classOrder);
    }
    
    /**
     * Generates an action message regarding adding an administrator.
     *
     * @param       int $idAdmin New admin id
     *
     * @throws      \InvalidArgumentException If admin id is null or less than
     * or equal to zero
     */
    public function addAdmin(int $idAdmin) : void
    {
        $this->validateAdminId($idAdmin);
        $this->action = $this->add("Admin - id_admin=".$idAdmin);
    }

    private function validateAdminId($id)
    {
        if (empty($id) || $id <= 0) {
            throw new \InvalidArgumentException("Admin id cannot be null or".
                                                "less than or equal to zero");
        }
    }
    
    /**
     * Generates an action message regarding updating an administrator.
     *
     * @param       int $idAdmin Admin id
     *
     * @throws      \InvalidArgumentException If admin id is null or less than
     * or equal to zero
     */
    public function updateAdmin(int $idAdmin) : void
    {
        $this->validateAdminId($idAdmin);
        $this->action = $this->update("Admin - id_admin=".$idAdmin);
    }
    
    /**
     * Generates an action message regarding removing an administrator.
     *
     * @param       int $idAdmin Admin id
     *
     * @throws      \InvalidArgumentException If admin id is null or less than
     * or equal to zero
     */
    public function deleteAdmin(int $idAdmin) : void
    {
        $this->validateAdminId($idAdmin);
        $this->action = $this->delete("Admin - id_admin=".$idAdmin);
    }
    
    /**
     * Generates an action message regarding updating a student.
     *
     * @param       int $idStudent Student id
     *
     * @throws      \InvalidArgumentException If student id is null or less than
     * or equal to zero
     */
    public function updateStudent(int $idStudent) : void
    {
        $this->validateStudentId($idStudent);
        $this->action = $this->update("Student - id_student=".$idStudent);
    }

    private function validateStudentId($id)
    {
        if (empty($id) || $id <= 0) {
            throw new \InvalidArgumentException("Student id cannot be null or".
                                                "less than or equal to zero");
        }
    }
    
    /**
     * Generates an action message regarding removing a student.
     *
     * @param       int $idStudent Student id
     *
     * @throws      \InvalidArgumentException If student id is null or less than
     * or equal to zero
     */
    public function deleteStudent(int $idStudent) : void
    {
        $this->validateStudentId($idStudent);
        $this->action = $this->delete("Student - id_student=".$idStudent);
    }
    
    /**
     * Generates an action message regarding the response to a support topic.
     *
     * @param       int $idTopic Support topic id
     *
     * @throws      \InvalidArgumentException If topic id is null or less than
     * or equal to zero
     */
    public function answerTopic(int $idTopic) : void
    {
        $this->validateTopicId($idTopic);
        $this->action = $this->update("Topic answered - id_topic=".$idTopic);
    }

    private function validateTopicId($id)
    {
        if (empty($id) || $id <= 0) {
            throw new \InvalidArgumentException("Topic id cannot be null or ".
                                                "less than or equal to zero");
        }
    }
    
    /**
     * Generates an action message regarding opening a support topic.
     *
     * @param       int $idTopic Support topic id
     *
     * @throws      \InvalidArgumentException If topic id is null or less than
     * or equal to zero
     */
    public function openTopic(int $idTopic) : void
    {
        $this->validateTopicId($idTopic);
        $this->action = $this->update("Topic opened - id_topic=".$idTopic);
    }
    
    /**
     * Generates an action message regarding closing a support topic.
     *
     * @param       int $idTopic Support topic id
     *
     * @throws      \InvalidArgumentException If topic id is null or less than
     * or equal to zero
     */
    public function closeTopic(int $idTopic) : void
    {
        $this->validateTopicId($idTopic);
        $this->action = $this->update("Topic closed - id_topic=".$idTopic);
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
        $this->validateContent($content);
            
        return "[ADD] ".$content;
    }
    
    private function validateContent($content)
    {
        if (empty($content)) {
            throw new \InvalidArgumentException("Content cannot be null");
        }
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
        $this->validateContent($content);
        
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
        $this->validateContent($content);
        
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
