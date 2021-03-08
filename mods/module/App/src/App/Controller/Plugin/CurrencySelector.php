<?php

/**
 *
 * PHP Pro Bid
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.0
 */
/**
 * currency selector controller plugin class
 */
/**
 * MOD:- CURRENCY SELECTOR
 */
namespace App\Controller\Plugin;

use Cube\Controller\Plugin\AbstractPlugin,
    Cube\Controller\Front;

class CurrencySelector extends AbstractPlugin
{

    /**
     * cookie name
     */
    const SELECTED_CURRENCY = 'SelectedCurrency';

    public function preRoute()
    {
        $currency = $this->getRequest()->getParam('selected_currency');
        if ($currency) {
            $bootstrap = Front::getInstance()->getBootstrap();
            /* @var \Cube\Session $session */
            $session = $bootstrap->getResource('session');
            if ($currency !== 'default') {
                $session->set(self::SELECTED_CURRENCY, strtoupper($currency));
            } else {
                $session->set(self::SELECTED_CURRENCY, null);
            }
        }
    }

}

