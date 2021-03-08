<?php

/**
 *
 * Cube Framework
 *
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2017 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 *
 * @version     1.10 [rev.1.10.02]
 */

namespace Cube\Cache\Adapter;

class Memcache extends AbstractAdapter
{

    /**
     *
     * host where memcached is listening for connections
     *
     * @var string
     */
    public static $host = 'localhost';

    /**
     * port where memcached is listening for connections
     *
     * @var string
     */
    public static $port = '11211';

    /**
     *
     * holds an instance of the memcache object
     *
     * @var \Memcache
     */
    private static $_object;

    /**
     *
     * memcache variables namespace/prefix
     *
     * @var string
     */
    protected $_namespace;

    /**
     *
     * class constructor
     *
     * @param array $options configuration array
     *
     * @throws \RuntimeException
     */
    public function __construct($options = array())
    {
        parent::__construct($options);

        if (isset($options['namespace'])) {
            $this->setNamespace($options['namespace']);
        }

        if (!self::enabled()) {
            throw new \RuntimeException("Memcache cache module is not available.");
        }
    }

    /**
     *
     * returns an instance of the memcache object and creates it if it wasnt instantiated yet
     *
     * @return \Memcache
     */
    public static function getObject()
    {
        if (!self::$_object instanceof \Memcache) {
            if (class_exists('Memcache')) {

                $memcache = new \Memcache();
                $connect = @$memcache->connect(self::$host, self::$port);

                if ($connect !== false) {
                    $memcache->addServer(self::$host, self::$port);
                    self::$_object = $memcache;
                }
            }
        }

        return self::$_object;
    }

    /**
     *
     * get namespace
     *
     * @return string
     */
    public function getNamespace()
    {
        return $this->_namespace;
    }

    /**
     *
     * set namespace
     *
     * @param string $namespace
     *
     * @return $this
     */
    public function setNamespace($namespace)
    {
        $this->_namespace = $namespace;

        return $this;
    }

    /**
     *
     * check if module is available and enabled
     *
     * @return bool
     */
    public static function enabled()
    {
        return (self::getObject() instanceof \Memcache) ? true : false;
    }

    /**
     *
     * reads the contents of a cache file and returns the output or false if the file could not be found
     *
     * @param string $name
     * @param string $type
     *
     * @return string|false
     */
    public function read($name, $type)
    {
        $namespace = $this->getNamespace();

        return self::getObject()->get($namespace . $type . $name);
    }

    /**
     *
     * create/update cache file
     *
     * @param string $name
     * @param string $type
     * @param mixed  $data
     * @param int    $expires
     *
     * @return $this
     * @throws \RuntimeException
     */
    public function write($name, $type, $data, $expires = null)
    {
        $namespace = $this->getNamespace();

        if ($expires === null) {
            $expires = $this->getExpires();
        }

        $result = self::getObject()
            ->add($namespace . $type . $name, $data, false, $expires);

        if (!$result) {
            throw new \RuntimeException("Memcache add failure.");
        }

        return $this;
    }

    /**
     *
     * delete a variable from cache
     *
     * @param string $name
     * @param string $type
     *
     * @return boolean
     */
    public function delete($name, $type)
    {
        $namespace = $this->getNamespace();

        return self::getObject()->delete($namespace . $type . $name);
    }

    /**
     *
     * purge cache
     * - for memcache cache variables expire automatically so this method does nothing
     *
     * @param string  $type
     * @param boolean $force
     *
     * @return $this
     */
    public function purge($type, $force = false)
    {
        return $this;
    }

    /**
     *
     * clear cache
     *
     * @return $this
     */
    public function clear()
    {
        self::getObject()->flush();

        return $this;
    }

}

