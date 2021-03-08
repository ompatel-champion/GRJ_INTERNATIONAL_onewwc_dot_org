<?php

/**
 *
 * PHP Pro Bid
 *
 * @link        https://www.phpprobid.com
 * @copyright   Copyright (c) 2019 Online Ventures Software & CodeCube SRL
 * @license     https://www.phpprobid.com/license Commercial License
 *
 * @version     8.1 [rev.8.1.01]
 */

namespace Admin;

use App\Bootstrap as AppBootstrap,
    Ppb\Service,
    Ppb\Db\Table\Row\User as UserModel;

class Bootstrap extends AppBootstrap
{

    protected function _initLayout()
    {
        $this->getResource('view')->setLayoutsPath(\Ppb\Utility::getPath('themes') . '/admin');
    }

    protected function _initUser()
    {
        $this->bootstrap('settings');
        $this->bootstrap('authentication');

        if (isset($this->_storage['id'])) {
            $usersService = new Service\Users();

            $user = $usersService->findBy('id', $this->_storage['id']);

            if ($user instanceof UserModel) {
                $this->_role = $user['role'];

                return $user;
            }
        }

        return null;
    }

    protected function _initAcl()
    {
        $this->bootstrap('authentication');

        $front = $this->getResource('FrontController');

        $this->_acl = new Model\Acl();

        $front->registerPlugin(
            new Controller\Plugin\Acl($this->_acl, $this->_role));

        $view = $this->getResource('view');
        $view->navigation()->setAcl($this->_acl)
            ->setRole($this->_role);
    }


    protected function _initPlugins()
    {
        $this->_registerModuleControllerPlugins('Admin');
    }

    protected function _initAdminViewHelpers()
    {
        $this->bootstrap('viewHelpers');

        /** @var \Cube\Translate\Adapter\AbstractAdapter $translate */
        $translate = $this->getResource('translate')->getAdapter();

        $view = $this->getResource('view');

        $navigation = $this->getResource('navigation');
        $container = $navigation->findOneBy('label', $translate->_('Admin CP'));

        $view->navigation()
            ->setInitialContainer($container);
    }
}

