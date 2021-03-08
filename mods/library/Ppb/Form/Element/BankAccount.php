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
 * address selector form element
 */
/**
 * MOD:- BANK TRANSFER
 */

namespace Ppb\Form\Element;

use Cube\Form\Element\Radio,
    Cube\Controller\Front;

class BankAccount extends Radio
{
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

        $view = Front::getInstance()->getBootstrap()->getResource('view');
        $view->setHelper('bankAccount', new \App\View\Helper\BankAccount());

        foreach ((array)$this->_multiOptions as $key => $bankAccount) {
            $checked = ($value == $key) ? ' checked="checked" ' : '';


            $output .= '<label class="radio bank-account-selection">'
                . '<input type="' . $this->_element . '" name="' . $this->_name . '" value="' . $key . '" '
                . $this->renderAttributes()
                . $checked
                . $this->_endTag

                . '<div class="left">' . $view->bankAccount($bankAccount)->display() . '</div>'
                . '</label>'
                . "\n";
        }


        return $output;
    }
}