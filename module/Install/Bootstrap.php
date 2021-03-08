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

namespace Install;

use Cube\Application\Bootstrap as ApplicationBootstrap,
    Cube\Authentication\Authentication,
    Ppb\View\Helper as PpbViewHelper,
    Ppb\Service,
    Ppb\Db\Table\Row\User as UserModel;

class Bootstrap extends ApplicationBootstrap
{
    /**
     *
     * settings array
     *
     * @var array
     */
    protected $_settings = array();

    /**
     *
     * database connection flag
     *
     * @var bool
     */
    protected $_connected = false;

    /**
     *
     * acl object
     *
     * @var \Cube\Permissions\Acl
     */
    protected $_acl;

    /**
     *
     * acl role
     *
     * @var string|\Cube\Permissions\RoleInterface
     */
    protected $_role = 'Guest';

    /**
     *
     * current logged in user storage
     *
     * @var array|null
     */
    protected $_storage;

    protected function _initConnected()
    {
        $this->bootstrap('db');
        $db = $this->getResource('db');

        if ($db instanceof \Cube\Db\Adapter\AbstractAdapter) {
            /** @var \Cube\Db\Adapter\PDO\Mysql $db */
            try {
                $this->_connected = $db->canConnect();
            } catch (\Exception $e) {

            }
        }

        return $this->_connected;
    }

    protected function _initSettings()
    {
        if ($this->_connected === true) {
            $settingsService = new Service\Settings();

            try {
                $this->_settings = $settingsService->get();
            } catch (\Exception $e) {
            }
        }

        return $this->_settings;
    }

    protected function _initAuthentication()
    {
        $authentication = Authentication::getInstance();

        if ($authentication->hasIdentity()) {
            $storage = $authentication->getStorage()->read();

            if ($storage['role'] == 'Admin') {
                $this->_role = $storage['role'];
                $this->_storage = $storage;
            }
        }

        $view = $this->getResource('view');
        $view->loggedInUser = $this->_storage;
    }

    protected function _initUser()
    {
        if (isset($this->_storage['id'])) {
            $usersService = new Service\Users();

            $user = $usersService->findBy('id', $this->_storage['id']);

            if ($user instanceof UserModel) {
                if ($user['role'] == 'Admin') {
                    $this->_role = $user['role'];

                    return $user;
                }
            }
        }

        return null;
    }

    protected function _initAcl()
    {
        $front = $this->getResource('FrontController');

        $this->_acl = new Model\Acl();

        $front->registerPlugin(
            new Controller\Plugin\Acl($this->_acl, $this->_role));

        $view = $this->getResource('view');
        $view->navigation()->setAcl($this->_acl)
            ->setRole($this->_role);
    }

    protected function _initControllerPlugins()
    {
        $front = $this->getResource('FrontController');

        $front->registerPlugin(
            new Controller\Plugin\InstallerEnabled());
    }

    protected function _initModRewrite()
    {
        if (!\Ppb\Utility::checkModRewrite()) {
            \Ppb\Utility::activateStandardRouter();
        }
    }

    protected function _initViewHelpers()
    {
        /** @var \Cube\Translate\Adapter\AbstractAdapter $translate */
        $translate = $this->getResource('translate')->getAdapter();

        $view = $this->getResource('view');

        $sitePath = (!empty($this->_settings['site_path'])) ? $this->_settings['site_path'] : '/';

        /**
         * @8.0 view helpers
         * - view helpers that require a parameter in the constructor and that add header code
         * are manually initialized
         */
        $view->setHelper('url', new PpbViewHelper\Url($sitePath));

        /* set globals */
        $view->setGlobal('settings', $this->_settings)
            ->setGlobal('themesFolder', \Ppb\Utility::getFolder('themes'));

        $navigation = $this->getResource('navigation');
        $container = $navigation->findOneBy('label', $translate->_('Installer Navigation'));

        $view->navigation()
            ->setInitialContainer($container)
            ->setPath($navigation->getPath())
            ->setPartial('navigation/navigation.phtml');

        /* add global css */
        $view->script()
            ->addHeaderCode('<link href="' . $view->baseUrl . '/css/bootstrap.min.css" rel="stylesheet" type="text/css">')
            ->addHeaderCode('<link href="' . $view->baseUrl . '/js/slick/slick.css" rel="stylesheet" type="text/css">')
            ->addHeaderCode('<link href="' . $view->baseUrl . '/js/slick/slick-theme.css" rel="stylesheet" type="text/css">')
            ->addHeaderCode('<link href="' . $view->baseUrl . '/js/magnific-popup/magnific-popup.css" rel="stylesheet" type="text/css">')
            ->addHeaderCode('<link href="' . $view->baseUrl . '/css/default.css" rel="stylesheet" type="text/css">')
            ->addHeaderCode('<!--[if lt IE 9]><link href="' . $view->baseUrl . '/css/style.ie.css" media="all" rel="stylesheet" type="text/css"><![endif]-->')
            ->addHeaderCode('<link href="' . $view->baseUrl . '/css/mods.css" rel="stylesheet" type="text/css">')
            ->addHeaderCode('<link href="' . $view->baseUrl . '/img/favicon.png" rel="shortcut icon" type="image/vnd.microsoft.icon">')
            ->addHeaderCode('<script src="' . $view->baseUrl . '/js/feather.min.js" type="text/javascript"></script>');

        /* add javascript plugins */
        $view->script()
            ->addHeaderCode('<script type="text/javascript" src="' . $view->baseUrl . '/js/jquery.min.js"></script>')
            ->addHeaderCode('<script type="text/javascript" src="' . $view->baseUrl . '/js/jquery-migrate-3.0.0.min.js"></script>')
            ->addBodyCode('<script>feather.replace();</script>')
            ->addBodyCode('<script type="text/javascript" src="' . $view->baseUrl . '/js/popper.min.js"></script>')
            ->addBodyCode('<script type="text/javascript" src="' . $view->baseUrl . '/js/bootstrap.min.js"></script>')
            ->addBodyCode('<script type="text/javascript" src="' . $view->baseUrl . '/js/fontawesome/fa-v4-shims.min.js"></script>')
            ->addBodyCode('<script type="text/javascript" src="' . $view->baseUrl . '/js/fontawesome/fontawesome-all.min.js"></script>')
            ->addBodyCode('<script type="text/javascript" src="' . $view->baseUrl . '/js/masonry.pkgd.min.js"></script>')
            ->addBodyCode('<script type="text/javascript" src="' . $view->baseUrl . '/js/bootbox.min.js"></script>')
            ->addBodyCode('<script type="text/javascript" src="' . $view->baseUrl . '/js/slick/slick.min.js"></script>')
            ->addBodyCode('<script type="text/javascript" src="' . $view->baseUrl . '/js/magnific-popup/jquery.magnific-popup.js"></script>')
            ->addBodyCode('<script type="text/javascript">
                    var baseUrl = "' . $view->baseUrl . '";
                    var paths = {};
                    var modRewrite = false;
                    paths.calculatePostage = "' . $view->url(array('module' => 'listings', 'controller' => 'listing', 'action' => 'calculate-postage')) . '";
                    paths.quickNavigation = "' . $view->url(array('module' => 'admin', 'controller' => 'index', 'action' => 'quick-navigation')) . '";
                    var msgs = {};
                    msgs.close = "' . $translate->_('Close') . '";
                    msgs.cancel = "' . $translate->_('Cancel') . '";
                    msgs.ok = "' . $translate->_('OK') . '";
                    msgs.confirmThisAction = "' . $translate->_('Please confirm this action.') . '";
                    var slickAutoplay = true;
                    var slickAutoplaySpeed = 3000;
                </script>')
            ->addBodyCode('<script type="text/javascript" src="' . $view->baseUrl . '/js/cookie.js"></script>')
            ->addBodyCode('<script type="text/javascript" src="' . $view->baseUrl . '/js/functions.js"></script>')
            ->addBodyCode('<script type="text/javascript" src="' . $view->baseUrl . '/js/global.js"></script>')
            ->addBodyCode('<script type="text/javascript" src="' . $view->baseUrl . '/js/mods.js"></script>');
    }
}