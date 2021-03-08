<?php

/**
 * 
 * Cube Framework 
 * 
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2020 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 * 
 * @version     3.0 [rev.3.0.01]
 */

/**
 * submit form element generator class
 */

namespace Cube\Form\Element;

use Cube\Form\Element;

class Submit extends Element
{

    /**
     *
     * type of element - override the variable from the parent class
     * 
     * @var string
     */
    protected $_element = 'submit';

    /**
     * 
     * class constructor
     * 
     * @param string $name
     */
    public function __construct($name)
    {
        parent::__construct($this->_element, $name);
    }

    /**
     *
     * get translated submit element value
     *
     * @return string
     */
    public function getValue()
    {
        return $this->getTranslate()->_(parent::getValue());
    }

}

