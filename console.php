#!/usr/bin/env php
<?php
/**
 * AlphaOne Building Consent System
 * Copyright 2021
 * Generated in PhpStorm.
 * Developer: Camilo Lozano III - www.camilord.com
 *                              - github.com/camilord
 *                              - linkedin.com/in/camilord
 *
 * Zeacurity - console.php
 * Username: Camilo
 * Date: 7/02/2021
 * Time: 9:46 AM
 */

define('APP_PATH', __DIR__);

// application.php
require __DIR__.'/vendor/autoload.php';

use Symfony\Component\Console\Application;
use camilord\Zeacurity\Console\Commands\AuthBlackListerCommand;
use camilord\Zeacurity\Console\Commands\AppendBlackListToIpTablesCommand;

$application = new Application();

$application->add(new AuthBlackListerCommand());
$application->add(new AppendBlackListToIpTablesCommand());

$application->run();