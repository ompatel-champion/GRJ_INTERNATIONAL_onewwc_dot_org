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
 * bank account creation form
 */
/**
 * MOD:- BANK TRANSFER
 */

namespace Members\Form;

use Ppb\Form\AbstractBaseForm,
    Cube\Validate,
    Ppb\Model\Elements;

class BankAccount extends AbstractBaseForm
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
     * custom field elements model
     *
     * @var \Ppb\Model\Elements\CustomField
     */
    protected $_model;

    /**
     * class constructor
     *
     * @param array $formId
     * @param string $action
     * @param int|null $userId if $userId is null we have an admin bank account
     */
    public function __construct($formId = null, $action = null, $userId = null)
    {
        parent::__construct($action);

        $this->setTitle('Add Bank Account');

        if (is_array($formId)) {
            $this->_includedForms = array_merge($this->_includedForms, $formId);
        }
        else if ($formId !== null) {
            array_push($this->_includedForms, $formId);
        }

        $this->setMethod(self::METHOD_POST);

        $this->_model = new Elements\BankAccount($formId);

        $this->addElements(
            $this->_model->getElements());

        if (count($this->getElements()) > 0) {
            $this->addSubmitElement();
            $this->setPartial('forms/generic-horizontal.phtml');
        }
    }

    /**
     *
     * override setData() method
     *
     * @param array $data
     *
     * @return array
     */
    public function setData(array $data = null)
    {
        $this->_model->setData($data);
        $this->addElements(
            $this->_model->getElements());

        if (count($this->getElements()) > 0) {
            $this->addSubmitElement();
        }

        parent::setData($data);

        return $this;
    }

    /**
     *
     * will generate the edit bank account form
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
                sprintf($translate->_('Edit Bank Account - ID: #%s'), $id));
        }

        return $this;
    }

}