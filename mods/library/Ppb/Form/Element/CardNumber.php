<?php

/**
 * 
 * Cube Framework 
 * 
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2014 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 * 
 * @version     1.0
 */
/**
 * card number / cvc form element generator class
 * when rendering, this element will not generate the name and value tags
 */

namespace Ppb\Form\Element;

use Cube\Form\Element;

class CardNumber extends Element
{

    /**
     *
     * type of element - override the variable from the parent class
     * 
     * @var string
     */
    protected $_element = 'text';

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
     * renders the html form element
     *
     * @return string   the html code of the element
     */
    public function render()
    {
//        $value = (string)$this->getValue();

        return $this->getPrefix() . ' '
        . '<input type="' . $this->_type . '" '
        . $this->renderAttributes()
//        . 'name="' . $this->_name . $multiple . '" '
//        . 'value="' . $value . '" '
        . $this->_endTag . ' '
        . $this->getSuffix();
    }

}

