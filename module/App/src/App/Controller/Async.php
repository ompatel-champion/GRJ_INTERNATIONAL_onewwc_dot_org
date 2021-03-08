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

/**
 * async controller
 */

namespace App\Controller;

use Ppb\Controller\Action\AbstractAction,
    Cube\View,
    Cube\Controller\Front,
    Cube\Db\Table\AbstractTable,
    Cube\Db\Expr,
    Cube\Db\Select,
    Cube\Validate,
    Ppb\Service;

class Async extends AbstractAction
{

    /**
     *
     * view object
     *
     * @var \Cube\View
     */
    protected $_view;

    public function init()
    {
        $this->_view = new View();
    }

    public function SelectCategory()
    {
        $id = $this->getRequest()->getParam('id', 0);
        $option = $this->getRequest()->getParam('option');
        $storeId = $this->getRequest()->getParam('storeId');
        $categoriesDisplay = $this->getRequest()->getParam('categoriesDisplay');

        $refresh = false;
        $categoryName = null;
        $boxes = null;

        $categoriesService = new Service\Table\Relational\Categories();

        $translate = $this->getTranslate();

        if ($option != 'reset') {
            $categoriesSelect = $categoriesService->getTable()->select()
                ->order(array('parent_id ASC', 'order_id ASC', 'name ASC'));

            $categoriesTableColumns = array_values($categoriesService->getTable()->info(AbstractTable::COLS));

            if (in_array($categoriesDisplay, $categoriesTableColumns)) {
                $categoriesSelect->where("$categoriesDisplay = ?", 1);
            }
            else {
                $categoriesSelect->where('enable_auctions = ?', 1);
            }

            if ($storeId) {
                $categoriesSelect->where("user_id is null OR user_id = '{$storeId}'");

                $usersService = new Service\Users();
                $storeCategories = $usersService->findBy('id', $storeId)
                    ->getStoreSettings('store_categories');

                if ($storeCategories) {
                    $categoriesSelect->where("parent_id is not null OR id IN (" . implode(', ', $storeCategories) . ")")
                        ->order('parent_id ASC');
                }
            }
            else {
                $categoriesSelect->where('user_id is null');
            }

            $select = $categoriesService->getTable()
                ->select(array('nb_rows' => new Expr('count(*)')))
                ->where('parent_id = ?', $id);

            $stmt = $select->query();

            $nbChildren = (integer)$stmt->fetchColumn('nb_rows');

            if ($option == 'change' || !$id || ($nbChildren > 0)) {
                $array = $categoriesService->getCategoriesSelectData($id, $categoriesSelect);

                foreach ((array)$array as $row) {
                    $boxes .= $this->_view->formElement('select', 'category_data', $row['selected'])
                        ->setMultiOptions($row['values'])
                        ->setAttributes(array(
                            'size'  => '10',
                            'class' => 'form-control input-medium category-selector'))
                        ->render();
                }
            }

            $breadcrumbs = $categoriesService->getBreadcrumbs($id);

            $categoryName = implode(' :: ', array_values($breadcrumbs));
        }

        if (empty($boxes)) {
            // check for refresh data
            // 1. if we have category specific custom fields
            $customFieldsService = new Service\CustomFields();

            $select = $customFieldsService->getTable()
                ->select(array('nb_rows' => new Expr('count(*)')))
                ->where('type = ?', 'item')
                ->where('active = ?', 1)
                ->where("category_ids != ?", '');

            $stmt = $select->query();

            $nbCustomFields = (integer)$stmt->fetchColumn('nb_rows');

            if ($nbCustomFields > 0) {
                $refresh = true;
            }
            else {
                // 2. if we have category specific fees
                $select = $categoriesService->getTable()
                    ->select(array('nb_rows' => new Expr('count(*)')))
                    ->where('parent_id is null')
                    ->where('custom_fees = ?', 1);

                $stmt = $select->query();

                $nbCustomFeesCategories = (integer)$stmt->fetchColumn('nb_rows');

                if ($nbCustomFeesCategories > 0) {
                    $refresh = true;
                }
            }
        }

        $data = array(
            'category_id'   => $id,
            'category_name' => ($categoryName) ? $categoryName : $translate->_('Select Category'),
            'output'        => $boxes,
            'refresh'       => $refresh,
        );

        $this->getResponse()->setHeader('Content-Type: application/json');

        $this->_view->setContent(
            json_encode($data));

        return $this->_view;
    }

    public function SelectLocation()
    {
        // action body
        $id = $this->getRequest()->getParam('id');
        $name = $this->getRequest()->getParam('name', 'state');
        $class = $this->getRequest()->getParam('class', 'form-control input-medium');
        $default = $this->getRequest()->getParam('default', false);

        $locationsService = new Service\Table\Relational\Locations();
        $locations = $locationsService->getMultiOptions($id, null, $default);

        $element = null;
        if (count($locations) > 0) {
            $element = $this->_view->formElement('select', $name)
                ->setMultiOptions($locations)
                ->setAttributes(array(
                    'class' => $class,
                ))
                ->render();
        }
        else {
            $element = $this->_view->formElement('text', $name)
                ->setAttributes(array(
                    'class' => $class,
                ))
                ->render();
        }

        $this->getResponse()->setHeader('Content-Type: text/plain');

        $this->_view->setContent($element);

        return $this->_view;
    }

    public function UpdateStockLevelsElement()
    {
        $params = $this->getRequest()->getParams();

        $categoriesFilter = array(0);

        $categoriesService = new Service\Table\Relational\Categories();

        if ($categoryId = $this->getRequest()->getParam('category_id')) {
            $categoriesFilter = array_merge($categoriesFilter, array_keys(
                $categoriesService->getBreadcrumbs($categoryId)));
        }

        if ($addlCategoryId = $this->getRequest()->getParam('addl_category_id')) {
            $categoriesFilter = array_merge($categoriesFilter, array_keys(
                $categoriesService->getBreadcrumbs($addlCategoryId)));
        }

        $customFieldsService = new Service\CustomFields();
        $customFields = $customFieldsService->getFields(
            array(
                'type'         => 'item',
                'active'       => 1,
                'category_ids' => $categoriesFilter,
            ))->toArray();

        $isProductAttributes = false;
        foreach ($customFields as $key => $customField) {
            $customFields[$key]['form_id'] = array($customField['type'], 'product_edit');
            $customFields[$key]['id'] = 'custom_field_' . $customField['id'];
            $customFields[$key]['subform'] = 'details';

            if (!empty($customField['multiOptions'])) {
                $multiOptions = \Ppb\Utility::unserialize($customField['multiOptions']);
                $customFields[$key]['bulk']['multiOptions'] = (!empty($multiOptions['key'])) ?
                    array_flip(array_filter($multiOptions['key'])) : array();
            }

            if ($customField['product_attribute']) {
                $isProductAttributes = true;
                $customFields[$key]['attributes'] = array('class' => 'product-attribute');
            }
        }

        $element = null;

        $data = array(
            'output' => null,
            'empty'  => true,
        );

        if ($isProductAttributes) {
            /** @var \Ppb\Form\Element\StockLevels $element */
            $element = $this->_view->formElement('\\Ppb\\Form\\Element\\StockLevels', 'stock_levels')
                ->setAttributes(array(
                    'class' => 'form-control input-mini',
                ))
                ->setCustomFields($customFields)
                ->setFormData($params)
                ->setValue($this->getRequest()->getParam('stock_levels'))
                ->setRequired($isProductAttributes);

            $data = array(
                'output' => $element->render(),
                'empty'  => $element->isEmpty(),
            );
        }


        $this->getResponse()->setHeader('Content-Type: application/json');

        $this->_view->setContent(
            json_encode($data));

        return $this->_view;
    }

    public function CheckDirectPaymentMethod()
    {
        $userId = $this->getRequest()->getParam('userId');
        $gatewayId = $this->getRequest()->getParam('gatewayId');

        $active = false;

        $paymentGatewaysService = new Service\Table\PaymentGateways();
        $paymentGateway = $paymentGatewaysService->getData($userId, $gatewayId, true, true);

        if (count($paymentGateway) > 0) {
            $className = '\\Ppb\\Model\\PaymentGateway\\' . $paymentGateway['name'];

            if (class_exists($className)) {
                /** @var \Ppb\Model\PaymentGateway\AbstractPaymentGateway $gatewayModel */
                $gatewayModel = new $className($userId);
                $active = $gatewayModel->enabled();
            }
        }

        $data = array(
            'active' => $active,
        );

        $this->getResponse()->setHeader('Content-Type: application/json');

        $this->_view->setContent(
            json_encode($data));

        return $this->_view;
    }

    public function NewsletterSubscription()
    {
        $translate = $this->getTranslate();

        $email = $this->getRequest()->getParam('email');

        $status = true;
        $message = null;

        $validateNotEmpty = new Validate\NotEmpty();
        $validateNotEmpty->setName('Email Address')
            ->setValue($email);
        if (!$validateNotEmpty->isValid()) {
            $status = false;
            $message = sprintf($validateNotEmpty->getMessage(), $translate->_('Email Address'));
        }
        else {
            $validateEmail = new Validate\Email();
            $validateEmail->setName('Email Address')
                ->setValue($email);
            if (!$validateEmail->isValid()) {
                $status = false;
                $message = sprintf($validateEmail->getMessage(), $translate->_('Email Address'));
            }
        }

        if ($status === true) {
            $userId = (!empty($this->_user['id'])) ? $this->_user['id'] : null;
            $userId = $this->getRequest()->getParam('userId', $userId);

            $newslettersSubscribersService = new Service\NewslettersSubscribers();

            $subscribed = $newslettersSubscribersService->findBy('email', $email);

            if (!$subscribed) {
                if ($this->_settings['newsletter_subscription_email_confirmation']) {
                    $mail = new \Members\Model\Mail\User();
                    $mail->newsletterSubscriptionConfirmation($email)->send();
                }

                $newslettersSubscribersService->save(array(
                    'newsletter_subscription' => 1,
                    'email'                   => $email,
                    'user_id'                 => $userId,
                    'confirmed'               => ($this->_settings['newsletter_subscription_email_confirmation']) ? 0 : 1,
                ));

                if ($this->_settings['newsletter_subscription_email_confirmation']) {
                    $message = $translate->_('Thank you for subscribing to our newsletter. A confirmation email has been sent to your address.');
                }
                else {
                    $message = $translate->_('Thank you for subscribing to our newsletter');
                }
            }
            else {
                $status = false;
                $message = $translate->_('The email address you have entered has already been subscribed.');
            }
        }

        if ($message !== null) {
            $message = '<span class="' . (($status) ? 'text-success' : 'text-danger') . '">' . $message . '</span>';
        }

        $data = array(
            'status'  => $status,
            'message' => $message,
        );

        $this->getResponse()->setHeader('Content-Type: application/json');

        $this->_view->setContent(
            json_encode($data));

        return $this->_view;
    }

    public function ListingUpdates()
    {
        session_write_close();

        set_time_limit(30);

        $data = array();
        $href = null;


        $lastUpdate = $this->getRequest()->getParam('timestamp');
        $ids = array_filter((array)$this->getRequest()->getParam('ids'));

        if (empty($lastUpdate)) {
            $lastUpdate = time();
        }

        if (count($ids) > 0) {
            $view = Front::getInstance()->getBootstrap()->getResource('view');

            $listingsService = new Service\Listings();
            $cronService = new Service\Cron();

            $select = $listingsService->getTable()
                ->select(array('nb_rows' => new Expr('count(*)')))
                ->where('updated_at > ?', date('Y-m-d H:i:s', $lastUpdate))
                ->where('id IN (?)', $ids);

            $cronService->markClosedExpiredListings($ids)
                ->startScheduledListings($ids);

            $stmt = $select->query();

            $nbUpdatedListings = (integer)$stmt->fetchColumn('nb_rows');

            if ($nbUpdatedListings > 0) {
                $select->reset(Select::COLUMNS)
                    ->columns('*');

                /** @var \Ppb\Db\Table\Rowset\Listings $listings */
                $listings = $listingsService->fetchAll($select);

                if (count($listings) > 0) {
                    $lastUpdate = time();
                }

                /** @var \Ppb\Db\Table\Row\Listing $listing */
                foreach ($listings as $listing) {
                    $data[] = array(
                        'id'            => $listing['id'],
                        'status'        => $view->listing($listing)->status(false),
                        'price'         => $view->partial('partials/current-price.phtml', array('listing' => $listing)),
                        'countdown'     => $view->listing()->countdown(true),
                        'startTime'     => $view->date($listing['start_time']),
                        'endTime'       => $view->date($listing['end_time']),
                        'nbBids'        => $view->listing()->nbBids(),
                        'nbOffers'      => $view->listing()->nbOffers(),
                        'nbSales'       => $view->listing()->nbSales(),
                        'activity'      => $view->listing()->activity(),
                        'reserve'       => $view->listing()->reserveMessage(),
                        'yourBid'       => $view->listing()->yourBid(),
                        'yourBidStatus' => $view->listing()->yourBidStatus(),
                        'minimumBid'    => $view->amount($listing->minimumBid(), $listing['currency']),
                        'bidsHistory'   => $view->partial('listings/history/bids.phtml', array('listing' => $listing)),
                        'offersHistory' => $view->partial('listings/history/offers.phtml', array('listing' => $listing)),
                        'salesHistory'  => $view->partial('listings/history/sales.phtml', array('listing' => $listing)),
                    );
                }
            }
        }

        $response = array(
            'data'      => $data,
            'timestamp' => $lastUpdate,
        );

        $this->getResponse()->setHeader('Content-Type: application/json');

        $this->_view->setContent(
            json_encode($response));

        return $this->_view;
    }

    public function SelectizeCategories()
    {
        $results = array();

        $term = $this->getRequest()->getParam('term');

        $this->getResponse()->setHeader('Content-Type: application/json');

        $categoriesService = new Service\Table\Relational\Categories();

        $select = $categoriesService->getTable()->select();

        if ($term) {
            $select->where('full_name LIKE ?', '%' . str_replace(' ', '%', $term) . '%');
        }

        $categories = $categoriesService->getMultiOptions($select, null, false, true);

        if (count($categories) > 0) {
            foreach ($categories as $id => $name) {
                $results[] = array(
                    'value' => $id,
                    'label' => $name,
                );
            }
        }

        $this->getResponse()->setHeader('Content-Type: application/json');

        $this->_view->setContent(
            json_encode($results));

        return $this->_view;
    }

    public function CartDropdown()
    {
        $view = Front::getInstance()->getBootstrap()->getResource('view');

        $partial = $this->getRequest()->getParam('partial', 'partials/cart-dropdown.phtml'); // for future reference

        $cartDropdown = $view->cart(null, $partial)->dropdown();

        $this->getResponse()->setHeader('Content-Type: text/plain');

        $this->_view->setContent($cartDropdown);

        return $this->_view;
    }

    public function HeaderLinks()
    {
        $view = Front::getInstance()->getBootstrap()->getResource('view');

        $partial = $this->getRequest()->getParam('partial');

        $headerLinks = $view->headerLinks($partial)->render();

        $this->getResponse()->setHeader('Content-Type: text/plain');

        $this->_view->setContent($headerLinks);

        return $this->_view;
    }

}

