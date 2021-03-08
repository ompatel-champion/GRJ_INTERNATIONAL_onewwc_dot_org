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
 * members module acl controller plugin class
 */

namespace Members\Controller\Plugin;

use Cube\Permissions\Acl as PermissionsAcl,
    Cube\Controller\Plugin\AbstractPlugin,
    Cube\Controller\Front;

class Acl extends AbstractPlugin
{

    /**
     *
     * acl object
     * 
     * @var \Cube\Permissions\Acl
     */
    protected $_acl;

    /**
     *
     * user role
     * 
     * @var string
     */
    protected $_role;

    /**
     * 
     * class constructor
     * 
     * @param \Cube\Permissions\Acl $acl    the acl to use
     * @param string $role                  the role of the user
     */
    public function __construct(PermissionsAcl $acl, $role)
    {
        $this->_acl = $acl;
        $this->_role = (string) $role;
    }

    public function preDispatcher()
    {
        $request = $this->getRequest();

        $controller = $request->getController();
        $action = $request->getAction();

        if (!$this->_acl->hasResource($controller)) {
            $this->getResponse()
                    ->setHeader(' ')
                    ->setResponseCode(404);

            $controller = 'error';
            $action = 'not-found';

            $request->setController($controller)
                    ->setAction($action);
        }
        else if (!$this->_acl->isAllowed($this->_role, $controller, $action)) {
            if (in_array($this->_role, array('Guest', 'Incomplete', 'Suspended'))) {
                $request->setModule('members');
                $controller = 'user';

                switch ($this->_role) {
                    case 'Guest':
                        $action = 'login';
                        break;
                    case 'Incomplete':
                        $action = 'activate';
                        break;
                    case 'Suspended':
                        $controller = 'summary';
                        $action = 'index';
                        break;
                }
            }
            else {
                $view = Front::getInstance()->getBootstrap()->getResource('view');
                $redirectUrl = $view->url(null, 'members-index');

                $this->getResponse()
                    ->setRedirect($redirectUrl)
                    ->sendHeaders();

                exit();
            }

            $request->setController($controller)
                    ->setAction($action);
        }
    }

}

