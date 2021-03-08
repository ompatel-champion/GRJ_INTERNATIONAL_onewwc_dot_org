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
 * vouchers table service class
 * - percentage vouchers apply on each fee row specifically
 * - flat amount vouchers apply on the total (not on each row)
 */


namespace Ppb\Service;

use Ppb\Db\Table;

class Vouchers extends AbstractService
{

    /**
     *
     * class constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->setTable(
            new Table\Vouchers());
    }

    /**
     *
     * find voucher by code and owner
     * (if null get an admin voucher)
     *
     * @param string $voucherCode
     * @param int    $userId
     *
     * @return \Ppb\Db\Table\Row\Voucher|null
     */
    public function findBy($voucherCode, $userId = null)
    {
        $select = $this->_table->select()
            ->where('code = ?', strval($voucherCode));

        if ($userId) {
            $select->where('user_id = ?', $userId);
        }
        else {
            $select->where('user_id is null');
        }

        return $this->_table->fetchRow($select);
    }

    /**
     *
     * delete a voucher row from the table
     *
     * @param int $id     the id of the row to be deleted
     * @param int $userId the id of owner of the row
     *
     * @return int     returns the number of affected rows
     */
    public function delete($id, $userId = null)
    {
        $table = $this->getTable();
        $adapter = $table->getAdapter();

        $where[] = $adapter->quoteInto('id = ?', $id);

        if ($userId !== null) {
            $where[] = $adapter->quoteInto('user_id = ?', $userId);
        }

        return $table->delete($where);
    }
}

