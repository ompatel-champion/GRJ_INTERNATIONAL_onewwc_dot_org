<?php

/**
 *
 * Cube Framework
 *
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2018 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 *
 * @version     2.0 [rev.2.0.01]
 */

namespace Cube\Form\Element;

use Cube\Form\Element,
    Cube\Controller\Front,
    Cube\Session;

/**
 * csrf (cross site request forgery) form element generator class
 *
 * Class Csrf
 *
 * @package Cube\Form\Element
 */
class Csrf extends Element
{

    const SESSION_NAMESPACE = 'Csrf';

    /**
     *
     * type of element - override the variable from the parent class
     *
     * @var string
     */
    protected $_element = 'csrf';

    /**
     *
     * session object
     *
     * @var \Cube\Session
     */
    protected $_session;

    /**
     *
     * csrf token
     *
     * @var string
     */
    protected $_token;

    /**
     *
     * class constructor
     *
     * @param string $name
     */
    public function __construct($name = 'csrf')
    {
        parent::__construct($this->_element, $name);

        $this->addValidator('Csrf')
            ->setSession()
            ->setHidden(true);
    }

    /**
     *
     * get session object
     *
     * @return \Cube\Session
     */
    public function getSession()
    {
        if (!($this->_session instanceof Session)) {
            $this->setSession();
        }

        return $this->_session;
    }

    /**
     *
     * set session object
     *
     * @param \Cube\Session $session
     *
     * @return $this
     */
    public function setSession(Session $session = null)
    {
        if ($session === null) {
            $session = Front::getInstance()->getBootstrap()->getResource('session');
        }

        if (!$session instanceof Session) {
            $session = new Session();
            $session->setNamespace(self::SESSION_NAMESPACE);
        }

        $this->_session = $session;

        return $this;
    }

    /**
     *
     * get token or generate if empty
     *
     * @return string
     */
    public function getToken()
    {
        if (!$this->_token) {
            $this->setToken(
                sha1(uniqid(rand(), true)));
        }

        return $this->_token;
    }

    /**
     *
     * set token and save it to the session
     *
     * @param string $token
     *
     * @return $this
     */
    public function setToken($token)
    {
        $session = $this->getSession();
        $name = $this->getName();

        $tokens = array_filter((array)$session->get($name));

        array_push($tokens, $token);

        $session->set($name, $tokens);

        $this->_token = $token;

        return $this;
    }

    /**
     *
     * render element
     *
     * @return string
     */
    public function render()
    {
        $value = $this->getToken();

        return '<input type="hidden" name="' . $this->_name . '" '
            . ((!empty($value)) ? 'value="' . $value . '" ' : '')
            . $this->_endTag;
    }

}

