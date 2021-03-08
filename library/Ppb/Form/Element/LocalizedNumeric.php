<?php

/**
 *
 * PHP Pro Bid
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2018 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     8.0 [rev.8.0.01]
 */

/**
 * localized number input element
 * the format and decimals are set automatically by the locale format instance
 * - highly recommended to use the localized numeric filter together with this element
 * OR
 * - when saving in the database, the value from the field needs to be
 * formatted with the Cube\Locale\Format::localizedToNumeric() method
 */

namespace Ppb\Form\Element;

use Cube\Form\Element,
    Cube\Locale\Format as LocaleFormat;

class LocalizedNumeric extends Element
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
        $value = $this->getValue();

        $localizedValue = LocaleFormat::getInstance()->numericToLocalized($value, true);

        $multiple = ($this->getMultiple() === true) ? $this->_brackets : '';

        $attributes = array(
            'type="' . $this->_type . '"',
            'name="' . $this->_name . $multiple . '"',
            'value="' . $localizedValue . '"',
            $this->renderAttributes()
        );

        return $this->getPrefix() . ' '
            . '<input ' .  implode(' ', array_filter($attributes))
            . $this->_endTag . ' '
            . $this->getSuffix();
    }
}

