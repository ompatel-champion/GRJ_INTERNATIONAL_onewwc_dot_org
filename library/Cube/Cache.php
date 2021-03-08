<?php

/**
 *
 * Cube Framework
 *
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2017 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 *
 * @version     1.10 [rev.1.10.01]
 */

namespace Cube;

class Cache
{

    /**
     *
     * returns an instance of the object and creates it if it wasn't instantiated yet
     *
     * @return \Cube\Cache
     */
    private static $_instance;

    /**
     *
     * cache adapter
     *
     * @var \Cube\Cache\Adapter\AbstractAdapter
     */
    protected $_adapter;

    /**
     *
     * class constructor
     *
     * @param array $options configuration array
     *
     * @throws \RuntimeException
     */
    protected function __construct($options = array())
    {
        // initialize adapter from configuration
        if (!isset($options['adapter'])) {
            throw new \RuntimeException("Cache adapter not specified.");
        }

        $adapterClass = $options['adapter'];

        $this->setAdapter(
            new $adapterClass($options));
    }

    /**
     *
     * initialize application as singleton
     *
     * @param array $options configuration array
     *
     * @return \Cube\Cache
     */
    public static function getInstance($options = array())
    {
        if (!self::$_instance instanceof self) {
            self::$_instance = new self($options);
        }

        return self::$_instance;
    }

    /**
     *
     * get adapter
     *
     * @return Cache\Adapter\AbstractAdapter
     */
    public function getAdapter()
    {
        return $this->_adapter;
    }

    /**
     *
     * set adapter
     *
     * @param Cache\Adapter\AbstractAdapter $adapter
     *
     * @return $this
     */
    public function setAdapter($adapter)
    {
        $this->_adapter = $adapter;

        return $this;
    }

    /**
     *
     * get cache adapter queries flag
     *
     * @return bool
     */
    public function getCacheQueries()
    {
        return $this->getAdapter()->getCacheQueries();
    }

    /**
     *
     * get cache adapter metadata flag
     *
     * @return bool
     */
    public function getCacheMetadata()
    {
        return $this->getAdapter()->getCacheMetadata();
    }

    /**
     *
     * get cache adapter routes flag
     *
     * @return bool
     */
    public function getCacheRoutes()
    {
        return $this->getAdapter()->getCacheRoutes();
    }

    /**
     *
     * read data from cache
     *
     * @param string $name
     * @param string $type
     *
     * @return string|false
     */
    public function read($name, $type)
    {
        return $this->getAdapter()->read($name, $type);
    }

    /**
     *
     * write data to cache
     *
     * @param string $name
     * @param string $type
     * @param mixed  $data
     * @param int $expires
     *
     * @return $this
     */
    public function write($name, $type, $data, $expires = null)
    {
        $this->getAdapter()->write($name, $type, $data, $expires);

        return $this;
    }

}

