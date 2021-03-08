<?php
/**
 *
 * PHP Pro Bid
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2019 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     8.1 [rev.8.1.01]
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);

ini_set('gd.jpeg_ignore_warning', 1);

define('APPLICATION_PATH', realpath(__DIR__));

set_include_path(APPLICATION_PATH . DIRECTORY_SEPARATOR . 'library');

require_once 'Cube/Application.php';

$application = Cube\Application::init(array_merge(
    include 'config/global.config.php',
    include 'config/namespaces.config.php',
    include 'config/translate.config.php',
    include 'config/cache.config.php'));

$application->bootstrap()
    ->run();
