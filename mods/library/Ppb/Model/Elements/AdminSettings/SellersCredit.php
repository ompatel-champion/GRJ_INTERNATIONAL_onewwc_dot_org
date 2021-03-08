<?php

/**
 *
 * PHP Pro Bid
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2018 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     8.0 [rev.8.0.07]
 */

/**
 * sample class that will generate extra elements for the admin elements model
 * we can have a multiple number of such classes, they just need to have a different name
 * any elements in this class will override original elements
 */
/**
 * MOD:- SELLERS CREDIT
 */

namespace Ppb\Model\Elements\AdminSettings;

use Ppb\Model\Elements\AbstractElements;

class SellersCredit extends AbstractElements
{
    /**
     *
     * related class
     *
     * @var bool
     */
    protected $_relatedClass = true;

    /**
     *
     * get model elements
     *
     * @return array
     */
    public function getElements()
    {
        $settings = $this->getSettings();

        return array(
            ## -- START :: ADD -- [ MOD:- SELLERS CREDIT ]
            /**
             * ++++++++++++++
             * SELLERS CREDIT [MOD]
             * ++++++++++++++
             */
            array(
                'form_id'      => 'sellers_credit',
                'id'           => 'enable_sellers_credit',
                'element'      => 'checkbox',
                'label'        => $this->_('Enable Sellers Credit'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check the above checkbox to enable the sellers credit module. <br>'
                    . 'With this module, buyers will be able to pay sellers for purchased items directly from their credit balance on the website.<br>'
                    . '<strong>Important</strong>: This module will only work in account mode.'),
            ),
            array(
                'form_id'      => 'sellers_credit',
                'id'           => 'mandatory_credit_payments',
                'element'      => 'checkbox',
                'label'        => $this->_('Mandatory Credit'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check the above checkbox in order for buyers to be required to have enough credit in their account balance '
                    . 'in order to be allowed to purchase items.'),
            ),
            array(
                'form_id'      => 'sellers_credit',
                'id'           => 'automatic_credit_payments',
                'element'      => 'checkbox',
                'label'        => $this->_('Automatic Credit Payments'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check the above checkbox to enable the automatic payments for items having the sellers credit payment method enabled.'),
            ),
            array(
                'form_id'     => 'sellers_credit',
                'id'          => 'sellers_credit_minimum_withdrawal_limit',
                'element'     => 'text',
                'label'       => $this->_('Minimum Withdrawal Limit'),
                'prefix'      => $settings['currency'],
                'description' => $this->_('Enter a minimum amount that sellers are required to have in their balance before being able to request a withdrawal.'),
                'attributes'  => array(
                    'class' => 'form-control input-mini',
                ),
                'validators'  => array(
                    'Numeric',
                ),
            ),
            ## -- END :: ADD -- [ MOD:- SELLERS CREDIT ]
        );
    }
}

