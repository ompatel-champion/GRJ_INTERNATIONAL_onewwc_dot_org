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
 * converted amount display view helper class
 */
/**
 * MOD:- CURRENCY SELECTOR
 */

namespace App\View\Helper;

use Ppb\View\Helper\Amount;

class AmountConverted extends Amount
{

    /**
     *
     * amount view helper
     *
     * @param float  $amount        the amount to be displayed
     * @param string $currency      the currency - default currency used if this is null
     * @param string $format        display format, used if custom outputs are needed
     *                              eg: (+%s)
     * @param bool   $overrideZero
     *
     * @return string|$this
     */
    public function amountConverted($amount = null, $currency = null, $format = null, $overrideZero = false)
    {
        return parent::amount($amount, $currency, $format, $overrideZero, true);
    }

}