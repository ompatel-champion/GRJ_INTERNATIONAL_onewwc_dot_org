<?php

/**
 *
 * PHP Pro Bid
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2020 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     8.2 [rev.8.2.02]
 */

namespace Listings\Controller;

use Ppb\Controller\Action\AbstractAction,
    Cube\Controller\Front,
    Cube\View,
    Listings\Form,
    Ppb\Service,
    Ppb\Model\Elements\User\CartCheckout,
    Cube\Authentication\Authentication,
    Ppb\Authentication\Adapter,
    Ppb\Db\Table\Row\User as UserModel;

class Cart extends AbstractAction
{

    /**
     *
     * sales/cart service
     *
     * @var \Ppb\Service\Sales
     */
    protected $_sales;

    /**
     *
     * user token
     *
     * @var string
     */
    protected $_userToken = null;

    public function init()
    {
        $this->_sales = new Service\Sales();

        $bootstrap = Front::getInstance()->getBootstrap();

        $this->_userToken = strval($bootstrap->getResource('session')->getCookie(UserModel::USER_TOKEN));
    }

    /**
     *
     * view and manage a shopping cart
     * will allow the removal of products, editing of quantities, selection of shipping address and selection of shipping method
     *
     * @return array
     */
    public function Index()
    {
        $id = $this->getRequest()->getParam('id');

        $multiOptions = $this->_sales->getMultiOptions($this->_userToken);

        $saleId = (in_array($id, array_keys($multiOptions))) ? $id : current(array_keys($multiOptions));

        $form = null;
        if ($saleId) {
            /** @var \Ppb\Db\Table\Row\Sale $sale */
            $sale = $this->_sales->findBy('id', $saleId);
            $form = new Form\Cart($sale);

            $form->setData(
                $this->getRequest()->getParams());

            if ($form->isPost(
                $this->getRequest())
            ) {
                if ($form->isValid()) {
                    if ($this->getRequest()->getParam(Form\Cart::BTN_UPDATE_CART)) {
                        $this->_flashMessenger->setMessage(array(
                            'msg'   => $this->_('The shopping cart has been updated successfully.'),
                            'class' => 'alert-success',
                        ));

                        $quantities = (array)$this->getRequest()->getParam('quantity');
                        $sale->updateQuantities($quantities);

                        $this->_helper->redirector()->redirect('index', null, null, array('id' => $saleId));
                    }
                    else if ($this->getRequest()->getParam(Form\Cart::BTN_CHECKOUT)) {
                        $this->_helper->redirector()->redirect('checkout', null, null, array('id' => $saleId));
                    }
                }

                if (count($form->getMessages())) {
                    $this->_flashMessenger->setMessage(array(
                        'msg'   => $form->getMessages(),
                        'class' => 'alert-danger',
                    ));
                }
            }
        }


        return array(
            'headline'     => $this->_('Shopping Cart'),
            'id'           => $saleId,
            'multiOptions' => $multiOptions,
            'form'         => $form,
            'messages'     => $this->_flashMessenger->getMessages(),
        );
    }

    public function Checkout()
    {
        $id = $this->getRequest()->getParam('id');

        $multiOptions = $this->_sales->getMultiOptions($this->_userToken);

        $form = null;

        if (in_array($id, array_keys($multiOptions))) {
            /** @var \Ppb\Db\Table\Row\Sale $sale */
            $sale = $this->_sales->findBy('id', $id);
            $canCheckout = $sale->canCheckout();

            if ($canCheckout !== true) {
                $this->_flashMessenger->setMessage(array(
                    'msg'   => $canCheckout,
                    'class' => 'alert-danger',
                ));

                $this->_helper->redirector()->redirect('index', null, null, array('id' => $id));
            }

            $form = new Form\Checkout($sale, null);

            $params = $this->getRequest()->getParams();
            $form->setData($params);


            if ($this->getRequest()->getParam('voucher_add')) {
                $sale->saveVoucherDetails($this->getRequest()->getParam('voucher_code'));
            }

            if ($form->isPost(
                $this->getRequest())
            ) {
                if ($form->isValid() === true) {
                    $usersService = new Service\Users();

                    if (!$this->_user) {
                        $authentication = Authentication::getInstance();

                        $usersService = new Service\Users();

                        $userId = $usersService->save($params);

                        $user = $usersService->findBy('id', $userId);
                        $user->processRegistration();

                        // log user in
                        $authentication->authenticate(
                            new Adapter(array(), $userId));

                        if ($authentication->hasIdentity()) {
                            /** @var \Cube\View $view */
                            $view = Front::getInstance()->getBootstrap()->getResource('view');

                            $user = $authentication->getStorage()->read();
                            $view->set('loggedInUser', $user);
                        }
                    }
                    else {
                        $userId = $this->_user['id'];
                    }

                    // save shipping/billing addresses if new
                    $billingAddressId = $this->getRequest()->getParam(CartCheckout::PRF_BLG . 'address_id');
                    if (!$billingAddressId) {
                        $billingAddressId = $usersService->getUsersAddressBook()->save($params, $userId,
                            CartCheckout::PRF_BLG);
                    }

                    if ($this->getRequest()->getParam('alt_ship')) {
                        $shippingAddressId = $this->getRequest()->getParam(CartCheckout::PRF_SHP . 'address_id');
                        if (!$shippingAddressId) {
                            $shippingAddressId = $usersService->getUsersAddressBook()->save($params, $userId,
                                CartCheckout::PRF_SHP);
                        }
                    }
                    else {
                        $shippingAddressId = $billingAddressId;
                    }

                    $params = array_merge($params, array(
                        'buyer_id'            => $userId,
                        'seller_id'           => $sale['seller_id'],
                        'pending'             => 0,
                        'billing_address_id'  => $billingAddressId,
                        'shipping_address_id' => $shippingAddressId,
                        'checkout'            => true,
                    ));

                    $this->_sales->save($params);

                    $this->_flashMessenger->setMessage(array(
                        'msg'   => $this->_('Thank you for your purchase.'),
                        'class' => 'alert-success',
                    ));

                    // get the sale object again, we need that to be able to use all updated fields
                    $sale = $this->_sales->findBy('id', $id);

                    if ($sale->isActive(false)) {
                        $paymentMethodId = $sale['payment_method_id'];
                        $paymentMethods = array_map(function ($element) {
                            return $element['id'];
                        }, $sale->getPaymentMethods('direct'));

                        $this->_helper->redirector()->redirect('direct-payment', 'payment', 'app',
                            array(
                                'id'       => $sale['id'],
                                'redirect' => (in_array($paymentMethodId, $paymentMethods)) ? true : false
                            ));
                    }
                    else {
                        $this->_helper->redirector()->redirect('browse', 'invoices', 'members',
                            array(
                                'type'    => 'bought',
                                'sale_id' => $sale['id']
                            ));
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
                'msg'   => $this->_('The shopping cart you have selected doesnt exist.'),
                'class' => 'alert-danger',
            ));
            $this->_helper->redirector()->redirect('index');
        }

        return array(
            'headline' => $this->_('Checkout'),
            'form'     => $form,
            'messages' => $this->_flashMessenger->getMessages(),
        );
    }

    public function Add() // @8.0: DEPRECATED
    {
        $quantity = (($quantity = $this->getRequest()->getParam('quantity')) < 1) ? 1 : $quantity;
        $partial = $this->getRequest()->getParam('partial');

        $async = $this->getRequest()->getParam('async');

        $view = new View();

        $mainView = Front::getInstance()->getBootstrap()->getResource('view');

        $listingsService = new Service\Listings();
        /** @var \Ppb\Db\Table\Row\Listing $listing */
        $listing = $listingsService->fetchAll(
            $listingsService->getTable()->select()
                ->forUpdate()
                ->where('id = ?', (int)$this->getRequest()->getParam('id')))
            ->getRow(0);

        $canAddToCart = $listing->canAddToCart($quantity, $this->getRequest()->getParam('product_attributes'));

        if ($canAddToCart !== true) {
            if ($async) {
                $data = array(
                    'success'      => false,
                    'message'      => '<span class="text-danger">' . $canAddToCart . '</span>',
                    'cartDropdown' => null,
                );

                $this->getResponse()->setHeader('Content-Type: application/json');

                $view->setContent(
                    json_encode($data));

                return $view;
            }
            else {
                $this->_flashMessenger->setMessage(array(
                    'msg'   => $canAddToCart,
                    'class' => 'alert-danger',
                ));

                $this->_helper->redirector()->redirect('details', 'listing', null, $this->getRequest()->getParams());
            }
        }
        else {
            $listing->addToCart(
                $quantity, $this->getRequest()->getParam('product_attributes'));

            if ($async) {
                $listing = $listingsService->findBy('id', $listing['id']);

                $data = array(
                    'success'      => true,
                    'message'      => '<span class="text-success">'
                        . sprintf($this->getTranslate()->_('%s x "%s" has been added to the shopping cart.'), $quantity, $listing['name'])
                        . '</span>',
                    'cartDropdown' => $mainView->cartDropdown($partial)->render(),
                );

                $this->getResponse()->setHeader('Content-Type: application/json');

                $view->setContent(
                    json_encode($data));

                return $view;
            }
            else {
                $this->_flashMessenger->setMessage(array(
                    'msg'   => $this->_('The product has been added to the shopping cart.'),
                    'class' => 'alert-success',
                ));

                $this->_helper->redirector()->redirect('index');
            }
        }
    }

    public function Delete()
    {
        $id = $this->getRequest()->getParam('item_id');

        $async = $this->getRequest()->getParam('async');

        $view = new View();

        $mainView = Front::getInstance()->getBootstrap()->getResource('view');

        $salesListings = new Service\Table\SalesListings();

        $result = $salesListings->deleteOne($id, $this->_userToken);

        if ($result === false) {
            $msg = $this->_('Could not remove the selected product.');

            if ($async) {
                $data = array(
                    'success'      => false,
                    'message'      => '<span class="text-danger">' . $msg . '</span>',
                    'cartDropdown' => $mainView->cart(null, 'partials/cart-dropdown.phtml')->dropdown(),
                );
            }
            else {
                $this->_flashMessenger->setMessage(array(
                    'msg'   => $msg,
                    'class' => 'alert-danger',
                ));
            }
        }
        else {
            $msg = $this->_('The product has been removed from the shopping cart.');
            if ($async) {
                $data = array(
                    'success'      => true,
                    'message'      => '<span class="text-success">' . $msg . '</span>',
                    'cartDropdown' => $mainView->cart(null, 'partials/cart-dropdown.phtml')->dropdown(),
                );
            }
            else {
                $this->_flashMessenger->setMessage(array(
                    'msg'   => $msg,
                    'class' => 'alert-success',
                ));
            }
        }

        if ($async) {
            $this->getResponse()->setHeader('Content-Type: application/json');

            $view->setContent(
                json_encode($data));

            return $view;
        }
        else {
            $this->_helper->redirector()->redirect('index');
        }
    }

    public function MoveWishList()
    {
        $id = $this->getRequest()->getParam('item_id');

        $async = $this->getRequest()->getParam('async');

        $view = new View();

        $mainView = Front::getInstance()->getBootstrap()->getResource('view');

        $salesListings = new Service\Table\SalesListings();

        $result = $salesListings->moveWishList($id, $this->_userToken);

        if ($result === false) {
            $msg = $this->_('Could not find the selected product.');

            if ($async) {
                $data = array(
                    'success'      => false,
                    'message'      => '<span class="text-danger">' . $msg . '</span>',
                    'cartDropdown' => $mainView->cart(null, 'partials/cart-dropdown.phtml')->dropdown(),
                );
            }
            else {
                $this->_flashMessenger->setMessage(array(
                    'msg'   => $msg,
                    'class' => 'alert-danger',
                ));
            }
        }
        else {
            $msg = $this->_('The product has moved to your wish list.');
            if ($async) {
                $data = array(
                    'success'      => true,
                    'message'      => '<span class="text-success">' . $msg . '</span>',
                    'cartDropdown' => $mainView->cart(null, 'partials/cart-dropdown.phtml')->dropdown(),
                );
            }
            else {
                $this->_flashMessenger->setMessage(array(
                    'msg'   => $msg,
                    'class' => 'alert-success',
                ));
            }
        }

        if ($async) {
            $this->getResponse()->setHeader('Content-Type: application/json');

            $view->setContent(
                json_encode($data));

            return $view;
        }
        else {
            $this->_helper->redirector()->redirect('index');
        }
    }

    public function EmptyCart()
    {
        if (!empty($this->_userToken)) {
            $salesService = new Service\Sales();

            $sales = $salesService->fetchAll(
                $salesService->getTable()->select()
                    ->where('pending = ?', 1)
                    ->where('user_token = ?', $this->_userToken)
            );

            /** @var \Ppb\Db\Table\Row\Sale $sale */
            foreach ($sales as $sale) {
                $sale->delete(true);
            }

            $this->_flashMessenger->setMessage(array(
                'msg'   => $this->_('Your shopping cart has been emptied.'),
                'class' => 'alert-success',
            ));
        }

        $this->_helper->redirector()->redirect('index');
    }

    public function CartCheckoutDetails()
    {
        $view = Front::getInstance()->getBootstrap()->getResource('view');

        $id = $this->getRequest()->getParam('id');
        $voucherCode = $this->getRequest()->getParam('voucher_code');
        $params = $this->getRequest()->getParams();

        $salesService = new Service\Sales();

        /** @var \Ppb\Db\Table\Row\Sale $sale */
        $sale = $salesService->findBy('id', $id);

        $form = new Form\Checkout($sale);
        $form->setData($params);

        $sale->saveVoucherDetails(
            $this->getRequest()->getParam('voucher_code'));

        $cartCheckoutDetails = $view->cart($sale, 'partials/cart-checkout-details.phtml')->checkoutDetails($form);

        $voucher = null;
        if ($voucherCode) {
            $vouchersService = new \Ppb\Service\Vouchers();
            $voucher = $vouchersService->findBy($voucherCode, $sale['seller_id']);
        }

        $voucherMessage = $view->partial('partials/voucher-message.phtml', array(
            'voucher'     => $voucher,
            'listingId'   => $sale->getListingsIds(),
            'voucherCode' => $voucherCode,
        ));

        $this->getResponse()->setHeader('Content-Type: application/json');

        $outputView = new View();

        $outputView->setContent(
            json_encode(array(
                'cartCheckoutDetails' => $cartCheckoutDetails,
                'voucherMessage'      => $voucherMessage,
                'shippingDetails'     => $form->getShippingDetails(),

            )));

        return $outputView;
    }
}

