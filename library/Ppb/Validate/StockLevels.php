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
 * stock levels field validator class
 */

namespace Ppb\Validate;

use Cube\Validate\AbstractValidate,
    Ppb\Form\Element\StockLevels as StockLevelsElement;

class StockLevels extends AbstractValidate
{

    const NO_STOCK = 1;
    const QUANTITY_NOT_NUMERIC = 2;
    const PRICE_NOT_NUMERIC = 3;

    protected $_messages = array(
        self::NO_STOCK             => "'%s' is required and cannot be empty.",
        self::QUANTITY_NOT_NUMERIC => "'%s': the quantity fields only accept positive integer values.",
        self::PRICE_NOT_NUMERIC    => "'%s': the price fields only accept numeric values.",
    );

    /**
     *
     * checks if at least one stock option has been entered and
     * if the price fields contain numeric values
     *
     * @return bool          return true if the validation is successful
     */
    public function isValid()
    {
        $value = $this->getValue();

        $array = array();
        if (is_array($value)) {
            foreach ($value as $k => $v) {
                $array[StockLevelsElement::FIELD_OPTIONS][] = $v[StockLevelsElement::FIELD_OPTIONS];
                $array[StockLevelsElement::FIELD_PRICE][] = $v[StockLevelsElement::FIELD_PRICE];
                $array[StockLevelsElement::FIELD_QUANTITY][] = $v[StockLevelsElement::FIELD_QUANTITY];
            }

            $value = $array;

            if (isset($value[StockLevelsElement::FIELD_OPTIONS])) {
                $values = array_filter($value[StockLevelsElement::FIELD_OPTIONS]);

                if (count($values) > 0) {
                    $prices = array_filter($value[StockLevelsElement::FIELD_PRICE]);
                    foreach ($prices as $price) {
                        if (!is_numeric($price)) {
                            $this->setMessage($this->_messages[self::PRICE_NOT_NUMERIC]);

                            return false;
                        }
                    }

                    $quantities = array_filter($value[StockLevelsElement::FIELD_QUANTITY], function($val) {
                        return ($val !== null && $val !== false && $val !== '');
                    });
                    foreach ($quantities as $quantity) {
                        if (!preg_match('#^[0-9]+$#', $quantity)) {
                            $this->setMessage($this->_messages[self::QUANTITY_NOT_NUMERIC]);

                            return false;
                        }
                    }

                    if (count($quantities) > 0) {
                        return true;
                    }
                }

                $this->setMessage($this->_messages[self::NO_STOCK]);

                return false;
            }
        }

        return true;
    }

}

