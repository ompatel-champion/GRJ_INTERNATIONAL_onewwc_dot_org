<?php
/**
 *
 * PHP Pro Bid
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2017 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     1.9 [rev.1.9.01]
 */
/**
 * digits filter
 */

namespace Cube\Filter;

class Digits extends AbstractFilter
{

    /**
     *
     * replace a string with its integer value
     *
     * @param string $value
     *
     * @return string
     */
    public function filter($value)
    {
        $value = abs(intval($value));

        return (string)$value;
    }
} 