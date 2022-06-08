<?php
namespace panel\controllers;


use panel\config\Controller;
use panel\repositories\pdo\MySqlPDODatabase;
use panel\domain\Admin;


/**
 * It will be responsible for site's page not found behavior.
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
        $header = array(
            'title' => 'Page not found - Learning platform',
            'description' => "Page not found",
            'robots' => 'noindex'
        );
        $viewArgs = array(
            'header' => $header
        );
        $dbConnection = new MySqlPDODatabase();
        $admin = Admin::getLoggedIn($dbConnection);
        
        if (empty($admin)) {
            $this->loadTemplate('error/404', $viewArgs, false);
        }
            
        $viewArgs['username'] = $admin->getName();
        $viewArgs['authorization'] = $admin->getAuthorization();
        
        $this->loadTemplate('error/404', $viewArgs, true);
    }
}

