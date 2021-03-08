<?php

/**
 *
 * PHP Pro Bid
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2016 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.7
 */
/**
 * members module - tools management controller
 */
/**
 * MOD:- EBAY IMPORTER
 *
 * @version 2.0
 * MOD:- DISCOUNT RULES
 *
 * @version 2.1 
 */

namespace Members\Controller;

use Ppb\Service,
    Ppb\Service\Listings\BulkLister\Ebay as EbayService,
    Cube\View,
    Cube\Paginator,
    Ppb\Db\Table\Row\Listing as ListingModel,
    Ppb\Db\Table\Row\User as UserModel,
    Members\Form,
    Listings\Form\Listing as ListingForm;

class ToolsExtended extends Tools
{
    /**
     *
     * ebay user (ebay_users table)
     *
     * @var \Cube\Db\Table\Row
     */
    protected $_ebayUser;

    public function EbayImport()
    {
        $form = new Form\EbayImport();

        $params = $this->getRequest()->getParams();
        $form->setData($params);

        if ($form->isPost(
            $this->getRequest())
        ) {

            if ($form->isValid() === true) {
                $importType = $this->getRequest()->getParam('import_type');

                if (in_array($importType, array(EbayService::IMPORT_TYPE_ALL_INC_DUPLICATES, EbayService::IMPORT_TYPE_ALL_WO_DUPLICATES))) {
                    $this->_user->removeEbayImportedListings();
                }

                $form->postAsync();
            }
            else {
                $this->_flashMessenger->setMessage(array(
                    'msg'   => $form->getMessages(),
                    'class' => 'alert-danger',
                ));
            }
        }

        return array(
            'controller' => 'Selling',
            'headline'   => $this->_('Ebay Import Tool'),
            'form'       => $form,
            'messages'   => $this->_flashMessenger->getMessages(),
        );
    }

    public function EbayGenerateToken()
    {
        $ebayAPIService = new Service\EbayAPI();

        $ebayToken = $this->getRequest()->getParam('ebaytkn');
        $ebayUsername = $this->getRequest()->getParam('username');
        $ebayMarketplace = $this->getRequest()->getParam('marketplace');

        $token = null;
        if (strlen($ebayToken)) {
            $token = $ebayToken;
        }
        else {
            $sessionId = $this->getRequest()->getParam('SessionID');

            if (isset($sessionId)) {
                $token = $ebayAPIService->callTradeAPI('FetchToken', "\n  <SessionID>{$sessionId}</SessionID>\n", 'eBayAuthToken');
            }
            else {
                $this->_flashMessenger->setMessage(array(
                    'msg'   => $this->_('The token could not be generated.'),
                    'class' => 'alert-danger',
                ));
            }

        }

        if (!empty($token)) {
            // save token
            $ebayUsersService = new Service\EbayUsers();
            $ebayUser = $ebayUsersService->findUser($ebayUsername, $this->_user['id']);

            $data = array(
                'ebay_username'    => $ebayUsername,
                'ebay_token'       => $token,
                'ebay_marketplace' => $ebayMarketplace,
                'user_id'          => $this->_user['id'],
            );

            if (count($ebayUser) > 0) {
                $data['id'] = $ebayUser['id'];
            }

            $ebayUsersService->save($data);

            $this->_flashMessenger->setMessage(array(
                'msg'   => $this->_('The token has been successfully generated.'),
                'class' => 'alert-success',
            ));
        }

        $this->_helper->redirector()->redirect('ebay-import', null, null, array('ebay_username' => $ebayUsername, 'ebay_marketplace' => $ebayMarketplace));
    }

    public function ParseEbayItemsAsync()
    {
        $pageNumber = $this->getRequest()->getParam('pageNumber', 1);
        $entriesPerPage = $this->getRequest()->getParam('entriesPerPage', 25);

        $totalListings = $this->getRequest()->getParam('totalListings');
        $uploadAs = $this->getRequest()->getParam('uploadAs');
        $importType = $this->getRequest()->getParam('importType');

        $parseErrors = array();
        $listingMessages = array();

        $ebayUsername = $this->getRequest()->getParam('ebayUsername');
        $ebayMarketplace = $this->getRequest()->getParam('ebayMarketplace');

        $ebayBulkListerService = new Service\Listings\BulkLister\Ebay();
        $ebayBulkListerService->setPrefilledFields(
            $this->_user->getPrefilledFields());

        $ebayUsersService = new Service\EbayUsers();
        $ebayUser = $ebayUsersService->findUser($ebayUsername, $this->_user['id']);

        if (!$ebayUser) {
            $ebayUserId = $ebayUsersService->save(array(
                'ebay_username'    => $ebayUsername,
                'ebay_marketplace' => $ebayMarketplace,
                'user_id'          => $this->_user['id'],
            ));
        }
        else {
            $ebayUserId = $ebayUser['id'];
        }

        /** @var \Cube\Db\Table\Row $ebayUser */
        $ebayUser = $ebayUsersService->findBy('id', $ebayUserId);

        $rows = $ebayBulkListerService->import($ebayUser, $pageNumber, $entriesPerPage, $totalListings, $importType);
        // we will import the rows using the remote xml - a procedure to be developed.

        $listingForm = new ListingForm('bulk');
        $duplicateRows = 0;

        $validRows = array();
        foreach ($rows as $id => $data) {
            if ($data === null) {
                $duplicateRows++;
            }
            else {
                $listingForm->setData($data)
                    ->removeElement('csrf');

                if (!$listingForm->isValid()) {
                    $msgTitle = array(sprintf($this->_("<strong>Parse error(s) - ebay item id #%s:</strong>"), $data['ebay_item_id']));

                    $parseErrors = array_merge($parseErrors, $msgTitle, $listingForm->getMessages());
                }
                else {
                    $validRows[] = $data;
                }
            }

            $listingForm->clearMessages();
        }

        if (count($validRows) > 0) {
            // save listings
            foreach ($validRows as $id => $data) {
                if ($uploadAs == 'bulk') {
                    $data['draft'] = 1;
                }

                $data['ebay_user_id'] = $ebayUserId;

                $listingId = $ebayBulkListerService->save($data);

                $listingModel = $ebayBulkListerService->findBy('id', $listingId, false, true);
                $message = $listingModel->processPostSetupActions();

                if ($message) {
                    $listingMessages[] = $message;
                }
            }
        }

        $this->getResponse()->setHeader('Content-Type: application/json');

        $view = new View();
        $view->setContent(json_encode(array(
            'counter'         => count($rows),
            'parseErrors'     => $parseErrors,
            'listingMessages' => $listingMessages,
            'validRows'       => count($validRows),
            'duplicateRows'   => $duplicateRows,
        )));

        return $view;
    }

    public function EbayRemoveItems()
    {
        $this->_user->removeEbayImportedListings();

        $this->_flashMessenger->setMessage(array(
            'msg'   => $this->_('The ebay listings you have imported were removed successfully.'),
            'class' => 'alert-success',
        ));

        $this->_helper->redirector()->redirect('ebay-import');
    }
    

    /**
     *
     * discount rules service
     *
     * @var \Ppb\Service\DiscountRules
     */
    protected $_discountRules;

    public function init()
    {
        parent::init();

        $this->_discountRules = new Service\DiscountRules();
    }

    public function DiscountRules()
    {
        $inAdmin = $this->_loggedInAdmin();

        $select = $this->_discountRules->getTable()->select()
            ->order(array('priority DESC'));

        if (!$inAdmin) {
            $select->where('user_id = ?', $this->_user['id']);
        }

        $paginator = new Paginator(
            new Paginator\Adapter\DbTableSelect($select, $this->_vouchers->getTable()));

        $pageNumber = $this->getRequest()->getParam('page');
        $paginator->setPageRange(5)
            ->setItemCountPerPage(10)
            ->setCurrentPageNumber($pageNumber);

        return array(
            'controller' => ($inAdmin) ? 'Tools' : 'Selling',
            'paginator'  => $paginator,
            'messages'   => $this->_flashMessenger->getMessages(),
        );
    }


    public function AddDiscountRule()
    {
        $this->_forward('edit-discount-rule');
    }

    public function EditDiscountRule()
    {
        $id = $this->getRequest()->getParam('id');

        $inAdmin = $this->_loggedInAdmin();

        if ($id) {
            $select = $this->_discountRules->getTable()->select()
                ->where('id = ?', $id);

            if (!$inAdmin) {
                $select->where('user_id = ?', $this->_user['id']);
            }

            $data = $this->_discountRules->getTable()->fetchRow($select);

            if (count($data) > 0) {
                $data = $data->toArray();
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

        $form = new \Members\Form\DiscountRule(null, $userId);

        if ($id) {
            $form->setData($data)
                ->generateEditForm();
        }

        if ($this->getRequest()->isPost()) {
            $params = $this->getRequest()->getParams();
            $form->setData($params);

            $formData = array_filter($form->getData());

            // needed for form element filters
            $params = array_merge($params, $formData);

            if ($form->isValid() === true) {
                $params['user_id'] = ($inAdmin) ? null : $this->_user['id'];

                $this->_discountRules->save($params);

                $this->_flashMessenger->setMessage(array(
                    'msg'   => ($id) ?
                        $this->_('The discount rule has been edited successfully') :
                        $this->_('The discount rule has been created successfully.'),
                    'class' => 'alert-success',
                ));

                if ($inAdmin) {
                    $this->_helper->redirector()->redirect('discount-rules', 'tools', 'admin', array());
                }
                else {
                    $this->_helper->redirector()->redirect('discount-rules', null, null, array());
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
            'controller' => ($inAdmin) ? 'Tools' : 'Selling',
            'headline'   => ($id) ? $this->_('Edit Discount Rule') : $this->_('Create Discount Rule'),
            'form'       => $form,
            'messages'   => $this->_flashMessenger->getMessages(),
        );
    }

    public function DeleteDiscountRule()
    {
        $id = $this->getRequest()->getParam('id');

        $inAdmin = $this->_loggedInAdmin();

        $userId = ($inAdmin) ? null : $this->_user['id'];

        $result = $this->_discountRules->delete($id, $userId);

        $translate = $this->getTranslate();

        if ($result) {
            $this->_flashMessenger->setMessage(array(
                'msg'   => sprintf($translate->_("Discount Rule ID: #%s has been deleted."), $id),
                'class' => 'alert-success',
            ));
        }
        else {
            $this->_flashMessenger->setMessage(array(
                'msg'   => $this->_('Deletion failed. The discount rule could not be found.'),
                'class' => 'alert-danger',
            ));
        }

        if ($inAdmin) {
            $this->_helper->redirector()->redirect('discount-rules', 'tools', 'admin', array());
        }
        else {
            $this->_helper->redirector()->redirect('discount-rules', null, null, array());
        }
    }
        
}

