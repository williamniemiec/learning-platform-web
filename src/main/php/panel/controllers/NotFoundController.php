<?php
namespace controllers;


use core\Controller;
use database\pdo\MySqlPDODatabase;
use models\Admin;


/**
 * It will be responsible for site's page not found behavior.
 *
 * @author		William Niemiec &lt; williamniemiec@hotmail.com &gt;
 * @version		1.0.0
 * @since		1.0.0
 */
class NotFoundController extends Controller
{
    //-------------------------------------------------------------------------
    //        Methods
    //-------------------------------------------------------------------------
    /**
     * @Override
     */
    public function index()
    {
        $dbConnection = new MySqlPDODatabase();
        
        $header = array(
            'title' => 'Page not found - Learning platform',
            'description' => "Page not found",
            'robots' => 'noindex'
        );
        
        $viewArgs = array(
            'header' => $header
        );
        
        $admin = Admin::getLoggedIn($dbConnection);
        
        if (empty($admin))
            $this->load_template('error/404', $viewArgs, false);
            
        $viewArgs['username'] = $admin->getName();
        $viewArgs['authorization'] = $admin->getAuthorization();
        
        $this->load_template('error/404', $viewArgs, true);
    }
}

