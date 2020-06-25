<?php
namespace controllers;

use core\Controller;
use models\Admins;


/**
 * Responsible for the behavior of the view {@link adminsManager/admins_manager.php}.
 * 
 * @author		William Niemiec &lt; williamniemiec@hotmail.com &gt;
 * @version		1.0
 * @since		1.0
 */
class AdminsController extends Controller
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
        
        $header = array(
            'title' => 'Admins manager - Learning platform',
            'robots' => 'index'
        );
        
        $viewArgs = array(
            'username' => $admins->getName(),
            'header' => $header
        );
        
        $this->loadTemplate("adminsManager/admins_manager", $viewArgs);
    }
    
    
    //-------------------------------------------------------------------------
    //        Ajax
    //-------------------------------------------------------------------------
}
