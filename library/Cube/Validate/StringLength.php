<?php

/**
 *
 * Cube Framework
 *
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2017 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 *
 * @version     1.10 [rev.1.10.01]
 */

/**
 * checks if the variable is greater than a set value (with option to check if greater or equal)
 */

namespace Cube\Validate;

class StringLength extends AbstractValidate
{

    const NO_STRING = 1;
    const TOO_SHORT = 2;
    const TOO_LONG = 3;

    protected $_messages = array(
        self::NO_STRING => "'%s' expects a string, invalid type given.",
        self::TOO_SHORT => "'%s' must contain at least %value% characters.",
        self::TOO_LONG  => "'%s' must contain no more than %value% characters.",
    );

    /**
     *
     * minimum characters allowed
     *
     * @var int
     */
    private $_min;

    /**
     *
     * maximum characters allowed
     *
     * @var int
     */
    private $_max;

    /**
     *
     * class constructor
     *
     * initialize the minimum and maximum values allowed
     *
     * @param array $data       data[0] -> min value;
     *                          data[1] -> max value;
     */
    public function __construct(array $data = null)
    {
        $this->setMin($data[0])
            ->setMax($data[1]);
    }

    /**
     *
     * get the minimum characters allowed
     *
     * @return int
     */
    public function getMin()
    {
        return $this->_min;
    }

    /**
     *
     * set minimum characters allowed
     *
     * @param int $min
     *
     * @return $this
     */
    public function setMin($min)
    {
        $this->_min = (integer)$min;

        return $this;
    }

    /**
     *
     * get max characters allowed
     *
     * @return int
     */
    public function getMax()
    {
        return $this->_max;
    }

    /**
     *
     * set max number of characters allowed
     *
     * @param int $max
     *
     * @return $this
     */
    public function setMax($max)
    {
        $this->_max = (integer)$max;

        return $this;
    }

    /**
     *
     * checks if the string length is within the allowed values
     * if the value is empty it will return true as the NotEmpty class should check that.
     *
     * @return bool          return true if the validation is successful
     */
    public function isValid()
    {
        $value = $this->getValue();

        if (empty($value)) {
            return true;
        }

        $min = $this->getMin();
        $max = $this->getMax();

        if (!is_string($value)) {
            $this->setMessage($this->_messages[self::NO_STRING]);

            return false;
        }
        else if (strlen($value) < $min) {
            $this->setMessage($this->_messages[self::TOO_SHORT]);
            $this->setMessage(
                str_replace('%value%', $min, $this->getMessage()));

            return false;
        }
        else if (strlen($value) > $max &&
            $max > $min
        ) {
            $this->setMessage($this->_messages[self::TOO_LONG]);
            $this->setMessage(
                str_replace('%value%', $max, $this->getMessage()));

            return false;
        }

        return true;
    }

}

