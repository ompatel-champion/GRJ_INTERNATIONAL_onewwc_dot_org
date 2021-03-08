<?php

/**
 * 
 * PHP Pro Bid
 * 
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 * 
 * @version     7.0
 */
/**
 * listings durations table
 */

namespace Ppb\Db\Table;

use Cube\Db\Table\AbstractTable;

class Durations extends AbstractTable
{

    /**
     *
     * table name
     * 
     * @var string
     */
    protected $_name = 'durations';

    /**
     *
     * primary key
     * 
     * @var string
     */
    protected $_primary = 'id';

}