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
 * digits only validator class
 */

namespace Cube\Validate;

class Digits extends AbstractValidate
{
    
    protected $_message = "'%s' can only contain digits.";

    /**
     * 
     * checks if the variable contains digits only
     * 
     * @return bool          return true if the validation is successful
     */
    public function isValid()
    {
        $value = $this->getValue();

        if (empty($value)) {
            return true;
        }

        if (!preg_match('#^-?[0-9]+$#', $value)) {
            return false;
        }

        return true;
    }

}

