<?php

/**
 *
 * PHP Pro Bid
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2019 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     8.1 [rev.8.1.04]
 */

/**
 * members module - tools management controller
 */
/**
 * MOD:- ADVANCED CLASSIFIEDS
 */

namespace Members\Controller;

use Members\Controller\Action\AbstractAction,
    Ppb\Service,
    Cube\Paginator,
    Ppb\Db\Table\Row\Listing as ListingModel,
    Ppb\Db\Table\Row\Voucher as VoucherModel,
    Ppb\Db\Table\Row\BlockedUser as BlockedUserModel,
    Ppb\Service\PostmenShippingAPI,
    Members\Form,
    Listings\Form\Listing as ListingForm;

class Tools extends AbstractAction
{

    /**
     *
     * vouchers service
     *
     * @var \Ppb\Service\Vouchers
     */
    protected $_vouchers;

    /**
     *
     * blocked users service
     *
     * @var \Ppb\Service\BlockedUsers
     */
    protected $_blockedUsers;

    /**
     *
     * postmen shipper accounts service
     *
     * @var \Ppb\Service\PostmenShipperAccounts
     */
    protected $_postmenShipperAccounts;

    public function init()
    {
        $this->_vouchers = new Service\Vouchers();
        $this->_blockedUsers = new Service\BlockedUsers();
        $this->_postmenShipperAccounts = new Service\PostmenShipperAccounts();
    }

    public function GlobalSettings()
    {
        switch ($this->getRequest()->getParam('formId')) {
            case 'email_notifications':
                $formId = 'email_notifications';
                $controller = 'My Account';
                $headline = $this->_('Email Notifications');
                break;
            case 'social_media':
                $formId = 'social_media';
                $controller = 'My Account';
                $headline = $this->_('Social Media');
                break;
            default:
                $formId = 'global_settings';
                $controller = 'Selling';
                $headline = $this->_('Global Settings');
                break;
        }
        $form = new Form\Register($formId, null, $this->_user);

        if ($this->getRequest()->isPost()) {
            $params = $this->getRequest()->getParams();

            $form->setData($params);

            if ($form->isValid() === true) {
                $this->_user->updateGlobalSettings($params);

                $this->_flashMessenger->setMessage(array(
                    'msg'   => $this->_('The settings have been saved successfully.'),
                    'class' => 'alert-success',
                ));
            }
            else {
                $this->_flashMessenger->setMessage(array(
                    'msg'   => $form->getMessages(),
                    'class' => 'alert-danger',
                ));
            }
        }
        else {
            $form->setData(
                $this->_user->getGlobalSettings());
        }

        return array(
            'controller' => $controller,
            'headline'   => $headline,
            'form'       => $form,
            'user'       => $this->_user,
            'messages'   => $this->_flashMessenger->getMessages()
        );
    }

    public function EmailNotifications()
    {
        $this->_forward('global-settings', null, null, array('formId' => 'email_notifications'));
    }

    public function SocialMedia()
    {
        $this->_forward('global-settings', null, null, array('formId' => 'social_media'));
    }

    public function FeesCalculator()
    {
        $listingFees = false;
        $listingSetupService = null;

        $form = new Form\FeesCalculator();

        $params = $this->getRequest()->getParams();
        $form->setData($params);

        if ($form->isPost(
            $this->getRequest())
        ) {


            if ($form->isValid() === true) {
                $listingsService = new Service\Listings();
                $listingModel = new ListingModel(array(
                    'data'  => $params,
                    'table' => $listingsService->getTable()
                ));

                ## -- ADD -- [ MOD:- ADVANCED CLASSIFIEDS ]
                $listingSetupServiceClass = '\Ppb\Service\Fees\ListingSetup';

                if ($listingModel->isClassified()) {
                    $listingSetupServiceClass = '\Ppb\Service\Fees\ClassifiedSetup';
                }
                ## -- ./ADD -- [ MOD:- ADVANCED CLASSIFIEDS ]

                ## -- START :: CHANGE -- [ MOD:- ADVANCED CLASSIFIEDS ]
                /** @var \Ppb\Service\Fees\ListingSetup $listingSetupService */
                $listingSetupService = new $listingSetupServiceClass(
                    $listingModel, $this->_user);
                ## -- END :: CHANGE -- [ MOD:- ADVANCED CLASSIFIEDS ]

                $listingFees = $listingSetupService->calculate();
            }
            else {
                $this->_flashMessenger->setMessage(array(
                    'msg'   => $form->getMessages(),
                    'class' => 'alert-danger',
                ));
            }
        }

        return array(
            'controller'          => 'Selling',
            'headline'            => $this->_('Fees Calculator'),
            'form'                => $form,
            'listingFees'         => $listingFees,
            'listingSetupService' => $listingSetupService,
            'messages'            => $this->_flashMessenger->getMessages(),
        );
    }

    public function PostageSetup()
    {
        $form = new Form\PostageSetup();

        if ($form->isPost(
            $this->getRequest())
        ) {
            $params = $this->getRequest()->getParams();

            $form->setData($params);

            if ($form->isValid() === true) {
                $this->_user->updatePostageSettings(
                    $form->getData());

                $this->_flashMessenger->setMessage(array(
                    'msg'   => $this->_('The settings have been saved successfully.'),
                    'class' => 'alert-success',
                ));
            }
            else {
                $this->_flashMessenger->setMessage(array(
                    'msg'   => $form->getMessages(),
                    'class' => 'alert-danger',
                ));
            }
        }
        else {
            $form->setData(
                $this->_user->getPostageSettings());
        }

        return array(
            'controller' => 'Selling',
            'headline'   => $this->_('Postage Setup'),
            'form'       => $form,
            'messages'   => $this->_flashMessenger->getMessages()
        );
    }

    public function PrefilledFields()
    {
        $form = new Form\PrefilledFields();

        if ($form->isPost(
            $this->getRequest())
        ) {
            $params = $this->getRequest()->getParams();

            $form->setData($params);

            if ($form->isValid() === true) {
                $this->_user->updatePrefilledFields($params);

                $this->_flashMessenger->setMessage(array(
                    'msg'   => $this->_('The settings have been saved successfully.'),
                    'class' => 'alert-success',
                ));
            }
            else {
                $this->_flashMessenger->setMessage(array(
                    'msg'   => $form->getMessages(),
                    'class' => 'alert-danger',
                ));
            }
        }
        else {
            $form->setData(
                (array)$this->_user->getPrefilledFields());
        }

        return array(
            'controller' => 'Selling',
            'headline'   => $this->_('Selling Prefilled Fields'),
            'form'       => $form,
            'messages'   => $this->_flashMessenger->getMessages(),
        );
    }

    public function WatchedItems()
    {
        return array(
            'headline'        => $this->_('Wish List'),
            'isMembersModule' => false,
        );
    }

    public function FavoriteStores()
    {
        $favoriteStoresService = new Service\FavoriteStores();

        if ($this->getRequest()->getParam('option') == 'remove') {
            $favoriteStoresService->delete($this->getRequest()->getParam('id'), $this->_user['id']);

            $this->_flashMessenger->setMessage(array(
                'msg'   => $this->_('The store has been removed from your favorites list.'),
                'class' => 'alert-success',
            ));
        }

        $select = $favoriteStoresService->getTable()->select()
            ->where('user_id = ?', $this->_user['id']);

        $paginator = new Paginator(
            new Paginator\Adapter\DbTableSelect($select, $favoriteStoresService->getTable()));

        $pageNumber = $this->getRequest()->getParam('page');
        $paginator->setPageRange(5)
            ->setItemCountPerPage(20)
            ->setCurrentPageNumber($pageNumber);

        return array(
            'controller' => 'Buying',
            'paginator'  => $paginator,
            'messages'   => $this->_flashMessenger->getMessages(),
        );
    }

    public function Vouchers()
    {
        $code = $this->getRequest()->getParam('code');

        $inAdmin = $this->_loggedInAdmin();

        $select = $this->_vouchers->getTable()->select()
            ->order(array('created_at DESC'));

        if ($inAdmin) {
            $select->where('user_id is null');
        }
        else {
            $select->where('user_id = ?', $this->_user['id']);
        }

        if ($code !== null) {
            $params = '%' . str_replace(' ', '%', $code) . '%';
            $select->where('code LIKE ?', $params);
        }

        $paginator = new Paginator(
            new Paginator\Adapter\DbTableSelect($select, $this->_vouchers->getTable()));

        $pageNumber = $this->getRequest()->getParam('page');
        $paginator->setPageRange(5)
            ->setItemCountPerPage(10)
            ->setCurrentPageNumber($pageNumber);

        return array(
            'controller' => ($inAdmin) ? 'Tools' : 'Selling',
            'code'       => $code,
            'paginator'  => $paginator,
            'messages'   => $this->_flashMessenger->getMessages(),
        );
    }

    public function BulkLister()
    {
        $form = new Form\BulkLister();

        $data = $paymentGateways = $offlinePaymentMethods = $elements = array();

        $filter = $this->getRequest()->getParam('filter');
        $option = $this->getRequest()->getParam('option');
        $uploadAs = $this->getRequest()->getParam('upload_as');

        $bulkListerService = new Service\Listings\BulkLister();
        $bulkListerService->setPrefilledFields(
            $this->_user->getPrefilledFields());

        switch ($filter) {

            case 'categories':
                $categoriesService = new Service\Table\Relational\Categories();
                $select = $categoriesService->getTable()->getAdapter()
                    ->select()
                    ->from(array('c' => 'categories'), '*')
                    ->joinLeft(array('cc' => 'categories'), 'cc.parent_id = c.id', 'cc.id AS cc_id')
                    ->where('c.user_id is null OR c.user_id = ?', $this->_user['id'])
                    ->where('cc.id is null')
                    ->group('c.id');
                $data = $categoriesService->getMultiOptions($select, null, false, true);
                break;

            case 'locations':
                $locationsService = new Service\Table\Relational\Locations();
                $select = $locationsService->getTable()->select()
                    ->order(array('parent_id ASC', 'order_id ASC', 'name ASC'));
                $data = $locationsService->getMultiOptions($select, null, false, true);
                break;

            case 'payment_methods':
                $offlinePaymentMethodsService = new Service\Table\OfflinePaymentMethods();
                $offlinePaymentMethods = $offlinePaymentMethodsService->getMultiOptions();

                $paymentGatewaysService = new Service\Table\PaymentGateways();
                $paymentGateways = $paymentGatewaysService->getMultiOptions($this->_user['id']);

                break;

            case 'structure':
                $elements = $bulkListerService->getBulkElements();
                break;
        }

        if ($option == 'download-sample') {
            $bulkListerService->downloadSampleFile();
        }

        if ($form->isPost(
            $this->getRequest())
        ) {
            $params = $this->getRequest()->getParams();

            $form->setData($params);

            $parseErrors = array();
            $listingMessages = array();

            if ($form->isValid() === true) {
                $rows = $bulkListerService->parseCSV($params['csv']);

                $listingForm = new ListingForm('bulk');

                $validRows = array();
                foreach ($rows as $id => $data) {
                    $listingForm->setData($data)
                        ->removeElement('csrf');

                    if (!$listingForm->isValid()) {
                        $msgTitle = array(sprintf($this->_("<strong>Parse error(s) - row #%s:</strong>"), $id));

                        $parseErrors = array_merge($parseErrors, $msgTitle, $listingForm->getMessages());
                    }
                    else {
                        $validRows[] = $data;
                    }

                    $listingForm->clearMessages();
                }

                if (count($parseErrors) > 0) {
                    $this->_flashMessenger->setMessage(array(
                        'msg'   => $parseErrors,
                        'class' => 'alert-danger',
                    ));
                }
                else if (count($validRows) > 0) {
                    // save listings
                    foreach ($validRows as $id => $data) {
                        if ($uploadAs == 'bulk') {
                            $data['draft'] = 1;
                        }

                        $listingId = $bulkListerService->save($data);

                        $listingModel = $bulkListerService->findBy('id', $listingId, false, true);
                        $message = $listingModel->processPostSetupActions();

                        if ($message) {
                            $listingMessages[] = $message;
                        }
                    }

                    $this->_flashMessenger->setMessage(array(
                        'msg'   => sprintf(
                            $this->_('The bulk file has been processed. %s listings have been uploaded.'), count($validRows)),
                        'class' => 'alert-success',
                    ));

                    if (count($listingMessages) > 0) {
                        $this->_flashMessenger->setMessage(array(
                            'msg'   => $listingMessages,
                            'class' => 'alert-info',
                        ));
                    }

                    $redirectParams = ($uploadAs == 'bulk') ? array('filter' => 'drafts') : array('filter' => 'open');

                    $this->_helper->redirector()->redirect('browse', 'selling', null, $redirectParams);
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
            'controller'            => 'Selling',
            'headline'              => $this->_('Bulk Lister'),
            'form'                  => $form,
            'elements'              => $elements,
            'data'                  => $data,
            'offlinePaymentMethods' => $offlinePaymentMethods,
            'paymentGateways'       => $paymentGateways,
            'filter'                => $filter,
            'messages'              => $this->_flashMessenger->getMessages(),
        );
    }


    public function AddVoucher()
    {
        $this->_forward('edit-voucher');
    }

    public function EditVoucher()
    {
        $id = $this->getRequest()->getParam('id');

        $inAdmin = $this->_loggedInAdmin();

        $userId = ($inAdmin) ? null : $this->_user['id'];

        $form = new \Members\Form\Voucher(null, $userId);


        if ($id) {
            $select = $this->_vouchers->getTable()->select()
                ->where('id = ?', $id);

            if (!$inAdmin) {
                $select->where('user_id = ?', $userId);
            }

            $voucher = $this->_vouchers->getTable()->fetchRow($select);

            if ($voucher instanceof VoucherModel) {
                $data = $voucher->toArray();
            }
            else {
                $id = null;
                $data = array();
            }

            $form->setData($data)
                ->generateEditForm();
        }

        if ($this->getRequest()->isPost()) {
            $form->setData(
                $this->getRequest()->getParams());

            if ($form->isValid() === true) {
                $params = $form->getData();

                $params['user_id'] = $userId;

                $this->_vouchers->save($params);

                $this->_flashMessenger->setMessage(array(
                    'msg'   => ($id) ?
                        $this->_('The voucher has been edited successfully') :
                        $this->_('The voucher has been created successfully.'),
                    'class' => 'alert-success',
                ));

                if ($inAdmin) {
                    $this->_helper->redirector()->redirect('vouchers', 'tools', 'admin', array());
                }
                else {
                    $this->_helper->redirector()->redirect('vouchers', null, null, array());
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
            'headline'   => ($id) ? $this->_('Edit Voucher') : $this->_('Create Voucher'),
            'form'       => $form,
            'messages'   => $this->_flashMessenger->getMessages(),
        );
    }

    public function DeleteVoucher()
    {
        $id = $this->getRequest()->getParam('id');

        $inAdmin = $this->_loggedInAdmin();

        $userId = ($inAdmin) ? null : $this->_user['id'];

        $result = $this->_vouchers->delete($id, $userId);

        $translate = $this->getTranslate();

        if ($result) {
            $this->_flashMessenger->setMessage(array(
                'msg'   => sprintf($translate->_("Voucher ID: #%s has been deleted."), $id),
                'class' => 'alert-success',
            ));
        }
        else {
            $this->_flashMessenger->setMessage(array(
                'msg'   => $this->_('Deletion failed. The voucher could not be found.'),
                'class' => 'alert-danger',
            ));
        }

        if ($inAdmin) {
            $this->_helper->redirector()->redirect('vouchers', 'tools', 'admin', array());
        }
        else {
            $this->_helper->redirector()->redirect('vouchers', null, null, array());
        }
    }

    public function BlockedUsers()
    {
        $inAdmin = $this->_loggedInAdmin();

        $select = $this->_blockedUsers->getTable()->select()
            ->order(array('created_at DESC'));

        if (!$inAdmin) {
            $select->where('user_id = ?', $this->_user['id']);
        }

        $paginator = new Paginator(
            new Paginator\Adapter\DbTableSelect($select, $this->_blockedUsers->getTable()));

        $pageNumber = $this->getRequest()->getParam('page');
        $paginator->setPageRange(5)
            ->setItemCountPerPage(10)
            ->setCurrentPageNumber($pageNumber);

        return array(
            'controller' => ($inAdmin) ? 'Tools' : 'Selling',
            'paginator'  => $paginator,
            'inAdmin'    => $inAdmin,
            'messages'   => $this->_flashMessenger->getMessages(),
        );
    }

    public function AddBlockedUser()
    {
        $this->_forward('edit-blocked-user');
    }

    public function EditBlockedUser()
    {
        $id = $this->getRequest()->getParam('id');

        $inAdmin = $this->_loggedInAdmin();

        if ($id) {
            $select = $this->_blockedUsers->getTable()->select()
                ->where('id = ?', $id);

            if (!$inAdmin) {
                $select->where('user_id = ?', $this->_user['id']);
            }

            $blockedUser = $this->_blockedUsers->getTable()->fetchRow($select);

            if ($blockedUser instanceof BlockedUserModel) {
                $data = $blockedUser->toArray();
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
        else if (!empty($data['user_id'])) {
            $userId = $data['user_id'];
        }

        $form = new \Members\Form\BlockedUser(null, $userId);

        if ($id) {
            $form->setData($data)
                ->generateEditForm();
        }

        if ($this->getRequest()->isPost()) {
            $params = $this->getRequest()->getParams();
            $form->setData($params);

            if ($form->isValid() === true) {
                if (!$inAdmin) {
                    $params['user_id'] = $this->_user['id'];
                }

                $this->_blockedUsers->save($params);

                $this->_flashMessenger->setMessage(array(
                    'msg'   => ($id) ?
                        $this->_('The blocked user has been edited successfully') :
                        $this->_('The blocked user has been added successfully.'),
                    'class' => 'alert-success',
                ));

                if ($inAdmin) {
                    $this->_helper->redirector()->redirect('blocked-users', 'tools', 'admin', array());
                }
                else {
                    $this->_helper->redirector()->redirect('blocked-users', null, null, array());
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
            'headline'   => ($id) ? $this->_('Edit Blocked User') : $this->_('Add Blocked User'),
            'form'       => $form,
            'messages'   => $this->_flashMessenger->getMessages(),
        );
    }

    public function DeleteBlockedUser()
    {
        $id = $this->getRequest()->getParam('id');

        $inAdmin = $this->_loggedInAdmin();

        $userId = ($inAdmin) ? null : $this->_user['id'];

        $result = $this->_blockedUsers->delete($id, $userId);

        $translate = $this->getTranslate();

        if ($result) {
            $this->_flashMessenger->setMessage(array(
                'msg'   => sprintf($translate->_("Blocked User ID: #%s has been deleted."), $id),
                'class' => 'alert-success',
            ));
        }
        else {
            $this->_flashMessenger->setMessage(array(
                'msg'   => $this->_('Deletion failed. The blocked user could not be found.'),
                'class' => 'alert-danger',
            ));
        }

        if ($inAdmin) {
            $this->_helper->redirector()->redirect('blocked-users', 'tools', 'admin', array());
        }
        else {
            $this->_helper->redirector()->redirect('blocked-users', null, null, array());
        }
    }

    public function Postmen()
    {
        $form = new Form\PostmenAccount();

        if ($form->isPost(
            $this->getRequest())
        ) {
            $params = $this->getRequest()->getParams();

            $form->setData($params);

            if ($form->isValid() === true) {
                $this->_user->updateGlobalSettings(
                    $form->getData());

                $this->_flashMessenger->setMessage(array(
                    'msg'   => $this->_('The settings have been saved successfully.'),
                    'class' => 'alert-success',
                ));
            }
            else {
                $this->_flashMessenger->setMessage(array(
                    'msg'   => $form->getMessages(),
                    'class' => 'alert-danger',
                ));
            }
        }
        else {
            $form->setData(
                $this->_user->getGlobalSettings());
        }

        $postmenShipperAccountsService = new Service\PostmenShipperAccounts();

        $table = $postmenShipperAccountsService->getTable();
        $select = $postmenShipperAccountsService->getTable()->select()
            ->where('user_id = ?', $this->_user['id'])
            ->order('created_at DESC');

        $paginator = new Paginator(
            new Paginator\Adapter\DbTableSelect($select, $table));

        $pageNumber = $this->getRequest()->getParam('page');
        $paginator->setPageRange(10)
            ->setItemCountPerPage(10)
            ->setCurrentPageNumber($pageNumber);

        return array(
            'user'      => $this->_user,
            'paginator' => $paginator,
            'form'      => $form,
            'messages'  => $this->_flashMessenger->getMessages(),
        );
    }

    public function RetrievePostmenShipperAccounts()
    {
        $postmenShippingApi = new PostmenShippingAPI(
            $this->_user->getGlobalSettings(PostmenShippingAPI::API_KEY),
            $this->_user->getGlobalSettings(PostmenShippingAPI::API_MODE)
        );

        try {
            $shipperAccounts = $postmenShippingApi->retrieveShipperAccounts();

            if (count($shipperAccounts) > 0) {
                $postmenShipperAccountsService = new Service\PostmenShipperAccounts();
                $postmenShipperAccountsService->deleteByUserId($this->_user['id']);

                foreach ($shipperAccounts as $shipperAccount) {
                    $postmenShipperAccountsService->save(array(
                        'user_id' => $this->_user['id'],
                        'details' => $shipperAccount,
                    ));
                }
            }

            $this->_flashMessenger->setMessage(array(
                'msg'   => $this->_('The shipper accounts have been retrieved and saved successfully.'),
                'class' => 'alert-success',
            ));
        } catch (\Exception $e) {
            $this->_flashMessenger->setMessage(array(
                'msg'   => $e->getMessage(),
                'class' => 'alert-danger',
            ));
        }

        $this->_helper->redirector()->redirect('postmen');
    }

}

