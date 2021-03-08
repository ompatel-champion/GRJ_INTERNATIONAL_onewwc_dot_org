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
 * view helpers abstract class
 */

namespace Ppb\View\Helper;

use Cube\View\Helper\AbstractHelper as CubeAbstractHelper,
    Cube\Controller\Front,
    Ppb\Db\Table\Row\User;

abstract class AbstractHelper extends CubeAbstractHelper
{

    /**
     *
     * logged in user model
     *
     * @var \Ppb\Db\Table\Row\User
     */
    protected $_user;

    /**
     *
     * settings array
     *
     * @var array
     */
    protected $_settings;

    /**
     *
     * get user model
     *
     * @return \Ppb\Db\Table\Row\User
     */
    public function getUser()
    {
        if (!$this->_user instanceof User) {
            $user = Front::getInstance()->getBootstrap()->getResource('user');

            if ($user instanceof User) {
                $this->setUser(
                    $user);
            }
        }

        return $this->_user;
    }

    /**
     *
     * set the user model of the currently logged in user
     *
     * @param \Ppb\Db\Table\Row\User $user
     *
     * @return $this
     */
    public function setUser(User $user)
    {
        $this->_user = $user;

        return $this;
    }

    /**
     *
     * get settings array
     *
     * @return array
     */
    public function getSettings()
    {
        if (!is_array($this->_settings)) {
            $this->setSettings(
                Front::getInstance()->getBootstrap()->getResource('settings'));
        }

        return $this->_settings;
    }

    /**
     *
     * set the settings array
     *
     * @param array $settings
     *
     * @return $this
     */
    public function setSettings(array $settings)
    {
        $this->_settings = $settings;

        return $this;
    }

}

