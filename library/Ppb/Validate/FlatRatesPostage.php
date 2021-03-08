<?php

/**
 *
 * PHP Pro Bid
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2017 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.10 [rev.7.10.01]
 */
/**
 * flat rates location groups postage validator class
 */

namespace Ppb\Validate;

use Cube\Validate\AbstractValidate,
    Ppb\Form\Element\FlatRatesLocationGroups;

class FlatRatesPostage extends AbstractValidate
{

    const NO_POSTAGE = 1;
    const PRICE_NOT_NUMERIC = 2;

    protected $_messages = array(
        self::NO_POSTAGE        => "'%s' is required and cannot be empty.",
        self::PRICE_NOT_NUMERIC => "'%s': the price fields only accept numeric values.",
    );

    /**
     *
     * checks if at least one row has been added and that the price fields contain localized numeric values
     *
     * @return bool          return true if the validation is successful
     */
    public function isValid()
    {
        $value = $this->getValue();

        if (isset($value[FlatRatesLocationGroups::FIELD_NAME])) {
            $values = array_filter($value[FlatRatesLocationGroups::FIELD_NAME]);

            if (count($values) > 0) {
                $prices = array_filter($value[FlatRatesLocationGroups::FIELD_FIRST]) + array_filter($value[FlatRatesLocationGroups::FIELD_ADDL]);
                foreach ($prices as $price) {
                    if (!is_numeric($price)) {
                        $this->setMessage($this->_messages[self::PRICE_NOT_NUMERIC]);

                        return false;
                    }
                }

                return true;
            }
        }

        $this->setMessage($this->_messages[self::NO_POSTAGE]);

        return false;
    }

}

