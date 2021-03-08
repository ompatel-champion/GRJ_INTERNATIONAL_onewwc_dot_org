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
 * hidden form element generator class
 */

namespace Cube\Form\Element;

use Cube\Form\Element;

class Hidden extends Element
{
    /**
     * new line character
     */

    const NL = "\n";

    /**
     *
     * type of element - override the variable from the parent class
     *
     * @var string
     */
    protected $_element = 'hidden';

    /**
     *
     * whether to add keys to multiple elements or not
     *
     * @var bool
     */
    protected $_forceCountMultiple = false;

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
     * set force count multiple flag
     *
     * @param boolean $forceCountMultiple
     *
     * @return $this
     */
    public function setForceCountMultiple($forceCountMultiple)
    {
        $this->_forceCountMultiple = (bool)$forceCountMultiple;

        return $this;
    }

    /**
     *
     * get force count multiple flag
     *
     * @return boolean
     */
    public function getForceCountMultiple()
    {
        return $this->_forceCountMultiple;
    }

    /**
     *
     * return the value(s) of the element, either the element's data or default value(s)
     * if force value is true, return the default value
     *
     * @return mixed
     */
    public function getValue()
    {
        if ($this->isForceValue() === true) {
            return $this->_value;
        }

        return parent::getValue();
    }

    /**
     *
     * renders the html form element
     * the method is run by all subclasses who don't have it overridden
     * if a post value is an array, we will render a multiple element
     * for serialized data, we change the brackets to ' rather than "
     *
     * @return string   the html code of the element
     */
    public function render()
    {
        $render = array();

        $value = $this->getValue();

        if (is_array($value)) {
            $this->setMultiple(true);
        }
        else {
            $value = array($value);
        }

        foreach ($value as $key => $val) {
            $forceCountMultiple = $this->getForceCountMultiple();
            $multiple = ($this->getMultiple() === true) ?
                ((is_string($key) || $forceCountMultiple) ? '[' . $key . ']' : $this->_brackets) : '';

            if (is_array($val)) {
                $element = new Hidden($this->_name . $multiple);
                $element->setForceCountMultiple($forceCountMultiple)
                    ->setValue($val);

                $render[] = $element->render();
            }
            else {
                $attributes = array(
                    'type="' . $this->_type . '"',
                    'name="' . $this->_name . $multiple . '"',
                    'value="' . $val . '"',
                    $this->renderAttributes()
                );

                $render[] = '<input ' . implode(' ', array_filter($attributes))
                    . $this->_endTag;
            }
        }

        return implode(self::NL, $render);
    }

}

