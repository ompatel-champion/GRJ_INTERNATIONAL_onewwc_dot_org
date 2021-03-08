<?php

/**
 *
 * PHP Pro Bid
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2018 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     8.0 [rev.8.0.02]
 */

/**
 * shopping cart drop-down button/menu view helper class
 *
 * DEPRECATED [@version 8.0]
 */
/**
 * MOD:- CURRENCY SELECTOR
 */

namespace Listings\View\Helper;

use Ppb\View\Helper\AbstractHelper,
    Cube\Controller\Front,
    Ppb\Service,
    Ppb\Db\Table\Row\User as UserModel;

class CartDropdown extends AbstractHelper
{
    /**
     *
     * the view partial to be used
     *
     * @var string
     */
    protected $_partial = 'partials/cart-dropdown.phtml';

    /**
     *
     * cart dropdown helper initialization class
     *
     * @param string $partial
     *
     * @return $this
     */
    public function cartDropdown($partial = null)
    {
        if ($partial !== null) {
            $this->setPartial($partial);
        }

        return $this;
    }

    /**
     *
     * render partial
     *
     * @return string
     */
    public function render()
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

            ## -- ADD 1L -- [ MOD:- CURRENCY SELECTOR ]
            $view->amount(false)->setConvert(true);

            $view->setVariables(array(
                'sales'   => $sales,
                'nbItems' => $sales->countItems(),
                'price'   => $view->amount($price, $settings['currency'], null, true),
            ));

            ## -- CHANGE -- [ MOD:- CURRENCY SELECTOR ]
            $output = $view->process(
                $this->getPartial(), true);

            $view->amount(false)->setConvert(false);

            return $output;
            ## -- ./CHANGE -- [ MOD:- CURRENCY SELECTOR ]
        }

        return '';
    }

}

