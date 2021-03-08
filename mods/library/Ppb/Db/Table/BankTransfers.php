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

class BankTransfers extends AbstractTable
{

    /**
     *
     * table name
     *
     * @var string
     */
    protected $_name = 'bank_transfers';

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
    protected $_rowClass = '\Ppb\Db\Table\Row\BankTransfer';

    /**
     * class name for rowset
     *
     * @var string
     */
    protected $_rowsetClass = '\Ppb\Db\Table\Rowset\BankTransfers';

    /**
     *
     * reference map
     *
     * @var array
     */
    protected $_referenceMap = array(
        'Transaction' => array(
            self::COLUMNS         => 'transaction_id',
            self::REF_TABLE_CLASS => '\Ppb\Db\Table\Transactions',
            self::REF_COLUMNS     => 'id',
        ),
        'BankAccount' => array(
            self::COLUMNS         => 'bank_account_id',
            self::REF_TABLE_CLASS => '\Ppb\Db\Table\BankAccounts',
            self::REF_COLUMNS     => 'id',
        ),
    );
}

