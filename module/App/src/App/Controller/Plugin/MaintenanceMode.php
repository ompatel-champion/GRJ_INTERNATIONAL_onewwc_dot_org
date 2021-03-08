<?php

/**
 *
 * PHP Pro Bid
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2018 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     8.0 [rev.8.0.01]
 */

/**
 * maintenance mode checker controller plugin class
 */

namespace App\Controller\Plugin;

use Cube\Controller\Plugin\AbstractPlugin,
    Cube\ModuleManager,
    Cube\Authentication,
    Cube\Session,
    Cube\Controller\Front;

class MaintenanceMode extends AbstractPlugin
{

    public function preDispatcher()
    {
        $request = $this->getRequest();

        $module = $request->getModule();

        if ($module != 'Admin') {
            $bootstrap = Front::getInstance()->getBootstrap();
            $settings = $bootstrap->getResource('settings');

            if ($settings['maintenance_mode']) {
                $redirect = true;
                /** @var \Cube\Session $session */
                $config = include APPLICATION_PATH . DIRECTORY_SEPARATOR . ModuleManager::MODULES_PATH . '/Admin/config/module.config.php';
                if (array_key_exists('session', $config)) {
                    $session = new Session($config['session']);

                    $storage = new Authentication\Storage\Session($config['session']['namespace'], null, $session);
                    $authentication = new Authentication\Authentication($storage);

                    if ($authentication->hasIdentity()) {
                        $storage = $authentication->getStorage()->read();

                        $role = $storage['role'];

                        if (strcasecmp($role, 'admin') === 0) {
                            $redirect = false;
                        }
                    }

                    if ($redirect) {
                        $this->getResponse()->setHeader(' ')
                            ->setResponseCode(503);

                        $request->setModule('app')
                            ->setController('index')
                            ->setAction('maintenance-mode');
                    }
                }
            }
        }
    }
}

