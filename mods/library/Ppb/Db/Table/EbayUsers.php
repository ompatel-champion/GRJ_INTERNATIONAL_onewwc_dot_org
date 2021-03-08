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
 * ebay users table
 */
/**
 * MOD:- EBAY IMPORTER
 */

namespace Ppb\Db\Table;

use Cube\Db\Table\AbstractTable;

class EbayUsers extends AbstractTable
{

    /**
     *
     * table name
     * 
     * @var string
     */
    protected $_name = 'ebay_users';

    /**
     *
     * primary key
     * 
     * @var string
     */
    protected $_primary = 'id';


    /**
     *
     * reference map
     *
     * @var array
     */
    protected $_referenceMap = array(
        'User' => array(
            self::COLUMNS => 'user_id',
            self::REF_TABLE_CLASS => '\Ppb\Db\Table\Users',
            self::REF_COLUMNS => 'id',
        ),
    );

    /**
     *
     * dependent tables
     *
     * @var array
     */
    protected $_dependentTables = array(
        '\Ppb\Db\Table\Listings',
    );
}