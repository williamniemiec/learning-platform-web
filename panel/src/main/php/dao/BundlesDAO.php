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
        $this->withQuery("
            SELECT  *
            FROM    bundles
            WHERE   id_bundle = ?
        ");
        $this->runQueryWithArguments($idBundle);
        
        return $this->parseGetResponseQuery();
    }

    private function validateBundleId($id)
    {
        if (empty($id) || $id <= 0) {
            throw new \InvalidArgumentException("Bundle id cannot be empty or ".
                                                "less than or equal to zero");
        }
    }

    private function parseGetResponseQuery()
    {
        if (!$this->hasResponseQuery()) {
            return null;
        }

        $bundleRaw = $this->getResponseQuery();
        
        return new Bundle(
            (int) $bundleRaw['id_bundle'], 
            $bundleRaw['name'], 
            (float) $bundleRaw['price'],
            $bundleRaw['logo'],
            $bundleRaw['description']
        );
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
    public function getAll(
        int $limit = -1, 
        int $offset = -1, 
        string $name = '', 
        BundleOrderTypeEnum $orderBy = null, 
        OrderDirectionEnum $orderType = null
    ) : array
    {
        $type = $orderType;

        if (empty($orderType)) {
            $type = new OrderDirectionEnum(OrderDirectionEnum::ASCENDING);
        }

        $this->withQuery($this->buildGetAllQuery($name, $orderBy, $type, $limit, $offset));
        $this->runQueryWithArguments($this->buildGetAllQueryArguments($name));

        return $this->parseGetAllResponseQuery();
    }

    private function buildGetAllQuery($name, $orderBy, $type, $limit, $offset)
    {
        $query = "
            SELECT      id_bundle, name, bundles.price, logo, description,
                        COUNT(id_course) AS courses,
                        COUNT(id_student) AS sales
            FROM        bundles 
                        NATURAL LEFT JOIN bundle_courses
                        LEFT JOIN purchases USING (id_bundle)
            GROUP BY    id_bundle, name, bundles.price, description
        ";
        
        if (!empty($name)) {
            $query .= " HAVING name LIKE ?";
        }
        
        $query .= " ORDER BY ".$orderBy->get()." ".$type->get();

        if ($limit > 0) {
            if ($offset > 0) {
                $query .= " LIMIT ".$offset.",".$limit;
            }
            else {
                $query .= " LIMIT ".$limit;
            }
        }

        return $query;
    }

    private function buildGetAllQueryArguments($name)
    {
        $bindParams = array();
        
        if (!empty($name)) {
            $bindParams[] = $name.'%';
        }

        return $bindParams;
    }

    private function parseGetAllResponseQuery()
    {
        if (!$this->hasResponseQuery()) {
            return array();
        }

        $bundles = array();
        $i = 0;
            
        foreach ($this->getAllResponseQuery() as $bundle) {
            $bundles[$i]['bundle'] = new Bundle(
                (int) $bundle['id_bundle'],
                $bundle['name'],
                (float) $bundle['price'],
                $bundle['logo'],
                $bundle['description']
            );
            $bundles[$i]->setTotalStudents((int) $bundle['sales']);
            $i++;
        }

        return $bundles;
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
        $this->withQuery($this->buildNewQuery($bundle));
        $this->runQueryWithArguments($this->buildChangeQueryArguments($bundle));

        return $this->parseNewResponseQuery();
    }

    private function validateBundle($bundle)
    {
        if (empty($bundle)) {
            throw new \InvalidArgumentException("Bundle cannot be empty");
        }
    }

    private function buildNewQuery($bundle)
    {
        $query = "
            INSERT INTO bundles
            SET name = ?, price = ?
        ";
        
        if (!empty($bundle->getDescription())) {
            $query .= ", description = ?";
        }
        
        if (!empty($bundle->getLogo())) {
            $query .= ", logo = ?";
        }

        return $query;
    }

    private function buildChangeQueryArguments($bundle)
    {
        $bindParams = array(
            $bundle->getName(),
            $bundle->getPrice()
        );
        
        if (!empty($bundle->getDescription())) {
            $bindParams[] = $bundle->getDescription();
        }
        
        if (!empty($bundle->getLogo())) {
            $bindParams[] = $bundle->getLogo();
        }

        return $bindParams;
    }

    private function parseNewResponseQuery()
    {
        if (!$this->hasResponseQuery()) {
            return false;
        }

        $action = new Action();
        $action->addBundle($this->db->lastInsertId());
        $adminsDao = new AdminsDAO($this->db, Admin::getLoggedIn($this->db));
        $adminsDao->newAction($action);
        
        return true;
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
        $this->withQuery($this->buildUpdateQuery($bundle));
        $this->runQueryWithArguments($this->buildChangeQueryArguments($bundle));
        
        return $this->parseUpdateResponseQuery($bundle->getId());
    }

    private function buildUpdateQuery($bundle)
    {
        $query = "
            UPDATE bundles
            SET name = ?, price = ?
        ";
        
        if (!empty($bundle->getDescription())) {
            $query .= ", description = ?";
        }
        
        if (!empty($bundle->getLogo())) {
            $query .= ", logo = ?";
        }

        $query .= " WHERE id_bundle = ".$bundle->getId();

        return $query;
    }
    
    private function parseUpdateResponseQuery($bundleId)
    {
        if (!$this->hasResponseQuery()) {
            return false;
        }

        $action = new Action();
        $action->updateBundle($bundleId);
        $adminsDao = new AdminsDAO($this->db, Admin::getLoggedIn($this->db));
        $adminsDao->newAction($action);
        
        return true;
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
        $this->withQuery("
            DELETE FROM bundles
            WHERE id_bundle = ?
        ");
        $this->runQueryWithArguments($idBundle);
        
        return $this->parseDeleteResponseQuery($idBundle);
    }

    private function parseDeleteResponseQuery($bundleId)
    {
        if (!$this->hasResponseQuery()) {
            return false;
        }

        $action = new Action();
        $action->deleteBundle($bundleId);
        $adminsDao = new AdminsDAO($this->db, Admin::getLoggedIn($this->db));
        $adminsDao->newAction($action);
        
        return true;
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
        $this->withQuery("
            UPDATE  bundles
            SET     logo = NULL
            WHERE   id_bundle = ".$idBundle
        );
        
        return $this->parseUpdateResponseQuery($idBundle);
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
        $this->withQuery("
            INSERT INTO bundle_courses
            (id_bundle, id_course)
            VALUES (?, ?)
        ");
        $this->runQueryWithArguments($idBundle, $idCourse);
        
        return $this->parseUpdateResponseQuery($idBundle);
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
        $this->withQuery("
            DELETE FROM bundle_courses
            WHERE id_bundle = ? AND id_course = ?
        ");
        $this->runQueryWithArguments($idBundle, $idCourse);
        
        return $this->parseUpdateResponseQuery($idBundle);
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
        $this->withQuery("
            DELETE FROM bundle_courses
            WHERE id_bundle = ".$idBundle
        );
        
        return $this->parseUpdateResponseQuery($idBundle);
    }
    
    /**
     * Gets total of bundles.
     *
     * @return      int Total of bundles
     */
    public function count() : int
    {
        $this->withQuery("
            SELECT  COUNT(*) AS total
            FROM    bundles
        ");
        $this->runQueryWithoutArguments();

        return ((int) $this->getResponseQuery()['total']);
    }
}