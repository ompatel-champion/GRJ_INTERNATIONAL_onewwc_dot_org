<?php

/**
 *
 * PHP Pro Bid
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.2
 */
/**
 * MOD:- BANK TRANSFER
 */

namespace Ppb\Model\Elements;

use Ppb\Service;

class BankAccount extends AbstractElements
{

    /**
     *
     * form id
     *
     * @var array
     */
    protected $_formId = array();

    /**
     *
     * class constructor
     */
    public function __construct($formId = null)
    {
        parent::__construct();

        $this->_formId = (array)$formId;
    }

    /**
     *
     * get form elements
     *
     * @return array
     */
    public function getElements()
    {
        $banksService = new Service\Table\Banks();

        $array = array(
            array(
                'form_id' => 'global',
                'id'      => 'id',
                'element' => 'hidden',
            ),

            /**
             * --------------
             * BANK ACCOUNT
             * --------------
             */
            array(
                'form_id'      => 'bank_account',
                'id'           => 'bank_name',
                'element'      => 'select',
                'label'        => $this->_('Bank Name'),
                'required'     => true,
                'attributes'   => array(
                    'class' => 'form-control input-medium',
                ),
                'multiOptions' => $banksService->getMultiOptions(),
            ),
            array(
                'form_id'    => 'bank_account',
                'id'         => 'bank_address',
                'element'    => 'text',
                'label'      => $this->_('Bank Address'),
//                'required'   => true,
                'attributes' => array(
                    'class' => 'form-control input-medium',
                ),
            ),
            array(
                'form_id'    => 'bank_account',
                'id'         => 'bank_account_holder_name',
                'element'    => 'text',
                'label'      => $this->_('Account Holder Name'),
                'required'   => true,
                'attributes' => array(
                    'class' => 'form-control input-medium',
                ),
            ),
            array(
                'form_id'    => 'bank_account',
                'id'         => 'bank_account_number',
                'element'    => 'text',
                'label'      => $this->_('Account Number'),
                'required'   => true,
                'attributes' => array(
                    'class' => 'form-control input-medium',
                ),
            ),
            array(
                'form_id'    => 'bank_account',
                'id'         => 'bank_account_routing_number',
                'element'    => 'text',
                'label'      => $this->_('Bank Routing Number'),
                'attributes' => array(
                    'class' => 'form-control input-medium',
                ),
            ),
        );

        return $array;
    }

}

