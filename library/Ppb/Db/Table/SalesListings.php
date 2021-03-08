<?php

/**
 *
 * PHP Pro Bid
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2020 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     8.2 [rev.8.2.01]
 */

namespace Ppb\Db\Table;

use Cube\Db\Table\AbstractTable;

class SalesListings extends AbstractTable
{

    /**
     *
     * table name
     *
     * @var string
     */
    protected $_name = 'sales_listings';

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
    protected $_rowClass = '\Ppb\Db\Table\Row\SaleListing';

    /**
     * class name for rowset
     *
     * @var string
     */
    protected $_rowsetClass = '\Ppb\Db\Table\Rowset\SalesListings';

    /**
     *
     * reference map
     *
     * @var array
     */
    protected $_referenceMap = array(
        'Listing' => array(
            self::COLUMNS         => 'listing_id',
            self::REF_TABLE_CLASS => '\Ppb\Db\Table\Listings',
            self::REF_COLUMNS     => 'id',
        ),
        'Sale'    => array(
            self::COLUMNS         => 'sale_id',
            self::REF_TABLE_CLASS => '\Ppb\Db\Table\Sales',
            self::REF_COLUMNS     => 'id',
        ),
    );

    /**
     *
     * dependent tables
     *
     * @var array
     */
    protected $_dependentTables = array(
        '\Ppb\Db\Table\Offers',
    );
}

