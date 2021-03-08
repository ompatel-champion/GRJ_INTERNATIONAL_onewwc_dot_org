<?php

/**
 *
 * PHP Pro Bid
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.1
 */
/**
 * MOD:- SELLERS CREDIT
 * MOD:- ESCROW PAYMENTS
 * MOD:- BANK TRANSFER
 * MOD:- ESCROW & BANK TRANSFERS
 * MOD:- PICKUP LOCATIONS
 */

namespace Admin\Controller;

use Admin\Form,
    Ppb\Db\Table,
    Cube\Paginator,
    Ppb\Service;

class ToolsExtended extends Tools
{

    public function BalanceWithdrawals()
    {
        $params = null;

        $userId = $this->getRequest()->getParam('id');
        if ($userId) {
            $params = array(
                'user_id' => $userId,
            );
        }
        $this->_forward('balance-withdrawals', 'account', 'members', $params);
    }
    
    public function PayEscrow()
    {
        $transactionId = $this->getRequest()->getParam('id');

        $transactionsService = new Service\Transactions();

        $transaction = $transactionsService->findBy('id', $transactionId);

        $redirected = false;
        /** @var \Ppb\Db\Table\Row\Transaction $transaction */
        if (count($transaction) > 0) {
            if ($transaction->canPayEscrow()) {
                $redirected = true;
                $this->_forward('direct-payment', 'payment', 'app', array('id' => $transaction['sale_id']));
            }
        }

        if (!$redirected) {
            $this->_helper->redirector()->redirect('accounting', null, null, array());
        }
    }
    
    public function BankTransfers()
    {
        $params = null;

        $userId = $this->getRequest()->getParam('id');
        if ($userId) {
            $params = array(
                'user_id' => $userId,
            );
        }
        $this->_forward('bank-transfers', 'account', 'members', $params);
    }    

    public function BankTransferPaySeller()
    {
        $this->_forward('bank-transfer', 'payment', 'app');
    }    
    
    public function PickupLocations()
    {
        $storeLocationsService = new Service\StorePickupLocations();

        $table = $storeLocationsService->getTable();
        $select = $storeLocationsService->getTable()->select()
            ->order('id DESC');

        $paginator = new Paginator(
            new Paginator\Adapter\DbTableSelect($select, $table));

        $pageNumber = $this->getRequest()->getParam('page');
        $paginator->setPageRange(5)
            ->setItemCountPerPage(5)
            ->setCurrentPageNumber($pageNumber);

        return array(
            'paginator' => $paginator,
            'messages'  => $this->_flashMessenger->getMessages(),
        );
    }

    public function AddPickupLocation()
    {
        $this->_forward('manage-pickup-location');
    }

    public function ManagePickupLocation()
    {
        $storeLocationsService = new Service\StorePickupLocations();

        $id = $this->getRequest()->getParam('id');
        $user = null;

        $formId = array('store-location');

        $form = new \Members\Form\Register(
            $formId);

        if ($id) {
            $storeLocation = $storeLocationsService->findBy('id', $id);
            $form->setData(
                $storeLocation->getData())
                ->generateEditForm($id);
        }

        if ($this->getRequest()->isPost()) {
            $params = $this->getRequest()->getParams();
            $form->setData($params);

            if ($form->isValid() === true) {
                $id = $storeLocationsService->save($params);

                $this->_flashMessenger->setMessage(array(
                    'msg'   => ($id) ?
                        $this->_('The pickup location has been edited successfully') :
                        $this->_('The pickup location has been created successfully.'),
                    'class' => 'alert-success',
                ));

                $this->_helper->redirector()->redirect('pickup-locations');
            }
            else {
                $this->_flashMessenger->setMessage(array(
                    'msg'   => $form->getMessages(),
                    'class' => 'alert-danger',
                ));
            }
        }

        return array(
            'form'     => $form,
            'messages' => $this->_flashMessenger->getMessages()
        );
    }


    public function DeletePickupLocation()
    {
        $storeLocationsService = new Service\StorePickupLocations();

        /** @var \Ppb\Db\Table\Row\StorePickupLocation $storeLocation */
        $storeLocation = $storeLocationsService->findBy('id', $this->getRequest()->getParam('id'));

        if (($result = $storeLocation->canDelete()) === true) {
            $storeLocation->delete();

            $this->_flashMessenger->setMessage(array(
                'msg'   => $this->_('The pickup location has been deleted.'),
                'class' => 'alert-success',
            ));
        }
        else {
            $this->_flashMessenger->setMessage(array(
                'msg'   => $result,
                'class' => 'alert-danger',
            ));
        }

        $this->_helper->redirector()->redirect('pickup-locations');
    }
    
}