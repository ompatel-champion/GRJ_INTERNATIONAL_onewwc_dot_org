<?php

/**
 *
 * PHP Pro Bid
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2019 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     8.1 [rev.8.1.01]
 */

/**
 * checks if the variable is greater than a set value (with option to check if greater or equal)
 */

namespace Ppb\Validate;

use Cube\Validate\GreaterThan,
    Cube\Locale;

class DateGreaterThan extends GreaterThan
{

    /**
     *
     * min value in original format
     *
     * @var string
     */
    private $_inputMinValue;


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
        $this->setInputMinValue($minValue);

        parent::setMinValue(
            Locale\DateTime::getInstance()->localizedToDateTime($minValue));

        return $this;
    }

    /**
     *
     * get input min value
     *
     * @return string
     */
    public function getInputMinValue()
    {
        return $this->_inputMinValue;
    }

    /**
     *
     * set input min value
     *
     * @param string $inputMinValue
     *
     * @return $this
     */
    public function setInputMinValue($inputMinValue)
    {
        $this->_inputMinValue = $inputMinValue;

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
        $message = $this->getMessage();

        $isValid = parent::isValid();

        $this->setMessage(
            str_replace('%value%', $this->getInputMinValue(), $message));

        return $isValid;
    }

}

