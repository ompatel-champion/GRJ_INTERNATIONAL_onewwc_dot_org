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

namespace Cube\Cache\Adapter;

class Files extends AbstractAdapter
{

    /**
     *
     * cache folder
     *
     * @var string
     */
    protected $_folder;

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

        if (!isset($options['folder'])) {
            throw new \RuntimeException("Cache folder not specified.");
        }

        $this->_folder = $options['folder'];
    }

    /**
     *
     * get cache folder
     *
     * @return string
     */
    public function getFolder()
    {
        return $this->_folder;
    }

    /**
     *
     * set cache folder
     *
     * @param string $folder
     *
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function setFolder($folder)
    {
        if (!file_exists($folder)) {
            throw new \InvalidArgumentException(
                sprintf("The cache folder '%s' could not be found.", $folder));
        }

        $this->_folder = $folder;

        return $this;
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
        $cacheFile = $this->_cacheFileName($name, $type);
        if (!file_exists($cacheFile)) {
            return false;
        }

        $contents = file_get_contents($cacheFile);

        return ($this->_serialization === true) ? unserialize($contents) : $contents;
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
        $cacheFile = $this->_cacheFileName($name, $type);

        if (!$fp = fopen($cacheFile, 'w')) {
            throw new \RuntimeException(
                sprintf("Could not open cache file '%s'.", $name));
        }

        if (!flock($fp, LOCK_EX)) {
            throw new \RuntimeException(
                sprintf("Could not lock cache file '%s'.", $name));
        }

        if ($this->_serialization === true) {
            $data = serialize($data);
        }

        if (!fwrite($fp, $data)) {
            throw new \RuntimeException(
                sprintf("Could not write to cache file '%s'.", $name));
        }

        flock($fp, LOCK_UN);
        fclose($fp);

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
        $cacheFile = $this->_cacheFileName($name, $type);

        if (file_exists($cacheFile)) {
            unlink($cacheFile);

            return true;
        }

        return false;
    }

    /**
     *
     * purge cache variables
     *
     * @param string  $type
     * @param boolean $force
     *
     * @return $this
     */
    public function purge($type, $force = false)
    {
        $cacheFolder = $this->_folder . DIRECTORY_SEPARATOR . $type;

        foreach (glob($cacheFolder . '/*') as $file) {
            if ((filemtime($file) < time() - $this->_expires) || $force === true) {
                @unlink($file);
            }
        }

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
        $this->purge(self::ROUTES, true)
            ->purge(self::METADATA, true)
            ->purge(self::QUERIES, true);

        return $this;
    }

    /**
     *
     * generate full cache file path
     *
     * @param string $name
     * @param string $type
     *
     * @return string
     */
    protected function _cacheFileName($name, $type)
    {
        return $this->_folder . DIRECTORY_SEPARATOR . $type . DIRECTORY_SEPARATOR . $name;
    }
}

