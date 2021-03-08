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
 * bank transfers table service class
 */
/**
 * MOD:- BANK TRANSFER
 */

namespace Ppb\Service;

use Ppb\Db\Table\BankTransfers as BankTransfersTable,
    Cube\Db\Expr;

class BankTransfers extends AbstractService
{
    /**
     * bank transfer types
     */
    const TRANSFER_TYPE_WIRE = 'wire_transfer';
    const TRANSFER_TYPE_CLERK = 'clerk';
    const TRANSFER_TYPE_ATM = 'atm';
    const TRANSFER_TYPE_INTERNET = 'internet';
    const TRANSFER_TYPE_OTHER = 'other';

    /**
     * bank transfer statuses
     */
    const STATUS_PENDING = 'pending';
    const STATUS_PAID = 'paid';
    const STATUS_DECLINED = 'declined';
    const STATUS_CANCELLED = 'cancelled';

    /**
     *
     * default transfer types array
     *
     * @var array
     */
    protected $_transferTypes = array(
        self::TRANSFER_TYPE_WIRE     => 'Wire Transfer',
        self::TRANSFER_TYPE_CLERK    => 'Deposit with Bank Clerk',
        self::TRANSFER_TYPE_ATM      => 'ATM Deposit',
        self::TRANSFER_TYPE_INTERNET => 'Internet Banking',
        self::TRANSFER_TYPE_OTHER    => 'Other',
    );

    /**
     *
     * transfer statuses array
     *
     * @var array
     */
    protected $_transferStatuses = array(
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
            new BankTransfersTable());
    }

    /**
     *
     * set transfer types array
     *
     * @param array $transferTypes
     *
     * @return $this
     */
    public function setTransferTypes($transferTypes)
    {
        $this->_transferTypes = $transferTypes;

        return $this;
    }

    /**
     *
     * get transfer types array
     *
     * @return array
     */
    public function getTransferTypes()
    {
        return $this->_transferTypes;
    }

    /**
     *
     * set transfer statuses array
     *
     * @param array $transferStatuses
     */
    public function setTransferStatuses($transferStatuses)
    {
        $this->_transferStatuses = $transferStatuses;
    }

    /**
     *
     * get transfer statuses array
     *
     * @return array
     */
    public function getTransferStatuses()
    {
        return $this->_transferStatuses;
    }

    /**
     *
     * create or update a bank transfer record
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
     * delete a bank transfer row from the table
     *
     * @param integer $id the id of the content page
     *
     * @return integer     returns the number of affected rows
     */
    public function delete($id)
    {
        return $this->_table->delete(
            $this->_table->getAdapter()->quoteInto('id = ?', $id));
    }

}

