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

namespace Cube\Cache\Adapter;

abstract class AbstractAdapter
{

    /**
     * cache types
     */
    const ROUTES = 'routes';
    const METADATA = 'metadata';
    const QUERIES = 'queries';

    /**
     * used for cached queries
     */
    const CACHE_COL = 'cache_col';
    const CACHE_WHERE = 'cache_where';

    /**
     *
     * whether to cache select queries
     *
     * @var bool
     */
    protected $_cacheQueries = false;

    /**
     *
     * whether to cache table metadatas
     *
     * @var bool
     */
    protected $_cacheMetadata = true;

    /**
     *
     * whether to cache routes
     *
     * @var bool
     */
    protected $_cacheRoutes = false;

    /**
     *
     * whether to serialize data upon read and write
     *
     * @var bool
     */
    protected $_serialization = true;

    /**
     *
     * number of seconds after a cached variable expires
     *
     * @var integer
     */
    protected $_expires = 259200; // 3 days

    /**
     *
     * class constructor
     *
     * @param array $options configuration array
     */
    public function __construct($options = array())
    {
        if (isset($options['queries'])) {
            $this->setCacheQueries($options['queries']);
        }

        if (isset($options['metadata'])) {
            $this->setCacheMetadata($options['metadata']);
        }

        if (isset($options['routes'])) {
            $this->setCacheRoutes($options['routes']);
        }

        if (isset($options['serialization'])) {
            $this->setSerialization($options['serialization']);
        }
    }

    /**
     *
     * get cache queries flag
     *
     * @return bool
     */
    public function getCacheQueries()
    {
        return $this->_cacheQueries;
    }

    /**
     *
     * set cache queries flag
     *
     * @param bool $cacheQueries
     *
     * @return $this
     */
    public function setCacheQueries($cacheQueries)
    {
        $this->_cacheQueries = $cacheQueries;

        return $this;
    }

    /**
     *
     * get cache metadata flag
     *
     * @return bool
     */
    public function getCacheMetadata()
    {
        return $this->_cacheMetadata;
    }

    /**
     *
     * set cache metadata flag
     *
     * @param bool $cacheMetadata
     *
     * @return $this
     */
    public function setCacheMetadata($cacheMetadata)
    {
        $this->_cacheMetadata = (bool)$cacheMetadata;

        return $this;
    }


    /**
     *
     * get cache routes flag
     *
     * @return bool
     */
    public function getCacheRoutes()
    {
        return $this->_cacheRoutes;
    }

    /**
     *
     * set cache routes flag
     *
     * @param bool $cacheRoutes
     *
     * @return $this
     */
    public function setCacheRoutes($cacheRoutes)
    {
        $this->_cacheRoutes = (bool)$cacheRoutes;

        return $this;
    }

    /**
     *
     * get serialization flag
     *
     * @return bool
     */
    public function getSerialization()
    {
        return $this->_serialization;
    }

    /**
     *
     * set serialization flag
     *
     * @param bool $serialization
     *
     * @return $this
     */
    public function setSerialization($serialization)
    {
        $this->_serialization = (bool)$serialization;

        return $this;
    }

    /**
     *
     * get expiration time
     *
     * @return int
     */
    public function getExpires()
    {
        return $this->_expires;
    }

    /**
     *
     * set expiration time
     *
     * @param int $expires
     *
     * @return $this
     */
    public function setExpires($expires)
    {
        $this->_expires = (int)$expires;

        return $this;
    }

    /**
     *
     * by default, adapters are enabled
     *
     * @return bool
     */
    public static function enabled()
    {
        return true;
    }

    /**
     *
     * read from cache
     *
     * @param string $name
     * @param string $type
     */
    abstract public function read($name, $type);

    /**
     *
     * write to cache
     *
     * @param string $name
     * @param string $type
     * @param mixed  $data
     * @param int    $expires
     */
    abstract public function write($name, $type, $data, $expires = null);

    /**
     *
     * delete variable from cache
     *
     * @param string $name
     * @param string $type
     */
    abstract public function delete($name, $type);

    /**
     *
     * purge cached variables
     *
     * @param string  $type
     * @param boolean $force
     */
    abstract public function purge($type, $force = false);

    /**
     *
     * clear cache
     */
    abstract public function clear();

}

