<?php

/**
 * 
 * PHP Pro Bid
 * 
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2017 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 * 
 * @version     7.9 [rev.7.9.01]
 */
/**
 * caching engine table
 */

namespace Ppb\Db\Table;

use Cube\Db\Table\AbstractTable;

class Cache extends AbstractTable
{

    /**
     *
     * table name
     * 
     * @var string
     */
    protected $_name = 'cache';

    /**
     *
     * primary key
     * 
     * @var string
     */
    protected $_primary = 'id';

}