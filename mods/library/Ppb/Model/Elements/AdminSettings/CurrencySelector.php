<?php

/**
 *
 * PHP Pro Bid
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2018 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     8.0 [rev.8.0.07]
 */

/**
 * MOD:- CURRENCY SELECTOR
 */

namespace Ppb\Model\Elements\AdminSettings;

use Ppb\Model\Elements\AbstractElements,
    Ppb\Form\Element\Selectize,
    Ppb\Service\Table\Currencies as CurrenciesService;

class CurrencySelector extends AbstractElements
{
    /**
     *
     * related class
     *
     * @var bool
     */
    protected $_relatedClass = true;

    /**
     *
     * currencies table service
     *
     * @var \Ppb\Service\Table\Currencies
     */
    protected $_currencies;

    /**
     *
     * get currencies table service
     *
     * @return \Ppb\Service\Table\Currencies
     */
    public function getCurrencies()
    {
        if (!$this->_currencies instanceof CurrenciesService) {
            $this->setCurrencies(
                new CurrenciesService());
        }

        return $this->_currencies;
    }

    /**
     *
     * set currencies service
     *
     * @param \Ppb\Service\Table\Currencies $currencies
     *
     * @return $this
     */
    public function setCurrencies(CurrenciesService $currencies)
    {
        $this->_currencies = $currencies;

        return $this;
    }

    /**
     *
     * get model elements
     *
     * @return array
     */
    public function getElements()
    {
        $translate = $this->getTranslate();

        return array(
            array(
                'form_id'      => 'currency_selector',
                'id'           => 'enable_currency_selector',
                'element'      => 'checkbox',
                'label'        => $this->_('Enable Currency Selector'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check above to enable the module.<br> '
                    . 'Enabling the module will allow buyers to pay sellers for products using their preferred currency.<br>'
                    . '<b>Important</b>: This only applies to products.')
            ),
            array(
                'form_id'      => 'currency_selector',
                'id'           => 'allowed_currencies',
                'subtitle'     => $this->_('Allowed Currencies'),
                'element'      => '\\Ppb\\Form\\Element\\Selectize',
                'label'        => $this->_('Select Currencies'),
                'description'  => $this->_('Choose which currencies will be allowed in the selector, or leave empty to allow all currencies.'),
                'multiOptions' => $this->getCurrencies()->getMultiOptions('iso_code'),
                'attributes'   => array(
                    'id'          => 'selectizeCurrencyIds',
                    'class'       => 'form-control input-large',
                    'placeholder' => $translate->_('Choose Currencies...'),
                ),
                'multiple'     => true,
                'dataUrl'      => Selectize::NO_REMOTE,
            ),
        );
    }
}

