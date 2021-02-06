<?php
/**
 * AlphaOne Building Consent System
 * Copyright 2021
 * Generated in PhpStorm.
 * Developer: Camilo Lozano III - www.camilord.com
 *                              - github.com/camilord
 *                              - linkedin.com/in/camilord
 *
 * Zeacurity - BaseDataQuery.php
 * Username: Camilo
 * Date: 7/02/2021
 * Time: 10:57 AM
 */

namespace camilord\Zeacurity\DataQuery;


use camilord\utilus\Database\xSQL;

/**
 * Class BaseDataQuery
 * @package camilord\Zeacurity\DataQuery
 */
abstract class BaseDataQuery
{
    /**
     * @var xSQL
     */
    private $db;

    /**
     * BaseDataQuery constructor.
     * @param xSQL $db
     */
    public function __construct(xSQL $db)
    {
        $this->db = $db;
    }

    /**
     * @return xSQL
     */
    public function getDb(): xSQL
    {
        return $this->db;
    }
}