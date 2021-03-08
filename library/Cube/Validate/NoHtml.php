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
 * no html validator class
 */

namespace Cube\Validate;

class NoHtml extends AbstractValidate
{

    protected $_message = "'%s' cannot contain any html code.";

    /**
     * 
     * checks if the variable contains only text (no html)
     *
     * @return bool          return true if the validation is successful
     */
    public function isValid()
    {
        $value = $this->getValue();

        if (empty($value)) {
            return true;
        }

        if ($value != strip_tags($value)) {
            return false;
        }

        return true;
    }

}

