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
 * user account options form
 */
namespace Admin\Form;

use Ppb\Form\AbstractBaseForm,
    Cube\Validate,
    Ppb\Filter,
    Ppb\Db\Table\Row\User;

class UserOptions extends AbstractBaseForm
{

    const BTN_SUBMIT = 'submit';

    /**
     *
     * submit buttons values
     *
     * @var array
     */
    protected $_buttons = array(
        self::BTN_SUBMIT => 'Save',
    );

    /**
     *
     * class constructor
     *
     * @param \Ppb\Db\Table\Row\User $user   selected user
     * @param string                 $action the form's action
     */
    public function __construct(User $user, $action = null)
    {
        parent::__construct($action);

        $settings = $this->getSettings();

        $translate = $this->getTranslate();

        $this->setMethod(self::METHOD_POST);

        $id = $this->createElement('hidden', 'id')
            ->setBodyCode("<script type=\"text/javascript\">
                    function checkFormFields()
                    {
                        if ($('input:radio[name=\"account_mode\"]:checked').val() == 'account') {
                            $('[name=\"balance\"]').closest('.form-group').show();
                            $('[name=\"balance_adjustment_reason\"]').closest('.form-group').show();
                            $('[name=\"max_debit\"]').closest('.form-group').show();
                        }
                        else {
                            $('[name=\"balance\"]').closest('.form-group').hide();
                            $('[name=\"balance_adjustment_reason\"]').closest('.form-group').hide();
                            $('[name=\"max_debit\"]').closest('.form-group').hide();
                        }

                        if ($('input:checkbox[name=\"store_active\"]').is(':checked')) {
                            $('[name=\"assign_default_store_account\"]').closest('.form-group').show();
                        }
                        else {
                            $('[name=\"assign_default_store_account\"]').closest('.form-group').hide();
                        }
                    }

                    $(document).ready(function() {
                        checkFormFields();
                    });

                    $(document).on('change', '.field-changeable', function() {
                        checkFormFields();
                    });
                </script>");
        $this->addElement($id);

        $accountType = $this->createElement('radio', 'account_mode');
        $accountType->setLabel('Account Type')
            ->setAttributes(array(
                'class' => 'field-changeable',
            ))
            ->setMultiOptions(array(
                'live'    => $translate->_('Live'),
                'account' => $translate->_('Account Mode'),
            ))
            ->setDescription('Select account type.');
        $this->addElement($accountType);

        $balance = $this->createElement('\\Ppb\\Form\\Element\\LocalizedNumeric', 'balance')
            ->setLabel('Account Balance')
            ->setPrefix($settings['currency'])
            ->setSuffix('[ Positive value: Debit ] [ Negative value: Credit ]')
            ->setAttributes(array(
                'class' => 'form-control input-small'
            ))
            ->setDescription('Edit the user\'s account balance.')
            ->addFilter(new Filter\LocalizedNumeric())
            ->addValidator(new Validate\Numeric());
        $this->addElement($balance);

        $balanceAdjustmentReason = $this->createElement('text', 'balance_adjustment_reason')
            ->setLabel('Balance Adjustment Reason')
            ->setAttributes(array(
                'class' => 'form-control input-large',
            ))
            ->setDescription('(Optional) When modifying the account balance, enter a reason in the field above.');
        $this->addElement($balanceAdjustmentReason);


        $maxDebit = $this->createElement('\\Ppb\\Form\\Element\\LocalizedNumeric', 'max_debit')
            ->setLabel('Max. Debit')
            ->setPrefix($settings['currency'])
            ->setAttributes(array(
                'class' => 'form-control input-mini'
            ))
            ->setDescription('Enter the maximum debit balance allowed.')
            ->addFilter(new Filter\LocalizedNumeric())
            ->addValidator(
                new Validate\Numeric())
            ->addValidator(
                new Validate\GreaterThan(array(0, true)));
        $this->addElement($maxDebit);

        $verifiedUser = $this->createElement('checkbox', 'user_verified');
        $verifiedUser->setLabel('Verified User')
            ->setMultiOptions(
                array(1 => null))
            ->setDescription('Check above to set account status to Verified.');
        $this->addElement($verifiedUser);

        if ($settings['private_site']) {
            $sellingCapabilities = $this->createElement('checkbox', 'is_seller');
            $sellingCapabilities->setLabel('Can List')
                ->setAttributes(array(
                    'class' => 'field-changeable',
                ))
                ->setMultiOptions(
                    array(1 => null))
                ->setDescription('Check above to allow the user to create listings.');
            $this->addElement($sellingCapabilities);
        }

        if ($settings['preferred_sellers']) {
            $preferredSeller = $this->createElement('checkbox', 'preferred_seller');
            $preferredSeller->setLabel('Preferred Seller')
                ->setMultiOptions(
                    array(1 => null))
                ->setDescription('Check above to set the account to Preferred Seller.');
            $this->addElement($preferredSeller);
        }

        if ($settings['enable_stores']) {
            $preferredSeller = $this->createElement('checkbox', 'store_active');
            $preferredSeller->setLabel('Enable Store')
                ->setAttributes(array(
                    'class' => 'field-changeable',
                ))
                ->setMultiOptions(
                    array(1 => null))
                ->setDescription('Check above to activate the user\'s store.');
            $this->addElement($preferredSeller);

            $defaultStoreAccount = $this->createElement('checkbox', 'assign_default_store_account');
            $defaultStoreAccount->setLabel('Assign Default Store Account')
                ->setMultiOptions(
                    array(1 => null))
                ->setDescription('Check above to set the store subscription to "Default" (allows unlimited listings and doesn\'t expire).');
            $this->addElement($defaultStoreAccount);
        }


        $this->addSubmitElement($this->_buttons[self::BTN_SUBMIT], self::BTN_SUBMIT);

        $this->setPartial('forms/popup-form.phtml');
    }

}