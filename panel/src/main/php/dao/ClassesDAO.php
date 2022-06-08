<?php
declare (strict_types=1);

namespace panel\dao;


use panel\repositories\Database;


/**
 * Responsible for representing classes.
 */
abstract class ClassesDAO extends DAO
{
    //-------------------------------------------------------------------------
    //        Constructor
    //-------------------------------------------------------------------------
    protected function __construct(Database $db)
    {
        parent::__construct($db);
    }


    //-------------------------------------------------------------------------
    //        Methods
    //-------------------------------------------------------------------------
    /**
     * Gets all classes from a module.
     *
     * @param       int $id_module Module id
     *
     * @return      array Classes that belongs to the module
     *
     * @throws      \InvalidArgumentException If module id is empty, less than
     * or equal to zero
     */
    public abstract function getAllFromModule(int $id_module) : array;
    
    /**
     * Gets total of classes.
     *
     * @param       Database $db Database
     *
     * @return      array Total of classes and length. The returned array has
     * the following keys:
     * <ul>
     *  <li>total_classes</li>
     *  <li>total_length</li>
     * </ul>
     */
    public static function getTotal(Database $db) : array
    {
        return $db->getConnection()->query("
            SELECT  SUM(total) AS total_classes, 
                    SUM(length) AS total_length 
            FROM (
                SELECT  COUNT(*) AS total, SUM(length) as length
                FROM    videos
                UNION
                SELECT  COUNT(*) AS total, SUM(5) AS length
                FROM questionnaires
            ) AS tmp
        ")->fetch();
    }

    protected function validateModuleId($id)
    {
        if (empty($id) || $id <= 0) {
            throw new \InvalidArgumentException("Module id cannot be empty or ".
                                                "less than or equal to zero");
        }
    }

    protected function validateClassOrder($order)
    {
        if (empty($order) || $order <= 0) {
            throw new \InvalidArgumentException("Class order cannot be empty ".
                                                "or less than or equal to zero");
        }
    }
}