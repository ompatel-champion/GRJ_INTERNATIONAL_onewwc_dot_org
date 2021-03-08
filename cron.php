<?php
/**
 *
 * PHP Pro Bid
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2018 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     8.0 [rev.8.0.02]
 */

ini_set('display_errors', 0);
error_reporting(0);

define('APPLICATION_PATH', realpath(__DIR__));
set_include_path(APPLICATION_PATH . DIRECTORY_SEPARATOR . 'library');

require_once 'Cube/Application.php';

$application = Cube\Application::init(array_merge(
    include 'config/global.config.php',
    include 'config/namespaces.config.php',
    include 'config/translate.config.php',
    include 'config/cache.config.php'));

$application->bootstrap();

$request = new \Cube\Controller\Request();
$command = $request->getParam('command', null);

$service = new \Ppb\Service\Cron();
$service->run($command);

