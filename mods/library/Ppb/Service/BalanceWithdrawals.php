<?php

/**
 *
 * PHP Pro Bid
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.1
 */
/**
 * balance withdrawals table service class
 */
/**
 * MOD:- SELLERS CREDIT
 */

namespace Ppb\Service;

use Ppb\Db\Table\BalanceWithdrawals as BalanceWithdrawalsTable,
    Cube\Db\Expr;

class BalanceWithdrawals extends AbstractService
{

    /**
     * balance withdrawal statuses
     */
    const STATUS_PENDING = 'pending';
    const STATUS_PAID = 'paid';
    const STATUS_DECLINED = 'declined';
    const STATUS_CANCELLED = 'cancelled';

    /**
     *
     * statuses array
     *
     * @var array
     */
    protected $_withdrawalStatuses = array(
        self::STATUS_PENDING   => 'Pending',
        self::STATUS_PAID      => 'Paid',
        self::STATUS_DECLINED  => 'Declined',
        self::STATUS_CANCELLED => 'Cancelled',
    );

    /**
     *
     * class constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->setTable(
            new BalanceWithdrawalsTable());
    }

    /**
     *
     * set withdrawal statuses array
     *
     * @param array $transferStatuses
     */
    public function setWithdrawalStatuses($transferStatuses)
    {
        $this->_withdrawalStatuses = $transferStatuses;
    }

    /**
     *
     * get withdrawal statuses array
     *
     * @return array
     */
    public function getWithdrawalStatuses()
    {
        return $this->_withdrawalStatuses;
    }

    /**
     *
     * create or update a balance withdrawal record
     *
     * @param array $data
     *
     * @return $this
     */
    public function save($data)
    {
        $row = null;

        $data = $this->_prepareSaveData($data);

        if (array_key_exists('id', $data)) {
            $select = $this->_table->select()
                ->where("id = ?", $data['id']);

            unset($data['id']);

            $row = $this->_table->fetchRow($select);
        }

        if (count($row) > 0) {
            $data['updated_at'] = new Expr('now()');
            $this->_table->update($data, "id='{$row['id']}'");
        }
        else {
            $data['created_at'] = new Expr('now()');
            $this->_table->insert($data);
        }

        return $this;
    }

    /**
     *
     * delete a balance withdrawal row from the table
     *
     * @param integer $id
     *
     * @return integer     returns the number of affected rows
     */
    public function delete($id)
    {
        return $this->_table->delete(
            $this->_table->getAdapter()->quoteInto('id = ?', $id));
    }

}

