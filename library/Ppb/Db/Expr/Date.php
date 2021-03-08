<?php
/**
 *
 * PHP Pro Bid
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2017 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.10 [rev.7.10.01]
 */
/**
 * this class converts a date string into a mysql datetime format
 *
 */

namespace Ppb\Db\Expr;

use Cube\Db\Expr;

class Date extends Expr
{
    /**
     *
     * magic method
     * return the expression
     *
     * @return string
     */
    public function __toString()
    {
        return "'" . date("Y-m-d", strtotime($this->_expression)) . "'";
    }
} 