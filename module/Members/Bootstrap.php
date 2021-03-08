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

namespace Members;

use App\Bootstrap as AppBootstrap,
    Ppb\Service,
    Ppb\Db\Table\Row\User as UserModel;

class Bootstrap extends AppBootstrap
{

    protected function _initUser()
    {
        $this->bootstrap('settings');
        $this->bootstrap('authentication');
        $this->bootstrap('rememberMe');

        if (isset($this->_storage['id'])) {
            $usersService = new Service\Users();

            $user = $usersService->findBy('id', $this->_storage['id'], true);

            if ($user instanceof UserModel) {
                $this->_role = $user->getRole();
                $user['role'] = $this->_role;

                return $user;
            }
        }

        return null;
    }

    /**
     *
     * method that initializes the _members.phtml sub-layout
     */
    protected function _initSubLayout()
    {
        $view = $this->getResource('view');
        $view->set('isMembersModule', true);
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
        $this->_registerModuleControllerPlugins('Listings');
    }
}

