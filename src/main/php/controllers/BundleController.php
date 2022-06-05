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
	    $this->redirect_to_root();
	}
	
	public function open($id_bundle)
	{
	    $db_connection = new MySqlPDODatabase();
	    
	    $bundles_dao = new BundlesDAO($db_connection);
	    $bundle = $bundles_dao->get($id_bundle);
	    
	    $header = array(
	        'title' => $bundle->get_name().' - Learning Platform',
	        'styles' => array('BundleStyle', 'gallery'),
	        'description' => $bundle->get_description(),
	        'keywords' => array('learning platform', 'bundle', $bundle->get_name()),
	        'robots' => 'index'
	    );
	    
	    $view_args = array(
	        'header' => $header,
	        'bundle' => $bundle,
	        'has_bundle' => false,
	        'courses' => $bundle->get_courses($db_connection),
	        'total_classes' => $bundle->get_total_classes($db_connection),
	        'total_length' => $bundle->get_total_length($db_connection),
	        'scripts' => array('BundleScript')
	    );
	    
	    if (Student::is_logged()) {
	        $student = Student::get_logged_in($db_connection);
	        $students_dao = new StudentsDAO($db_connection, $student->get_id());
	        $notifications_dao = new NotificationsDAO($db_connection, $student->get_id());
	        
	        $view_args['notifications'] = array(
                'notifications' => $notifications_dao->get_notifications(10),
                'total_unread' => $notifications_dao->count_unread_notification()
            );
	        
	        $view_args['username'] = $student->get_name();
	        $view_args['extensionBundles'] = $bundles_dao->extension_bundles(
	            $id_bundle, $student->get_id()
            );
	        $view_args['unrelatedBundles'] = $bundles_dao->unrelated_bundles(
	            $id_bundle, 
	            $student->get_id()
            );
	        $view_args['has_bundle'] = $students_dao->has_bundle($id_bundle);
	    }
	    else {
	        $view_args['extensionBundles'] = $bundles_dao->extension_bundles($id_bundle);
	        $view_args['unrelatedBundles'] = $bundles_dao->unrelated_bundles($id_bundle);
	    }
	    
	    $this->load_template("BundleView", $view_args, Student::is_logged());
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
	    if ($this->get_http_request_method() != 'POST') {
	        return;
		}
	        
        $db_connection = new MySqlPDODatabase();
        $bundles_dao = new BundlesDAO($db_connection);
        $student = Student::get_logged_in($db_connection);
        $id_student = empty($student) ? -1 : $student->get_id();
        
        echo json_encode($bundles_dao->getAll(
            $id_student, 
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
	    if ($this->get_http_request_method() != 'POST' || empty($_POST['id_bundle'])) {
	        return;
		}
	    
        $link = '';
	        
        if (!Student::is_logged()) {
            $_SESSION['redirect'] = BASE_URL."bundle/open/".$_POST['id_bundle'];
            $link = BASE_URL."login";
        }
        else {
            $db_connection = new MySqlPDODatabase();
            $students_dao = new StudentsDAO($db_connection, Student::get_logged_in($db_connection)->get_id());
            $students_dao->addBundle((int)$_POST['id_bundle']);
            $link = BASE_URL."bundle/open/".$_POST['id_bundle'];
        }
        
        echo $link;
	}
}
