<?php
declare (strict_types=1);

namespace models\dao;


use database\Database;
use models\enum\OrderDirectionEnum;
use models\Bundle;
use models\enum\BundleOrderTypeEnum;
use models\util\IllegalAccessException;


/**
 * Responsible for managing 'bundles' table.
 *
 * @author		William Niemiec &lt; williamniemiec@hotmail.com &gt;
 * @version		1.0.0
 * @since		1.0.0
 */
class BundlesDAO
{
    //-------------------------------------------------------------------------
    //        Attributes
    //-------------------------------------------------------------------------
    private $db;
    private $id_admin;
    
    
    //-------------------------------------------------------------------------
    //        Constructor
    //-------------------------------------------------------------------------
    /**
     * Creates 'bundles' table manager.
     *
     * @param       Database $db Database
     * @param       int $id_admin [Optional] Admin id logged in
     */
    public function __construct(Database $db, int $id_admin = -1)
    {
        $this->db = $db->getConnection();
    }
    
    
    //-------------------------------------------------------------------------
    //        Methods
    //-------------------------------------------------------------------------
    /**
     * Gets a bundle
     *
     * @param       int $id_bundle Bundle id or null if there is no bundle with
     * the given id
     *
     * @return      Bundle Bundle with the given id
     *
     * @throws      \InvalidArgumentException If bundle id is empty, less than
     * or equal to zero
     */
    public function get(int $id_bundle) : Bundle
    {
        if (empty($id_bundle) || $id_bundle <= 0)
            throw new \InvalidArgumentException("Bundle id cannot be empty ".
                "or less than or equal to zero");
            
        $response = null;
        
        // Query construction
        $sql = $this->db->prepare("
            SELECT  *
            FROM    bundles
            WHERE   id_bundle = ?
        ");
            
        // Executes query
        $sql->execute(array($id_bundle));
        
        // Parses results
        if ($sql && $sql->rowCount() > 0) {
            $bundle = $sql->fetch();
            $response = new Bundle(
                $bundle['id_bundle'],
                $bundle['name'],
                $bundle['price'],
                $bundle['description']
            );
        }
        
        return $response;
    }
    
    /**
     * Gets all registered bundles. If a filter option is provided, it gets
     * only those bundles that satisfy these filters.
     *
     * @param       int $id_student [Optional] Student id
     * @param       int $limit [Optional] Maximum bundles returned
     * @param       string $name [Optional] Bundle name
     * @param       BundleOrderTypeEnum $orderBy [Optional] Ordering criteria
     * @param       OrderDirectionEnum $orderType [Optional] Order that the
     * elements will be returned. Default is ascending.
     *
     * @return      array Bundles with the provided filters or empty array if
     * no bundles are found. If a student id is provided, also returns, for
     * each bundle, if this student has it. Each position of the returned array
     * has the following keys:
     * <ul>
     *  <li><b>bundle</b>: Bundle information</li>
     *  <li><b>has_bundle</b>: If the student with the given id has this
     *  bundle</li>
     * </ul>
     */
    public function getAll(int $id_student = -1, int $limit = -1, string $name = '',
        BundleOrderTypeEnum $orderBy = null, OrderDirectionEnum $orderType = null) : array
    {
        $response = array();
        
        if (empty($orderType))
            $orderType = new OrderDirectionEnum(OrderDirectionEnum::ASCENDING);
            
        // Query construction
        $query = "
            SELECT      id_bundle, name, price, description,
                        COUNT(id_course) AS total_courses,
        ";
        
        // If a student was provided, for each bundle add the information if he
        // has the bundle or not
        if ($id_student > 0) {
            $query .= "
                CASE
                    WHEN id_student = ? THEN 1
                    ELSE 0
                END AS has_bundle,
            ";
        }
        
        $query .= "
                        COUNT(id_student) as total_students
            FROM        bundles
                        NATURAL JOIN bundle_courses
                        NATURAL JOIN purchases
            GROUP BY    id_bundle, name, price, description
        ";
        
        // Sets order by criteria (if any)
        if (!empty($orderBy))
            $query .= " ORDER BY ".$orderBy->get()." ".$orderType->get();
        
        // Limits the search to a specified name (if a name was specified)
        if (!empty($name))
            $query .= empty($orderBy) ? " HAVING name LIKE ?" : " HAVING name LIKE ?";
            
        // Limits the results (if a limit was given)
        if ($limit > 0)
            $query .= " LIMIT ".$limit;
            
        // Prepares query
        $sql = $this->db->prepare($query);
        
        // Executes query
        if (!empty($name))
            $sql->execute(array($id_student, $name.'%'));
        else
            $sql->execute(array($id_student));
            
        // Parses results
        if ($sql && $sql->rowCount() > 0) {
            $bundles = $sql->fetchAll();
            $i = 0;
            
            foreach ($bundles as $bundle) {
                $response[$i]['bundle'] = new Bundle(
                    $bundle['id_bundle'],
                    $bundle['name'],
                    $bundle['price'],
                    $bundle['description']
                );
                
                if ($id_student > 0)
                    $response[$i]['has_bundle'] = $bundle['has_bundle'] > 0;
            }
        }
            
        return $response;
    }
    
    /**
     * Creates a new bundle.
     * 
     * @param       string $name Bundle name
     * @param       float $price Bundle price
     * @param       string $description Bundle description
     * 
     * @return      bool If bundle has been successfully added
     * 
     * @throws      IllegalAccessException If current admin does not have
     * authorization to create bundles
     * @throws      \InvalidArgumentException If name or price is empty or if 
     * price or admin id provided in the constructor is empty, less than or 
     * equal to zero
     */
    public function new(int $name, float $price, string $description = "") : bool
    {
        if (empty($this->id_admin) || $this->id_admin <= 0)
            throw new \InvalidArgumentException("Admin id logged in must be ".
                "provided in the constructor");
            
        if ($this->getAuthorization()->getLevel() != 0 && 
            $this->getAuthorization()->getLevel() != 2)
            throw new IllegalAccessException("Current admin does not have ".
                "authorization to perform this action");
        
        if (empty($name))
            throw new \InvalidArgumentException("Name cannot be empty");
        
        if (empty($price) || $price < 0)
            throw new \InvalidArgumentException("Invalid price");
            
        // Query construction
        $query = "
            INSERT INTO bundles
            SET name = ?, price = ?
        ";
        
        if (!empty($description)) {
            $query .= ", description = ?";
        }
        
        // Prepares query
        $sql = $this->db->prepare($query);
        
        // Executes query
        if (!empty($description)) {
            $sql->execute(array($name, $price, $description));
        }
        else {
            $sql->execute(array($name, $price));
        }
        
        return $sql->rowCount() > 0;
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
     * @throws      \InvalidArgumentException If bundle is empty or admin id 
     * provided in the constructor is empty, less than or equal to zero
     */
    public function update(Bundle $bundle) : bool
    {
        if (empty($this->id_admin) || $this->id_admin <= 0)
            throw new \InvalidArgumentException("Admin id logged in must be ".
                "provided in the constructor");
            
        if ($this->getAuthorization()->getLevel() != 0 &&
            $this->getAuthorization()->getLevel() != 2)
            throw new IllegalAccessException("Current admin does not have ".
                "authorization to perform this action");
            
        if (empty($bundle))
            throw new \InvalidArgumentException("Bundle cannot be empty");

        // Query construction
        $query = "
            UPDATE bundles
            SET name = ?, price = ?
        ";
        
        if (!empty($bundle->getDescription())) {
            $query .= ", description = ?";
        }
        
        // Prepares query
        $sql = $this->db->prepare($query);
        
        // Executes query
        if (!empty($bundle->getDescription())) {
            $sql->execute(array(
                $bundle->getName(), 
                $bundle->getPrice(), 
                $bundle->getDescription()
            ));
        }
        else {
            $sql->execute(array(
                $bundle->getName(),
                $bundle->getPrice()
            ));
        }
        
        return $sql->rowCount() > 0;
    }
    
    /**
     * Removes a bundle.
     * 
     * @param       int $id_bundle Bundle id
     * 
     * @return      bool If bundle has been successfully removed
     * 
     * @throws      IllegalAccessException If current admin does not have
     * authorization to remove bundles
     * @throws      \InvalidArgumentException If bundle id is empty or admin id 
     * provided in the constructor is empty, less than or equal to zero
     */
    public function remove($id_bundle)
    {
        if (empty($this->id_admin) || $this->id_admin <= 0)
            throw new \InvalidArgumentException("Admin id logged in must be ".
                "provided in the constructor");
            
        if ($this->getAuthorization()->getLevel() != 0 &&
            $this->getAuthorization()->getLevel() != 2)
            throw new IllegalAccessException("Current admin does not have ".
                "authorization to perform this action");
            
        if (empty($id_bundle) || $id_bundle <= 0)
                throw new \InvalidArgumentException("Bundle id cannot be empty ".
                    "or less than or equal to zero");
        
        // Query construction
        $sql = $this->db->prepare("
            DELETE FROM bundles
            WHERE id_bundle = ?
        ");
        
        // Executes query
        $sql->execute(array($id_bundle));
        
        return $sql->rowCount() > 0;
    }
    
    /**
     * Adds a course to a bundle.
     * 
     * @param       int $id_bundle Bundle id
     * @param       int $id_course Course id
     * 
     * @return      bool If course has been successfully added to the bundle
     * 
     * @throws      IllegalAccessException If current admin does not have
     * authorization to update bundles
     * @throws      \InvalidArgumentException If bundle id, course id or admin
     * id provided in the constructor is empty, less than or equal to zero
     */
    public function addCourse(int $id_bundle, int $id_course) : bool
    {
        if (empty($this->id_admin) || $this->id_admin <= 0)
            throw new \InvalidArgumentException("Admin id logged in must be ".
                "provided in the constructor");
            
        if ($this->getAuthorization()->getLevel() != 0 &&
            $this->getAuthorization()->getLevel() != 2)
            throw new IllegalAccessException("Current admin does not have ".
                "authorization to perform this action");
            
        if (empty($id_bundle) || $id_bundle <= 0)
            throw new \InvalidArgumentException("Bundle id cannot be empty ".
                "or less than or equal to zero");
        
        if (empty($id_course) || $id_course <= 0)
            throw new \InvalidArgumentException("Course id cannot be empty ".
                "or less than or equal to zero");
                
        // Query construction
        $sql = $this->db->prepare("
            INSERT INTO bundle_courses
            (id_bundle, id_course)
            VALUES (?, ?)
        ");
        
        // Executes query
        $sql->execute(array($id_bundle, $id_course));
        
        return $sql->rowCount() > 0;
    }
    
    /**
     * Removes a course from a bundle.
     * 
     * @param       int $id_bundle Bundle id
     * @param       int $id_course Course id
     * 
     * @return      bool If course has been successfully removed from the bundle
     * 
     * @throws      IllegalAccessException If current admin does not have
     * authorization to update bundles
     * @throws      \InvalidArgumentException If bundle id, course id or admin
     * id provided in the constructor is empty, less than or equal to zero
     */
    public function deleteCourseFromBundle(int $id_bundle, int $id_course) : bool
    {
        if (empty($this->id_admin) || $this->id_admin <= 0)
            throw new \InvalidArgumentException("Admin id logged in must be ".
                "provided in the constructor");
            
        if ($this->getAuthorization()->getLevel() != 0 &&
            $this->getAuthorization()->getLevel() != 2)
            throw new IllegalAccessException("Current admin does not have ".
                "authorization to perform this action");
            
        if (empty($id_bundle) || $id_bundle <= 0)
            throw new \InvalidArgumentException("Bundle id cannot be empty ".
                "or less than or equal to zero");
            
        if (empty($id_course) || $id_course <= 0)
            throw new \InvalidArgumentException("Course id cannot be empty ".
                "or less than or equal to zero");
        
        // Query construction
        $sql = $this->db->prepare("
            DELETE FROM bundle_courses
            WHERE id_bundle = ? AND id_course = ?
        ");
        
        // Executes query
        $sql->execute(array($id_bundle, $id_course));
        
        return $sql->rowCount() > 0;
    }
}