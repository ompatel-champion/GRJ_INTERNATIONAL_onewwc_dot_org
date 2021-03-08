<?php

/**
 *
 * PHP Pro Bid
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2018 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     8.0 [rev.8.0.01]
 */

/**
 * shopping cart view helper class
 */

namespace Listings\View\Helper;

use Ppb\View\Helper\AbstractHelper,
    Cube\Controller\Front,
    Ppb\Service,
    Ppb\Db\Table\Row\User as UserModel;

class Cart extends AbstractHelper
{

    /**
     *
     * sale object
     *
     * @var \Ppb\Db\Table\Row\Sale
     */
    protected $_sale = null;

    /**
     *
     * display total box flag
     *
     * @var bool
     */
    protected $_displayTotal = false;

    /**
     *
     * display cart view / checkout buttons flag
     *
     * @var bool
     */
    protected $_displayCheckoutBtns = false;

    /**
     *
     * display remove button flag
     *
     * @var bool
     */
    protected $_displayRemoveBtn = false;

    /**
     *
     * display wish list button flag
     *
     * @var bool
     */
    protected $_displayWishListBtn = false;

    /**
     *
     * cart helper initialization class
     *
     * @param \Ppb\Db\Table\Row\Sale $cart
     * @param string                 $partial
     *
     * @return $this
     */
    public function cart($cart = null, $partial = null)
    {
        if ($cart !== null) {
            $this->setSale($cart);
        }

        if ($partial !== null) {
            $this->setPartial($partial);
        }

        $this->setDisplayTotal(false)
            ->setDisplayCheckoutBtns(false)
            ->setDisplayRemoveBtn(false)
            ->setDisplayWishListBtn(false);

        return $this;
    }

    /**
     *
     * get sale object
     *
     * @return \Ppb\Db\Table\Row\Sale
     */
    public function getSale()
    {
        return $this->_sale;
    }

    /**
     *
     * set sale object
     *
     * @param \Ppb\Db\Table\Row\Sale $sale
     *
     * @return $this
     */
    public function setSale($sale)
    {
        $this->_sale = $sale;

        return $this;
    }

    /**
     *
     * get display total box
     *
     * @return bool
     */
    public function isDisplayTotal()
    {
        return $this->_displayTotal;
    }

    /**
     *
     * set display total box
     *
     * @param bool $displayTotal
     *
     * @return $this
     */
    public function setDisplayTotal($displayTotal = true)
    {
        $this->_displayTotal = $displayTotal;

        return $this;
    }

    /**
     *
     * get display cart view / checkout buttons flag
     *
     * @return bool
     */
    public function isDisplayCheckoutBtns()
    {
        return $this->_displayCheckoutBtns;
    }

    /**
     *
     * set display cart view / checkout buttons flag
     *
     * @param bool $displayCheckoutBtns
     *
     * @return $this
     */
    public function setDisplayCheckoutBtns($displayCheckoutBtns = true)
    {
        $this->_displayCheckoutBtns = $displayCheckoutBtns;

        return $this;
    }

    /**
     *
     * get display remove button flag
     *
     * @return bool
     */
    public function isDisplayRemoveBtn()
    {
        return $this->_displayRemoveBtn;
    }

    /**
     *
     * set display remove button button flag
     *
     * @param bool $displayRemoveBtn
     *
     * @return $this
     */
    public function setDisplayRemoveBtn($displayRemoveBtn = true)
    {
        $this->_displayRemoveBtn = $displayRemoveBtn;

        return $this;
    }

    /**
     *
     * get display wish list button flag
     *
     * @return bool
     */
    public function isDisplayWishListBtn()
    {
        return $this->_displayWishListBtn;
    }

    /**
     *
     * set display wish list button flag
     *
     * @param bool $displayWishListBtn
     *
     * @return $this
     */
    public function setDisplayWishListBtn($displayWishListBtn = true)
    {
        $this->_displayWishListBtn = $displayWishListBtn;

        return $this;
    }

    /**
     *
     * cart dropdown render
     *
     * @return string
     */
    public function dropdown()
    {
        $settings = $this->getSettings();

        if ($settings['enable_shopping_cart']) {
            $view = $this->getView();

            /** @var \Cube\Session $session */
            $session = Front::getInstance()->getBootstrap()->getResource('session');

            $salesService = new Service\Sales();

            /** @var \Ppb\Db\Table\Rowset\Sales $sales */
            $sales = $salesService->fetchAll(
                $salesService->getTable()->select()
                    ->where('pending = ?', 1)
                    ->where('user_token = ?', strval($session->getCookie(UserModel::USER_TOKEN)))
                    ->order(array('updated_at DESC', 'created_at DESC'))
            );

            $price = $sales->calculateTotal($settings['currency'], true);

            $view->setVariables(array(
                'sales'   => $sales,
                'nbItems' => $sales->countItems(),
                'price'   => $view->amount($price, null, null, true),
            ));

            return $view->process(
                $this->getPartial(), true);
        }

        return '';
    }

    /**
     *
     * cart items box render
     *
     * @return string
     */
    public function box()
    {
        $sale = $this->getSale();

        if ($sale) {
            $view = $this->getView();
            $view->setVariables(array(
                'sale'                => $sale,
                'displayTotal'        => $this->isDisplayTotal(),
                'displayCheckoutBtns' => $this->isDisplayCheckoutBtns(),
                'displayRemoveBtn'    => $this->isDisplayRemoveBtn(),
                'displayWishListBtn'  => $this->isDisplayWishListBtn(),
            ));

            return $view->process(
                $this->getPartial(), true);
        }

        return '';
    }

    /**
     *
     * checkout details box (right side)
     *
     * @param \Listings\Form\Checkout $checkoutForm
     *
     * @return string
     */
    public function checkoutDetails($checkoutForm = null)
    {
        $sale = $this->getSale();

        if ($sale) {
            $view = $this->getView();
            $view->setVariables(array(
                'sale'         => $sale,
                'checkoutForm' => $checkoutForm,
            ));

            return $view->process(
                $this->getPartial(), true);
        }

        return '';
    }

}

