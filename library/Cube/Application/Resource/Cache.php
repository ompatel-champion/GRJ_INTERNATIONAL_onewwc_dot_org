<?php

/**
 * 
 * Cube Framework 
 * 
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2017 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 * 
 * @version     1.9 [rev.1.9.01]
 */
/**
 * creates a cache resource
 */

namespace Cube\Application\Resource;

use Cube\Cache as CacheObject;

class Cache extends AbstractResource
{

    /**
     *
     * cache object
     * 
     * @var \Cube\Cache 
     */
    protected $_cache;

    /**
     *
     * initialize translate object
     *
     * @throws \InvalidArgumentException
     * @return \Cube\Cache
     */
    public function init()
    {
        if (!($this->_cache instanceof CacheObject)) {
            if (!isset($this->_options['cache']['adapter'])) {
                $this->_options['cache']['adapter'] = '\\Cube\\Cache\\Adapter\\Files';
            }

            $this->_cache = CacheObject::getInstance($this->_options['cache']);
        }

        return $this->_cache;
    }

}

