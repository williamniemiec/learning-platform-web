<?php
/**
 * Copyright (c) William Niemiec.
 *
 * This source code is licensed under the MIT license found in the
 * LICENSE file in the root directory of this source tree.
 */

namespace controllers;


use config\Controller;
use repositories\pdo\MySqlPDODatabase;
use domain\Student;
use dao\NotificationsDAO;
use dao\StudentsDAO;


/**
 * Responsible for the behavior of the PurchasesView.
 */
class PurchasesController extends Controller
{
    //-------------------------------------------------------------------------
    //        Constructor
    //-------------------------------------------------------------------------
    /**
     * It will check if student is logged; otherwise, redirects him to home
     * page.
     */
    public function __construct()
    {
        if (!Student::isLogged()) {
            $this->redirectToRoot();
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
        $dbConnection = new MySqlPDODatabase();
        $student = Student::getLoggedIn($dbConnection);
        $notificationsDao = new NotificationsDAO($dbConnection, $student->getId());
        $studentsDao = new StudentsDAO($dbConnection, $student->getId());
        $limit = 10;
        $index = $this->getIndex();
        $offset = $limit * ($index - 1);
        $header = array(
            'title' => 'Purchases - Learning Platform',
            'description' => "Student purchases",
            'robots' => 'noindex'
        );
        $viewArgs = array(
            'header' => $header,
            'username' => $student->getName(),
            'purchases' => $studentsDao->getPurchases($limit, $offset),
            'notifications' => array(
                'notifications' => $notificationsDao->getNotifications(10),
                'total_unread' => $notificationsDao->countUnreadNotification()),
            'totalPages' => ceil($studentsDao->countPurchases() / $limit),
            'currentIndex' => $index
        );
        
        $this->loadTemplate("PurchasesView", $viewArgs, Student::isLogged());
    }

    private function getIndex()
    {
        if (!$this->hasIndexBeenSent()) {
            return 1;
        }

        return ((int) $_GET['index']);
    }

    private function hasIndexBeenSent()
    {
        return  !empty($_POST['index']);
    }
}
