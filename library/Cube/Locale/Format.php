<?php

/**
 *
 * Cube Framework
 *
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2017 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 *
 * @version     1.9 [rev.1.9.01]
 */

namespace Cube\Locale;

class Format
{

    /**
     * US numeric format
     * 1,234,567.89
     */
    const US = 1;
    /**
     * EU numeric format
     * 1.234.567,89
     */
    const EU = 2;

    /**
     * available formats
     */
    protected $_formats = array(
        self::US,
        self::EU,
    );

    /**
     *
     * format set in settings
     *
     * @var int
     */
    protected $_format;

    /**
     *
     * decimals for numeric to localized
     *
     * @var int
     */
    protected $_decimals = 0;

    /**
     *
     * holds an instance of the object
     *
     * @var $this
     */
    private static $_instance;

    /**
     *
     * returns an instance of the object and creates it if it wasnt instantiated yet
     *
     * @return $this
     */
    public static function getInstance()
    {
        if (!self::$_instance instanceof self) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     *
     * get format
     *
     * @return int
     */
    public function getFormat()
    {
        return $this->_format;
    }

    /**
     *
     * set format
     *
     * @param int $format
     *
     * @return $this
     */
    public function setFormat($format)
    {
        $this->_format = $format;

        return $this;
    }

    /**
     *
     * get decimals
     *
     * @return int
     */
    public function getDecimals()
    {
        return $this->_decimals;
    }

    /**
     *
     * set decimals
     *
     * @param int $decimals
     *
     * @return $this
     */
    public function setDecimals($decimals)
    {
        $this->_decimals = $decimals;

        return $this;
    }


    /**
     *
     * take a localized numeric value and convert it to a standard number
     * or return false if the number cannot be formatted based on the settings input
     *
     * @1.9 if decimals is set to 0 then we will format all input
     *
     * @param string $value
     * @param bool   $valueOnFalse
     *
     * @return float|false
     */
    public function localizedToNumeric($value, $valueOnFalse = false)
    {
        if ($this->_isNumeric($value) && $this->getDecimals() > 0) {
            return $value;
        }

        $format = $this->getFormat();

        switch ($format) {
            case self::US:
                if (!preg_match('#^(-+)?\d{1,3}(?:,?\d{3})*(?:\.\d+)?$#', $value)) {
                    return ($valueOnFalse) ? $value : false;
                }

                $value = str_replace(',', '', $value);

                break;
            case self::EU:
                if (!preg_match('#^(-+)?\d{1,3}(?:\.?\d{3})*(?:\,\d+)?$#', $value)) {
                    return ($valueOnFalse) ? $value : false;
                }

                $value = str_replace(array('.', ','), array('', '.'), $value);
                break;
        }


        return ($this->_isNumeric($value) || $valueOnFalse) ? $value : false;

    }

    /**
     *
     * take a numeric value and convert it to a localized number
     * or return false if the initial input is not numeric
     *
     * @param string $value
     * @param bool   $valueOnFalse
     *
     * @return string|false
     */
    public function numericToLocalized($value, $valueOnFalse = false)
    {
        if (!$this->_isNumeric($value)) {
            return ($valueOnFalse) ? $value : false;
        }

        $format = $this->getFormat();
        $decimals = $this->getDecimals();

        switch ($format) {
            case self::US:
                $value = number_format($value, $decimals, '.', ',');
                break;
            case self::EU:
                $value = number_format($value, $decimals, ',', '.');
                break;
        }

        return $value;
    }

    /**
     *
     * we use preg rather than is_numeric as we only want to allow decimal values
     * (1234567.890)
     *
     * @param string $value
     *
     * @return bool
     */
    protected function _isNumeric($value)
    {
        return (bool)preg_match('#^-?\d*\.?\d+$#', $value);
    }
}

