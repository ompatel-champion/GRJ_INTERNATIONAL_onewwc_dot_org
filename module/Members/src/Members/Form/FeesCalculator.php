<?php

/**
 *
 * PHP Pro Bid
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2019 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     8.2 [rev.8.2.01]
 */

/**
 * fees calculator form
 */

namespace Members\Form;

use Ppb\Form\AbstractBaseForm,
    Ppb\Model\Elements,
    Cube\Form\Element\Csrf;

class FeesCalculator extends AbstractBaseForm
{

    const BTN_SUBMIT = 'fees_calculator';

    /**
     *
     * submit buttons values
     *
     * @var array
     */
    protected $_buttons = array(
        self::BTN_SUBMIT => 'Calculate',
    );

    /**
     *
     * override include forms array
     *
     * @var array
     */
    protected $_includedForms = array('fees_calculator');

    /**
     *
     * don't add submit button automatically
     *
     * @var bool
     */
    protected $_addSubmitButton = false;

    /**
     *
     * class constructor
     *
     * @param string $action the form's action
     */
    public function __construct($action = null)
    {
        parent::__construct($action);

        $this->setMethod(self::METHOD_POST);

        $formId = 'fees_calculator';

        $model = new Elements\Listing($formId);

        $model->setFormId((array)$formId);

        $this->setIncludedForms(array(
            $model->getData('listing_type'), $formId));

        $this->addElements(
            $model->getElements());

        $this->setModel($model);

        $this->addSubmitElement($this->_buttons[self::BTN_SUBMIT], self::BTN_SUBMIT);

        $this->setPartial('forms/generic-horizontal.phtml');
    }

    /**
     *
     * method to create a form element from an array
     *
     * @param array $elements
     * @param bool  $allElements
     * @param bool  $clearElements
     *
     * @return $this
     */
    public function addElements(array $elements, $allElements = false, $clearElements = true)
    {
        if ($clearElements) {
            $this->clearElements();

            $this->addElement(new Csrf());
        }

        $includedForms = $this->getIncludedForms();

        foreach ($elements as $element) {
            $formId = $element['form_id'];

            $one = array_values(array_intersect((array)$formId, $includedForms));
            $two = array_values(array_intersect((array)$formId, array('global', 'fees_calculator')));

            if (
                (
                    is_array($formId) && (
                        ($one == $includedForms) ||
                        ($two == array('global', 'fees_calculator'))
                    )
                ) ||
                (is_string($formId) && $formId == 'fees_calculator') ||
                $allElements === true
            ) {
                $formElement = $this->createElementFromArray($element);

                if ($formElement !== null) {
                    $this->addElement($formElement);
                }
            }
        }

        $this->generateEditForm();

        return $this;
    }

    /**
     *
     * set form data
     *
     * @param array $data
     *
     * @return $this
     */
    public function setData(array $data = null)
    {
        if (!empty($data['listing_type'])) {
            $this->setIncludedForms(array(
                $data['listing_type'], 'fees_calculator'));
        }

        parent::setData($data);

        $this->addSubmitElement($this->_buttons[self::BTN_SUBMIT], self::BTN_SUBMIT);

        return $this;
    }

}