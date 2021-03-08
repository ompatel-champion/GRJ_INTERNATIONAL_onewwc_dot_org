<?php

/**
 *
 * PHP Pro Bid
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.3
 */
/**
 * members module - account controller
 */
/**
 * MOD:- SELLERS CREDIT
 *
 * @version 1.2
 * MOD:- ESCROW PAYMENTS
 * Empty Extended Controller
 * MOD:- BANK TRANSFER
 */

namespace Members\Controller;

use Ppb\Service,
    Ppb\Db\Table,
    Ppb\Db\Expr\DateTime,
    Ppb\Model\PaymentGateway,
    Ppb\Db\Table\Row\Sale as SaleModel,
    Cube\Db\Expr,
    Cube\Paginator;

class AccountExtended extends Account
{
    public function BalanceWithdrawals()
    {
        $balanceWithdrawalsService = new Service\BalanceWithdrawals();

        $option = $this->getRequest()->getParam('option');
        $userId = $this->getRequest()->getParam('user_id');

        if ($this->getRequest()->isPost()) {
            $amount = $this->getRequest()->getParam('amount');
            $currency = $this->_settings['currency'];

            if ($this->_user->canPaySellersCredit($amount, $currency) === true) {
                $this->_user->updateBalance($amount);

                $balanceWithdrawalsService->save(array(
                    'user_id'  => $this->_user['id'],
                    'amount'   => $amount,
                    'currency' => $currency,
                ));

                $withdrawalId = $balanceWithdrawalsService->getTable()->lastInsertId();

                /** @var \Ppb\Db\Table\Row\BalanceWithdrawal $balanceWithdrawal */
                $balanceWithdrawal = $balanceWithdrawalsService->findBy('id', $withdrawalId);
                $mail = new \Members\Model\Mail\BalanceWithdrawals();
                $mail->adminNotification($balanceWithdrawal)->send();


                $this->_flashMessenger->setMessage(array(
                    'msg'   => $this->_('The withdrawal request has been saved. Thank you for your business.'),
                    'class' => 'alert-success',
                ));
            }
            else {
                $this->_flashMessenger->setMessage(array(
                    'msg'   => $this->_('The withdrawal request could not be saved. Not enough credit.'),
                    'class' => 'alert-danger',
                ));
            }
        }

        if (!empty($option)) {
            $withdrawalId = $this->getRequest()->getParam('id');

            /** @var \Ppb\Db\Table\Row\BalanceWithdrawal $balanceWithdrawal */
            $balanceWithdrawal = $balanceWithdrawalsService->findBy('id', $withdrawalId);

            switch ($option) {
                case 'accept':
                    if ($balanceWithdrawal->canAccept()) {
                        $balanceWithdrawal->accept();

                        $this->_flashMessenger->setMessage(array(
                            'msg'   => $this->_('The withdrawal request has been marked as paid.'),
                            'class' => 'alert-success',
                        ));

                    }
                    break;

                case 'decline':
                    if ($balanceWithdrawal->canDecline()) {
                        $balanceWithdrawal->decline();

                        $this->_flashMessenger->setMessage(array(
                            'msg'   => $this->_('The withdrawal request has been declined.'),
                            'class' => 'alert-success',
                        ));
                    }
                    break;

                case 'cancel':
                    if ($balanceWithdrawal->canCancel()) {
                        $balanceWithdrawal->cancel();

                        $this->_flashMessenger->setMessage(array(
                            'msg'   => $this->_('The bank transfer has been cancelled.'),
                            'class' => 'alert-success',
                        ));

                    }
                    break;
                case 'delete':
                    if ($balanceWithdrawal->canDelete()) {
                        $this->_flashMessenger->setMessage(array(
                            'msg'   => $this->_('The bank transfer has been deleted.'),
                            'class' => 'alert-success',
                        ));

                        $balanceWithdrawal->delete();
                    }
                    break;
            }
        }

        $inAdmin = $this->_loggedInAdmin();

        $dateFrom = $this->getRequest()->getParam('date_from');
        $dateTo = $this->getRequest()->getParam('date_to');
        $filter = $this->getRequest()->getParam('filter');

        $select = $balanceWithdrawalsService->getTable()->getAdapter()->select()
            ->from(array('b' => 'balance_withdrawals'))
            ->joinLeft(array('c' => 'currencies'), 'c.iso_code = b.currency', 'c.conversion_rate');

        if (!$inAdmin) {
            $select->where('b.user_id = ?', $this->_user['id']);
        }

        if ($dateFrom) {
            $select->where('b.created_at > ?', new DateTime($dateFrom));
        }

        if ($dateTo) {
            $select->where('b.created_at < ?', new DateTime($dateTo));
        }

        if ($filter) {
            $select->where('b.status = ?', $filter);
        }

        $totals = array(
            'pending'   => 0,
            'paid'      => 0,
            'declined'  => 0,
            'cancelled' => 0,
        );

        // create totals columns
        foreach ($totals as $key => $total) {
            $where = clone $select;
            $where->reset(\Cube\Db\Select::COLUMNS)
                ->columns(array('b.amount', 'b.currency'))
                ->columns('sum(b.amount / IF(c.conversion_rate > 0, c.conversion_rate, 1)) as total_amount')
                ->where('b.status = ?', $key);

            $totals[$key] = $balanceWithdrawalsService->fetchAll($where)->getRow(0)->getData('total_amount');
        }

        $select->order('b.created_at DESC');

        $paginator = new Paginator(
            new Paginator\Adapter\DbTableSelect($select, $balanceWithdrawalsService->getTable()));

        $pageNumber = $this->getRequest()->getParam('page');
        $paginator->setPageRange(5)
            ->setItemCountPerPage(20)
            ->setCurrentPageNumber($pageNumber);

        return array(
            'controller' => ($inAdmin) ? 'Tools' : 'My Account',
            'paginator'  => $paginator,
            'messages'   => $this->_flashMessenger->getMessages(),
            'inAdmin'    => $inAdmin,
            'userId'     => $userId,
            'dateFrom'   => $dateFrom,
            'dateTo'     => $dateTo,
            'totals'     => $totals,
        );
    }


    public function BankAccounts()
    {
        $bankAccountsService = new Service\BankAccounts();

        $inAdmin = $this->_loggedInAdmin();

        $table = $bankAccountsService->getTable();
        $select = $bankAccountsService->getTable()->select()
            ->order('id DESC');

        if (!$inAdmin) {
            $select->where('user_id = ?', $this->_user['id']);
        }
        else {
            $select->where('user_id is null');
        }

        $paginator = new Paginator(
            new Paginator\Adapter\DbTableSelect($select, $table));

        $pageNumber = $this->getRequest()->getParam('page');
        $paginator->setPageRange(5)
            ->setItemCountPerPage(5)
            ->setCurrentPageNumber($pageNumber);

        $array = array(
            'controller' => ($inAdmin) ? 'Fees' : 'My Account',
            'user'       => $this->_user,
            'paginator'  => $paginator,
        );

        if (!$inAdmin) {
            $array['messages'] = $this->_flashMessenger->getMessages();
        }

        return $array;
    }

    public function AddBankAccount()
    {
        $this->_forward('edit-bank-account');
    }

    public function EditBankAccount()
    {
        $id = $this->getRequest()->getParam('id');

        $bankAccountsService = new Service\BankAccounts();

        $inAdmin = $this->_loggedInAdmin();

        if ($id) {
            $select = $bankAccountsService->getTable()->select()
                ->where('id = ?', $id);

            if (!$inAdmin) {
                $select->where('user_id = ?', $this->_user['id']);
            }

            /** @var \Ppb\Db\Table\Row\BankAccount $bankAccount */
            $bankAccount = $bankAccountsService->getTable()->fetchRow($select);

            if (count($bankAccount) > 0) {
                if (($result = $bankAccount->canDelete()) !== true) {
                    $this->_flashMessenger->setMessage(array(
                        'msg'   => $result,
                        'class' => 'alert-danger',
                    ));

                    if ($inAdmin) {
                        $this->_helper->redirector()->redirect('gateways', 'fees', 'admin', array());
                    }
                    else {
                        $this->_helper->redirector()->redirect('bank-accounts', null, null, array());
                    }
                }

                $data = $bankAccount->toArray();
            }
            else {
                $id = null;
                $data = array();
            }
        }

        $userId = null;
        if (!$inAdmin) {
            $userId = $this->_user['id'];
        }

        $form = new \Members\Form\BankAccount(array('bank_account'), null, $userId);

        if ($id) {
            $form->setData($data)
                ->generateEditForm();
        }

        if ($this->getRequest()->isPost()) {
            $params = $this->getRequest()->getParams();
            $form->setData($params);

            if ($form->isValid() === true) {
                $params['user_id'] = ($inAdmin) ? null : $this->_user['id'];

                $bankAccountsService->save($params, $userId);

                $this->_flashMessenger->setMessage(array(
                    'msg'   => ($id) ?
                            $this->_('The bank account has been edited successfully') :
                            $this->_('The bank account has been added successfully.'),
                    'class' => 'alert-success',
                ));

                if ($inAdmin) {
                    $this->_helper->redirector()->redirect('gateways', 'fees', 'admin', array());
                }
                else {
                    $this->_helper->redirector()->redirect('bank-accounts', null, null, array());
                }
            }
            else {
                $this->_flashMessenger->setMessage(array(
                    'msg'   => $form->getMessages(),
                    'class' => 'alert-danger',
                ));
            }
        }

        return array(
            'controller' => ($inAdmin) ? 'Fees' : 'Account',
            'headline'   => ($id) ? $this->_('Edit Bank Account') : $this->_('Add Bank Account'),
            'form'       => $form,
            'messages'   => $this->_flashMessenger->getMessages(),
        );
    }


    public function DeleteBankAccount()
    {
        $id = $this->getRequest()->getParam('id');

        $bankAccountsService = new Service\BankAccounts();

        $inAdmin = $this->_loggedInAdmin();

        $select = $bankAccountsService->getTable()->select()
            ->where('id = ?', $id);

        if (!$inAdmin) {
            $select->where('user_id = ?', $this->_user['id']);
        }

        /** @var \Ppb\Db\Table\Row\BankAccount $bankAccount */
        $bankAccount = $bankAccountsService->getTable()->fetchRow($select);

        if (count($bankAccount) > 0) {
            if (($result = $bankAccount->canDelete()) === true) {
                $bankAccount->delete();

                $this->_flashMessenger->setMessage(array(
                    'msg'   => $this->_('The bank account has been deleted.'),
                    'class' => 'alert-success',
                ));
            }
            else {
                $this->_flashMessenger->setMessage(array(
                    'msg'   => $result,
                    'class' => 'alert-danger',
                ));
            }
        }

        if ($inAdmin) {
            $this->_helper->redirector()->redirect('gateways', 'fees', 'admin', array());
        }
        else {
            $this->_helper->redirector()->redirect('bank-accounts', null, null, array());
        }
    }

    public function BankTransfers()
    {
        $bankTransfersService = new Service\BankTransfers();

        $option = $this->getRequest()->getParam('option');
        $userId = $this->getRequest()->getParam('user_id');

        if (!empty($option)) {
            $bankTransferId = $this->getRequest()->getParam('id');

            /** @var \Ppb\Db\Table\Row\BankTransfer $bankTransfer */
            $bankTransfer = $bankTransfersService->findBy('id', $bankTransferId);

            switch ($option) {
                case 'accept':
                    if ($bankTransfer->canAccept()) {
                        $transaction = $bankTransfer->findParentRow('\Ppb\Db\Table\Transactions');

                        $paymentGatewaysService = new Service\Table\PaymentGateways();
                        $gateway = $paymentGatewaysService->findBy('name', 'BankTransfer');

                        $transaction->save(array(
                            'gateway_id'               => $gateway['id'],
                            'gateway_transaction_code' => 'BankTransferTXN',
                            'gateway_status'           => 'paid',
                            'paid'                     => 1,
                        ));

                        // run callback process
                        $transactionDetails = \Ppb\Utility::unserialize($transaction['transaction_details']);

                        $className = $transactionDetails['class'];
                        $feesService = new $className();

                        if ($feesService instanceof Service\Fees) {
                            $feesService->callback(SaleModel::PAYMENT_PAID_BANK_TRANSFER, $transactionDetails['data']);
                        }

                        $bankTransfer->save(array(
                            'transfer_status' => Service\BankTransfers::STATUS_PAID,
                            'updated_at'      => new Expr('now()'),
                        ));

                        $this->_flashMessenger->setMessage(array(
                            'msg'   => $this->_('The bank transfer has been marked as paid.'),
                            'class' => 'alert-success',
                        ));

                    }
                    break;

                case 'decline':
                    if ($bankTransfer->canDecline()) {
                        $this->_flashMessenger->setMessage(array(
                            'msg'   => $this->_('The bank transfer has been declined.'),
                            'class' => 'alert-success',
                        ));

                        $bankTransfer->save(array(
                            'transfer_status' => Service\BankTransfers::STATUS_DECLINED,
                            'updated_at'      => new Expr('now()'),
                        ));
                    }
                    break;

                case 'cancel':
                    if ($bankTransfer->canCancel()) {
                        $this->_flashMessenger->setMessage(array(
                            'msg'   => $this->_('The bank transfer has been cancelled.'),
                            'class' => 'alert-success',
                        ));

                        $bankTransfer->save(array(
                            'transfer_status' => Service\BankTransfers::STATUS_CANCELLED,
                            'updated_at'      => new Expr('now()'),
                        ));
                    }
                    break;
                case 'delete':
                    if ($bankTransfer->canDelete()) {
                        $this->_flashMessenger->setMessage(array(
                            'msg'   => $this->_('The bank transfer has been deleted.'),
                            'class' => 'alert-success',
                        ));

                        $bankTransfer->delete();
                    }
                    break;
            }
        }

        $inAdmin = $this->_loggedInAdmin();

        $dateFrom = $this->getRequest()->getParam('date_from');
        $dateTo = $this->getRequest()->getParam('date_to');
        $filter = $this->getRequest()->getParam('filter');

        $select = $bankTransfersService->getTable()->getAdapter()->select()
            ->from(array('b' => 'bank_transfers'))
            ->joinLeft(array('t' => 'transactions'), 't.id = b.transaction_id', 't.amount')
            ->joinLeft(array('s' => 'sales'), 's.id = t.sale_id', 's.seller_id AS seller_id')
            ->joinLeft(array('c' => 'currencies'), 'c.iso_code = t.currency', 'c.conversion_rate');

        if (!$inAdmin) {
            $select->where('t.user_id = "' . $this->_user['id'] . '" OR (seller_id = "' . $this->_user['id'] . '")');
        }

        if ($dateFrom) {
            $select->where('b.created_at > ?', new DateTime($dateFrom));
        }

        if ($dateTo) {
            $select->where('b.created_at < ?', new DateTime($dateTo));
        }

        if ($filter) {
            $select->where('b.transfer_status = ?', $filter);
        }

        $totals = array(
            'pending'   => 0,
            'paid'      => 0,
            'declined'  => 0,
            'cancelled' => 0,
        );

        // create totals columns
        foreach ($totals as $key => $total) {
            $where = clone $select;
            $where->reset(\Cube\Db\Select::COLUMNS)
                ->columns(array('t.amount', 't.currency'))
                ->columns('sum(t.amount / IF(c.conversion_rate > 0, c.conversion_rate, 1)) as total_amount')
                ->where('b.transfer_status = ?', $key);

            $totals[$key] = $bankTransfersService->fetchAll($where)->getRow(0)->getData('total_amount');
        }

        $select->order('b.created_at DESC');

        $paginator = new Paginator(
            new Paginator\Adapter\DbTableSelect($select, $bankTransfersService->getTable()));

        $pageNumber = $this->getRequest()->getParam('page');
        $paginator->setPageRange(5)
            ->setItemCountPerPage(20)
            ->setCurrentPageNumber($pageNumber);

        return array(
            'controller' => ($inAdmin) ? 'Tools' : 'My Account',
            'paginator'  => $paginator,
            'messages'   => $this->_flashMessenger->getMessages(),
            'inAdmin'    => $inAdmin,
            'userId'     => $userId,
            'dateFrom'   => $dateFrom,
            'dateTo'     => $dateTo,
            'totals'     => $totals,
        );
    }

}

