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
 * balance withdrawals table row object model
 */
/**
 * MOD:- BALANCE WITHDRAWALS
 */

namespace Ppb\Db\Table\Row;

use Ppb\Service,
    Cube\Db\Expr;

class BalanceWithdrawal extends AbstractRow
{

    /**
     *
     * check if a balance can be paid
     *
     * @return bool
     */
    public function canAccept()
    {
        $user = $this->getUser();

        if (
            $this->getData('status') == Service\BalanceWithdrawals::STATUS_PENDING &&
            $user->getData('role') == 'Admin'
        ) {
            return true;
        }

        return false;
    }

    /**
     *
     * mark as paid/accepted
     *
     * @return $this
     */
    public function accept()
    {
        if ($this->canAccept()) {
            $this->save(array(
                'status'     => Service\BalanceWithdrawals::STATUS_PAID,
                'updated_at' => new Expr('now()'),
            ));
        }

        return $this;
    }

    /**
     *
     * check if a withdrawal request can be declined
     *
     * @return bool
     */
    public function canDecline()
    {
        $user = $this->getUser();

        if (
            $this->getData('status') == Service\BalanceWithdrawals::STATUS_PENDING &&
            $user->getData('role') == 'Admin'
        ) {
            return true;
        }

        return false;
    }

    /**
     *
     * decline withdrawal request
     *
     * @return $this
     */
    public function decline()
    {
        if ($this->canDecline()) {
            $this->_returnAmount();

            $this->save(array(
                'status'     => Service\BalanceWithdrawals::STATUS_DECLINED,
                'updated_at' => new Expr('now()'),
            ));
        }

        return $this;
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

        if (
            $user['id'] == $this->getData('user_id') &&
            $this->getData('status') == Service\BalanceWithdrawals::STATUS_PENDING
        ) {
            if ($user->getData('role') == 'Admin' || $user['id'] == $this->getData('user_id')) {
                return true;
            }
        }

        return false;
    }

    /**
     *
     * cancel withdrawal request
     *
     * @return $this
     */
    public function cancel()
    {
        if ($this->canCancel()) {
            $this->_returnAmount();

            $this->save(array(
                'status'     => Service\BalanceWithdrawals::STATUS_CANCELLED,
                'updated_at' => new Expr('now()'),
            ));
        }

        return $this;
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

        if ($user->getData('role') == 'Admin' && $this->getData('status') != Service\BalanceWithdrawals::STATUS_PENDING) {
            return true;
        }

        return false;
    }

    protected function _returnAmount()
    {
        /** @var \Ppb\Db\Table\Row\User $user */
        $user = $this->findParentRow('\Ppb\Db\Table\Users');

        $currenciesService = new Service\Table\Currencies();

        $amount = $currenciesService->convertAmount(
            $this->getData('amount'), $this->getData('currency'));

        $user->updateBalance((-1) * $amount);

        return $this;
    }
}

