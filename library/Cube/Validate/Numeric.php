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
 * numeric only validator class
 */

namespace Cube\Validate;

class Numeric extends AbstractValidate
{

    protected $_message = "'%s' must contain a numeric value.";

    /**
     *
     * checks if the variable contains a numeric value
     *
     * @return bool          return true if the validation is successful
     */
    public function isValid()
    {
        $value = $this->getValue();

        if (empty($value)) {
            return true;
        }

        if (!preg_match('#^-?\d*\.?\d+$#', $value)) {
            return false;
        }

        return true;
    }

}

