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
 * MOD:- ESCROW PAYMENTS
 */

/**
 * class that will generate extra elements for the admin elements model
 * we can have a multiple number of such classes, they just need to have a different name
 * any elements in this class will override original elements
 */

namespace Ppb\Model\Elements\Listing;

use Ppb\Model\Elements\AbstractElements,
    Cube\Controller\Front,
    Ppb\Db\Table\Row\User as UserModel,
    Ppb\Service;

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
     * listing owner
     *
     * @var \Ppb\Db\Table\Row\User
     */
    protected $_user;

    /**
     *
     * get current user
     *
     * @return \Ppb\Db\Table\Row\User
     */
    public function getUser()
    {
        if (!$this->_user instanceof UserModel) {
            $this->setUser(
                Front::getInstance()->getBootstrap()->getResource('user'));
        }

        return $this->_user;
    }

    /**
     *
     * set current user
     *
     * @param \Ppb\Db\Table\Row\User $user
     *
     * @return $this
     */
    public function setUser(UserModel $user)
    {
        $this->_user = $user;

        return $this;
    }

    /**
     *
     * get model elements
     *
     * @return array
     */
    public function getElements()
    {
        $settings = $this->getSettings();

        $paymentGatewaysService = new Service\Table\PaymentGateways();
        $paymentMethodsService = new Service\Table\OfflinePaymentMethods();

        $paymentGateways = $paymentGatewaysService->getMultiOptions($this->_user['id']);
        $paymentMethods = $paymentMethodsService->getMultiOptions();

        return array(
            array(
                'form_id'      => array('auction', 'product', 'product_edit', 'prefilled', 'bulk'),
                'subform'      => 'shipping',
                'before'       => array('id', 'direct_payment'),
                'subtitle'     => $this->_('Escrow Payment'),
                'id'           => 'enable_escrow',
                'element'      => ($settings['enable_escrow_payments']) ? 'checkbox' : false,
                'label'        => $this->_('Enable Escrow'),
                'multiOptions' => array(
                    1 => null,
                ),
                'attributes'   => array(
                    'class' => 'field-changeable',
                ),
                'description'  => $this->_('Check the above checkbox if you want to enable escrow payment for this listing.'),
                'bodyCode'     => "
                    <script type=\"text/javascript\">
                        function checkEscrowPaymentFormFields()
                        {                                                       
                            /* ## -- START :: ADD -- [ MOD:- ESCROW PAYMENTS ] */
                            if ($('input:checkbox[name=\"enable_escrow\"]').is(':checked')) {
                                $('input:checkbox[name^=\"direct_payment\"]').prop('checked', false).closest('.form-group').hide();
                                $('input:checkbox[name^=\"offline_payment\"]').prop('checked', false).closest('.form-group').hide();
                            }
                            else {
                                $('input:checkbox[name^=\"direct_payment\"]').closest('.form-group').show();
                                $('input:checkbox[name^=\"offline_payment\"]').closest('.form-group').show();
                            }
                            /* ## -- END :: ADD -- [ MOD:- ESCROW PAYMENTS ] */
                        }
    
                        $(document).ready(function() {
                            checkEscrowPaymentFormFields();
                        });
                        
                        $(document).on('change', '.field-changeable', function() {
                            checkEscrowPaymentFormFields();
                        });
                    </script>"
            ),
            array(
                'form_id'    => array('auction', 'product', 'product_edit'),
                'subform'    => 'shipping',
                'id'         => 'check_payment_methods',
                'element'    => 'hidden',
                ## -- ONE LINE :: CHANGE -- [ MOD:- ESCROW PAYMENTS ]
                'validators' => (!$this->getData('enable_escrow') && (count($paymentGateways) > 0 || count($paymentMethods) > 0)) ?
                    array('\\Ppb\\Validate\\PaymentMethods') : null,
            )
        );
    }
}

