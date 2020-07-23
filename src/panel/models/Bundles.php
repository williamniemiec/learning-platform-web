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
    
    public function getAll($limit = -1, $orderBy = '', $orderType = '')
    {
        $response = array();
        $query = "
            SELECT      id_bundle, name, price, description,
                        COUNT(id_course) as total_courses
            FROM        bundles NATURAL JOIN bundle_courses
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
        
        if ($limit > 0)
            $query .= " LIMIT ".$limit;
        
        $sql = $this->db->query($query);
        
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
    
    public function new($name, $price, $description = "")
    {
        $query = "
            INSERT INTO bundles
            SET name = ?, price = ?
        ";
        
        if (!empty($description)) {
            $query .= ", description = ?";
        }
        
        $sql = $this->db->prepare($query);
        
        if (!empty($description)) {
            $sql->execute(array($name, $price, $description));
        }
        else {
            $sql->execute(array($name, $price));
        }
        
        return $sql->rowCount() > 0;
    }
    
    public function edit(Bundle $bundle)
    {
        $query = "
            UPDATE bundles
            SET name = ?, price = ?
        ";
        
        if (!empty($bundle->getDescription())) {
            $query .= ", description = ?";
        }
        
        $sql = $this->db->prepare($query);
        
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
        
    }
    
    public function delete($id_bundle)
    {
        $sql = $this->db->prepare("
            DELETE FROM bundles
            WHERE id_bundle = ?
        ");
        
        $sql->execute(array($id_bundle));
    }
    
    public function addCourse($id_bundle, $id_course)
    {
        $sql = $this->db->prepare("
            INSERT INTO bundle_courses
            (id_bundle, id_course)
            VALUES (?, ?)
        ");
        
        $sql->execute(array($id_bundle, $id_course));
        
        return $sql->rowCount() > 0;
    }
    
    public function deleteCourseFromBundle($id_bundle, $id_course)
    {
        $sql = $this->db->prepare("
            DELETE FROM bundle_courses
            WHERE id_bundle = ? AND id_course = ?
        ");
        
        $sql->execute(array($id_bundle, $id_course));
        
        return $sql->rowCount() > 0;
    }
}