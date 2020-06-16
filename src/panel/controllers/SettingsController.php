<?php
namespace controllers;

use core\Controller;
use models\Admins;


/**
 * Responsible for the behavior of the view {@link settings/settings.php}.
 * 
 * @author		William Niemiec &lt; williamniemiec@hotmail.com &gt;
 * @version		1.0
 * @since		1.0
 */
class SettingsController extends Controller
{
    //-------------------------------------------------------------------------
    //        Constructor
    //-------------------------------------------------------------------------
    /**
     * It will check if admin is logged; otherwise, redirects him to login
     * page.
     */
    public function __construct()
    {
        if (!Admins::isLogged()){
            header("Location: ".BASE_URL."login");
            exit;
        }
    }
    
    
    //-------------------------------------------------------------------------
    //        Methods
    //-------------------------------------------------------------------------
    /**
     * @Override
     */
    public function index ()
    {
        $admins = new Admins($_SESSION['a_login']);
        
        $header = array(
            'title' => 'Settings - Learning platform'
        );
        
        $varArgs = array(
            'adminName' => $admins->getName(),
            'header' => $header
        );
        
        $this->loadTemplate("settings/settings", $varArgs);
    }
    
    /**
     * Edits information about current admin.
     */
    public function edit()
    {
        $admins = new Admins($_SESSION['a_login']);
        
        $header = array(
            'title' => 'Settings - Learning platform'
        );
        
        $varArgs = array(
            'adminName' => $admins->getName(),
            'header' => $header
        );
        
        $this->loadTemplate("settings/settings_edit", $varArgs);
    }
}
