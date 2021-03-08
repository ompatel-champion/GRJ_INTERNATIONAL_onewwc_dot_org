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
 * MOD:- EBAY IMPORTER
 * MOD:- DISCOUNT RULES
 */

namespace Ppb\Db\Table;

use Cube\Db\Table\AbstractTable;

class Users extends AbstractTable
{

    /**
     *
     * table name
     * 
     * @var string
     */
    protected $_name = 'users';

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
    protected $_rowClass = '\Ppb\Db\Table\Row\User';

    /**
     * class name for rowset
     *
     * @var string
     */
    protected $_rowsetClass = '\Ppb\Db\Table\Rowset\Users';

    /**
     *
     * reference map
     * 
     * @var array
     */
    protected $_referenceMap = array(
        'StoreSubscription' => array(
            self::COLUMNS => 'store_subscription_id',
            self::REF_TABLE_CLASS => '\Ppb\Db\Table\StoresSubscriptions',
            self::REF_COLUMNS => 'id',
        ),
        'StoreCategory' => array(
            self::COLUMNS => 'store_category_id',
            self::REF_TABLE_CLASS => '\Ppb\Db\Table\Categories',
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
        '\Ppb\Db\Table\Accounting',
        '\Ppb\Db\Table\Bids',
        '\Ppb\Db\Table\BlockedUsers',
        '\Ppb\Db\Table\Categories',
        '\Ppb\Db\Table\Listings',
        '\Ppb\Db\Table\ListingsWatch',
        '\Ppb\Db\Table\Offers',
        '\Ppb\Db\Table\PaymentGatewaysSettings',
        '\Ppb\Db\Table\Sales',
        '\Ppb\Db\Table\Transactions',
        '\Ppb\Db\Table\UsersAddressBook',
        '\Ppb\Db\Table\Messaging',
        '\Ppb\Db\Table\Vouchers',
        '\Ppb\Db\Table\FavoriteStores',
        '\Ppb\Db\Table\NewslettersRecipients',
        '\Ppb\Db\Table\UsersStatistics',
        '\Ppb\Db\Table\NewslettersSubscribers',
        '\Ppb\Db\Table\ContentEntries',
        '\Ppb\Db\Table\PostmenShipperAccounts',
        ## -- START :: ADD -- [ MOD:- EBAY IMPORTER ]
        '\Ppb\Db\Table\EbayUsers',
        ## -- END :: ADD -- [ MOD:- EBAY IMPORTER ]
        ## -- START :: ADD -- [ MOD:- DISCOUNT RULES @version 1.0 ]
        '\Ppb\Db\Table\DiscountRules',
        ## -- END :: ADD -- [ MOD:- DISCOUNT RULES @version 1.0 ]
    );

}

