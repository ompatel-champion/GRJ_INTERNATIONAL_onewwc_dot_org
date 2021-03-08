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

namespace Ppb\Db\Table;

use Cube\Db\Table\AbstractTable;

class PostmenShipperAccounts extends AbstractTable
{

    /**
     *
     * table name
     *
     * @var string
     */
    protected $_name = 'postmen_shipper_accounts';

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
    protected $_rowClass = '\Ppb\Db\Table\Row\PostmenShipperAccount';

    /**
     * class name for rowset
     *
     * @var string
     */
    protected $_rowsetClass = '\Ppb\Db\Table\Rowset\PostmenShipperAccounts';

    /**
     *
     * reference map
     *
     * @var array
     */
    protected $_referenceMap = array(
        'User' => array(
            self::COLUMNS         => 'user_id',
            self::REF_TABLE_CLASS => '\Ppb\Db\Table\Users',
            self::REF_COLUMNS     => 'id',
        ),
    );

}

