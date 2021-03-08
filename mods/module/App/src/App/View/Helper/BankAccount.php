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
 * bank account view helper class
 */
/**
 * MOD:- BANK TRANSFER
 */

namespace App\View\Helper;

use Cube\View\Helper\AbstractHelper,
    Ppb\Db\Table\Row\BankAccount as BankAccountModel,
    Ppb\Service;

class BankAccount extends AbstractHelper
{

    /**
     *
     * bank account model
     *
     * @var \Ppb\Db\Table\Row\BankAccount
     */
    protected $_bankAccount;

    /**
     *
     * bank accounts service
     *
     * @var \Ppb\Service\BankAccounts
     */
    protected $_bankAccounts;

    /**
     *
     * main method, only returns object instance
     *
     * @param int|string|\Ppb\Db\Table\Row\BankAccount $bankAccount
     *
     * @return $this
     */
    public function bankAccount($bankAccount)
    {
        if ($bankAccount !== null) {
            $this->setBankAccount($bankAccount);
        }

        return $this;
    }

    /**
     *
     * set bank accounts service
     *
     * @param \Ppb\Service\BankAccounts $bankAccounts
     *
     * @return $this
     */
    public function setBankAccounts(Service\BankAccounts $bankAccounts)
    {
        $this->_bankAccounts = $bankAccounts;

        return $this;
    }

    /**
     *
     * get bank accounts service
     *
     * @return \Ppb\Service\BankAccounts
     */
    public function getBankAccounts()
    {
        if (!$this->_bankAccounts instanceof Service\BankAccounts) {
            $this->setBankAccounts(
                new Service\BankAccounts());
        }

        return $this->_bankAccounts;
    }


    /**
     *
     * get bank account data
     *
     * @return \Ppb\Db\Table\Row\BankAccount
     * @throws \InvalidArgumentException
     */
    public function getBankAccount()
    {
        if (!$this->_bankAccount instanceof BankAccountModel) {
            throw new \InvalidArgumentException("The bank account model has not been instantiated");
        }

        return $this->_bankAccount;
    }

    /**
     *
     * set bank account data
     *
     * @param int|string|\Ppb\Db\Table\Row\BankAccount $bankAccount
     *
     * @throws \InvalidArgumentException
     * @return $this
     */
    public function setBankAccount($bankAccount)
    {
        if (is_int($bankAccount) || is_string($bankAccount)) {
            $bankAccountsService = $this->getBankAccounts();
            $bankAccount = $bankAccountsService->findBy('id', $bankAccount);
        }

        if (!$bankAccount instanceof BankAccountModel) {
            throw new \InvalidArgumentException("The method requires a string, an integer or an object of type \Ppb\Db\Table\Row\BankAccount.");
        }

        $this->_bankAccount = $bankAccount;

        return $this;
    }

    /**
     *
     * display the user's bank account
     *
     * @param boolean|string $display
     *
     * @return string
     */
    public function display($display = true)
    {
        $translate = $this->getTranslate();
        $output = null;

        try {
            $bankAccount = $this->getBankAccount();
        } catch (\Exception $e) {
            return '<em>' . $translate->_('Bank Account Removed/Unavailable') . '</em>';
        }

        if ($display === true) {
            $scriptHelper = $this->getView()->getHelper('script');
            $scriptHelper->addHeaderCode('<link href="' . $this->getView()->baseUrl . '/css/bank-account.css" rel="stylesheet">');

            $bankAccountFields = $this->getBankAccounts()->getBankAccountFields();

            $output = '<dl class="dl-variable">';
            foreach ($bankAccountFields as $field => $label) {
                $output .= '<dt><span>' . $translate->_($label) . '</span></dt>'
                    . '<dd>' . ((!empty($bankAccount[$field])) ? $bankAccount[$field] : $translate->_('N/A')) . '</dd>';
            }
            $output .= '</dl>';
        }
        else if (is_string($display)) {
            $output = implode($display, $bankAccount['account']);
        }

        return $output;
    }


}

