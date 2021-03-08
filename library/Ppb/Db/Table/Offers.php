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

/**
 * offers table
 */

namespace Ppb\Db\Table;

use Cube\Db\Table\AbstractTable;

class Offers extends AbstractTable
{

    /**
     *
     * table name
     *
     * @var string
     */
    protected $_name = 'offers';

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
    protected $_rowClass = '\Ppb\Db\Table\Row\Offer';

    /**
     * class name for rowset
     *
     * @var string
     */
    protected $_rowsetClass = '\Ppb\Db\Table\Rowset\Offers';

    /**
     *
     * reference map
     *
     * @var array
     */
    protected $_referenceMap = array(
        'User'        => array(
            self::COLUMNS         => 'user_id',
            self::REF_TABLE_CLASS => '\Ppb\Db\Table\Users',
            self::REF_COLUMNS     => 'id',
        ),
        'Receiver'    => array(
            self::COLUMNS         => 'receiver_id',
            self::REF_TABLE_CLASS => '\Ppb\Db\Table\Users',
            self::REF_COLUMNS     => 'id',
        ),
        'Listing'     => array(
            self::COLUMNS         => 'listing_id',
            self::REF_TABLE_CLASS => '\Ppb\Db\Table\Listings',
            self::REF_COLUMNS     => 'id',
        ),
        'Topic'       => array(
            self::COLUMNS         => 'topic_id',
            self::REF_TABLE_CLASS => '\Ppb\Db\Table\Offers',
            self::REF_COLUMNS     => 'topic_id',
        ),
        'SaleListing' => array(
            self::COLUMNS         => 'sale_listing_id',
            self::REF_TABLE_CLASS => '\Ppb\Db\Table\SalesListings',
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