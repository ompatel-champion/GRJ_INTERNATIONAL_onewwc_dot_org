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
 * MOD:- SELLERS CREDIT
 */

namespace Members\Model\Mail;

use Ppb\Model\BaseMail,
    Ppb\Service,
    Ppb\Db\Table\Row\BalanceWithdrawal;

class BalanceWithdrawals extends BaseMail
{

    public function adminNotification(BalanceWithdrawal $withdrawal)
    {
        $username = $withdrawal->findParentRow('\Ppb\Db\Table\Users')->getData('username');
        $this->setData(array(
            'username' => $username,
            'withdrawal' => $withdrawal,
        ));

        $this->_mail->setFrom($this->_settings['admin_email'], $this->_settings['email_admin_title'])
            ->setTo($this->_settings['admin_email'])
            ->setSubject('Balance Withdrawal Request');

        $this->_view->headerMessage = $this->_('Balance Withdrawal Request');
        $this->_view->clearContent()
            ->process(__DIR__ . '/../../../../view/emails/balance-withdrawal-request.phtml');

        return $this;
    }

}

