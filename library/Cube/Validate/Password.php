<?php

/**
 *
 * Cube Framework
 *
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2019 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 *
 * @version     2.1 [rev.2.1.02]
 */

/**
 * checks if the variable is greater than a set value (with option to check if greater or equal)
 */

namespace Cube\Validate;

class Password extends AbstractValidate
{

    const MIN_LENGTH = 4;

    const LENGTH = 1;
    const NO_SPACES = 2;
    const UPPERCASE = 3;
    const DIGIT = 4;
    const SPECIAL = 5;

    /**
     *
     * message
     *
     * @var string
     */
    protected $_message = "'%s' does not meet the strength requirements: %settings%.";

    protected $_conditions = array(
        self::LENGTH    => 'at least %value% characters',
        self::NO_SPACES => 'no spaces',
        self::UPPERCASE => 'at least one uppercase letter',
        self::DIGIT     => 'at least one digit',
        self::SPECIAL   => 'at least one special character',
    );

    /**
     *
     * minimum character length
     *
     * @var float
     */
    protected $_minLength = self::MIN_LENGTH;

    /**
     *
     * uppercase letters
     *
     * @var bool
     */
    protected $_uppercase = false;

    /**
     *
     * digits
     *
     * @var bool
     */
    protected $_digit = false;

    /**
     *
     * special characters
     *
     * @var bool
     */
    protected $_special = false;


    /**
     *
     * class constructor
     *
     * @param array $data       data[0] -> min character length
     *                          data[1] -> require at least one uppercase letter
     *                          data[2] -> require at least one digit
     *                          data[3] -> require at least one special character
     */
    public function __construct(array $data = null)
    {
        if (isset($data[0])) {
            $this->setMinLength($data[0]);
        }

        if (isset($data[1])) {
            $this->setUppercase($data[1]);
        }

        if (isset($data[2])) {
            $this->setDigit($data[2]);
        }

        if (isset($data[3])) {
            $this->setSpecial($data[3]);
        }
    }

    /**
     *
     * get the minimum character length
     *
     * @return float
     */
    public function getMinLength()
    {
        return $this->_minLength;
    }

    /**
     *
     * set the minimum character length
     *
     * @param mixed $minLength
     *
     * @return $this
     */
    public function setMinLength($minLength)
    {
        $this->_minLength = $minLength;

        return $this;
    }

    /**
     *
     * get uppercase
     *
     * @return bool
     */
    public function isUppercase()
    {
        return $this->_uppercase;
    }

    /**
     *
     * set uppercase
     *
     * @param bool $uppercase
     *
     * @return $this
     */
    public function setUppercase($uppercase = true)
    {
        $this->_uppercase = $uppercase;

        return $this;
    }

    /**
     *
     * get digit
     *
     * @return bool
     */
    public function isDigit()
    {
        return $this->_digit;
    }

    /**
     *
     * set digit
     *
     * @param bool $digit
     *
     * @return $this
     */
    public function setDigit($digit = true)
    {
        $this->_digit = $digit;

        return $this;
    }

    /**
     *
     * get special
     *
     * @return bool
     */
    public function isSpecial()
    {
        return $this->_special;
    }

    /**
     *
     * set special
     *
     * @param bool $special
     *
     * @return $this
     */
    public function setSpecial($special = true)
    {
        $this->_special = $special;

        return $this;
    }

    /**
     *
     * checks if the string matches the requirements set
     *
     * @return bool  return true if all requirements are met
     */
    public function isValid()
    {
        $translate = $this->getTranslate();

        $errors = array();

        $value = $this->getValue();

        if (empty($value)) {
            return true;
        }

        if (strlen($value) < $this->getMinLength()) {
            $errors[] = str_replace('%value%', $this->getMinLength(), $translate->_($this->_conditions[self::LENGTH]));
        }

        if (preg_match("/\s/", $value)) {
            $errors[] = $translate->_($this->_conditions[self::NO_SPACES]);
        }

        if ($this->isUppercase()) {
            if (!preg_match('/([A-Z]+)/', $value)) {
                $errors[] = $translate->_($this->_conditions[self::UPPERCASE]);
            }
        }

        if ($this->isDigit()) {
            if (!preg_match('/\d/', $value)) {
                $errors[] = $translate->_($this->_conditions[self::DIGIT]);
            }
        }

        if ($this->isSpecial()) {
            if (!preg_match('/\W/', $value)) {
                $errors[] = $translate->_($this->_conditions[self::SPECIAL]);
            }
        }

        $this->setMessage(
            str_replace('%settings%', implode(', ', $errors), $translate->_($this->getMessage())));

        return (count($errors) > 0) ? false : true;
    }

}

