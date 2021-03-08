<?php

/**
 *
 * Cube Framework
 *
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2019 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 *
 * @version     2.2 [rev.2.2.01]
 */

/**
 * creates a checkbox element
 */

namespace Cube\Form\Element;

use Cube\Form\Element;

class Checkbox extends Element
{

    /**
     *
     * type of element - override the variable from the parent class
     *
     * @var string
     */
    protected $_element = 'checkbox';

    /**
     *
     * display selected options first
     *
     * @var bool
     */
    protected $_selectedOptionsFirst = false;

    /**
     *
     * product attribute flag
     *
     * @var bool
     */
    protected $_productAttribute = false;

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
     * is selected options first
     *
     * @return bool
     */
    public function isSelectedOptionsFirst()
    {
        return $this->_selectedOptionsFirst;
    }

    /**
     *
     * set selected options first
     *
     * @param bool $selectedOptionsFirst
     *
     * @return $this
     */
    public function setSelectedOptionsFirst($selectedOptionsFirst)
    {
        $this->_selectedOptionsFirst = $selectedOptionsFirst;

        return $this;
    }

    /**
     *
     * check product attribute flag
     *
     * @return bool
     */
    public function isProductAttribute()
    {
        return $this->_productAttribute;
    }

    /**
     *
     * set product attribute flag
     *
     * @param bool $productAttribute
     *
     * @return $this
     */
    public function setProductAttribute($productAttribute)
    {
        $this->_productAttribute = $productAttribute;

        return $this;
    }

    /**
     *
     * render the form element
     *
     * @return string
     */
    public function render()
    {
        $output = null;
        $value = $this->getValue();

        $translate = $this->getTranslate();

        $multiple = '';
        if (count((array)$this->_multiOptions) > 1 || $this->getMultiple() === true) {
            $multiple = $this->_brackets;
        }

        $output .= '<input type="hidden" name="' . $this->_name . $multiple . '" value=""'
            . $this->_endTag;

        if ($this->isSelectedOptionsFirst()) {
            $reverseMultiOptions = array_reverse((array)$this->_multiOptions, true);
            foreach ($reverseMultiOptions as $key => $option) {
                if (in_array($key, (array)$value)) {
                    $this->_multiOptions = array($key => $option) + $this->_multiOptions;
                }
            }
        }

        $this->addAttribute('class', 'form-check-input');

        if ($this->isProductAttribute()) {
            $this->addAttribute('class', 'product-attribute');
        }

        foreach ((array)$this->_multiOptions as $key => $option) {
            $checked = (in_array($key, (array)$value)) ? 'checked="checked"' : '';

            if (is_array($option)) {
                $title = isset($option[0]) ? $option[0] : null;
                $description = isset($option[1]) ? $option[1] : null;

                if (isset($option[2])) {
                    $this->addMultiOptionAttributes($key, $option[2]);
                }
            }
            else {
                $title = $option;
                $description = null;
            }

            $attributes = array(
                'type="' . $this->_element . '"',
                'name="' . $this->_name . $multiple . '"',
                'value="' . $key . '"',
                $this->renderAttributes(),
                $this->renderOptionAttributes($key),
                $checked,
            );

            $output .= '<div class="form-check">'
                . '<label class="form-check-label">'
                . ' <input ' . implode(' ', array_filter($attributes)) . '>'
                . $translate->_($title)
                . '&nbsp;'
                . '</label>'
                . (($description !== null) ? '<small class="form-text text-dark mb-1">' . $translate->_($description) . '</small>' : '')
                . '</div>'
                . "\n";
        }

        return $output;
    }

}

