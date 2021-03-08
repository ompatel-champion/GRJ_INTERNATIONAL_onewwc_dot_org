<?php

/**
 *
 * PHP Pro Bid
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2015 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.4
 */
/**
 * MOD:- BANK TRANSFER
 */

namespace Admin\Controller;

use Ppb\Service,
    Admin\Form;

class FeesExtended extends Fees
{
    public function AddBankAccount()
    {
        $this->_forward('add-bank-account', 'account', 'members');
    }

    public function EditBankAccount()
    {
        $this->_forward('edit-bank-account', 'account', 'members');
    }

    public function DeleteBankAccount()
    {
        $this->_forward('delete-bank-account', 'account', 'members');
    }
}