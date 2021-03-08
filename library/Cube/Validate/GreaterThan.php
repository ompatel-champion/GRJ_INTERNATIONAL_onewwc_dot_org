<?php

/**
 *
 * Cube Framework
 *
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2019 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 *
 * @version     2.1 [rev.2.1.01]
 */

/**
 * checks if the variable is greater than a set value (with option to check if greater or equal)
 */

namespace Cube\Validate;

class GreaterThan extends AbstractValidate
{

    const GREATER_EQUAL = 1;
    const GREATER = 2;
    const TOO_LONG = 3;

    protected $_messages = array(
        self::GREATER_EQUAL => "'%s' must be greater or equal to %value%.",
        self::GREATER       => "'%s' must be greater than %value%.",
    );

    /**
     *
     * the minimum value allowed for the validator to check
     *
     * @var float
     */
    protected $_minValue;

    /**
     *
     * if true, it will check for equal values as well
     *
     * @var bool
     */
    protected $_equal = false;

    /**
     *
     * strict checking for the empty value
     *
     * @var bool
     */
    protected $_strict = false;

    /**
     *
     * class constructor
     *
     * initialize the minimum value allowed and the equal check
     *
     * @param array $data       data[0] -> min value;
     *                          data[1] -> accept equal values (default = false)
     *                          data[2] -> strict checking for the empty value
     */
    public function __construct(array $data = null)
    {
        if (isset($data[0])) {
            $this->setMinValue($data[0]);
        }

        if (isset($data[1])) {
            $this->setEqual($data[1]);
        }

        if (isset($data[2])) {
            $this->setStrict($data[2]);
        }
    }

    /**
     *
     * get the minimum value accepted by the validator
     *
     * @return float
     */
    public function getMinValue()
    {
        return $this->_minValue;
    }

    /**
     *
     * set the minimum value the validator will compare against
     *
     * @param mixed $minValue
     *
     * @return $this
     */
    public function setMinValue($minValue)
    {
        $this->_minValue = $minValue;

        return $this;
    }

    /**
     *
     * check if equal values are accepted
     *
     * @return bool
     */
    public function getEqual()
    {
        return $this->_equal;
    }

    /**
     *
     * set whether to validate equal values
     *
     * @param bool $equal
     *
     * @return $this
     */
    public function setEqual($equal = true)
    {
        $this->_equal = (bool)$equal;

        if ($this->_equal === true) {
            $this->setMessage($this->_messages[self::GREATER_EQUAL]);
        }
        else {
            $this->setMessage($this->_messages[self::GREATER]);
        }

        return $this;
    }

    /**
     *
     * get strict value
     *
     * @return bool
     */
    public function getStrict()
    {
        return $this->_strict;
    }

    /**
     *
     * set strict value
     *
     * @param bool $strict
     *
     * @return $this
     */
    public function setStrict($strict = true)
    {
        $this->_strict = (bool)$strict;

        return $this;
    }

    /**
     *
     * checks if the variable is greater than (or equal to) the set minimum value
     * also returns true if value is empty (or null if strict is enabled)
     *
     * @return bool          return true if the validation is successful
     */
    public function isValid()
    {
        $value = $this->getValue();

        $minValue = $this->getMinValue();
        $isStrict = $this->getStrict();
        $isEqual = $this->getEqual();

        $this->setMessage(
            str_replace('%value%', $minValue, $this->getMessage()));

        if (((empty($value) || (doubleval($value) == 0)) && $isStrict === false)
            || (is_null($value) && $isStrict === true)) {
            return true;
        }
        else if ($isEqual === true) {
            if ($value < $minValue) {
                return false;
            }

            return true;
        }
        else {
            if ($value <= $minValue) {
                return false;
            }

            return true;
        }
    }

}

