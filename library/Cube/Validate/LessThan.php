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
 * checks if the variable is smaller than a set value (with option to check if smaller or equal)
 */

namespace Cube\Validate;

class LessThan extends AbstractValidate
{

    const LESS = 1;
    const LESS_EQUAL = 2;

    protected $_messages = array(
        self::LESS       => "'%s' must be smaller than %value%.",
        self::LESS_EQUAL => "'%s' must be smaller or equal to %value%."
    );

    /**
     *
     * the maximum value allowed for the validator to check
     *
     * @var float
     */
    protected $_maxValue;

    /**
     *
     * if true, it will check for equal values as well
     *
     * @var bool
     */
    protected $_equal = false;

    /**
     *
     * class constructor
     *
     * initialize the maximum value allowed and the equal check
     *
     * @param array $data       data[0] -> max value;
     *                          data[1] -> accept equal values (default = false)
     */
    public function __construct(array $data = null)
    {
        if (isset($data[0])) {
            $this->setMaxValue($data[0]);
        }

        if (isset($data[1])) {
            $this->setEqual($data[1]);
        }
    }

    /**
     *
     * get the maximum value accepted by the validator
     *
     * @return float
     */
    public function getMaxValue()
    {
        return $this->_maxValue;
    }

    /**
     *
     * set the maximum value the validator will compare against
     *
     * @param mixed $maxValue
     *
     * @return $this
     */
    public function setMaxValue($maxValue)
    {
        $this->_maxValue = $maxValue;

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
            $this->setMessage($this->_messages[self::LESS_EQUAL]);
        }
        else {
            $this->setMessage($this->_messages[self::LESS]);
        }

        return $this;
    }

    /**
     *
     * checks if the variable is smaller than (or equal to) the set maximum value
     *
     * @return bool          return true if the validation is successful
     */
    public function isValid()
    {
        $value = $this->getValue();

        $maxValue = $this->getMaxValue();
        $isEqual = $this->getEqual();

        $this->setMessage(
            str_replace('%value%', $maxValue, $this->getMessage()));

        if ($isEqual === true) {
            if ($value > $maxValue) {
                return false;
            }

            return true;
        }
        else {
            if ($value >= $maxValue) {
                return false;
            }

            return true;
        }
    }

}

