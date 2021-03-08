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
 * currency selector view helper class
 */
/**
 * MOD:- CURRENCY SELECTOR
 */

namespace App\View\Helper;

use Ppb\View\Helper\AbstractHelper,
    Ppb\Service\Table\Currencies as CurrenciesService,
    App\Controller\Plugin\CurrencySelector as CurrencySelectorPlugin,
    Cube\Controller\Front;

class CurrencySelector extends AbstractHelper
{

    /**
     *
     * the view partial to be used
     *
     * @var string
     */
    protected $_partial = 'partials/currency-selector.phtml';

    /**
     *
     * cart dropdown helper initialization class
     *
     * @param string $partial
     *
     * @return $this
     */
    public function currencySelector($partial = null)
    {
        if ($partial !== null) {
            $this->setPartial($partial);
        }

        return $this;
    }

    /**
     *
     * currency selector helper
     *
     * @return string
     */
    public function render()
    {
        $settings = $this->getSettings();

        if ($settings['enable_currency_selector']) {
            $view = $this->getView();

            $translate = $this->getTranslate();

            $bootstrap = Front::getInstance()->getBootstrap();
            /** @var \Cube\Session $session */
            $session = $bootstrap->getResource('session');

            $currencies = array();

            $activeCurrency = strtoupper($session->get(CurrencySelectorPlugin::SELECTED_CURRENCY));

            $currenciesService = new CurrenciesService();

            $select = $currenciesService->getTable()->select();

            if (!empty($settings['allowed_currencies'])) {
                $select->where('iso_code IN (?)', @unserialize($settings['allowed_currencies']));
            }

            $rowset = $currenciesService->getTable()->fetchAll($select);

            /** @var \Cube\Db\Table\Row $row */
            foreach ($rowset as $row) {
                $currencies[(string)$row['iso_code']] = $row->toArray();
            }

            $currencies = array_merge(array('default' => array(
                'iso_code'    => '',
                'symbol'      => $translate->_('€£$'),
                'description' => $translate->_('Default currency'),
            )), $currencies);

            if (empty($activeCurrency)) {
                $activeCurrency = $translate->_('€£$');
            }
            else if (in_array($activeCurrency, array_keys($currencies))) {
                $activeCurrencyRow = $currencies[$activeCurrency];
                if (!empty($activeCurrencyRow['symbol'])) {
                    $activeCurrency = $activeCurrencyRow['symbol'];
                }
            }

            $view->setVariables(array(
                'currencies'     => $currencies,
                'activeCurrency' => $activeCurrency,
            ));


            return $view->process(
                $this->getPartial(), true);
        }

        return '';
    }

}

