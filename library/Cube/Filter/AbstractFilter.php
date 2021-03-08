<?php
/**
 *
 * Cube Framework
 *
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2014 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 *
 * @version     1.0
 */
/**
 * abstract filter class
 */
namespace Cube\Filter;

abstract class AbstractFilter
{

    /**
     *
     * filter the input value
     *
     * @param mixed $value
     * @return mixed
     */
    abstract public function filter($value);
} 