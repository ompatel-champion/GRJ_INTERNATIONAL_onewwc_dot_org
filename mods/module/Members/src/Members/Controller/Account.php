<?php

/**
 *
 * PHP Pro Bid
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2019 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     8.1 [rev.8.1.01]
 */

/**
 * members module - account controller
 */
/**
 * MOD:- ESCROW PAYMENTS
 *
 * @version 1.1
 */

namespace Members\Controller;

use Members\Controller\Action\AbstractAction,
    Ppb\Service,
    Ppb\Db\Expr\DateTime,
    Cube\Paginator;
## -- START :: ADD -- [ MOD:- ESCROW PAYMENTS ]
use Ppb\Db\Table;
## -- END :: ADD -- [ MOD:- ESCROW PAYMENTS ]

class Account extends AbstractAction
{

    /**
     *
     * users address book service
     *
     * @var \Ppb\Service\UsersAddressBook
     */
    protected $_addressBook;

    public function PersonalInformation()
    {
        $this->_forward('register', 'user', null, array('type' => 'personal-information'));
    }

    public function AccountSettings()
    {
        $this->_forward('register', 'user', null, array('type' => 'account-settings'));
    }

    public function AddressBook()
    {
        $addressBook = new Service\UsersAddressBook();

        $table = $addressBook->getTable();
        $select = $addressBook->getTable()->select()
            ->where('user_id = ?', $this->_user['id'])
            ->order('is_primary DESC, id DESC');

        $paginator = new Paginator(
            new Paginator\Adapter\DbTableSelect($select, $table));

        $pageNumber = $this->getRequest()->getParam('page');
        $paginator->setPageRange(5)
            ->setItemCountPerPage(5)
            ->setCurrentPageNumber($pageNumber);

        return array(
            'controller' => 'My Account',
            'user'       => $this->_user,
            'paginator'  => $paginator,
            'messages'   => $this->_flashMessenger->getMessages(),
        );
    }

    public function AddAddress()
    {
        $this->_forward('edit-address');
    }

    public function EditAddress()
    {
        $this->_forward('register', 'user', null,
            array('type' => 'manage-address', 'address_id' => $this->getRequest()->getParam('id')));
    }

    public function PrimaryAddress()
    {
        $address = $this->_user->getAddress(
            $this->getRequest()->getParam('id'));

        $result = $address->setPrimary();

        if ($result) {
            $this->_flashMessenger->setMessage(array(
                'msg'   => $this->_('Your primary address has been changed.'),
                'class' => 'alert-success',
            ));
        }

        $this->_helper->redirector()->redirect('address-book', null, null, array());
    }

    public function DeleteAddress()
    {
        $address = $this->_user->getAddress(
            $this->getRequest()->getParam('id'));

        if (($result = $address->canDelete()) === true) {
            $address->delete();

            $this->_flashMessenger->setMessage(array(
                'msg'   => $this->_('The address has been deleted.'),
                'class' => 'alert-success',
            ));
        }
        else {
            $this->_flashMessenger->setMessage(array(
                'msg'   => $result,
                'class' => 'alert-danger',
            ));
        }

        $this->_helper->redirector()->redirect('address-book', null, null, array());
    }

    public function History()
    {
        $accountingService = new Service\Accounting();
        $transactionsService = new Service\Transactions();

        $translate = $this->getTranslate();

        $option = $this->getRequest()->getParam('option');
        $userId = $this->getRequest()->getParam('user_id');

        ## -- START :: ADD -- [ MOD:- ESCROW PAYMENTS ]
        $msg = $this->getRequest()->getParam('msg');
        $class = $this->getRequest()->getParam('class', 'alert-info');

        if (!empty($msg)) {
            $this->_flashMessenger->setMessage(array(
                'msg'   => $msg,
                'class' => $class,
            ));
        }
        ## -- END :: ADD -- [ MOD:- ESCROW PAYMENTS ]

        if (!empty($option)) {
            /** @var \Ppb\Db\Table\Row\Accounting $accounting */
            $accounting = $accountingService->findBy('id', $this->getRequest()->getParam('id'));

            switch ($option) {
                case 'refund_request':
                    if ($accounting->makeRefundRequest()) {
                        $this->_flashMessenger->setMessage(array(
                            'msg'   => sprintf(
                                $translate->_('The refund request for transaction: "%s" has been made.'),
                                $accounting->displayName()),
                            'class' => 'alert-success',
                        ));

                        $mail = new \Admin\Model\Mail\Admin();
                        $mail->refundRequest($accounting)->send();
                    }
                    break;

                case 'refund_accept':
                    if ($accounting->acceptRefundRequest()) {
                        $this->_flashMessenger->setMessage(array(
                            'msg'   => sprintf(
                                $translate->_('The refund request for transaction: "%s" has been accepted.'),
                                $accounting->displayName()),
                            'class' => 'alert-success',
                        ));

                        $mail = new \Members\Model\Mail\User();
                        $mail->refundAccepted($accounting)->send();
                    }
                    break;

                case 'refund_decline':
                    if ($accounting->rejectRefundRequest()) {
                        $this->_flashMessenger->setMessage(array(
                            'msg'   => sprintf(
                                $translate->_('The refund request for transaction: "%s" has been declined.'),
                                $accounting->displayName()),
                            'class' => 'alert-success',
                        ));

                        $mail = new \Members\Model\Mail\User();
                        $mail->refundRejected($accounting)->send();
                    }
                    break;
            }
        }

        $inAdmin = $this->_loggedInAdmin();

        $dateFrom = urldecode($this->getRequest()->getParam('date_from'));
        $dateTo = urldecode($this->getRequest()->getParam('date_to'));
        $filter = $this->getRequest()->getParam('filter');

        $select = $accountingService->getTable()->getAdapter()->select()
            ->from(array('t' => 'accounting'))
            ->joinLeft(array('c' => 'currencies'), 'c.iso_code = t.currency', 'c.conversion_rate');

        if (!$inAdmin) {
            $select->where('t.user_id = ?', $this->_user['id']);
        }
        else if ($userId) {
            $select->where('t.user_id = ?', $userId);
        }

        if ($dateFrom) {
            $select->where('t.created_at > ?', new DateTime($dateFrom));
        }

        if ($dateTo) {
            $select->where('t.created_at < ?', new DateTime($dateTo));
        }

        $totals = array(
            'debit'    => 0,
            'credit'   => 0,
            'payments' => 0,
        );

        ## -- START :: ADD -- [ MOD:- ESCROW PAYMENTS ]
        // A. received escrow payments
        $inEscrow = clone $select;
        $inEscrow->reset(\Cube\Db\Select::FROM)
            ->from(array('t' => 'transactions'))
            ->reset(\Cube\Db\Select::COLUMNS)
            ->joinLeft(array('c' => 'currencies'), 'c.iso_code = t.currency', 'c.conversion_rate')
            ->joinLeft(array('s' => 'sales'), 's.id = t.sale_id', 's.seller_id')
            ->columns(array('t.amount', 't.currency'))
            ->columns('sum(t.amount / IF(c.conversion_rate > 0, c.conversion_rate, 1)) as total_amount')
            ->where('t.paid = ?', 1)
            ->where('t.sale_id is not null')
            ->where('t.escrow_buyer_admin = ?', 1);

        if (!$inAdmin) {
            $inEscrow->where('s.seller_id = ?', $this->_user['id']);
        }

        $totals['escrow_received'] = $transactionsService->fetchAll($inEscrow)->getRow(0)->getData('total_amount');

        // B. paid escrow payments
        $inEscrow = clone $select;
        $inEscrow->reset(\Cube\Db\Select::FROM)
            ->from(array('t' => 'transactions'))
            ->reset(\Cube\Db\Select::COLUMNS)
            ->joinLeft(array('c' => 'currencies'), 'c.iso_code = t.currency', 'c.conversion_rate')
            ->joinLeft(array('s' => 'sales'), 's.id = t.sale_id', 's.seller_id')
            ->columns(array('t.amount', 't.currency'))
            ->columns('sum(t.amount / IF(c.conversion_rate > 0, c.conversion_rate, 1)) as total_amount')
            ->where('t.paid = ?', 1)
            ->where('t.sale_id is not null')
            ->where('t.escrow_buyer_admin = ?', 0)
            ->where('t.escrow_admin_seller = ?', 1);

        if (!$inAdmin) {
            $inEscrow->where('s.seller_id = ?', $this->_user['id']);
        }

        $totals['escrow_sent'] = $transactionsService->fetchAll($inEscrow)->getRow(0)->getData('total_amount');

        $totals['in_escrow'] = $totals['escrow_received'] - $totals['escrow_sent'];
        ## -- END :: ADD -- [ MOD:- ESCROW PAYMENTS ]

        // create totals columns
        // 1. debits
        $debits = clone $select;
        $debits->reset(\Cube\Db\Select::COLUMNS)
            ->columns(array('t.amount', 't.currency'))
            ->columns('sum(t.amount / IF(c.conversion_rate > 0, c.conversion_rate, 1)) as total_amount')
            ->where('t.amount > 0');

        $totals['debit'] = $accountingService->fetchAll($debits)->getRow(0)->getData('total_amount');

        // 2. credits
        $credits = clone $select;
        $credits->reset(\Cube\Db\Select::COLUMNS)
            ->columns(array('t.amount', 't.currency'))
            ->columns('abs(sum(t.amount / IF(c.conversion_rate > 0, c.conversion_rate, 1))) as total_amount')
            ->where('t.amount < 0');

        $totals['credit'] = $accountingService->fetchAll($credits)->getRow(0)->getData('total_amount');

        // 3. payments
        $payments = clone $select;
        $payments->reset(\Cube\Db\Select::FROM)
            ->from(array('t' => 'transactions'))
            ->reset(\Cube\Db\Select::COLUMNS)
            ->joinLeft(array('c' => 'currencies'), 'c.iso_code = t.currency', 'c.conversion_rate')
            ->columns(array('t.amount', 't.currency'))
            ->columns('sum(t.amount / IF(c.conversion_rate > 0, c.conversion_rate, 1)) as total_amount')
            ->where('t.paid = ?', 1)
            ->where('t.sale_id is null');

        $totals['payments'] = $transactionsService->fetchAll($payments)->getRow(0)->getData('total_amount');

        if ($filter) {
            if ($filter == 'refund_requests') {
                $select->having('refund_flag = ?', \Ppb\Db\Table\Row\Accounting::REFUND_REQUESTED);
            }
            else {
                $select->having('transaction_filter = ?', $filter);
            }
        }

        $select->reset(\Cube\Db\Select::FROM)
            ->from(array('t' => 'accounting'))
            ->reset(\Cube\Db\Select::COLUMNS);

        // listing setup fees in account mode
        $listingsSelect = clone $select;
        ## -- ONE LINE :: CHANGE -- [ MOD:- ESCROW PAYMENTS ]
        $listingsSelect->columns('id, name, sum(amount) AS total_amount, currency, user_id, listing_id, sale_id, "setup_debit" as transaction_type, "Debit" as transaction_filter, refund_flag, null as escrow_buyer_admin, null as escrow_admin_seller, created_at',
            '')
            ->where('listing_id is not null')
            ->group('listing_id');

        // credit adjustments
        $creditsSelect = clone $select;
        ## -- ONE LINE :: CHANGE -- [ MOD:- ESCROW PAYMENTS ]
        $creditsSelect->columns('id, name, amount AS total_amount, currency, user_id, listing_id, sale_id, "credit" as transaction_type, "Credit" as transaction_filter, refund_flag, null as escrow_buyer_admin, null as escrow_admin_seller, created_at',
            '')
            ->where('listing_id is null')
            ->where('amount < 0');


        // all payments made by users for site fees / account crediting
        $transactionsSelect = clone $select;
        $transactionsSelect->reset(\Cube\Db\Select::FROM)
            ->from(array('t' => 'transactions'))
            ->reset(\Cube\Db\Select::COLUMNS)
            ## -- ONE LINE :: CHANGE -- [ MOD:- ESCROW PAYMENTS ]
            ->columns('id, name, amount AS total_amount, currency, user_id, null as listing_id, sale_id, "receipt" as transaction_type, "Receipt" as transaction_filter, null as refund_flag, escrow_buyer_admin, escrow_admin_seller, created_at',
                '')
            ->where('paid = ?', 1)
            ## -- ONE LINE :: ADD -- [ MOD:- ESCROW PAYMENTS ]
            ->where('sale_id is null or escrow_buyer_admin = 1 or escrow_admin_seller = 1');

        // other fees charged against a user's account
        ## -- ONE LINE :: CHANGE -- [ MOD:- ESCROW PAYMENTS ]
        $select->columns('id, name, amount AS total_amount, currency, user_id, listing_id, sale_id, "other_debit" as transaction_type, "Debit" as transaction_filter, refund_flag, null as escrow_buyer_admin, null as escrow_admin_seller, created_at',
            '')
            ->where('listing_id is null')
            ->where('amount > 0')
            ->union(array($listingsSelect, $creditsSelect, $transactionsSelect))
            ->order('created_at DESC');

        $paginator = new Paginator(
            new Paginator\Adapter\DbTableSelect($select, $accountingService->getTable()));

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
            'filter'     => $filter,
            'totals'     => $totals,
        );
    }

    public function ViewInvoice()
    {
        $id = $this->getRequest()->getParam('id', 0);
        $listingId = $this->getRequest()->getParam('listing_id');
        $type = $this->getRequest()->getParam('type');

        $inAdmin = $this->_loggedInAdmin();

        switch ($type) {
            case 'accounting':
                $accountingService = new Service\Accounting();
                /** @var \Ppb\Db\Table\Row\Accounting $row */
                $select = $accountingService->getTable()->select();

                if (!$inAdmin) {
                    $select->where('user_id = ?', $this->_user['id']);
                }

                if ($listingId) {
                    $select->where('listing_id = ?', $listingId);
                }
                else {
                    $select->where('id = ?', $id);
                }

                $rowset = $accountingService->fetchAll($select);
                break;
            default:
                $transactionsService = new Service\Transactions();

                $select = $transactionsService->getTable()->select()
                    ->where('id = ?', $id);

                if (!$inAdmin) {
                    $select->where('user_id = ?', $this->_user['id']);
                }

                $rowset = $transactionsService->fetchAll($select);
                break;
        }

        if (!count($rowset)) {
            $this->_flashMessenger->setMessage(array(
                'msg'   => $this->_('The invoice cannot be accessed'),
                'class' => 'alert-danger',
            ));

            $this->_helper->redirector()->redirect('history');
        }

        return array(
            'controller' => ($inAdmin) ? 'Tools' : 'My Account',
            'headline'   => $this->_('View/Print Invoice'),
            'rowset'     => $rowset,

        );
    }
}

