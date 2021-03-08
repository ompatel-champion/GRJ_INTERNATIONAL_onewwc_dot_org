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
 * MOD:- ESCROW PAYMENTS
 */

namespace Ppb\Model\Elements\AdminSettings;

use Ppb\Model\Elements\AbstractElements;

class EscrowPayments extends AbstractElements
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
            ## -- START :: ADD -- [ MOD:- ESCROW PAYMENTS ]
            /**
             * ++++++++++++++
             * MOD:- ESCROW PAYMENTS
             * ++++++++++++++
             */
            array(
                'form_id'      => 'escrow_payments',
                'id'           => 'enable_escrow_payments',
                'element'      => 'checkbox',
                'label'        => $this->_('Enable Escrow Payments'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check the above checkbox to enable escrow payments.<br>'
                    . 'Important: When disabling the module after it was active, the setting will apply for newly created listings only.'),
            ),
            ## -- END :: ADD -- [ MOD:- ESCROW PAYMENTS ]
        );
    }
}

