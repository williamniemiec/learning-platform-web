<?php
declare (strict_types=1);

namespace models\dao;

use database\Database;


/**
 * Responsible for representing classes.
 *
 * @author		William Niemiec &lt; williamniemiec@hotmail.com &gt;
 * @version		1.0.0
 * @since		1.0.0
 */
abstract class ClassesDAO
{
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
                SELECT  COUNT(*) AS total, length
                FROM    videos
                UNION
                SELECT  COUNT(*) AS total, 5 AS length
                FROM questionnaires
            ) AS tmp
        ")->fetch();
    }
}