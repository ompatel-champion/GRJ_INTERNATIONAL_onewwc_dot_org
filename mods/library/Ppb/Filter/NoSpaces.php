<?php
/**
 *
 * PHP Pro Bid
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.3
 */
/**
 * no spaces filter - used for the post code
 */
/**
 * MOD:- PICKUP LOCATIONS
 */

namespace Ppb\Filter;

use Cube\Filter\AbstractFilter;

class NoSpaces extends AbstractFilter
{

    /**
     *
     * remove all spaces in the input string
     *
     * @param mixed $value
     *
     * @return mixed
     */
    public function filter($value)
    {
        return str_ireplace(' ', '', $value);
    }
} 