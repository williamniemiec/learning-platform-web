<?php
namespace models;

use core\Model;
use models\obj\Bundle;



/**
 * Responsible for managing bundles table.
 *
 * @author		William Niemiec &lt; williamniemiec@hotmail.com &gt;
 * @version		1.0
 * @since		1.0
 */
class Bundles extends Model
{
    //-------------------------------------------------------------------------
    //        Constructor
    //-------------------------------------------------------------------------
    /**
     * Creates bundles table manager.
     *
     * @apiNote     It will connect to the database when it is instantiated
     */
    public function __construct()
    {
        parent::__construct();
    }
    
    
    //-------------------------------------------------------------------------
    //        Methods
    //-------------------------------------------------------------------------
    public function get($id_bundle)
    {
        $response = null;
        
        $sql = $this->db->prepare("
            SELECT  *
            FROM    bundles
            WHERE   id_bundle = ?
        ");
        
        $sql->execute(array($id_bundle));
        
        if ($sql->rowCount() > 0) {
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
    
    public function getAll($id_student, $limit = -1, $name = '', $orderBy = '', $orderType = '')
    {
        $response = array();
//         $query = "
//             SELECT  *
//             FROM    bundles
//         ";
        
        $query = "
            SELECT      id_bundle, name, price, description,
                        COUNT(id_course) as total_courses,
                        COUNT(id_student) as has_bundle
            FROM        bundles NATURAL JOIN bundle_courses
                        NATURAL JOIN purchases
            WHERE       id_student = ?
            GROUP BY    id_bundle, name, price, description
        ";
        
        if (!empty($orderBy)) {
            $orderType = empty($orderType) ? '' : $orderType;
            if ($orderBy == 'price') {
                $query .= " ORDER BY price ".$orderType;
            }
            else if ($orderBy == 'courses') {
                $query .= " ORDER BY total_courses ".$orderType;
            }
        }
        
        if (!empty($name)) {
            
            $query .= empty($orderBy) ? " WHERE name LIKE ?" : " HAVING name LIKE ?";
        }
        
        if ($limit > 0)
            $query .= " LIMIT ".$limit;
        
        $sql = $this->db->prepare($query);
        
        if (!empty($name)) {
            $sql->execute(array($id_student, $name.'%'));
        }
        else {
            $sql->execute(array($id_student));
        }
        
        if ($sql->rowCount() > 0) {
            $bundles = $sql->fetchAll();
            
            foreach ($bundles as $bundle) {
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
    
    /** !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
     * Gets bundles that contain at least all courses that the supplied bundle
     * has, not including bundles that the student already has. 
     */
    public function extensionBundles($id_bundle, $id_student)
    {
        $response = array();
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
        
        $sql->execute(array($id_bundle, $id_bundle, $id_student));
        
        if ($sql->rowCount() > 0) {
            foreach ($sql->fetchAll(\PDO::FETCH_ASSOC) as $bundle) {
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
    
    
    /** !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
     * Gets bundles that do not contain any courses in common with the
     * supplied bundle. The bundles that he student already has are
     * disregarded.
     */
    public function unrelatedBundles($id_bundle, $id_student) 
    {
        $response = array(); 
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
        
        $sql->execute(array($id_bundle ,$id_bundle, $id_student));
        
        if ($sql->rowCount() > 0) {
            foreach ($sql->fetchAll(\PDO::FETCH_ASSOC) as $bundle) {
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
     * Gets total of classes from a bundle along with its duration (in minutes)
     * 
     * @param unknown $id_bundle
     * @return number
     */
    public function getTotalClasses($id_bundle)
    {
        $response = array(
            "total_classes" => 0,
            "total_length" => 0
        );
        
        $sql = $this->db->prepare("
            SELECT      SUM(total_classes) as total_classes, SUM(total_min) as total_length
            FROM        (SELECT      id_module, COUNT(*) AS total_classes, 5 AS total_min
                         FROM        questionnaires
                         GROUP BY    id_module
                         UNION ALL
                         SELECT      id_module, COUNT(*) AS total_classes, SUM(length) AS total_min
                         FROM        videos
                         GROUP BY    id_module) AS tmp
            GROUP BY    id_module
            HAVING      id_module IN (SELECT    id_module
                                      FROM      course_modules NATURAL JOIN bundle_courses
                                      WHERE     id_bundle = ?)
        ");
        
        $sql->execute(array($id_bundle));
        
        if ($sql->rowCount() > 0) {
            foreach ($sql->fetchAll(\PDO::FETCH_ASSOC) as $result) {
                $response["total_classes"] += $result["total_classes"];
                $response["total_length"] += $result["total_length"];
            }
        }
        
        return $response;
    }
}