<?php

/**
 *
 * PHP Pro Bid
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2020 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     8.2 [rev.8.2.01]
 */

namespace Listings\Controller;

use Ppb\Controller\Action\AbstractAction,
    Cube\Controller\Front,
    Cube\View,
    Listings\Form,
    Ppb\Service,
    Ppb\Db\Table\Row\Listing as ListingModel,
    Ppb\Db\Table\Row\User as UserModel,
    Ppb\Model\Shipping as ShippingModel;

class Listing extends AbstractAction
{

    /**
     *
     * view object
     *
     * @var \Cube\View
     */
    protected $_view;

    /**
     *
     * listings service
     *
     * @var \Ppb\Service\Listings
     */
    protected $_listings;

    public function init()
    {
        $this->_view = Front::getInstance()->getBootstrap()->getResource('view');
        $this->_listings = new Service\Listings();
    }

    public function Create()
    {
        $saveData = false;
        $params = array();
        $paymentBox = null;
        $savedListing = null;

        /** @var \Ppb\Db\Table\Row\User $user */
        $user = Front::getInstance()->getBootstrap()->getResource('user');

        $translate = $this->getTranslate();

        $option = $this->getRequest()->getParam('option');
        $id = $this->getRequest()->getParam('id');

        $currentStep = $this->getRequest()->getParam(Form\Listing::ELEMENT_STEP);


        $formId = null;

        if ($id !== null) { // get similar listing
            $savedListing = $this->_listings->findBy('id', $id, true, true);
            if ($savedListing !== null) {
                $params = $savedListing->getData();
                unset($params['last_count_operation']); // to count the new listing properly
            }

            if ($option == 'edit' && $savedListing) {
                if (
                    $savedListing->isProduct() &&
                    $savedListing->canEdit() &&
                    $savedListing->hasActivity()
                ) {
                    $formId = 'product_edit';
                }
            }
            else {
                $savedListing = null; // so that fees are applied properly when listing similar items
                unset($params['draft']); // to calculate fees properly in the preview step
            }

            $params['option'] = $option;
        }
        else if (($prefilledFields = $user->getPrefilledFields()) !== null) {
            $params = $prefilledFields;
        }

        $form = new Form\Listing($formId);

        if ($this->getRequest()->isPost()) {
            $params = array_merge(
                $params, $this->getRequest()->getParams());
        }

        $params = $this->getRequest()->filterInput($params);

        $form->setData($params);

        $formData = array_filter($form->getData());

        // needed for form element filters
        $params = array_merge($params, $formData);

        $listingModel = new ListingModel(array(
            'data'  => $params,
            'table' => $this->_listings->getTable()
        ));

        // if editing is disabled, redirect to the listing details page.
        if ($option == 'edit') {
            if (!$listingModel->canEdit()) {
                $this->_flashMessenger->setMessage(array(
                    'msg'   => $this->_('This listing cannot be edited.'),
                    'class' => 'alert-danger',
                ));
                $this->_helper->redirector()->redirect('details', null, null, array('id' => $id));
            }
        }

        $listingSetupService = new Service\Fees\ListingSetup(
            $listingModel, $user);

        if ($voucherCode = $this->getRequest()->getParam('voucher_code')) {
            $listingSetupService->setVoucher($voucherCode);
        }

        if ($option != 'edit') {
            $removeDraftButton = false;
            if ($id !== null && $option == 'list-draft') {
                $form->setTitle('List Draft');
                if (!$currentStep) {
                    $currentStep = 'preview';
                    $removeDraftButton = true;
                }
            }

            $form->generateSubForm($currentStep);

            if ($removeDraftButton) {
                $form->removeElement(Form\Listing::BTN_DRAFT);
            }

            // check if we have store only mode enabled, but the seller doesnt have an active store
            if ($form->hasElement('list_in') && !$form->isPost($this->getRequest()) && !$this->getRequest()->getParam('voucher_add') && $formId === null) {
                if (count($form->getModel()->getListIn()) == 0) {
                    $this->_flashMessenger->setMessage(array(
                        'msg'   => $this->_('<h4>Store Only Mode is Enabled</h4>'
                            . 'Please create a store or upgrade your subscription in order to be able to list items.'),
                        'class' => 'alert-danger',
                    ));
                    $form->clearElements();
                }
                else if (!$this->_user->isForceStore()) {
                    $this->_flashMessenger->setMessage(array(
                        'msg'   => $this->_('<h4>Account Upgrade Required</h4>'
                            . 'Please create a store in order to be able to list more items.'),
                        'class' => 'alert-danger',
                    ));
                    $form->clearElements();
                }
            }
        }
        else {
            $form->generateEditForm($id);

            if ($params === null) {
                $this->_flashMessenger->setMessage(array(
                    'msg'   => $this->_("The listing you are trying to edit does not exist or you are not it's owner."),
                    'class' => 'alert-danger',
                ));
                $form->clearElements();
            }
        }

        if ($savedListing instanceof ListingModel) {
            $listingSetupService->setSavedListing($savedListing);
        }

        $listingFees = $listingSetupService->calculate();

        if ($form->isPost(
            $this->getRequest())
        ) {
            if ($form->isValid() === true || isset($params[Form\Listing::BTN_PREV])) {

                if (isset($params[Form\Listing::BTN_NEXT])) {
                    $currentStep = $form->nextStep($currentStep);
                    if ($currentStep === false) {
                        $saveData = true;
                    }
                }
                else if (isset($params[Form\Listing::BTN_PREV])) {
                    $form->clearMessages();
                    $currentStep = $form->prevStep($currentStep);

                    if ($currentStep === false) {
                        $steps = $form->getSteps();
                        reset($steps);
                        $currentStep = current($steps);
                    }
                }
                else if (isset($params[Form\Listing::BTN_LIST])) {
                    $saveData = true;
                    $params['id'] = 0;
                    $params['draft'] = 0;
                    $currentStep = $form->nextStep($currentStep);
                }
                else if (isset($params[Form\Listing::BTN_DRAFT])) {
                    $saveData = true;
                    $params['id'] = 0;
                    $params['draft'] = 1;
                    $currentStep = $form->nextStep($currentStep);
                }
                else if ($option == 'edit') {
                    $saveData = true;
                }

                if ($saveData === true) {
                    $listingId = $this->_listings->save($params);

                    $listingModel = $this->_listings->findBy('id', $listingId, false, true);

                    $form->clearElements();

                    $this->_flashMessenger->setMessage(array(
                        'msg'   => ($option == 'edit') ?
                            sprintf(
                                $translate->_("Listing ID: #%s has been edited successfully."),
                                $listingId) :
                            (($params['draft']) ?
                                $this->_('The listing draft has been saved.') : $this->_('The listing has been created successfully.')),
                        'class' => 'alert-success',
                    ));


                    $message = $listingModel->processPostSetupActions($savedListing);

                    if (!$listingModel->isApproved() && !$listingModel->isDraft()) {
                        $this->_flashMessenger->setMessage(array(
                            'msg'   => $this->_('The listing is pending approval and will be reviewed by an administrator.'),
                            'class' => 'alert-info',
                        ));
                    }

                    if ($message) {
                        $this->_flashMessenger->setMessage(array(
                            'msg'   => $message,
                            'class' => 'alert-info',
                        ));
                    }

                    // send listing favorite store notification - for active items not listed in site
                    if ($option != 'edit' && $listingModel->isActive() && $listingModel['list_in'] != 'site') {
                        $favoriteStoresService = new Service\FavoriteStores();
                        $rowset = $favoriteStoresService->fetchAll(
                            $favoriteStoresService->getTable()->select()
                                ->where('store_id = ?', $listingModel['user_id'])
                        );

                        $mail = new \Members\Model\Mail\User();

                        /** @var \Cube\Db\Table\Row $favoriteStore */
                        foreach ($rowset as $favoriteStore) {
                            $mail->newListingFavoriteStoreNotification($listingModel, $favoriteStore->findParentRow('\Ppb\Db\Table\Users', 'User'))
                                ->send();
                        }
                    }

                    $totalAmount = $listingSetupService->getTotalAmount();
                    $userPaymentMode = $user->userPaymentMode();
                    if ($totalAmount > 0 && $userPaymentMode == 'live') {
                        $this->_helper->redirector()->redirect('listing-setup', 'payment', 'app',
                            array('id' => $listingId));
                    }
                    else {
                        $this->_helper->redirector()->redirect('confirm', null, null, array('id' => $listingId));
                    }
                }
            }

            if ($saveData === false) {
                $form->setData($params);
                if ($option != 'edit') {
                    $form->generateSubForm($currentStep);
                }
                else {
                    $form->generateEditForm($id);
                }
            }
        }

        if (count($form->getMessages())) {
            $this->_flashMessenger->setMessage(array(
                'msg'   => $form->getMessages(),
                'class' => 'alert-danger',
            ));
        }

        return array(
            'form'                => $form,
            'headline'            => $form->getTitle(),
            'messages'            => $this->_flashMessenger->getMessages(),
            // listing related data
            'listingModel'        => $listingModel,
            'listingSetupService' => $listingSetupService,
            'listingFees'         => $listingFees,
            'currentStep'         => $currentStep,
        );
    }

    public function Delete()
    {
        $id = $this->getRequest()->getParam('id');
        $listing = $this->_listings->findBy('id', (int)$id, true);

        $result = false;

        $translate = $this->getTranslate();

        if ($listing instanceof ListingModel) {
            if ($listing->canDelete()) {
                $result = $listing->delete();
            }
        }

        if ($result) {
            $this->_flashMessenger->setMessage(array(
                'msg'   => sprintf($translate->_("Listing ID: #%s has been deleted."), $id),
                'class' => 'alert-success',
            ));
        }
        else {
            $this->_flashMessenger->setMessage(array(
                'msg'   => $this->_('Deletion failed. The listing could not be found or deletion is not possible.'),
                'class' => 'alert-danger',
            ));
        }

        $this->_helper->redirector()->redirect('browse', 'selling', 'members', array(
            'filter' => $this->getRequest()->getParam('filter', 'open')));
    }

    public function Close()
    {
        $id = $this->getRequest()->getParam('id');
        $listing = $this->_listings->findBy('id', (int)$id, true);

        $translate = $this->getTranslate();

        if ($listing instanceof ListingModel) {
            if ($listing->canClose()) {
                $listing->close();
            }
        }

        if ($listing->getClosedFlag() === true) {
            $this->_flashMessenger->setMessage(array(
                'msg'   => sprintf($translate->_("Listing ID: #%s has been closed."), $id),
                'class' => 'alert-success',
            ));
        }
        else {
            $this->_flashMessenger->setMessage(array(
                'msg'   => $this->_('Error: the listing could not be closed or it was not found.'),
                'class' => 'alert-danger',
            ));
        }

        $this->_helper->redirector()->redirect('browse', 'selling', 'members', array(
            'filter' => $this->getRequest()->getParam('filter', 'open')));
    }

    public function Details()
    {
        $listing = $this->_listings->findBy('id', (int)$this->getRequest()->getParam('id'));
        $listing->addClick()
            ->addRecentlyViewedListing();

        /** @var \Ppb\Db\Table\Row\User $buyer */
        $buyer = Front::getInstance()->getBootstrap()->getResource('user');

        if (!empty($buyer)) {
            $buyer->setAddress(
                $this->getRequest()->getParam('shipping_address_id'));
        }

        $includedForms = array();

        if ($listing->isAuction()) {
            array_push($includedForms, 'bid');
        }

        if ($listing->isBuyOut()) {
            if ($listing->isShoppingCart() === true) {
                array_push($includedForms, 'cart');
            }
            else {
                array_push($includedForms, 'buy');
            }
        }

        if ($listing->isMakeOffer()) {
            array_push($includedForms, 'offer');
        }

        $purchaseForm = new Form\Purchase($includedForms, $listing, $buyer);
        $purchaseForm->setDetails(true);

        // META TAGS
        $this->_view->headTitle()->set($listing->getMetaTitle());
        $this->_view->headMeta()->setName('description', $listing->getMetaDescription());

        // Facebook meta tags
        $this->_view->headMeta()->setProperty('og:title', $listing->getData('name'))
            ->setProperty('og:type', 'other')
            ->setProperty('og:image', $listing->getMainImage(true))
            ->setProperty('og:image:width', '800')
            ->setProperty('og:image:height', '800')
            ->setProperty('og:url', $this->_settings['site_path'] . $this->_view->url($listing->link(), null, false, null, false))
            ->setProperty('og:description', $listing->shortDescription());

        // Twitter cards
        $this->_view->headMeta()
            ->setName('twitter:card', 'summary');

        // add canonical link
        $this->_view->script()->addHeaderCode('<link rel="canonical" href="' . $this->_view->url($listing->link()) . '">');

        return array(
            'listing'      => $listing,
            'seller'       => $listing->findParentRow('\Ppb\Db\Table\Users'),
            'purchaseForm' => $purchaseForm,
            'messages'     => $this->_flashMessenger->getMessages(),
            'live'         => true,
        );
    }

    public function Confirm()
    {

        $listing = $this->_listings->findBy('id', (int)$this->getRequest()->getParam('id'));

        return array(
            'headline' => $this->_('Confirmation'),
            'listing'  => $listing,
            'messages' => $this->_flashMessenger->getMessages(),
        );
    }

    public function Watch()
    {
        $id = $this->getRequest()->getParam('id');
        $async = $this->getRequest()->getParam('async');

        $listing = $this->_listings->findBy('id', (int)$id);

        $bootstrap = Front::getInstance()->getBootstrap();
        $session = $bootstrap->getResource('session');

        $userToken = strval($session->getCookie(UserModel::USER_TOKEN));
        $userId = (!empty($this->_user['id'])) ? $this->_user['id'] : null;

        $translate = $this->getTranslate();

        $listingsWatchService = new Service\ListingsWatch();

        if (!$listing->isWatched()) {
            $listingsWatchService->save(array(
                'user_token' => $userToken,
                'user_id'    => $userId,
                'listing_id' => $listing['id'],
            ));

            $action = 'add';
            $message = array(
                'msg'   => sprintf($translate->_("Listing ID: #%s has been added to your wishlist."), $id),
                'class' => 'alert-success',
            );
        }
        else {
            $listingsWatchService->delete($id, $userId, $userToken);

            $action = 'remove';
            $message = array(
                'msg'   => sprintf($translate->_("Listing ID: #%s has been removed from your wishlist."), $id),
                'class' => 'alert-danger',
            );
        }

        if ($async) {
            $view = new View();

            $this->getResponse()->setHeader('Content-Type: application/json');

            $data = array(
                'action'  => $action,
                'message' => $message,
            );

            $view->setContent(json_encode($data));

            return $view;
        }
        else {
            $this->_flashMessenger->setMessage($message);

            $this->_helper->redirector()->gotoUrl(
                $this->_view->url($listing->link()));
        }
    }

    public function CalculatePostage()
    {
        $data = array();
        $errors = null;

        $user = null;

        $translate = $this->getTranslate();

        $ids = (array)$this->getRequest()->getParam('ids');
        $qnt = (array)$this->getRequest()->getParam('quantity');

        $listingsService = new Service\Listings();

        $ownerId = null;

        foreach ($ids as $key => $id) {
            $listing = $listingsService->findBy('id', $id);

            $quantity = 1;

            if (isset($qnt[$key])) {
                if ($qnt[$key] > 1) {
                    $quantity = $qnt[$key];
                }
            }

            if ($listing !== null) {
                if ($ownerId === null || $listing['user_id'] == $ownerId) {
                    $data[] = array(
                        'listing'  => $listing,
                        'quantity' => $quantity,
                    );

                    if ($ownerId === null) {
                        $user = $listing->findParentRow('\Ppb\Db\Table\Users');
                        $ownerId = $listing['user_id'];
                    }
                }
            }
        }

        $postage = array();

        $view = clone $this->_view;

        $view->setNoLayout();

        if ($user instanceof UserModel) {
            $shippingModel = new ShippingModel($user);

            $shippingModel->setLocationId(
                $this->getRequest()->getParam('locationId'))
                ->setPostCode(
                    $this->getRequest()->getParam('postCode'));

            foreach ($data as $row) {
                $shippingModel->addData($row['listing'], $row['quantity']);
            }

            try {
                $postage = $shippingModel->calculatePostage();
            } catch (\RuntimeException $e) {
                $errors = $e->getMessage();
            }

            $view->setVariables(array(
                'enableSelection' => $this->getRequest()->getParam('enableSelection'),
                'formSubmit'      => $this->getRequest()->getParam('formSubmit'),
                'postageSettings' => $shippingModel->getPostageSettings(),
                'postageType'     => $shippingModel->getPostageType(),
                'postage'         => $postage,
                'postageId'       => $this->getRequest()->getParam('postageId'),
            ));
        }
        else {
            $errors = $translate->_('Error: cannot instantiate shipping calculation module - invalid seller selected.');
        }

        $view->setVariable('errors', $errors)
            ->process('/listings/listing/calculate-postage.phtml');

        return $view;
    }

    public function EmailFriend()
    {
        $id = $this->getRequest()->getParam('id');
        $listing = $this->_listings->findBy('id', (int)$id);

        $form = null;

        $form = new Form\EmailFriend();

        if ($form->isPost(
            $this->getRequest())
        ) {
            $form->setData($this->getRequest()->getParams());

            if ($form->isValid() === true) {
                $this->_flashMessenger->setMessage(array(
                    'msg'   => $this->_('The email has been sent successfully.'),
                    'class' => 'alert-success',
                ));

                $form->clearElements();

                $mail = new \Listings\Model\Mail\BuyerNotification();

                $emails = explode(',', $this->getRequest()->getParam('emails'));
                $message = $this->getRequest()->getParam('message');

                foreach ($emails as $email) {
                    $email = trim($email);
                    $mail->emailFriend($listing, $this->_user, $email, $message)->send();
                }

                $this->_helper->redirector()->gotoUrl(
                    $this->_view->url($listing->link()));
            }
            else {
                $this->_flashMessenger->setMessage(array(
                    'msg'   => $form->getMessages(),
                    'class' => 'alert-danger',
                ));
            }
        }

        return array(
            'headline' => $this->_('Email Listing to Friend'),
            'form'     => $form,
            'listing'  => $listing,
            'messages' => $this->_flashMessenger->getMessages(),
        );

    }
}

