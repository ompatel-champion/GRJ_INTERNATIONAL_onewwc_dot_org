<?php

/**
 *
 * Cube Framework
 *
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2015 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 *
 * @version     1.4
 */

namespace Cube\Authentication\Storage;

/**
 * authentication storage interface
 *
 * Interface StorageInterface
 *
 * @package Cube\Authentication\Storage
 */
interface StorageInterface
{

    public function isEmpty();

    public function read();

    /**
     *
     * set storage contents
     *
     * @param mixed $contents
     */
    public function write($contents);

    public function clear();
}

