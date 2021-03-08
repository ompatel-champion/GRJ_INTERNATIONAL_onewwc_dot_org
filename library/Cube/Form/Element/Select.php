<?php

/**
 *
 * Cube Framework
 *
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2018 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 *
 * @version     2.0 [rev.2.0.02]
 */

/**
 * select form element generator class
 * apply translator to select options
 */

namespace Cube\Form\Element;

use Cube\Form\Element;

class Select extends Element
{

    /**
     *
     * type of element - override the variable from the parent class
     *
     * @var string
     */
    protected $_element = 'select';

    /**
     *
     * hide default value
     *
     * @var bool
     */
    protected $_hideDefault = false;

    /**
     *
     * force default value
     *
     * @var bool
     */
    protected $_forceDefault = false;

    /**
     *
     * creates a simple select field with brackets only and no hidden single field
     *
     * @var bool
     */
    protected $_simpleMultiple = false;

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
     * get hide default option
     *
     * @return string
     */
    public function isHideDefault()
    {
        return $this->_hideDefault;
    }

    /**
     *
     * set hide default option
     *
     * @param boolean $default
     *
     * @return $this
     */
    public function setHideDefault($default = true)
    {
        $this->_hideDefault = (bool)$default;

        return $this;
    }

    /**
     *
     * get force default option
     *
     * @return bool
     */
    public function isForceDefault()
    {
        return $this->_forceDefault;
    }

    /**
     *
     * set force default option
     *
     * @param bool $forceDefault
     *
     * @return $this
     */
    public function setForceDefault($forceDefault)
    {
        $this->_forceDefault = $forceDefault;

        return $this;
    }

    /**
     *
     * get simple multiple flag
     *
     * @return bool
     */
    public function isSimpleMultiple()
    {
        return $this->_simpleMultiple;
    }

    /**
     *
     * set simple multiple flag
     *
     * @param bool $simpleMultiple
     *
     * @return $this
     */
    public function setSimpleMultiple($simpleMultiple)
    {
        $this->_simpleMultiple = $simpleMultiple;

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

        $multipleAttribute = $this->getAttribute('multiple');

        $this->removeAttribute('multiple');

        if ($this->isSimpleMultiple()) {
            $this->setBrackets('[]');
        }
        else if ($this->getMultiple() === true || !empty($multipleAttribute)) {
            $this->addAttribute('multiple', 'multiple', false);

            $brackets = '';
            if (isset($this->_customData['doubleBrackets'])) {
                if ($this->_customData['doubleBrackets'] === true) {
                    $brackets = $this->getBrackets();
                    $this->setBrackets($brackets . '[]');
                }
            }

            $output .= '<input type="hidden" name="' . $this->_name . $brackets . '" value=""'
                . $this->_endTag;
        }
        else {
            $this->setBrackets('');
        }

        $output .=
            $this->getPrefix()
            . ' <select name="' . $this->_name . $this->getBrackets() . '" '
            . $this->renderAttributes() . '>';

        if ($this->isForceDefault() || ($this->getRequired() && !$this->isHideDefault())) {
            $output .= '<option value="" selected>' . $translate->_('-- select --') . '</option>';
        }

        $output .= '<optgroup disabled hidden></optgroup>';

        foreach ((array)$this->_multiOptions as $key => $option) {
            $selected = (in_array($key, (array)$value)) ? 'selected' : '';

            if (is_array($option)) {
                $title = isset($option[0]) ? $option[0] : null;

                if (isset($option[1])) {
                    $this->addMultiOptionAttributes($key, $option[1]);
                }
            }
            else {
                $title = $option;
            }

            $attributes = array(
                'value="' . $key . '"',
                $this->renderOptionAttributes($key),
                $selected
            );

            $output .= '<option ' . implode(' ', array_filter($attributes)) . '>'
                . $translate->_($title) . '</option>';
        }

        $output .= '</select> '
            . $this->getSuffix();

        return $output;
    }

}

