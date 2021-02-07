<?php
/**
 * AlphaOne Building Consent System
 * Copyright 2021
 * Generated in PhpStorm.
 * Developer: Camilo Lozano III - www.camilord.com
 *                              - github.com/camilord
 *                              - linkedin.com/in/camilord
 *
 * Zeacurity - WhitelistIPsUtil.php
 * Username: Camilo
 * Date: 7/02/2021
 * Time: 10:41 AM
 */

namespace camilord\Zeacurity\Utils;

use camilord\utilus\Data\ArrayUtilus;

/**
 * Class WhitelistIPsUtil
 * @package src\Zeacurity\Utils
 */
class WhitelistIPsUtil
{
    /**
     * @return array
     */
    public static function getList(): array {
        $whitelist_file = APP_PATH.'/whitelist.ip.json';

        $whitelist_ips = [];
        if (file_exists($whitelist_file)) {
            $data = json_decode(file_get_contents($whitelist_file), true);
            if (ArrayUtilus::haveData($data)) {
                $whitelist_ips = $data;
            }
            unset($data);
        }

        return $whitelist_ips;
    }
}