<?php

/**
 *
 * PHP Pro Bid
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.2
 */
/**
 * balance withdrawals view helper class
 */
/**
 * MOD:- SELLERS CREDIT
 */

namespace App\View\Helper;

use Cube\View\Helper\AbstractHelper,
    Ppb\Db\Table\Row\BalanceWithdrawal as BalanceWithdrawalModel,
    Ppb\Service;

class BalanceWithdrawal extends AbstractHelper
{

    /**
     *
     * withdrawal model
     *
     * @var \Ppb\Db\Table\Row\BalanceWithdrawal
     */
    protected $_withdrawal;

    /**
     *
     * withdrawals service
     *
     * @var \Ppb\Service\BalanceWithdrawals
     */
    protected $_balanceWithdrawals;

    /**
     *
     * main method, only returns object instance
     *
     * @param int|string|\Ppb\Db\Table\Row\BalanceWithdrawal $withdrawal
     *
     * @return $this
     */
    public function balanceWithdrawal($withdrawal = null)
    {
        if ($withdrawal !== null) {
            $this->setWithdrawal($withdrawal);
        }

        return $this;
    }

    /**
     *
     * set balance withdrawals service
     *
     * @param \Ppb\Service\BalanceWithdrawals $withdrawals
     *
     * @return $this
     */
    public function setBalanceWithdrawals(Service\BalanceWithdrawals $withdrawals)
    {
        $this->_balanceWithdrawals = $withdrawals;

        return $this;
    }

    /**
     *
     * get service
     *
     * @return \Ppb\Service\BalanceWithdrawals
     */
    public function getBalanceWithdrawals()
    {
        if (!$this->_balanceWithdrawals instanceof Service\BalanceWithdrawals) {
            $this->setBalanceWithdrawals(
                new Service\BalanceWithdrawals());
        }

        return $this->_balanceWithdrawals;
    }


    /**
     *
     * get balance withdrawal data
     *
     * @return \Ppb\Db\Table\Row\BalanceWithdrawal
     * @throws \InvalidArgumentException
     */
    public function getWithdrawal()
    {
        if (!$this->_withdrawal instanceof BalanceWithdrawalModel) {
            throw new \InvalidArgumentException("The balance withdrawal model has not been instantiated");
        }

        return $this->_withdrawal;
    }

    /**
     *
     * set balance withdrawal data
     *
     * @param int|string|\Ppb\Db\Table\Row\BalanceWithdrawal $withdrawal
     *
     * @throws \InvalidArgumentException
     * @return $this
     */
    public function setWithdrawal($withdrawal)
    {
        if (is_int($withdrawal) || is_string($withdrawal)) {
            $balanceWithdrawalsService = $this->getBalanceWithdrawals();
            $withdrawal = $balanceWithdrawalsService->findBy('id', $withdrawal);
        }

        if (!$withdrawal instanceof BalanceWithdrawalModel) {
            throw new \InvalidArgumentException("The method requires a string, an integer or an object of type \Ppb\Db\Table\Row\BalanceWithdrawal.");
        }

        $this->_withdrawal = $withdrawal;

        return $this;
    }

    /**
     *
     * display the status of the balance withdrawal
     *
     * @return string
     */
    public function status()
    {
        $translate = $this->getTranslate();
        $output = null;

        $NA = '<em>' . $translate->_('N/A') . '</em>';
        try {
            $withdrawal = $this->getWithdrawal();
        } catch (\Exception $e) {
            return $NA;
        }

        $balanceWithdrawalsStatuses = $this->getBalanceWithdrawals()->getWithdrawalStatuses();

        $transferStatus = $withdrawal->getData('status');
        $output = (isset($balanceWithdrawalsStatuses[$transferStatus])) ? $balanceWithdrawalsStatuses[$transferStatus] : $NA;


        switch ($transferStatus) {
            case Service\BalanceWithdrawals::STATUS_PENDING:
                $output = '<span class="badge badge-info">' . $output . '</span>';
                break;
            case Service\BalanceWithdrawals::STATUS_PAID:
                $output = '<span class="badge badge-success">' . $output . '</span>';
                break;
            case Service\BalanceWithdrawals::STATUS_DECLINED:
                $output = '<span class="badge badge-danger">' . $output . '</span>';
                break;
            case Service\BalanceWithdrawals::STATUS_CANCELLED:
                $output = '<span class="badge badge-warning">' . $output . '</span>';
                break;

        }

        return $output;
    }

}

