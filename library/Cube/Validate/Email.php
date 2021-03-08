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
 * email address validator class
 */

namespace Cube\Validate;

class Email extends AbstractValidate
{

    protected $_message = "'%s' does not contain a valid email address.";

    /**
     *
     * checks if the variable contains a valid email address
     *
     * @return bool          return true if the validation is successful
     */
    public function isValid()
    {
        $value = $this->getValue();

        if (empty($value)) {
            return true;
        }

        if (!preg_match('#^\S+@\S+\.\S+$#', $value)) {
            return false;
        }

        return true;
    }

}

