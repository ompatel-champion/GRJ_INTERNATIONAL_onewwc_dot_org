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

namespace Cube\View\Helper;

use Cube\View;

/**
 * view helpers interface
 *
 * Interface HelperInterface
 *
 * @package Cube\View\Helper
 */
interface HelperInterface
{

    /**
     *
     * set the view object
     *
     * @param \Cube\View $view
     */
    public function setView(View $view);
}

