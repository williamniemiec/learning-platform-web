<?php
namespace controllers;


use config\Controller;
use repositories\pdo\MySqlPDODatabase;
use dao\NotificationsDAO;
use dao\BundlesDAO;
use dao\StudentsDAO;
use domain\Student;
use domain\enum\BundleOrderTypeEnum;
use domain\enum\OrderDirectionEnum;


/**
 * Responsible for the behavior of the BundleView.
 */
class BundleController extends Controller 
{    
    //-------------------------------------------------------------------------
    //        Methods
    //-------------------------------------------------------------------------
    /**
     * @Override
     */
	public function index ()
	{   
	    $this->redirectToRoot();
	}
	
	public function open($idBundle)
	{
	    $dbConnection = new MySqlPDODatabase();
	    
	    $bundlesDao = new BundlesDAO($dbConnection);
	    $bundle = $bundlesDao->get($idBundle);
	    
	    $header = array(
	        'title' => $bundle->getName().' - Learning Platform',
	        'styles' => array('BundleStyle', 'gallery'),
	        'description' => $bundle->getDescription(),
	        'keywords' => array('learning platform', 'bundle', $bundle->getName()),
	        'robots' => 'index'
	    );
	    
	    $viewArgs = array(
	        'header' => $header,
	        'bundle' => $bundle,
	        'has_bundle' => false,
	        'courses' => $bundle->getCourses($dbConnection),
	        'total_classes' => $bundle->getTotalClasses($dbConnection),
	        'total_length' => $bundle->getTotalLength($dbConnection),
	        'scripts' => array('BundleScript')
	    );
	    
	    if (Student::isLogged()) {
	        $student = Student::getLoggedIn($dbConnection);
	        $studentsDao = new StudentsDAO($dbConnection, $student->getId());
	        $notificationsDao = new NotificationsDAO($dbConnection, $student->getId());
	        
	        $viewArgs['notifications'] = array(
                'notifications' => $notificationsDao->getNotifications(10),
                'total_unread' => $notificationsDao->countUnreadNotification()
            );
	        
	        $viewArgs['username'] = $student->getName();
	        $viewArgs['extensionBundles'] = $bundlesDao->extensionBundles(
	            $idBundle, $student->getId()
            );
	        $viewArgs['unrelatedBundles'] = $bundlesDao->unrelatedBundles(
	            $idBundle, 
	            $student->getId()
            );
	        $viewArgs['has_bundle'] = $studentsDao->hasBundle($idBundle);
	    }
	    else {
	        $viewArgs['extensionBundles'] = $bundlesDao->extensionBundles($idBundle);
	        $viewArgs['unrelatedBundles'] = $bundlesDao->unrelatedBundles($idBundle);
	    }
	    
	    $this->loadTemplate("BundleView", $viewArgs, Student::isLogged());
	}
	
	
	//-------------------------------------------------------------------------
	//        Ajax
	//-------------------------------------------------------------------------
	/**
	 * Searches bundles.
	 *
	 * @param       string $_POST['name'] Name to be searched
	 * @param       string $_POST['filter']['type'] Ranking of results, which 
	 * can be:
	 * <ul>
	 *     <li>price</li>
	 *     <li>courses</li>
	 *     <li>sales</li>
	 * </ul>
	 * @param       string $_POST['filter']['order'] Sort type, which can be:
	 * <ul>
	 *     <li>asc (Ascending)</li>
	 *     <li>desc (Descending)</li>
	 * </ul> 
     * 
     * @return      string Bundles
	 * 
	 * @apiNote     Must be called using POST request method
	 */
	public function search()
	{
	    if ($this->getHttpRequestMethod() != 'POST') {
	        return;
		}
	        
        $dbConnection = new MySqlPDODatabase();
        $bundlesDao = new BundlesDAO($dbConnection);
        $student = Student::getLoggedIn($dbConnection);
        $idStudent = empty($student) ? -1 : $student->getId();
        
        echo json_encode($bundlesDao->getAll(
            $idStudent, 
            100, 
            $_POST['name'], 
            new BundleOrderTypeEnum($_POST['filter']['type']), 
            new OrderDirectionEnum($_POST['filter']['order'])
        ));
	}
	
	/**
	 * Buys a course. If user is logged in, buys it and refresh page; otherwise,
	 * redirects him to login page and, after this, redirects him to bundle page.
	 * 
	 * @param      int $_POST['id_bundle'] Bundle to be purchased
	 * 
	 * @return     string Link to redirect the user
	 * 
	 * @apiNote    Must be called using POST request method
	 */
	public function buy()
	{
	    if ($this->getHttpRequestMethod() != 'POST' || empty($_POST['id_bundle'])) {
	        return;
		}
	    
        $link = '';
	        
        if (!Student::isLogged()) {
            $_SESSION['redirect'] = BASE_URL."bundle/open/".$_POST['id_bundle'];
            $link = BASE_URL."login";
        }
        else {
            $dbConnection = new MySqlPDODatabase();
            $studentsDao = new StudentsDAO($dbConnection, Student::getLoggedIn($dbConnection)->getId());
            $studentsDao->addBundle((int)$_POST['id_bundle']);
            $link = BASE_URL."bundle/open/".$_POST['id_bundle'];
        }
        
        echo $link;
	}
}
