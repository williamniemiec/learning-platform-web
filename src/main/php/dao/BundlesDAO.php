<?php
declare (strict_types=1);

namespace dao;


use repositories\Database;
use domain\Bundle;
use domain\enum\OrderDirectionEnum;
use domain\enum\BundleOrderTypeEnum;


/**
 * Responsible for managing 'bundles' table.
 */
class BundlesDAO extends DAO
{
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
        parent::__construct($db);
    }
    
    
    //-------------------------------------------------------------------------
    //        Methods
    //-------------------------------------------------------------------------
    /**
     * Gets a bundle
     * 
     * @param       int idBundle Bundle id or null if there is no bundle with
     * the given id
     * 
     * @return      Bundle Bundle with the given id
     * 
     * @throws      \InvalidArgumentException If bundle id is empty or less 
     * than or equal to zero
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
            throw new \InvalidArgumentException("Bundle id cannot be empty or".
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
     * @param       int idStudent [Optional] Student id 
     * @param       int $limit [Optional] Maximum bundles returned
     * @param       string $name [Optional] Bundle name
     * @param       BundleOrderTypeEnum order_by [Optional] Ordering criteria 
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
    public function getAll(
        int $idStudent = -1, 
        int $limit = -1, 
        string $name = '',
        BundleOrderTypeEnum $orderBy = null, 
        OrderDirectionEnum $orderType = null
    ) : array
    {
        $type = $orderType;

        if (empty($type)) {
            $type = new OrderDirectionEnum(OrderDirectionEnum::ASCENDING);
        }

        $this->withQuery($this->buildGetAllQuery($idStudent, $limit, $name, $orderBy, $type));
        $this->runQueryWithArguments($this->buildGetAllQueryArguments($idStudent, $name));

        return $this->parseGetAllResponseQuery($idStudent);
    }

    private function buildGetAllQuery($idStudent, $limit, $name, $orderBy, $type)
    {
        $query = "
            SELECT      id_bundle, name, bundles.price, logo, description,
                        COUNT(id_course) AS courses,
        ";
        
        if ($idStudent > 0) {
            $query .= "
                        CASE
                            WHEN id_student = ? THEN 1
                            ELSE 0
                        END AS has_bundle,
            ";
        }
        
        $query .= "
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
            $query .= " LIMIT ".$limit;
        }

        return $query;
    }

    private function buildGetAllQueryArguments($idStudent, $name)
    {
        $bindParams = array();

        if ($idStudent > 0) {
            $bindParams[] = $idStudent;
        }
        
        if (!empty($name)) {
            $bindParams[] = $name.'%';
        }

        return $bindParams;
    }

    private function parseGetAllResponseQuery($idStudent)
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
            
            if ($idStudent > 0) {
                $bundles[$i]['has_bundle'] = ($bundle['has_bundle'] > 0);
            }

            $i++;
        }

        return $bundles;
    }
    
    /**
     * Gets bundles that contain at least all courses that the bundle with the
     * given id has, not including bundles that a student already has (if 
     * provided).
     * 
     *  @param      int idBundle Bundle id
     *  @param      int idStudent [Optional] Student id 
     *  
     *  @return     Bundle[] Bundles that are contained in the given bundle 
     *  disregarding those that the student already has
     *  
     *  @throws      \InvalidArgumentException If bundle id is empty or less 
     *  than or equal to zero
     */
    public function extensionBundles(int $idBundle, int $idStudent = -1) : array
    {
        $this->validateBundleId($idBundle);
        $this->withQuery($this->buildExtensionBundlesQuery($idStudent));
        $this->runQueryWithArguments($this->buildBundlesQueryArguments($idStudent, $idBundle));
        
        return $this->parseBundlesResponseQuery();
    }

    private function buildExtensionBundlesQuery($idStudent)
    {
        $query = "
            SELECT  b.id_bundle, b.name, b.price, b.logo, b.description
            FROM    bundles b
                    LEFT JOIN purchases USING (id_bundle)
            WHERE   id_bundle != ? AND
        ";
        
        if ($idStudent > 0) {
            $query .= " 
                    (id_student IS NULL OR id_student != ?) AND 
            ";
        }
        
        $query .= "
                    NOT EXISTS (
                        SELECT  *
                        FROM    bundle_courses 
                        WHERE   id_bundle = ? AND
                                id_course NOT IN (SELECT    id_course
                                                  FROM      bundle_courses
                                                  WHERE     id_bundle = b.id_bundle)
                    )
         ";

         return $query;
    }

    private function buildBundlesQueryArguments($idStudent, $idBundle)
    {
        $bindParams = array($idBundle);

        if ($idStudent > 0) {
            $bindParams[] = $idStudent;
        }

        $bindParams[] = $idBundle;

        return $bindParams;
    }

    private function parseBundlesResponseQuery()
    {
        if (!$this->hasResponseQuery()) {
            return array();
        }

        $bundles = array();
            
        foreach ($this->getAllResponseQuery() as $bundle) {
            $bundles[] = new Bundle(
                (int) $bundle['id_bundle'],
                $bundle['name'],
                (float) $bundle['price'],
                $bundle['logo'],
                $bundle['description']
            );
        }

        return $bundles;
    }
    
    /**
     * Gets bundles that do not contain any courses in common with a
     * supplied bundle, disregarding those that a student already has (if 
     * provided).
     * 
     * @param       int idBundle Bundle id
     * @param       int idStudent [Optional] Student id
     * 
     * @return      Bundle[] Bundles that does not have courses contained in 
     * the given bundle disregarding those that the student already has
     * 
     * @throws      \InvalidArgumentException If bundle id is empty or less than
     * or equal to zero
     */
    public function unrelatedBundles(int $idBundle, int $idStudent = -1) : array
    {
        $this->validateBundleId($idBundle);
        $this->withQuery("
            SELECT  *
            FROM    bundles b
            WHERE   id_bundle != ? AND
                    NOT EXISTS (
                        SELECT  *
                        FROM    bundle_courses
                        WHERE   id_bundle = ? AND
                                id_course IN (SELECT id_course
                                            FROM   bundle_courses
                                            WHERE  id_bundle = b.id_bundle)
                    )
        ");
        $this->runQueryWithArguments($this->buildBundlesQueryArguments($idStudent, $idBundle));
        
        return $this->parseBundlesResponseQuery();
    }
    
    /**
     * Gets the total number of classes that a bundle has along with its 
     * duration (in minutes).
     * 
     * @param       int idBundle Bundle id
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
    public function countTotalClasses(int $idBundle) : array
    {
        $this->validateBundleId($idBundle);
        $this->withQuery("
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
        $this->runQueryWithArguments($idBundle);

        return $this->parseTotalClassesResponseQuery();
    }

    private function parseTotalClassesResponseQuery()
    {
        if (!$this->hasResponseQuery()) {
            return array(
                "total_classes" => 0,
                "total_length" => 0
            );
        }

        $total = array(
            "total_classes" => 0,
            "total_length" => 0
        );
            
        foreach ($this->getAllResponseQuery() as $result) {
            $total["total_classes"] += $result["total_classes"];
            $total["total_length"] += $result["total_length"];
        }

        return $total;
    }
    
    /**
     * Gets total of bundles.
     *
     * @return      int Total of bundles
     */
    public function getTotal() : int
    {
        $this->withQuery("
            SELECT  COUNT(*) AS total
            FROM    bundles
        ");
        $this->runQueryWithoutArguments();
        
        return ((int) $this->getResponseQuery()['total']);
    }
}