<?php
declare (strict_types=1);

namespace dao;


use repositories\Database;
use domain\Admin;
use domain\Bundle;
use domain\Action;
use domain\enum\OrderDirectionEnum;
use domain\enum\BundleOrderTypeEnum;
use util\IllegalAccessException;


/**
 * Responsible for managing 'bundles' table.
 */
class BundlesDAO extends DAO
{
    //-------------------------------------------------------------------------
    //        Attributes
    //-------------------------------------------------------------------------
    private $admin;
    
    
    //-------------------------------------------------------------------------
    //        Constructor
    //-------------------------------------------------------------------------
    /**
     * Creates 'bundles' table manager.
     *
     * @param       Database $db Database
     * @param       Admin $admin [Optional] Admin logged in
     */
    public function __construct(Database $db, Admin $admin = null)
    {
        parent::__construct($db);
        $this->admin = $admin;
    }
    
    
    //-------------------------------------------------------------------------
    //        Methods
    //-------------------------------------------------------------------------
    /**
     * Gets a bundle
     *
     * @param       int $idBundle Bundle id or null if there is no bundle with
     * the given id
     *
     * @return      Bundle Bundle with the given id
     *
     * @throws      \InvalidArgumentException If bundle id is empty, less than
     * or equal to zero
     */
    public function get(int $idBundle) : Bundle
    {
        $this->validateBundleId($idBundle);
        
        $response = null;
        
        // Query construction
        $this->withQuery("
            SELECT  *
            FROM    bundles
            WHERE   id_bundle = ?
        ");
            
        // Executes query
        $sql->execute(array($idBundle));
        
        // Parses results
        if ($sql && $sql->rowCount() > 0) {
            $bundle = $sql->fetch();
            $response = new Bundle(
                (int)$bundle['id_bundle'],
                $bundle['name'],
                (float)$bundle['price'],
                $bundle['logo'],
                $bundle['description']
            );
        }
        
        return $response;
    }

    private function validateBundleId($id)
    {
        if (empty($id) || $id <= 0) {
            throw new \InvalidArgumentException("Bundle id cannot be empty or ".
                                                "less than or equal to zero");
        }
    }
    
    /**
     * Gets all registered bundles. If a filter option is provided, it gets 
     * only those bundles that satisfy these filters.
     * 
     * @param       int $limit [Optional] Maximum bundles returned
     * @param       int $offset [Optional] Ignores first results from the return           
     * @param       string $name [Optional] Bundle name
     * @param       BundleOrderTypeEnum $orderBy [Optional] Ordering criteria 
     * @param       OrderDirectionEnum $orderType [Optional] Order that the 
     * elements will be returned. Default is ascending
     * 
     * @return      Bundle[] Bundles with the provided filters or empty array if
     * no bundles are found.
     */
    public function getAll(int $limit = -1, int $offset = -1, string $name = '', 
        BundleOrderTypeEnum $orderBy = null, OrderDirectionEnum $orderType = null) : array
    {
        $response = array();
        $bindParams = array();

        if (empty($orderType))
            $orderType = new OrderDirectionEnum(OrderDirectionEnum::ASCENDING);
        
        // Query construction
        $query = "
            SELECT      id_bundle, name, bundles.price, logo, description,
                        COUNT(id_course) AS courses,
                        COUNT(id_student) AS sales
            FROM        bundles 
                        NATURAL LEFT JOIN bundle_courses
                        LEFT JOIN purchases USING (id_bundle)
            GROUP BY    id_bundle, name, bundles.price, description
        ";
        
        // Limits the search to a specified name (if a name was specified)
        if (!empty($name)) {
            $query .= empty($orderBy) ? " HAVING name LIKE ?" : " HAVING name LIKE ?";
            $bindParams[] = $name.'%';
        }
        
        // Sets order by criteria (if any)
        if (!empty($orderBy)) {
            $query .= " ORDER BY ".$orderBy->get()." ".$orderType->get();
        }

        // Limits the results (if a limit was given)
        if ($limit > 0) {
            if ($offset > 0)    
                $query .= " LIMIT ".$offset.",".$limit;
            else
                $query .= " LIMIT ".$limit;
        }
        
        // Prepares query
        $this->withQuery($query);

        // Executes query
        $sql->execute($bindParams);
        
        // Parses results
        if ($sql && $sql->rowCount() > 0) {
            $bundles = $sql->fetchAll();
            $i = 0;
            
            foreach ($bundles as $bundle) {
                $response[$i] = new Bundle(
                    (int)$bundle['id_bundle'],
                    $bundle['name'],
                    (float)$bundle['price'],
                    $bundle['logo'],
                    $bundle['description']
                );
                
                $response[$i]->setTotalStudents((int)$bundle['sales']);
                $i++;
            }
        }

        return $response;
    }
    
    /**
     * Creates a new bundle.
     * 
     * @param       Bundle $bundle Bundle to be created
     * 
     * @return      bool If bundle has been successfully added
     * 
     * @throws      IllegalAccessException If current admin does not have
     * authorization to create bundles
     * @throws      \InvalidArgumentException If bundle is empty or if admin
     * provided in the constructor is empty
     */
    public function new(Bundle $bundle) : bool
    {
        $this->validateLoggedAdmin();
        $this->validateAuthorization(0, 1);
        $this->validateBundle($bundle);
        
        
        $response = false;
        $bindParams = array(
            $bundle->getName(),
            $bundle->getPrice()
        );
            
        // Query construction
        $query = "
            INSERT INTO bundles
            SET name = ?, price = ?
        ";
        
        if (!empty($bundle->getDescription())) {
            $query .= ", description = ?";
            $bindParams[] = $bundle->getDescription();
        }
        
        if (!empty($bundle->getLogo())) {
            $query .= ", logo = ?";
            $bindParams[] = $bundle->getLogo();
        }

        // Prepares query
        $this->withQuery($query);
        
        // Executes query
        $sql->execute($bindParams);
        
        if (!empty($sql) && $sql->rowCount() > 0) {
            $response = true;
            $action = new Action();
            $adminsDAO = new AdminsDAO($this->db, Admin::getLoggedIn($this->db));
            $action->addBundle((int)$this->db->lastInsertId());
            $adminsDAO->newAction($action);
        }
        
        return $response;
    }

    private function validateBundle($bundle)
    {
        if (empty($bundle)) {
            throw new \InvalidArgumentException("Bundle cannot be empty");
        }
    }
    
    /**
     * Updates a bundle.
     * 
     * @param       Bundle $bundle Updated bundle
     * 
     * @return      bool If bundle has been successfully updated
     * 
     * @throws      IllegalAccessException If current admin does not have
     * authorization to update bundles
     * @throws      \InvalidArgumentException If bundle is empty or if admin  
     * provided in the constructor is empty
     */
    public function update(Bundle $bundle) : bool
    {
        $this->validateLoggedAdmin(); 
        $this->validateAuthorization(0, 1);
        $this->validateBundle($bundle);
        

        $response = false;
        $bindParams = array(
            $bundle->getName(),
            $bundle->getPrice()
        );
            
        // Query construction
        $query = "
            UPDATE bundles
            SET name = ?, price = ?
        ";
        
        if (!empty($bundle->getDescription())) {
            $query .= ", description = ?";
            $bindParams[] = $bundle->getDescription();
        }
        
        if (!empty($bundle->getLogo())) {
            $query .= ", logo = ?";
            $bindParams[] = $bundle->getLogo();
        }

        $query .= " WHERE id_bundle = ".$bundle->getId();
        
        // Prepares query
        $this->withQuery($query);
        
        // Executes query
        $sql->execute($bindParams);

        if (!empty($sql) && $sql->rowCount() > 0) {
            $response = true;
            $action = new Action();
            $adminsDAO = new AdminsDAO($this->db, Admin::getLoggedIn($this->db));
            $action->updateBundle($bundle->getId());
            $adminsDAO->newAction($action);
        }
        
        return $response;
    }
    
    /**
     * Removes a bundle.
     * 
     * @param       int $idBundle Bundle id
     * 
     * @return      bool If bundle has been successfully removed
     * 
     * @throws      IllegalAccessException If current admin does not have
     * authorization to remove bundles
     * @throws      \InvalidArgumentException If bundle id is empty, less than
     * or equal to zero or if admin id provided in the constructor is empty
     */
    public function remove($idBundle)
    {
        $this->validateLoggedAdmin(); 
        $this->validateAuthorization(0, 1);
        $this->validateBundleId($idBundle);
        $response = false;
        
        // Query construction
        $this->withQuery("
            DELETE FROM bundles
            WHERE id_bundle = ?
        ");
        
        // Executes query
        $sql->execute(array($idBundle));
        
        if (!empty($sql) && $sql->rowCount() > 0) {
            $response = true;
            $action = new Action();
            $adminsDAO = new AdminsDAO($this->db, Admin::getLoggedIn($this->db));
            $action->deleteBundle($idBundle);
            $adminsDAO->newAction($action);
        }
        
        return $response;
    }
    
    /**
     * Removes logo from a bundle.
     *
     * @param       int $idBundle Bundle id
     *
     * @return      bool If bundle logo has been successfully removed
     *
     * @throws      IllegalAccessException If current admin does not have
     * authorization to remove bundles
     * @throws      \InvalidArgumentException If bundle id is empty, less than
     * or equal to zero or if admin id provided in the constructor is empty
     */
    public function removeLogo(int $idBundle) : bool
    {
        $this->validateLoggedAdmin(); 
        $this->validateAuthorization(0, 1);
        $this->validateBundleId($idBundle);
        
        $response = false;
            
        // Query construction
        $sql = $this->db->query("
            UPDATE  bundles
            SET     logo = NULL
            WHERE   id_bundle = ".$idBundle
        );
        
        if (!empty($sql) && $sql->rowCount() > 0) {
            $response = true;
            $action = new Action();
            $adminsDAO = new AdminsDAO($this->db, Admin::getLoggedIn($this->db));
            $action->updateBundle($idBundle);
            $adminsDAO->newAction($action);
        }
        
        return $response;
    }
    
    /**
     * Adds a course to a bundle.
     * 
     * @param       int idBundle Bundle id
     * @param       int idCourse Course id
     * 
     * @return      bool If course has been successfully added to the bundle
     * 
     * @throws      IllegalAccessException If current admin does not have
     * authorization to update bundles
     * @throws      \InvalidArgumentException If bundle id, course id is empty,
     * less than or equal to zero or if admin id provided in the
     * constructor is empty
     */
    public function addCourse(int $idBundle, int $idCourse) : bool
    {
        $this->validateLoggedAdmin();
        $this->validateAuthorization(0, 1);
        $this->validateBundleId($idBundle);
        $this->validateCourseId($idCourse);
        $response = false;
            
        // Query construction
        $this->withQuery("
            INSERT INTO bundle_courses
            (id_bundle, id_course)
            VALUES (?, ?)
        ");
        
        // Executes query
        $sql->execute(array($idBundle, $idCourse));
        
        if (!empty($sql) && $sql->rowCount() > 0) {
            $response = true;
            $action = new Action();
            $adminsDAO = new AdminsDAO($this->db, Admin::getLoggedIn($this->db));
            $action->updateBundle($idBundle);
            $adminsDAO->newAction($action);
        }
        
        return $response;
    }

    private function validateCourseId($id)
    {
        if (empty($id) || $id <= 0) {
            throw new \InvalidArgumentException("Course id cannot be empty or ".
                                                "less than or equal to zero");
        }
    }
    
    /**
     * Removes a course from a bundle.
     * 
     * @param       int idBundle Bundle id
     * @param       int idCourse Course id
     * 
     * @return      bool If course has been successfully removed from the bundle
     * 
     * @throws      IllegalAccessException If current admin does not have
     * authorization to update bundles
     * @throws      \InvalidArgumentException If bundle id or course id  is 
     * empty, less than or equal to zero
     */
    public function deleteCourseFromBundle(int $idBundle, int $idCourse) : bool
    {
        $this->validateLoggedAdmin();  
        $this->validateAuthorization(0, 1);
        $this->validateBundleId($idBundle);
        $this->validateCourseId($idCourse);
        
        $response = false;
            
        // Query construction
        $this->withQuery("
            DELETE FROM bundle_courses
            WHERE id_bundle = ? AND id_course = ?
        ");
        
        // Executes query
        $sql->execute(array($idBundle, $idCourse));
        
        if (!empty($sql) && $sql->rowCount() > 0) {
            $response = true;
            $action = new Action();
            $adminsDAO = new AdminsDAO($this->db, Admin::getLoggedIn($this->db));
            $action->updateBundle($idBundle);
            $adminsDAO->newAction($action);
        }
        
        return $response;
    }
    
    /**
     * Removes all courses from a bundle.
     * 
     * @param       int idBundle Bundle id
     * 
     * @return      bool If all courses have been successfully removed from the 
     * bundle
     * 
     * @throws      IllegalAccessException If current admin does not have
     * authorization to update bundles
     * @throws      \InvalidArgumentException If bundle id is empty, less than 
     * or equal to zero or if admin id provided in the constructor is empty
     */
    public function deleteAllCourses(int $idBundle) : bool
    {
        $this->validateLoggedAdmin();
        $this->validateAuthorization(0, 1);
        $this->validateBundleId($idBundle);
                
        $response = false;
            
        $sql = $this->db->query("
            DELETE FROM bundle_courses
            WHERE id_bundle = ".$idBundle
        );
        
        if (!empty($sql) && $sql->rowCount() > 0) {
            $response = true;
            $action = new Action();
            $adminsDAO = new AdminsDAO($this->db, Admin::getLoggedIn($this->db));
            $action->updateBundle($idBundle);
            $adminsDAO->newAction($action);
        }
        
        return $response;
    }
    
    /**
     * Gets total of bundles.
     *
     * @return      int Total of bundles
     */
    public function count() : int
    {
        return (int)$this->db->query("
            SELECT  COUNT(*) AS total
            FROM    bundles
        ")->fetch()['total'];
    }
}