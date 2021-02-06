<?php
/**
 * AlphaOne Building Consent System
 * Copyright 2021
 * Generated in PhpStorm.
 * Developer: Camilo Lozano III - www.camilord.com
 *                              - github.com/camilord
 *                              - linkedin.com/in/camilord
 *
 * Zeacurity - BaseCommand.php
 * Username: Camilo
 * Date: 7/02/2021
 * Time: 9:51 AM
 */

namespace camilord\Zeacurity\Console;


use camilord\utilus\Database\xSQL;
use Symfony\Component\Console\Command\Command;

/**
 * Class BaseCommand
 * @package Zeacurity\Console
 */
abstract class BaseCommand extends Command
{
    /**
     * @var xSQL
     */
    private $db;

    protected function configure()
    {
        $db_params = json_decode(file_get_contents(APP_PATH.'/db.conf.json'), true);
        $this->db = new xSQL($db_params, true);
    }

    /**
     * @return xSQL
     */
    public function getDb()
    {
        return $this->db;
    }
}