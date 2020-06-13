<?php
namespace controllers;

use core\Controller;
use models\Students;
use models\Admins;
use models\Courses;


/**

*/
class SettingsController extends Controller
{
    //-----------------------------------------------------------------------
    //        Constructor
    //-----------------------------------------------------------------------
    public function __construct()
    {
        if (!Admins::isLogged()){
            header("Location: ".BASE_URL."login");
            exit;
        }
    }
    
    
    //-----------------------------------------------------------------------
    //        Methods
    //-----------------------------------------------------------------------
    /**
     * @Override
     */
    public function index ()
    {
        $admins = new Admins($_SESSION['a_login']);
        
        $params = array(
            'title' => 'Learning platform - Settings',
            'adminName' => $admins->getName()
        );
        
        $this->loadTemplate("settings", $params);
    }
    
    
    public function edit()
    {
        $admins = new Admins($_SESSION['a_login']);
        
        $params = array(
            'title' => 'Learning platform - Settings - Edition',
            'adminName' => $admins->getName()
        );
        
        $this->loadTemplate("settings/settings_edit", $params);
    }
}
