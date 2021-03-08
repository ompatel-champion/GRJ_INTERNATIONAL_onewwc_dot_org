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
 * url validator class
 */

namespace Cube\Validate;

class Url extends AbstractValidate
{

    protected $_message = "'%s' does not contain a valid URL.";

    /**
     *
     * checks if the variable contains a valid url
     * the url needs to end with a forward slash to be considered valid
     * example:
     *  http://site.com/  - valid
     *  http://www.site.com/  - valid
     *  http://www.site.com   - invalid
     *
     * @return bool          return true if the validation is successful
     */
    public function isValid()
    {
        $value = $this->getValue();

        if (empty($value)) {
            return true;
        }

        if (!preg_match('#^\S+://\S+$#', $value)) {
            return false;
        }

        return true;
    }

}

