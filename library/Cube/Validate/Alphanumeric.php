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
 * alphanumeric only validator class
 * space character is not allowed since version 1.2
 */

namespace Cube\Validate;

class Alphanumeric extends AbstractValidate
{

    protected $_message = "'%s' must contain an alphanumeric value.";

    /**
     *
     * checks if the variable contains an alphanumeric value
     *
     * @return bool          return true if the validation is successful
     */
    public function isValid()
    {
        $value = $this->getValue();

        if (empty($value)) {
            return true;
        }

        if (!preg_match('#^[0-9a-zA-Z\_\-]+$#', $value)) {
            return false;
        }

        return true;
    }

}

