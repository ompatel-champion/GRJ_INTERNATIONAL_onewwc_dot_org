<?php

/**
 *
 * PHP Pro Bid
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2015 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.6
 */
/**
 * payment forms controller
 */
/**
 * MOD:- SELLERS CREDIT
 * Empty Extended Controller
 * MOD:- ESCROW PAYMENTS
 * Empty Extended Controller
 * MOD:- BANK TRANSFER
 */

namespace App\Controller;

use Ppb\Service,
    App\Form;

class PaymentExtended extends Payment
{

    public function BankTransfer()
    {
        $this->_view->setViewFileName('bank-transfer.phtml');

        $form = null;

        $transactionId = $this->getRequest()->getParam('transaction_id');
        $transaction = null;
        $transactionName = null;

        $userId = (!empty($this->_user['id'])) ? $this->_user['id'] : 0;

        $select = $this->_transactions->getTable()->select()
            ->where('user_id = ? OR user_id is null', $userId)
            ->where('id = ?', $transactionId);

        /** @var \Ppb\Db\Table\Row\Transaction $transaction */
        $transaction = $this->_transactions->getTable()->fetchRow($select);


        if (count($transaction) > 0) {
            $form = new Form\BankTransfer($transaction);

            $gatewayModel = new \Ppb\Model\PaymentGateway\BankTransfer();
            $gatewayModel->setName(\Ppb\Utility::unserialize($transaction['name']));

            $transactionName = $gatewayModel->getName();

            if ($form->isPost(
                $this->getRequest())
            ) {

                $params = $this->getRequest()->getParams();
                $form->setData($params);

                if ($form->isValid() === true) {
                    // save transfer;
                    $bankTransfersService = new Service\BankTransfers();
                    $bankTransfersService->save($params);

                    $this->_flashMessenger->setMessage(array(
                        'msg'   => 'The bank transfer record has been saved. Thank you for your business.',
                        'class' => 'alert-success',
                    ));

                    $form->clearElements();

                    if (!$userId) {
                        $this->_helper->redirector()->redirect(
                            'completed', null, null, array());
                    }
                    else {
                        $this->_helper->redirector()->redirect(
                            'bank-transfers', 'account', 'members', array());
                    }
                }
                else {
                    $this->_flashMessenger->setMessage(array(
                        'msg'   => $form->getMessages(),
                        'class' => 'alert-danger',
                    ));
                }
            }
        }
        else {
            $this->_flashMessenger->setMessage(array(
                'msg'   => $this->_('Error: Could not generate the bank transfer form - the transaction does not exist or you are not a party in the transaction.'),
                'class' => 'alert-danger',
            ));
        }

        return array(
            'headline'        => $this->_('Bank Transfer'),
            'form'            => $form,
            'transaction'     => $transaction,
            'transactionName' => $transactionName,
            'messages'        => $this->_flashMessenger->getMessages(),
        );
    }

}

