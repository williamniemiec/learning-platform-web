<?php
declare (strict_types=1);

namespace models\dao;


use database\Database;
use models\Bundle;
use models\enum\OrderDirectionEnum;
use models\enum\BundleOrderTypeEnum;


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
    
    
    //-------------------------------------------------------------------------
    //        Constructor
    //-------------------------------------------------------------------------
    /**
     * Creates 'bundles' table manager.
     *
     * @param       Database $db Database
     */
    public function __construct(Database $db)
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
     * @throws      \InvalidArgumentException If bundle id is empty or less 
     * than or equal to zero
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
        if (!empty($orderBy)) {
            $query .= " ORDER BY ".$orderBy->get()." ".$orderType->get();
        }
        
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
     * Gets bundles that contain at least all courses that the bundle with the
     * given id has, not including bundles that a student already has.
     * 
     *  @param      int $id_bundle Bundle id
     *  @param      int $id_student Student id 
     *  
     *  @return     Bundle[] Bundles that are contained in the given bundle 
     *  disregarding those that the student already has
     *  
     *  @throws      \InvalidArgumentException If bundle id or student id is 
     * empty or less than or equal to zero
     */
    public function extensionBundles(int $id_bundle, int $id_student) : array
    {
        if (empty($id_bundle) || $id_bundle <= 0)
            throw new \InvalidArgumentException("Bundle id cannot be empty ".
                "or less than or equal to zero");
        
        if (empty($id_student) || $id_student <= 0)
            throw new \InvalidArgumentException("Student id cannot be empty ".
                "or less than or equal to zero");
            
        $response = array();
        
        // Query construction
        $sql = $this->db->prepare("
            SELECT  *
            FROM    bundles b
            WHERE   id_bundle != ? AND
                    NOT EXISTS (
                SELECT  *
                FROM    bundle_courses JOIN purchases USING (id_bundle)
                WHERE   id_bundle = ? AND
                        id_student != ? AND
                        id_course NOT IN (SELECT    id_course
                                          FROM      bundle_courses
                                          WHERE     id_bundle = b.id_bundle)
            )
        ");
        
        // Executes query
        $sql->execute(array($id_bundle, $id_bundle, $id_student));
        
        // Parses results
        if ($sql && $sql->rowCount() > 0) {
            foreach ($sql->fetchAll() as $bundle) {
                $response[] = new Bundle(
                    $bundle['id_bundle'],
                    $bundle['name'],
                    $bundle['price'],
                    $bundle['description']
                );
            }
        }
        
        return $response;
    }
    
    
    /**
     * Gets bundles that do not contain any courses in common with a
     * supplied bundle, disregarding those that a student already has.
     * 
     * @param       int $id_bundle Bundle id
     * @param       int $id_student Student id
     * 
     * @return      Bundle[] Bundles that does not have courses contained in the
     * given bundle disregarding those that the student already has
     * 
     * @throws      \InvalidArgumentException If bundle id or student id is 
     * empty or less than or equal to zero
     */
    public function unrelatedBundles(int $id_bundle, int $id_student) : array
    {
        if (empty($id_bundle) || $id_bundle <= 0)
            throw new \InvalidArgumentException("Bundle id cannot be empty ".
                "or less than or equal to zero");
            
        if (empty($id_student) || $id_student <= 0)
            throw new \InvalidArgumentException("Student id cannot be empty ".
                "or less than or equal to zero");
        
        $response = array(); 
        
        // Query construction
        $sql = $this->db->prepare("
            SELECT  *
            FROM    bundles b
            WHERE   id_bundle != ? AND
                    NOT EXISTS (
                SELECT  *
                FROM    bundle_courses
                WHERE   id_bundle = ? AND
                        id_course IN (SELECT id_course
                                      FROM   bundle_courses
                                      WHERE  id_bundle = b.id_bundle AND
                                             id_bundle NOT IN (SELECT   id_bundle
                                                               FROM     purchases
                                                               WHERE    id_student = ?))
            )
        ");
        
        // Executes query
        $sql->execute(array($id_bundle ,$id_bundle, $id_student));
        
        // Parses results
        if ($sql && $sql->rowCount() > 0) {
            foreach ($sql->fetchAll() as $bundle) {
                $response[] = new Bundle(
                    $bundle['id_bundle'], 
                    $bundle['name'], 
                    $bundle['price'],
                    $bundle['description']
                );
                
            }
        }
            
        return $response;
    }
    
    /**
     * Gets the total number of classes that a bundle has along with its 
     * duration (in minutes).
     * 
     * @param       int $id_bundle Bundle id
     * 
     * @return      array Total of classes that the bundle has along with its 
     * duration (in minutes). The returned array has the following keys:
     * <ul>
     *  <li><b>total_classes</b>: Total of classes that the bundle has</li>
     *  <li><b>total_length</b>: Total duration of the classes that the bundle
     *  has</li>
     * </ul>
     * 
     * @throws      \InvalidArgumentException If bundle id is empty or less 
     * than or equal to zero
     * 
     * @implSpec    It will always return an array with the two keys informed
     * above, even if both have zero value
     */
    public function countTotalClasses(int $id_bundle) : array
    {
        if (empty($id_bundle) || $id_bundle <= 0)
            throw new \InvalidArgumentException("Bundle id cannot be empty ".
                "or less than or equal to zero");
            
        $response = array(
            "total_classes" => 0,
            "total_length" => 0
        );
        
        // Query construction
        $sql = $this->db->prepare("
            SELECT      COUNT(id_module) AS total_classes, 
                        SUM(length) AS total_length
            FROM        (SELECT      id_module, 5 AS length
                         FROM        questionnaires
                         UNION ALL
                         SELECT      id_module, length
                         FROM        videos) AS tmp
            GROUP BY    id_module
            HAVING      id_module IN (SELECT    id_module
                                      FROM      course_modules NATURAL JOIN bundle_courses
                                      WHERE     id_bundle = ?)
        ");
        
        // Executes query
        $sql->execute(array($id_bundle));
        
        // Parses results
        if ($sql && $sql->rowCount() > 0) {
            foreach ($sql->fetchAll() as $result) {
                $response["total_classes"] += $result["total_classes"];
                $response["total_length"] += $result["total_length"];
            }
        }
        
        return $response;
    }
}