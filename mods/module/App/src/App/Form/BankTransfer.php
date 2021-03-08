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
 * bank transfer form
 */
/**
 * MOD:- BANK TRANSFER
 *
 * @version 1.1
 * MOD:- ESCROW & BANK TRANSFERS
 */

namespace App\Form;

use Ppb\Form\AbstractBaseForm,
    Ppb\Service,
    Cube\Validate;

class BankTransfer extends AbstractBaseForm
{

    const BTN_SUBMIT = 'bank_transfer';

    /**
     *
     * submit buttons values
     *
     * @var array
     */
    protected $_buttons = array(
        self::BTN_SUBMIT => 'Proceed',
    );

    /**
     *
     * class constructor
     *
     * @param \Ppb\Db\Table\Row\Transaction $transaction
     * @param string                        $action the form's action
     */
    public function __construct($transaction, $action = null)
    {
        parent::__construct($action);

        $translate = $this->getTranslate();

        $bankTransfersService = new Service\BankTransfers();
        $bankAccountsService = new Service\BankAccounts();

        $this->setMethod(self::METHOD_POST);


        $transactionElement = $this->createElement('hidden', 'transaction_id');
        $transactionElement->setValue($transaction->getData('id'));
        $this->addElement($transactionElement);


        $sellerId = null;
        if ($transaction->getData('sale_id') && !$transaction->getData('escrow_buyer_admin')) {
            $sellerId = $transaction->findParentRow('\Ppb\Db\Table\Sales')
                ->findParentRow('\Ppb\Db\Table\Users', 'Seller')
                ->getData('id');
        }

        $bankAccountsMultiOptions = $bankAccountsService->getMultiOptions($sellerId);

        $bankAccount = $this->createElement('\Ppb\Form\Element\BankAccount', 'bank_account_id');
        $bankAccount->setLabel('Select Bank Account')
            ->setDescription('Select the bank account you will be depositing the payment into.')
            ->setRequired()
            ->setMultiOptions($bankAccountsMultiOptions);
        $this->addElement($bankAccount);


        $accountHolderName = $this->createElement('text', 'account_holder_name');
        $accountHolderName->setLabel('Account Holder Name')
            ->setAttributes(array(
                'class' => 'form-control input-large'
            ))
            ->setRequired()
            ->addValidator(
                new Validate\NoHtml())
            ->addValidator(
                new Validate\StringLength(array(null, 255)));
        $this->addElement($accountHolderName);


        $transferType = $this->createElement('radio', 'transfer_type');
        $transferType->setLabel('Transfer Method')
            ->setRequired();

        $transferTypes = $bankTransfersService->getTransferTypes();
        foreach ($transferTypes as $key => $value) {
            $transferType->addMultiOption($key, $translate->_($value));
        }
        $this->addElement($transferType);


        $referenceNumber = $this->createElement('text', 'reference_number');
        $referenceNumber->setLabel('Reference Number')
            ->setAttributes(array(
                'class' => 'form-control input-large'
            ))
            ->setRequired()
            ->addValidator(
                new Validate\NoHtml())
            ->addValidator(
                new Validate\StringLength(array(null, 255)));
        $this->addElement($referenceNumber);


        $transferDate = $this->createElement('\Ppb\Form\Element\DateTime', 'transfer_date');
        $transferDate->setLabel('Transfer Date')
            ->setRequired()
            ->addValidator(
                new Validate\LessThan(array(date('Y-m-d H:i:s', time()), false)))
            ->setAttributes(array(
                'class' => 'form-control input-medium',

            ))
            ->setCustomData(array(
                'formData' => array(
                    'format'     => '"YYYY-MM-DD HH:mm"',
                    'maxDate'    => 'new Date()',
                    'useCurrent' => 'false',
                    'stepping'   => '5',
                    'showClear'  => 'true',
                ),
            ));
        $this->addElement($transferDate);


        $content = $this->createElement('textarea', 'additional_information');
        $content->setLabel('Addl. Information')
            ->setAttributes(array(
                'rows'  => 8,
                'class' => 'form-control',
            ))
            ->addValidator(
                new Validate\NoHtml());


        $this->addElement($content);

        $this->addSubmitElement($this->_buttons[self::BTN_SUBMIT], self::BTN_SUBMIT);

        $this->setPartial('forms/generic-horizontal.phtml');
    }

}