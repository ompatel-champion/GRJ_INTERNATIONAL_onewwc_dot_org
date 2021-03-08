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
 * MOD:- BANK TRANSFER
 */

namespace Ppb\Db\Table;

use Cube\Db\Table\AbstractTable;

class Banks extends AbstractTable
{

    /**
     *
     * table name
     * 
     * @var string
     */
    protected $_name = 'banks';

    /**
     *
     * primary key
     * 
     * @var string
     */
    protected $_primary = 'id';

}

