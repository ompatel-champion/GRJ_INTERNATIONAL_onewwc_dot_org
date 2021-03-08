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

namespace Ppb\Db\Table\Rowset;

abstract class AbstractStatus extends AbstractRowset
{

    /**
     *
     * admin flag
     *
     * @var bool
     */
    protected $_admin = false;


    /**
     *
     * automatic flag
     *
     * @var bool
     */
    protected $_automatic = false;
    /**
     *
     * output messages
     *
     * @var array
     */
    protected $_messages = array();

    /**
     *
     * actions counter
     *
     * @var int
     */
    protected $_counter = 0;

    /**
     *
     * set admin flag
     *
     * @param boolean $admin
     *
     * @return $this
     */
    public function setAdmin($admin)
    {
        $this->_admin = $admin;

        return $this;
    }

    /**
     *
     * get admin flag
     *
     * @return boolean
     */
    public function getAdmin()
    {
        return $this->_admin;
    }

    /**
     *
     * set automatic flag
     *
     * @param boolean $automatic
     *
     * @return $this
     */
    public function setAutomatic($automatic)
    {
        $this->_automatic = $automatic;

        return $this;
    }

    /**
     *
     * get automatic flag
     *
     * @return boolean
     */
    public function getAutomatic()
    {
        return $this->_automatic;
    }

    /**
     *
     * add single message
     *
     * @param string $message
     *
     * @return $this
     */
    public function addMessage($message)
    {
        $translate = $this->getTranslate();

        if (null !== $translate) {
            $message = $translate->_($message);
        }

        $this->_messages[] = $message;

        return $this;
    }

    /**
     *
     * get messages
     *
     * @return array
     */
    public function getMessages()
    {
        return $this->_messages;
    }

    /**
     *
     * increment counter
     *
     * @param int $value
     *
     * @return $this
     */
    public function incrementCounter($value = 1)
    {
        $this->_counter += $value;

        return $this;
    }

    /**
     *
     * reset counter
     *
     * @return $this
     */
    public function resetCounter()
    {
        $this->setCounter(0);

        return $this;
    }

    /**
     *
     * get counter value
     *
     * @return int
     */
    public function getCounter()
    {
        return $this->_counter;
    }

    /**
     *
     * set counter
     *
     * @param int $value
     *
     * @return $this
     */
    public function setCounter($value)
    {
        $this->_counter = $value;

        return $this;
    }

    /**
     *
     * proxy to status change class methods
     *
     * @param string $methodName
     * @param bool   $admin
     * @param bool   $automatic
     *
     * @return array
     */
    public function changeStatus($methodName = null, $admin = false, $automatic = false)
    {
        if (method_exists($this, $methodName)) {
            $this->setAdmin($admin)
                ->setAutomatic($automatic);

            $this->$methodName();
        }

        return $this->getMessages();
    }

}