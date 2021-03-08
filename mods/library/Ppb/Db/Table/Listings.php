<?php

/**
 * 
 * PHP Pro Bid
 * 
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2019 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 * 
 * @version     8.1 [rev.8.1.01]
 */

/**
 * listings table
 */
/**
 * MOD:- EBAY IMPORTER
 */

namespace Ppb\Db\Table;

use Cube\Db\Table\AbstractTable;

class Listings extends AbstractTable
{

    /**
     *
     * table name
     * 
     * @var string
     */
    protected $_name = 'listings';

    /**
     *
     * primary key
     * 
     * @var string
     */
    protected $_primary = 'id';

    /**
     * 
     * class name for row
     *
     * @var string
     */
    protected $_rowClass = '\Ppb\Db\Table\Row\Listing';

    /**
     * class name for rowset
     *
     * @var string
     */
    protected $_rowsetClass = '\Ppb\Db\Table\Rowset\Listings';

    /**
     *
     * cacheable queries
     *
     * @var bool
     */
    protected $_cacheableQueries = true;

    /**
     *
     * reference map
     * 
     * @var array
     */
    protected $_referenceMap = array(
        'Owner' => array(
            self::COLUMNS => 'user_id',
            self::REF_TABLE_CLASS => '\Ppb\Db\Table\Users',
            self::REF_COLUMNS => 'id',
        ),
        'Category' => array(
            self::COLUMNS => 'category_id',
            self::REF_TABLE_CLASS => '\Ppb\Db\Table\Categories',
            self::REF_COLUMNS => 'id',
        ),
        'AddlCategory' => array(
            self::COLUMNS => 'addl_category_id',
            self::REF_TABLE_CLASS => '\Ppb\Db\Table\Categories',
            self::REF_COLUMNS => 'id',
        ),
        'Country' => array(
            self::COLUMNS => 'country',
            self::REF_TABLE_CLASS => '\Ppb\Db\Table\Locations',
            self::REF_COLUMNS => 'id',
        ),
        'TaxType'      => array(
            self::COLUMNS         => 'tax_type_id',
            self::REF_TABLE_CLASS => '\Ppb\Db\Table\TaxTypes',
            self::REF_COLUMNS     => 'id',
        ),        
        ## -- START :: ADD -- [ MOD:- EBAY IMPORTER ]
        'EbayUser' => array(
            self::COLUMNS => 'ebay_user_id',
            self::REF_TABLE_CLASS => '\Ppb\Db\Table\EbayUsers',
            self::REF_COLUMNS => 'id',
        ),
        ## -- END :: ADD -- [ MOD:- EBAY IMPORTER ]
    );

    /**
     *
     * dependent tables
     * 
     * @var array
     */
    protected $_dependentTables = array(
        '\Ppb\Db\Table\Accounting',
        '\Ppb\Db\Table\Bids',
        '\Ppb\Db\Table\ListingsMedia',
        '\Ppb\Db\Table\ListingsWatch',
        '\Ppb\Db\Table\Offers',
        '\Ppb\Db\Table\SalesListings',
        '\Ppb\Db\Table\Messaging',
        '\Ppb\Db\Table\RecentlyViewedListings',
    );

}

