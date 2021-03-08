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
 * bank accounts table row object model
 */
/**
 * MOD:- BANK TRANSFER
 */

namespace Ppb\Db\Table\Row;

class BankAccount extends AbstractRow
{

    /**
     *
     * serializable fields
     *
     * @var array
     */
    protected $_serializable = array('account');

    /**
     *
     * check if an account can be edited (if not part of an invoice)
     * or return an error message string otherwise
     *
     * @return bool|string
     */
    public function canEdit()
    {
        $translate = $this->getTranslate();

        if ($this->_usedInBankTransfers()) {
            return $translate->_('Cannot edit a bank that has been used in a bank transfer.');
        }

        return true;
    }

    /**
     *
     * check if an address can be deleted (if not part of an invoice or the primary address)
     * or return an error message string otherwise
     *
     * @return bool|string
     */
    public function canDelete()
    {
        $translate = $this->getTranslate();

        if ($this->_usedInBankTransfers()) {
            return $translate->_('This address cannot be removed because it was used in a bank transfer.');
        }

        return true;
    }

    /**
     *
     * check if the bank account has been used in a bank transfer
     *
     * @return bool
     */
    protected function _usedInBankTransfers()
    {
        $rowset = $this->findDependentRowset('\Ppb\Db\Table\BankTransfers');
        if (count($rowset) > 0) {
            return true;
        }



        return false;
    }
}

