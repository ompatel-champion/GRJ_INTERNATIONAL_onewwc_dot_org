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
 * phone number validator class
 */

namespace Cube\Validate;

class Phone extends AbstractValidate
{

    protected $_message = "'%s' does not contain a valid phone number.";

    /**
     *
     * checks if the variable contains a valid phone number
     *
     * @return bool          return true if the validation is successful
     */
    public function isValid()
    {
        $value = $this->getValue();

        if (empty($value)) {
            return true;
        }

        $value = str_ireplace(array('+', '-', ' ', '(', ')'), '', $value);
        if (is_numeric($value)) {
            return true;
        }

        return false;
    }

}

