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
 * installation form
 */

namespace Install\Form;

use Install\Model\Elements,
    Ppb\Form\AbstractBaseForm,
    Ppb\Authentication\Adapter,
    Cube\Validate;

class Install extends AbstractBaseForm
{

    public function __construct($formId = null, $action = null)
    {
        parent::__construct($action);

        $this->setTitle('Installation')
            ->setMethod(self::METHOD_POST);

        $includedForms = $this->getIncludedForms();

        $this->setIncludedForms(
            array_merge($includedForms, (array)$formId));

        $this->setModel(
            new Elements\Install($formId));

        $this->addElements(
            $this->getModel()->getElements());

        if (count($this->getElements()) > 1) {
            $this->addSubmitElement();
            $this->getElement('submit')
                ->addAttribute('class', 'btn-loading-modal');
        }

        $this->setPartial('forms/generic-horizontal.phtml');
    }

    /**
     *
     * override setData() method
     *
     * @param array $data
     *
     * @return $this
     */
    public function setData(array $data = null)
    {
        parent::setData($data);

        if ($this->hasElement('submit')) {
            $this->getElement('submit')
                ->addAttribute('class', 'btn-loading-modal');
        }

        $translate = $this->getTranslate();

        if ($this->hasElement('admin_password')) {
            $passwordConfirmValidator = new Validate\Identical();
            $passwordConfirmValidator->setStrict()
                ->setVariableName($translate->_('Confirm Password'));

            if (isset($data['admin_password_confirm'])) {
                $passwordConfirmValidator->setVariableValue($data['admin_password_confirm']);
            }

            $this->getElement('admin_password')
                ->addValidator($passwordConfirmValidator);
        }

        return $this;
    }

    public function isValid()
    {
        $valid = parent::isValid();

        if ($this->hasElement('licensing_username') && $this->hasElement('licensing_password')) {
            // first we check for valid admin login details
            $adapter = new Adapter(array(
                'username' => $this->getData('licensing_username'),
                'password' => $this->getData('licensing_password'),
            ));

            $adapter->setCheckBlockedUser(false);

            $authenticationResult = $adapter->authenticate();
            $identity = $authenticationResult->getIdentity();

            if (!$authenticationResult->isValid() || $identity['role'] != 'Admin') {
                $this->setMessage('The authentication has failed.');
                $valid = false;
            }
        }

        return $valid;
    }
}

