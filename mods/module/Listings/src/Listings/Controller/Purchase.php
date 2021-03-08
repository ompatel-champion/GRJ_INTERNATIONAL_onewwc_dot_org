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
 * MOD:- DISCOUNT RULES
 */

namespace Listings\Controller;

use Ppb\Controller\Action\AbstractAction,
    Cube\Controller\Front,
    Listings\Form\Purchase as PurchaseForm,
    Ppb\Service,
    Ppb\Db\Table\Row\Voucher as VoucherModel;

class Purchase extends AbstractAction
{

    /**
     *
     * listing model
     * using select for update so that the listing can be altered by a single transaction at the same time.
     *
     * @var \Ppb\Db\Table\Row\Listing
     */
    protected $_listing;

    /**
     *
     * form type to generate
     * (bid|buy|offer)
     *
     * @var string
     */
    protected $_type;

    public function init()
    {
        $listingsService = new Service\Listings();
        $this->_listing = $listingsService->fetchAll(
            $listingsService->getTable()->select()
                ->forUpdate()
                ->where('id = ?', (int)$this->getRequest()->getParam('id')))
            ->getRow(0);

        $this->_type = $this->getRequest()->getParam('type');
        if (!in_array($this->_type, PurchaseForm::$formTypes)) {
            $this->_type = reset(PurchaseForm::$formTypes);
        }
    }

    public function Confirm()
    {
        /** @var \Cube\View $view */
        $view = Front::getInstance()->getBootstrap()->getResource('view');

        $cartBox = null;

        $modal = $this->getRequest()->getParam('modal');

        $canPurchase = $this->_listing->canPurchase($this->_type);

        /** @var \Ppb\Db\Table\Row\User $buyer */
        $buyer = Front::getInstance()->getBootstrap()->getResource('user');

        if (!empty($buyer)) {
            $buyer->setAddress(
                $this->getRequest()->getParam('shipping_address_id'));
        }

        $form = new PurchaseForm($this->_type, $this->_listing, $buyer);
        $form->setType($this->_type);

        $headline = $form->getTitle();

        if ($modal) {
            $this->_setNoLayout();
            $view->script()
                ->clearHeaderCode()
                ->clearBodyCode();

            $form->setAsync();
        }

        if ($canPurchase !== true) {
            if (!$modal) {
                $this->_flashMessenger->setMessage(array(
                    'msg'   => $canPurchase,
                    'class' => 'alert-danger',
                ));

                $this->_helper->redirector()->gotoUrl($view->url($this->_listing->link()));
            }
            else {
                return array(
                    'headline'    => $headline,
                    'canPurchase' => $canPurchase,
                    'form'        => null,
                );
            }
        }

        $params = $this->getRequest()->getParams();
        $quantity = $this->getRequest()->getParam('quantity');

        $voucherDetails = null;
        $price = $this->_listing->getData('buyout_price');

        ## -- START :: ADD -- [ MOD:- DISCOUNT RULES @version 1.0 ]
        $productAttributes = (array)$this->getRequest()->getParam('product_attributes');
        $discountedPrice = $this->_listing->discountedPrice($productAttributes);

        if ($discountedPrice !== false) {
            $price = $discountedPrice;
        }
        ## -- END :: ADD -- [ MOD:- DISCOUNT RULES @version 1.0 ]

        if ($voucherCode = $this->getRequest()->getParam('voucher_code')) {
            $vouchersService = new Service\Vouchers();
            $voucher = $vouchersService->findBy($voucherCode, $this->_listing->getData('user_id'));

            if ($voucher instanceof VoucherModel) {
                if ($voucher->isValid()) {
                    $voucherDetails = serialize($voucher->getData());
                    $price = $voucher->apply($price, $this->_listing->getData('currency'), $this->_listing->getData('id'));
                }
            }
        }

        $form->setData($params);

        if ($form->isPost(
            $this->getRequest())
        ) {
            if ($form->isValid() === true) {
                // save product attributes
                ## -- START :: REMOVE -- [ MOD:- DISCOUNT RULES @version 1.0 ]
                ## $productAttributes = $this->getRequest()->getParam('product_attributes');
                ## -- END :: REMOVE -- [ MOD:- DISCOUNT RULES @version 1.0 ]

                $data = array(
                    'quantity'            => $quantity,
                    'amount'              => ($this->_type == 'bid') ? $form->getData('bid_amount') : $form->getData('offer_amount'),
                    'shipping_address_id' => $this->getRequest()->getParam('shipping_address_id'),
                    'postage_id'          => $this->getRequest()->getParam('postage_id'),
                    'apply_insurance'     => $this->getRequest()->getParam('apply_insurance'),
                    'voucher_details'     => $voucherDetails,
                    'product_attributes'  => (count($productAttributes) > 0) ? serialize($productAttributes) : null
                );

                $message = $this->_listing->placeBid($data, $this->_type);

                if (!is_array($message)) {
                    $message = array(
                        'msg'   => $message,
                        'class' => 'alert-success'
                    );
                }

                $this->_flashMessenger->setMessage($message);

                ## -- ADD -- [ MOD:- PRODUCT BUNDLES ]
                if ($this->_type == 'cart') {
                    // now add bundled products if any
                    $bundledProducts = array_filter((array)$this->getRequest()->getParam('bundled_products'));
                    $listingsService = new Service\Listings();

                    foreach ($bundledProducts as $productId) {
                        $product = $listingsService->findBy('id', $productId);
                        if ($product !== null) {
                            $canAddToCart = $product->canAddToCart($quantity);

                            if ($canAddToCart !== true) {
                                $this->_flashMessenger->setMessage(array(
                                    'msg'   => $canAddToCart,
                                    'class' => 'alert-danger',
                                ));
                            }
                            else {
                                $message = $product->placeBid($data, $this->_type);

                                $this->_flashMessenger->setMessage(array(
                                    'msg'   => $message,
                                    'class' => 'alert-success',
                                ));
                            }
                        }
                    }
                }
                ## -- ./ADD -- [ MOD:- PRODUCT BUNDLES ]

                $redirectParams = array(
                    'id'       => $this->_listing['id'],
                    'sale_id'  => $this->_listing->getSaleId(),
                    'type'     => $this->_type,
                    'quantity' => $quantity,
                );

                if (!$form->isAsync()) {
                    $this->_helper->redirector()->redirect('success', null, null, $redirectParams);
                }
                else {
                    if ($this->_type == 'buy') {
                        $form->setRedirectParent()
                            ->setRedirectUrl($view->url(
                                array(
                                    'module'     => 'listings',
                                    'controller' => 'purchase',
                                    'action'     => 'success',
                                ) + $redirectParams));
                    }
                    else {
                        if ($this->_type == 'cart') {
                            /** @var \Cube\Form\Element\Csrf $csrf */
                            $csrf = $form->getElement('csrf');
                            $csrf->setToken(
                                $this->getRequest()->getParam('csrf'));

                            $salesService = new Service\Sales();
                            $sale = $salesService->findBy('id', $this->_listing->getSaleId());

                            $cartBox = $view->cart($sale, 'partials/cart-box.phtml')
                                ->setDisplayTotal()
                                ->setDisplayCheckoutBtns()
                                ->box();
                        }

                        $form->clearElements();
                    }
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
            'form'        => $form,
            'type'        => $this->_type,
            'headline'    => $headline,
            'listing'     => $this->_listing,
            'price'       => $price,
            'user'        => $buyer, // buyer
            'messages'    => $this->_flashMessenger->getMessages(),
            'canPurchase' => $canPurchase,
            'cartBox'     => $cartBox,
        );
    }

    public function Success()
    {
        switch ($this->_type) {
            case 'buy':
                // redirect to the purchase success page, just like the shopping cart checkout action
                $this->_flashMessenger->setMessage(array(
                    'msg'   => $this->_('Thank you for your purchase.'),
                    'class' => 'alert-success',
                ));

                $salesService = new Service\Sales();
                /** @var \Ppb\Db\Table\Row\Sale $sale */
                $sale = $salesService->findBy('id', $this->getRequest()->getParam('sale_id'));

                if ($sale->isActive(false)) {
                    $this->_helper->redirector()->redirect('direct-payment', 'payment', 'app',
                        array('id' => $sale['id']));
                }
                else {
                    $this->_helper->redirector()->redirect('browse', 'invoices', 'members',
                        array('type' => 'bought', 'sale_id' => $sale['id']));
                }

                break;
            case 'cart':
                // if not using async, redirect to cart after adding an item
                $this->_helper->redirector()->redirect('index', 'cart', 'listings', array());

                break;
        }

        $view = Front::getInstance()->getBootstrap()->getResource('view');
        $this->_helper->redirector()->gotoUrl($view->url($this->_listing->link()));
    }

}

