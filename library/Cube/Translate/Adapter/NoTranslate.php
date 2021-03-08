<?php

/**
 *
 * Cube Framework
 *
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2019 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 *
 * @version     2.1 [rev.2.1.01]
 */

namespace Cube\Translate\Adapter;

/**
 *
 * no translate adapter
 * will simply return the input value
 *
 * Class ArrayAdapter
 *
 * @package Cube\Translate\Adapter
 */
class NoTranslate extends AbstractAdapter
{

    /**
     *
     * return the input message
     *
     * @param string $message
     * @param string $locale
     *
     * @return string
     */
    public function translate($message, $locale = null)
    {
        return $message;
    }

    /**
     *
     * dummy method
     *
     * @param array $options
     *
     * @return $this
     */
    public function addTranslation($options = array())
    {
        return $this;
    }

} 