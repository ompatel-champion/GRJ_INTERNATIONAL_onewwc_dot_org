<?php

/**
 *
 * PHP Pro Bid
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2016 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.8
 */
/**
 * localized numeric only validator class
 *
 * based on the amount format selected in admin, will accept:
 *
 * DEFAULT: 1234567.90
 * and
 * US: 1,234,567.90
 * or
 * EU: 1.234.567,90
 */

namespace Ppb\Validate;

use Cube\Validate\Numeric,
    Cube\Locale\Format as LocaleFormat;

class LocalizedNumeric extends Numeric
{

    protected $_message = "'%s' must contain a localized numeric value.";

    /**
     *
     * checks if the variable contains a localized numeric value
     *
     * @return bool          return true if the validation is successful
     */
    public function isValid()
    {
        $value = $this->getValue();

        if (empty($value)) {
            return true;
        }

        $value = LocaleFormat::getInstance()->localizedToNumeric($value);

        if ($value !== false) {
            if (preg_match('#^-?\d*\.?\d+$#', $value)) {
                return true;
            }
        }

        return false;
    }

}

