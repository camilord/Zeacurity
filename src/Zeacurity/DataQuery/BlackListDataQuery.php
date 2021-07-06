<?php
/**
 * AlphaOne Building Consent System
 * Copyright 2021
 * Generated in PhpStorm.
 * Developer: Camilo Lozano III - www.camilord.com
 *                              - github.com/camilord
 *                              - linkedin.com/in/camilord
 *
 * Zeacurity - BlackListDataQuery.php
 * Username: Camilo
 * Date: 7/02/2021
 * Time: 10:56 AM
 */

namespace camilord\Zeacurity\DataQuery;


/**
 * Class BlackListDataQuery
 * @package camilord\Zeacurity\DataQuery
 */
class BlackListDataQuery extends BaseDataQuery
{
    /**
     * @param string $ip
     * @return bool
     */
    public function exists(string $ip): bool
    {
        $q = "SELECT id FROM blacklist WHERE ip = ?";
        $this->getDb()->query($q, [$ip]);
        return $this->getDb()->exists();
    }

    /**
     * @param string $ip
     * @param string $notes
     * @return int
     */
    public function add(string $ip, string $notes = ''): int
    {
        $q = "INSERT INTO blacklist (ip, notes, created) 
                VALUES (?, ?, NOW()) ";
        $this->getDb()->query($q, [ $ip, $notes ]);
        return (int)$this->getDb()->last_id();
    }

    /**
     * @param int $limit
     * @param int $offset
     * @return mixed
     */
    public function get_list($limit = 500, $offset = 0) {
        $q = "SELECT * FROM blacklist WHERE deleted IS NULL ORDER BY id DESC";
        $q .= vsprintf(" LIMIT %d, %d", [$offset, $limit]);
        $this->getDb()->query($q, []);
        return $this->getDb()->fetch_all_assoc();
    }
}