<?php

/**
 *
 * PHP Pro Bid
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.0
 */
/**
 * CUSTOM WORK -> STRIPE GATEWAY INTEGRATION
 */

namespace Ppb\Form\Element;

use Cube\Form\Element;

class CardDate extends Element
{

    const DATE_MONTH = 'month';
    const DATE_YEAR = 'year';

    /**
     *
     * type of element - override the variable from the parent class
     *
     * @var string
     */
    protected $_element = 'cardDate';

    /**
     *
     * class constructor
     *
     * @param string $name
     */
    public function __construct($name)
    {
        parent::__construct('text', $name);
        $this->setMultiple(true);
    }


    /**
     *
     * return the value(s) of the element, either the element's data or default value(s)
     *
     * @param string $key
     *
     * @return mixed
     */
    public function getValue($key = null)
    {
        $value = parent::getValue();

        if ($key !== null) {
            if (array_key_exists($key, (array)$value)) {
                return $value[$key];
            }
            else {
                return null;
            }
        }

        return $value;
    }

    /**
     *
     * render composite element
     *
     * @return string
     */
    public function render()
    {
        return $this->getPrefix() . ' '
        . '<input type="' . $this->_type . '" '
//        . 'name="' . $this->_name . '[' . self::DATE_MONTH . ']' . '" '
        . ' id="' . $this->_name . '_' . self::DATE_MONTH . '"'
        . $this->renderAttributes()
        . $this->_endTag . ' '
        . $this->getSuffix()
        . ' / '
        . $this->getPrefix() . ' '
        . '<input type="' . $this->_type . '" '
//        . 'name="' . $this->_name . '[' . self::DATE_YEAR . ']' . '" '
        . ' id="' . $this->_name . '_' . self::DATE_YEAR . '"'
        . $this->renderAttributes()
        . $this->_endTag . ' '
        . $this->getSuffix();
    }

}

