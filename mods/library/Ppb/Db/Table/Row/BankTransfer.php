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
 * bank transfers table row object model
 */
/**
 * MOD:- BANK TRANSFER
 */

namespace Ppb\Db\Table\Row;

use Ppb\Service;

class BankTransfer extends AbstractRow
{

    /**
     *
     * check if a bank transfer can be accepted
     * the admin can accept any bank transfer, while a user can only accept a bank transfer for a sale he has made
     *
     * @return bool
     */
    public function canAccept()
    {
        $user = $this->getUser();

        if ($this->getData('transfer_status') == Service\BankTransfers::STATUS_PENDING) {
            if ($user->getData('role') == 'Admin') {
                return true;
            }
            else {
                $sale = $this->findParentRow('\Ppb\Db\Table\Transactions')
                    ->findParentRow('\Ppb\Db\Table\Sales');

                if ($sale !== null) {
                    if ($user->getData('id') == $sale->getData('seller_id')) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     *
     * check if a bank transfer can be declinedqq
     * only the admin or the seller (if direct payment) can decline a bank transfer
     *
     * @return bool
     */
    public function canDecline()
    {
        $user = $this->getUser();

        if ($this->getData('transfer_status') == Service\BankTransfers::STATUS_PENDING) {
            if ($user->getData('role') == 'Admin') {
                return true;
            }
            else {
                $sale = $this->findParentRow('\Ppb\Db\Table\Transactions')
                    ->findParentRow('\Ppb\Db\Table\Sales');

                if ($sale !== null) {
                    if ($user->getData('id') == $sale->getData('seller_id')) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     *
     * check if a bank transfer can be cancelled
     * only the admin or the buyer (if direct payment) can cancel a bank transfer
     *
     * @return bool
     */
    public function canCancel()
    {
        $user = $this->getUser();

        if ($this->getData('transfer_status') == Service\BankTransfers::STATUS_PENDING) {
            if ($user->getData('role') == 'Admin') {
                return true;
            }
            else {
                $transaction = $this->findParentRow('\Ppb\Db\Table\Transactions');

                if (count($transaction) > 0) {
                    if ($user->getData('id') == $transaction->getData('user_id')) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     *
     * check if a bank transfer can be deleted (the admin only can delete a bank transfer)
     * or return an error message string otherwise
     *
     * @return bool
     */
    public function canDelete()
    {
        $user = $this->getUser();

        if ($user->getData('role') != 'Admin') {
            return false;
        }

        return true;
    }
}

