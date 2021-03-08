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
 * multiple email addresses validator class
 */

namespace Ppb\Validate;

use Cube\Validate\Email;

class MultipleEmails extends Email
{

    const NOT_VALID = 1;
    const DUPLICATE = 2;

    protected $_messages = array(
        self::NOT_VALID => "'%s' must contain one or multiple valid email addresses.",
        self::DUPLICATE => "'%s': you cannot enter an email address more than once.",
    );

    /**
     *
     * emails separator
     *
     * @var string
     */
    protected $_separator = ',';

    /**
     *
     * set emails separator
     *
     * @param string $separator
     *
     * @return $this
     */
    public function setSeparator($separator)
    {
        $this->_separator = $separator;

        return $this;
    }

    /**
     *
     * get emails separator
     *
     * @return string
     */
    public function getSeparator()
    {
        return $this->_separator;
    }


    /**
     *
     * checks if the variable contains a valid email address
     *
     * @return bool          return true if the validation is successful
     */
    public function isValid()
    {
        $separator = $this->getSeparator();

        $value = $this->getValue();
        $emails = array_map('trim', (array)explode($separator, $value));

        $duplicates = array_count_values($emails);

        foreach ($emails as $email) {
            parent::setValue(
                trim($email));

            if (!parent::isValid()) {
                $this->setMessage($this->_messages[self::NOT_VALID]);

                return false;
            }

            if ($duplicates[$email] > 1) {
                $this->setMessage($this->_messages[self::DUPLICATE]);

                return false;
            }
        }

        return true;
    }

}

