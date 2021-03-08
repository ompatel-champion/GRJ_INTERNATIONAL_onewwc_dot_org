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

namespace Cube\View\Helper;

/**
 * Class Pluralize
 *
 * @package Cube\View\Helper
 */
class Pluralize extends AbstractHelper
{

    /**
     *
     * based on the count variable, return the singular or plural version of a sentence
     *
     * @param int    $count
     * @param string $singular
     * @param string $plural
     *
     * @return string
     */
    public function pluralize($count, $singular, $plural)
    {
        return ($count === 1) ? $singular : $plural;
    }

}

