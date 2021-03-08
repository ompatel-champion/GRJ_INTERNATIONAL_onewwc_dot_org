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

class Apc extends AbstractAdapter
{

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

        if (!self::enabled()) {
            throw new \RuntimeException("APC cache module is not available.");
        }
    }

    /**
     *
     * check if module is available and enabled
     *
     * @return bool
     */
    public static function enabled()
    {
        return (extension_loaded('apc') && ini_get('apc.enabled')) ? true : false;
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
        $contents = apc_fetch($type . $name);

        if ($contents !== false) {
            return ($this->_serialization === true) ? unserialize($contents) : $contents;
        }

        return false;
    }

    /**
     *
     * create/update cache file
     *
     * @param string $name
     * @param string $type
     * @param mixed  $data
     * @param int $expires
     *
     * @return $this
     * @throws \RuntimeException
     */
    public function write($name, $type, $data, $expires = null)
    {
        if ($this->_serialization === true) {
            $data = serialize($data);
        }

        if ($expires === null) {
            $expires = $this->getExpires();
        }

        $result = apc_store($type . $name, $data, $expires);

        if (!$result) {
            throw new \RuntimeException(
                sprintf("APC store failure - '%s'.", $name));
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
        return apc_delete($type . $name);
    }

    /**
     *
     * purge cache
     * - for APC cache variables expire automatically so this method does nothing
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
        apc_clear_cache();

        return $this;
    }

}

