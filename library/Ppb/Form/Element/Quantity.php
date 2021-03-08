<?php

/**
 *
 * PHP Pro Bid
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2020 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     8.2 [rev.8.2.01]
 */

/**
 * listing quantity form element with plus / minus buttons
 */

namespace Ppb\Form\Element;

use Cube\Form\Element;

class Quantity extends Element
{

    const ELEMENT_CLASS = 'quantity-plus-minus';

    /**
     *
     * type of element - override the variable from the parent class
     *
     * @var string
     */
    protected $_element = 'text';

    /**
     *
     * block level element
     *
     * @var bool
     */
    protected $_block = false;

    /**
     *
     * class constructor
     *
     * @param string $name
     */
    public function __construct($name)
    {
        parent::__construct($this->_element, $name);

        $this->setBodyCode(
            "<script type=\"text/javascript\">
                $(document).ready(function() {
                    function updateQuantityPlusMinus(element) {
                        element.find('.btn-plus').on('click', function() {
                        const qtyInput = $(this).parent().find('input');
                        let qtyVal = parseInt(qtyInput.val());
                            let newQtyVal = 1;
                            if (!isNaN(qtyVal)) {
                                newQtyVal = qtyVal + 1;
                            }
                            qtyInput.val(newQtyVal).trigger('change');
                            
                        });
            
                        element.find('.btn-minus').on('click', function() {
                        const qtyInput = $(this).parent().find('input');
                        let qtyVal = parseInt(qtyInput.val());
                            let newQtyVal = '';
                            if (!isNaN(qtyVal) && qtyVal > 1) {
                                newQtyVal = qtyVal - 1;
                            }
                            qtyInput.val(newQtyVal).trigger('change');
                        });    
                    } 
                        
                    updateQuantityPlusMinus($('." . self::ELEMENT_CLASS . "'));
                }); 
            </script>");
    }

    /**
     *
     * is block level element
     *
     * @return bool
     */
    public function isBlock()
    {
        return $this->_block;
    }

    /**
     *
     * set block level element
     *
     * @param bool $block
     *
     * @return $this
     */
    public function setBlock($block)
    {
        $this->_block = $block;

        return $this;
    }

    /**
     *
     * render element
     *
     * @return string
     */
    public function render()
    {
        $multiple = ($this->getMultiple() === true) ? $this->_brackets : '';

        $this->addAttribute('class', 'has-icon-left has-icon-right');

        $attributes = array(
            'type="' . $this->_type . '"',
            'name="' . $this->_name . $multiple . '"',
            'value="' . $this->getValue() . '"',
            $this->renderAttributes()
        );

        return $this->getPrefix() . ' '
            . '<div class="has-icons ' . self::ELEMENT_CLASS . ' ' . ($this->isBlock() ? 'has-icons-block' : '') . '">'
            . '<span data-feather="minus" class="btn-minus icon-left"></span>'
            . '<input ' . implode(' ', array_filter($attributes))
            . $this->_endTag . ' '
            . '<span data-feather="plus" class="btn-plus icon-right"></span>'
            . '</div>'
            . ' '
            . $this->getSuffix();
    }

}

