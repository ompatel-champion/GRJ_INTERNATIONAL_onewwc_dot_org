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
 * radio buttons form element generator class
 */

namespace Cube\Form\Element;

use Cube\Form\Element;

class Radio extends Element
{

    /**
     *
     * type of element - override the variable from the parent class
     *
     * @var string
     */
    protected $_element = 'radio';

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
     * render the form element
     *
     * @return string
     */
    public function render()
    {
        $output = null;
        $value = $this->getValue();

        $translate = $this->getTranslate();

        foreach ((array)$this->_multiOptions as $key => $option) {
            $checked = ($value == $key) ? 'checked="checked" ' : '';

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

            $this->addAttribute('class', 'form-check-input');

            $attributes = array(
                'type="' . $this->_element . '"',
                'name="' . $this->_name . '"',
                'value="' . $key . '"',
                $this->renderAttributes(),
                $this->renderOptionAttributes($key),
                $checked
            );

            $output .= '<div class="form-check">'
                . '<label class="form-check-label">'
                . ' <input ' . implode(' ', array_filter($attributes)) . '>' . $translate->_($title)
                . '</label>'
                . (($description !== null) ? '<small class="form-text text-dark mb-1">' . $translate->_($description) . '</small>' : '')
                . '</div>'
                . "\n";
        }

        return $output;
    }

}

