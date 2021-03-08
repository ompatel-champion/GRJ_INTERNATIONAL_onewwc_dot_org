<?php

/**
 *
 * Cube Framework
 *
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2018 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 *
 * @version     2.0 [rev.2.0.01]
 */

/**
 * not in array validator class
 */

namespace Cube\Validate;

class NotInArray extends AbstractValidate
{

    protected $_message = "'%s' was found in the haystack.";

    /**
     *
     * array to compare the needle against
     *
     * @var array
     */
    protected $_haystack = array();

    /**
     *
     * class constructor
     *
     * initialize the haystack
     *
     * @param array $haystack
     */
    public function __construct(array $haystack = null)
    {
        if ($haystack !== null) {
            $this->setHaystack($haystack);
        }
    }

    /**
     *
     * get haystack
     *
     * @return array
     */
    public function getHaystack()
    {
        return $this->_haystack;
    }

    /**
     *
     * set haystack
     *
     * @param array $haystack
     *
     * @return $this
     */
    public function setHaystack(array $haystack)
    {
        $this->_haystack = $haystack;

        return $this;
    }

    /**
     *
     * checks if the variable is contained in the haystack submitted
     *
     * @return bool          return true if value is not in array
     */
    public function isValid()
    {
        if (in_array($this->_value, $this->_haystack)) {
            return false;
        }

        return true;
    }

}

