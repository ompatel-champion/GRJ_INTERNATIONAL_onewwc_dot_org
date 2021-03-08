<?php

/**
 * 
 * PHP Pro Bid
 * 
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2015 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 * 
 * @version     7.4
 */
/**
 * recently viewed listings table
 */

namespace Ppb\Db\Table;

use Cube\Db\Table\AbstractTable;

class RecentlyViewedListings extends AbstractTable
{

    /**
     *
     * table name
     * 
     * @var string
     */
    protected $_name = 'recently_viewed_listings';

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
        'Listing' => array(
            self::COLUMNS => 'listing_id',
            self::REF_TABLE_CLASS => '\Ppb\Db\Table\Listings',
            self::REF_COLUMNS => 'id',
        ),
    );

}