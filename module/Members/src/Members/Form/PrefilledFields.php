<?php

/**
 *
 * PHP Pro Bid
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2017 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.9 [rev.7.9.01]
 */
/**
 * selling prefilled fields form
 */

namespace Members\Form;

use Ppb\Form\AbstractBaseForm,
    Ppb\Model\Elements;

class PrefilledFields extends AbstractBaseForm
{

    const BTN_SUBMIT = 'prefilled_fields';

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
     * override include forms array
     *
     * @var array
     */
    protected $_includedForms = array('prefilled');

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

        $this->setModel(
            new Elements\Listing('prefilled'));

        $this->addElements(
            $this->getModel()->getElements());

        $this->addSubmitElement($this->_buttons[self::BTN_SUBMIT], self::BTN_SUBMIT);

        $this->setPartial('forms/generic-horizontal.phtml');
    }

    /**
     *
     * set form data
     *
     * @param array $data form data
     *
     * @return $this
     */
    public function setData(array $data = null)
    {
        parent::setData($data);

        $this->addSubmitElement($this->_buttons[self::BTN_SUBMIT], self::BTN_SUBMIT);

        return $this;
    }

}