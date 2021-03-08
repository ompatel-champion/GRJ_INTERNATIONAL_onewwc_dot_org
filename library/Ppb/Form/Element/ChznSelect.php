<?php

/**
 *
 * PHP Pro Bid
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2018 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     8.0 [rev.8.0.02]
 */

/**
 * multi select form element
 *
 * creates a select element that uses the jquery chosen plugin
 *
 * DEPRECATED [@version 8.0]
 */

namespace Ppb\Form\Element;

use Cube\Form\Element\Select,
    Cube\Controller\Front;

class ChznSelect extends Select
{

    const ELEMENT_CLASS = 'chzn-select';
    const SELECT_MULTIPLE_SIZE = '5';

    /**
     *
     * type of element - override the variable from the parent class
     *
     * @var string
     */
    protected $_element = 'ChznSelect';

    /**
     *
     * class constructor
     *
     * @param string $name
     * @param bool   $initialize
     */
    public function __construct($name, $initialize = true)
    {
        parent::__construct($name);

        if ($initialize === true) {
            $this->initialize();
        }

        $this->addAttribute('class', self::ELEMENT_CLASS);
    }

    /**
     *
     * initialize dependant libraries
     *
     * @return $this
     */
    public function initialize()
    {
        $baseUrl = Front::getInstance()->getRequest()->getBaseUrl();

        $this->setHeaderCode('<link href="' . $baseUrl . '/js/chosen/component-chosen.css" media="screen" rel="stylesheet" type="text/css">')
            ->setBodyCode('<script type="text/javascript" src="' . $baseUrl . '/js/chosen/chosen.jquery.min.js"></script>')
            ->setBodyCode(
                "<script type=\"text/javascript\">" . "\n"
                . " $('." . self::ELEMENT_CLASS . "').chosen(); " . "\n"
                . "</script>");

        return $this;
    }

    public function render()
    {
        $output = null;
        $value = $this->getValue();

        if ($this->getMultiple() === true) {
            $this->_attributes['multiple'] = 'multiple';

            $brackets = '';
            // used for when having a table with chznselect fields
            if (isset($this->_customData['doubleBrackets'])) {
                if ($this->_customData['doubleBrackets'] == true) {
                    $brackets = $this->getBrackets();
                    $this->setBrackets($brackets . '[]');
                }
            }

            $output .= '<input type="hidden" name="' . $this->_name . $brackets . '" value=""'
                . $this->_endTag;
        }

        if (!isset($this->_attributes['size'])) {
            $this->_attributes['size'] = self::SELECT_MULTIPLE_SIZE;
        }

        $output .= '<select name="' . $this->_name . $this->getBrackets() . '" '
            . $this->renderAttributes() . '>';

        foreach ((array)$this->_multiOptions as $key => $option) {
            $selected = (in_array($key, (array)$value)) ? 'selected' : '';
            $output .= '<option value="' . $key . '" ' . $selected . '>' . $option . '</option>';
        }

        $output .= '</select>';

        return $output;
    }

}

