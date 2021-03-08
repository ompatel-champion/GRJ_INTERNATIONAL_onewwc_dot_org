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
 * discount rule creation form
 */
/**
 * MOD:- DISCOUNT RULES
 *
 * @version 2.0
 */

namespace Members\Form;

use Ppb\Form\AbstractBaseForm,
    Ppb\Validate;

class DiscountRule extends AbstractBaseForm
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
     * @param string $action the form's action
     * @param int    $userId if user id = null the rule will apply to all users/listings
     */
    public function __construct($action = null, $userId = null)
    {
        parent::__construct($action);

        $settings = $this->getSettings();

        $this->setMethod(self::METHOD_POST);

        $id = $this->createElement('hidden', 'id');
        $this->addElement($id);

        $name = $this->createElement('text', 'name');
        $name->setLabel('Name')
            ->setDescription('Enter the name of the discount rule.')
            ->setAttributes(array(
                'class' => 'form-control input-large',
            ))
            ->setRequired()
            ->setValidators(array(
                'NoHtml',
                array('StringLength', array(null, 255)),
            ));
        $this->addElement($name);

        $conditions = $this->createElement('textarea', 'description');
        $conditions->setLabel('Description')
            ->setDescription('(Optional) Enter a description for the discount rule.')
            ->setAttributes(
                array(
                    'rows'  => 6,
                    'class' => 'form-control')
            );
        $this->addElement($conditions);


        $reductionAmount = $this->createElement('\\Ppb\\Form\\Element\\LocalizedNumeric', 'reduction_amount');
        $reductionAmount->setLabel('Reduction')
            ->setRequired()
            ->setValidators(array(
                'Numeric'
            ))
            ->setFilters(array(
                '\\Ppb\\Filter\\LocalizedNumeric',
            ))
            ->setAttributes(array(
                'class' => 'form-control input-small'
            ));
        $this->addElement($reductionAmount);

        $reductionType = $this->createElement('select', 'reduction_type');
        $reductionType->setDescription('Enter the reduction this discount rule will apply.')
            ->setMultiOptions(array(
                'percent' => '%',
                'flat'    => $settings['currency'],
            ))
            ->setAttributes(array(
                'class' => 'form-control input-small'
            ));
        $this->addElement($reductionType);

        $startDate = $this->createElement('\\Ppb\\Form\\Element\\DateTime', 'start_date');
        $startDate->setLabel('Start Date')
            ->setDescription('(Optional) Enter a start date for this discount rule, or leave the field empty if wishing for the discount rule to start right away.')
            ->setAttributes(array(
                'class' => 'form-control input-medium'
            ))
            ->setCustomData(array(
                'formData' => array(
                    'format'    => '"YYYY-MM-DD HH:mm"',
                    'minDate'   => 'new Date()',
                    'stepping'  => '5',
                    'showClear' => 'true',
                ),
            ))
            ->setValidators(array(
                array('GreaterThan', array(date('Y-m-d H:i:s', time()), false)),
            ));
        $this->addElement($startDate);

        $expirationDate = $this->createElement('\\Ppb\\Form\\Element\\DateTime', 'expiration_date');
        $expirationDate->setLabel('Expiration Date')
            ->setDescription('(Optional) Enter an expiration date for this discount rule.')
            ->setAttributes(array(
                'class' => 'form-control input-medium'
            ))
            ->setCustomData(array(
                'formData' => array(
                    'format'    => '"YYYY-MM-DD HH:mm"',
                    'minDate'   => 'new Date()',
                    'stepping'  => '5',
                    'showClear' => 'true',
                ),
            ));
        $this->addElement($expirationDate);


        $conditions = $this->createElement('textarea', 'conditions');
        $conditions->setLabel('Conditions')
            ->setDescription('(Optional) Add conditions that need to be met in order for the rule to apply.<br>'
                . '<div><small>Fields: userId, listingId, purchasedListingId, price, name, description</small></div>'
                . '<div><small>Conditional: AND, OR, ()</small></div>'
                . '<div><small>Operators: =, !=, <, <=, >, >=, IN, NOT IN, LIKE</small></div>'
                . '<div><small>Important: Variables will need to be enclosed in single quotes</small></div>')
            ->addValidator(new Validate\DiscountRuleCondition())
            ->setAttributes(
                array(
                    'rows'  => 6,
                    'class' => 'form-control textarea-code')
            );
        $this->addElement($conditions);


        $priority = $this->createElement('text', 'priority');
        $priority->setLabel('Priority')
            ->setDescription('(Optional) Enter the priority of the rule. A higher number means a higher priority.')
            ->setValidators(array(
                'Digits'
            ))
            ->setAttributes(array(
                'class' => 'form-control input-small'
            ));
        $this->addElement($priority);


        $stopFurtherRules = $this->createElement('checkbox', 'stop_further_rules');
        $stopFurtherRules->setLabel('Stop Further Rules')
            ->setDescription('Check the checkbox above if you wish to stop further rules from applying if this rule is applied.')
            ->setMultiOptions(array(
                '1' => null,
            ));
        $this->addElement($stopFurtherRules);


        $active = $this->createElement('checkbox', 'active');
        $active->setLabel('Active')
            ->setDescription('Check the checkbox above to activate this rule.')
            ->setMultiOptions(array(
                '1' => null,
            ));
        $this->addElement($active);


        $this->addSubmitElement($this->_buttons[self::BTN_SUBMIT], self::BTN_SUBMIT);

        $this->setPartial('forms/generic-horizontal.phtml');
    }

    /**
     *
     * will generate the edit form
     *
     * @param int $id
     *
     * @return $this
     */
    public function generateEditForm($id = null)
    {
        parent::generateEditForm($id);

        $id = ($id !== null) ? $id : $this->_editId;

        if ($id !== null) {
            $translate = $this->getTranslate();

            $this->setTitle(
                sprintf($translate->_('Edit Discount Rule - ID: #%s'), $id));
        }

        return $this;
    }


    /**
     *
     * set the data for the form, and also convert any serialized values to array
     *
     * @param array $data form data
     *
     * @return $this
     */
    public function setData(array $data = null)
    {
        $expirationDate = $this->getElement('expiration_date');

        $expirationDate->setValidators(array(
            array('GreaterThan', array($data['start_date'], false)),
        ));

        parent::setData($data);

        return $this;
    }
}