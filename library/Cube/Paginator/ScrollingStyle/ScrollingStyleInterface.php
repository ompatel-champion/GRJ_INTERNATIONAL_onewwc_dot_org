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

namespace Cube\Paginator\ScrollingStyle;

use Cube\Paginator;

interface ScrollingStyleInterface
{

    /**
     * 
     * returns an array of pages given a page number and range.
     *
     * @param  \Cube\Paginator $paginator
     * @param  integer $pageRange (Optional) Page range
     * @return array
     */
    public function getPages(Paginator $paginator, $pageRange = null);
}

