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
 * amount display view helper class
 */

namespace Ppb\View\Helper;

use Ppb\Service\Table\Currencies as CurrenciesService;

class Amount extends AbstractHelper
{
    /**
     * the maximum amount allowed for a decimal input value
     */

    /**
     * default display format
     */

    const DEFAULT_FORMAT = '%s';

    /**
     *
     * currencies
     *
     * @var array
     */
    protected $_currencies;

    /**
     *
     * currencies service
     *
     * @var \Ppb\Service\Table\Currencies
     */
    protected $_currenciesService;

    /**
     *
     * zero display value
     *
     * @var mixed
     */
    protected $_zero = null;

    /**
     *
     * fetch currencies from table
     *
     * @param string $isoCode currency to fetch (by iso code)
     *
     * @return \Cube\Db\Table\Row|null   selected or default currency row or null if requested currency cannot be found
     */
    public function getCurrency($isoCode = null)
    {
        if (empty($this->_currencies)) {
            $currencies = $this->getCurrenciesService()->fetchAll()->toArray();

            foreach ($currencies as $currency) {
                $this->_currencies[(string)$currency['iso_code']] = $currency;
            }
        }

        if ($isoCode === null) {
            $settings = $this->getSettings();
            $isoCode = $settings['currency'];
        }

        if (array_key_exists($isoCode, $this->_currencies)) {
            return $this->_currencies[$isoCode];
        }

        return null;
    }

    /**
     *
     * get currencies service
     *
     * @return \Ppb\Service\Table\Currencies
     */
    public function getCurrenciesService()
    {
        if (!$this->_currenciesService instanceof CurrenciesService) {
            $this->setCurrenciesService(
                new CurrenciesService());
        }

        return $this->_currenciesService;
    }

    /**
     *
     * set currencies service
     *
     * @param \Ppb\Service\Table\Currencies $currenciesService
     *
     * @return $this
     */
    public function setCurrenciesService(CurrenciesService $currenciesService)
    {
        $this->_currenciesService = $currenciesService;

        return $this;
    }

    /**
     *
     * get zero value
     *
     * @return mixed
     */
    public function getZero()
    {
        return $this->_zero;
    }

    /**
     *
     * set zero value
     *
     * @param string $zero
     *
     * @return \Ppb\View\Helper\Amount
     */
    public function setZero($zero)
    {
        $this->_zero = $zero;

        return $this;
    }

    /**
     *
     * amount view helper
     *
     * @param float  $amount        the amount to be displayed
     * @param string $currency      the currency - default currency used if this is null
     * @param string $format        display format, used if custom outputs are needed
     *                              eg: (+%s)
     * @param bool   $overrideZero
     * @param string $currencyTo    currency to convert to
     *
     * @return string|$this
     */
    public function amount($amount = null, $currency = null, $format = null, $overrideZero = false, $currencyTo = null)
    {
        if ($amount === false) {
            return $this;
        }

        $settings = $this->getSettings();
        $translate = $this->getTranslate();

        if ($format === null) {
            $format = self::DEFAULT_FORMAT;
        }

        if ($amount == 0 && $overrideZero === false) {
            if ($settings['display_free_fees']) {
                return sprintf($format, $translate->_('Free'));
            }

            return $translate->_($this->getZero());
        }

        if ($currency === null) {
            $currency = $settings['currency'];
        }

        if ($currencyTo !== null && $currencyTo !== $currency) {
            $amount = $this->getCurrenciesService()->convertAmount($amount, $currency, $currencyTo);
            $currency = $currencyTo;
        }

        $data = $this->getCurrency($currency);

        $symbol = $data['iso_code'];
        $spacing = ' ';
        if (!empty($data['symbol'])) {
            $symbol = $data['symbol'];
            $spacing = '';
        }

        switch ($settings['currency_format']) {
            case '1':
                $amount = number_format($amount, $settings['currency_decimals'], '.', ',');
                break;
            default:
                $amount = number_format($amount, $settings['currency_decimals'], ',', '.');
                break;
        }

        switch ($settings['currency_position']) {
            case '1':
                $output = $symbol . $spacing . $amount;
                break;
            case '3':
                $output = (!empty($data['symbol']) ? $data['symbol'] : '') . $amount . ' ' . $data['iso_code'];
                break;
            default:
                $output = $amount . $spacing . $symbol;
                break;
        }


        return sprintf($format, $output);
    }

}