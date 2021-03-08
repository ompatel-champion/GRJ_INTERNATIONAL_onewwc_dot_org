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
 * MOD:- SELLERS CREDIT
 */

namespace Ppb\Db\Table;

use Cube\Db\Table\AbstractTable;

class BalanceWithdrawals extends AbstractTable
{

    /**
     *
     * table name
     *
     * @var string
     */
    protected $_name = 'balance_withdrawals';

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
    protected $_rowClass = '\Ppb\Db\Table\Row\BalanceWithdrawal';

    /**
     * class name for rowset
     *
     * @var string
     */
    protected $_rowsetClass = '\Ppb\Db\Table\Rowset\BalanceWithdrawals';

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

