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
        $this->id_admin = $id_admin;
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
                (int)$bundle['id_bundle'],
                $bundle['name'],
                (float)$bundle['price'],
                $bundle['logo'],
                $bundle['description']
            );
        }
        
        return $response;
    }
    
    /**
     * Gets all registered bundles. If a filter option is provided, it gets 
     * only those bundles that satisfy these filters.
     * 
     * @param       int $limit [Optional] Maximum bundles returned
     * @param       string $name [Optional] Bundle name
     * @param       BundleOrderTypeEnum $orderBy [Optional] Ordering criteria 
     * @param       OrderDirectionEnum $orderType [Optional] Order that the 
     * elements will be returned. Default is ascending
     * 
     * @return      Bundle[] Bundles with the provided filters or empty array if
     * no bundles are found.
     */
    public function getAll(int $limit = -1, string $name = '', 
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
        if ($limit > 0) 
            $query .= " LIMIT ".$limit;
        
        // Prepares query
        $sql = $this->db->prepare($query);

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
     * @throws      \InvalidArgumentException If bundle is empty or if admin id
     * provided in the constructor is empty, less than or equal to zero
     */
    public function new(Bundle $bundle) : bool
    {
        if (empty($this->id_admin) || $this->id_admin <= 0)
            throw new \InvalidArgumentException("Admin id logged in must be ".
                "provided in the constructor");
            
        if ($this->getAuthorization()->getLevel() != 0 && 
            $this->getAuthorization()->getLevel() != 1)
            throw new IllegalAccessException("Current admin does not have ".
                "authorization to perform this action");
        
        if (empty($bundle))
            throw new \InvalidArgumentException("Bundle cannot be empty");
        
        $bindParams = array(
            'name' => $bundle->getName(),
            'price' => $bundle->getPrice()
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
        $sql = $this->db->prepare($query);
        
        // Executes query
        $sql->execute($bindParams);
        
        return !empty($sql) && $sql->rowCount() > 0;
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
            $this->getAuthorization()->getLevel() != 1)
            throw new IllegalAccessException("Current admin does not have ".
                "authorization to perform this action");
            
        if (empty($bundle))
            throw new \InvalidArgumentException("Bundle cannot be empty");

        $bindParams = array(
            'name' => $bundle->getName(),
            'price' => $bundle->getPrice()
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
        
        // Prepares query
        $sql = $this->db->prepare($query);
        
        // Executes query
        $sql->execute($bindParams);

        return !empty($sql) && $sql->rowCount() > 0;
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
            $this->getAuthorization()->getLevel() != 1)
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
            $this->getAuthorization()->getLevel() != 1)
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
            $this->getAuthorization()->getLevel() != 1)
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