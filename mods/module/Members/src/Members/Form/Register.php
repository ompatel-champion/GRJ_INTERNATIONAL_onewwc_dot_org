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
 * members module registration form
 */
/**
 * MOD:- GOOGLE OAUTH LOGIN
 * MOD:- FACEBOOK LOGIN
 * MOD:- PICKUP LOCATIONS
 */

namespace Members\Form;

use Ppb\Form\AbstractBaseForm,
    Cube\Validate,
    Ppb\Model\Elements;
## -- ADD 1L -- [ GOOGLE OAUTH LOGIN ]
use Cube\Controller\Front;
## -- ONE LINE :: ADD -- [ MOD:- PICKUP LOCATIONS ]
use Ppb\Validate\GeoLocation as GeoLocationValidator;

class Register extends AbstractBaseForm
{

    public function __construct($formId = null, $action = null, $user = null, $displaySubtitles = true)
    {
        parent::__construct($action);

        $this->setTitle('Create Account')
            ->setMethod(self::METHOD_POST)
            ->setDisplaySubtitles($displaySubtitles);

        $includedForms = $this->getIncludedForms();

        $this->setIncludedForms(
            array_merge($includedForms, (array)$formId));

        $model = new Elements\User($formId);

        if ($user !== null) {
            $model->setUser($user);
        }

        $this->addElements(
            $model->getElements());

        $this->setModel($model);

        /* submit button */
        $this->addSubmitElement('Submit');

        $this->setPartial('forms/generic-horizontal.phtml');

        ## -- ADD -- [ GOOGLE OAUTH LOGIN ]
        /** @var \Cube\Session $session */
        $session = Front::getInstance()->getBootstrap()->getResource('session');
        $googleOauthUserProfile = $session->get('googleOauthUserProfile');

        if (!empty($googleOauthUserProfile)) {
            $this->setData($googleOauthUserProfile);
            $session->unregister('googleOauthUserProfile');
        }
        ## -- ./ADD -- [ GOOGLE OAUTH LOGIN ]                     
        ## -- START :: ADD -- [ MOD:- FACEBOOK LOGIN ]
        /** @var \Cube\Session $session */
        $session = Front::getInstance()->getBootstrap()->getResource('session');
        $facebookUserProfile = $session->get('facebookUserProfile');

        if (!empty($facebookUserProfile)) {
            $this->setData($facebookUserProfile);
            $session->unregister('facebookUserProfile');
        }
        ## -- END :: ADD -- [ MOD:- FACEBOOK LOGIN ]        
    }

    /**
     *
     * override setData() method to add validators that depend on multiple elements
     *
     * @param array $data form data
     * @param bool  $addSubmitButton
     *
     * @return $this
     */
    public function setData(array $data = null, $addSubmitButton = false)
    {
        parent::setData($data);

        $translate = $this->getTranslate();

        if ($this->hasElement('password')) {
            $passwordConfirmValidator = new Validate\Identical();
            $passwordConfirmValidator->setStrict()
                ->setVariableName($translate->_('Confirm Password'));

            if (isset($data['password_confirm'])) {
                $passwordConfirmValidator->setVariableValue($data['password_confirm']);
            }

            $this->getElement('password')
                ->addValidator($passwordConfirmValidator);
        }

        return $this;
    }

    /**
     *
     * will generate the edit user form
     *
     * @param integer $id the id of the table row
     *
     * @return $this
     */
    public function generateEditForm($id = null)
    {
        parent::generateEditForm($id);

        $translate = $this->getTranslate();

        $id = ($id !== null) ? $id : $this->_editId;

        if ($id !== null) {
            $this->setTitle('Edit User');

            if ($this->hasElement('username')) {
                $this->getElement('username')
                    ->setAttributes(array('readonly' => 'readonly'))
                    ->setDescription(null)
                    ->clearValidators();
            }

            if ($this->hasElement('email')) {
                $this->getElement('email')
                    ->getValidator('Cube\\Validate\\Db\\NoRecordExists')
                    ->setExclude(array('field' => 'id', 'value' => $id));
            }

            if ($this->hasElement('password')) {
                $this->getElement('password')
                    ->setValue('')
                    ->setDescription('Type a new password if you want to change it.')
                    ->setRequired(false);
            }

            $this->removeElement('recaptcha')
                ->removeElement('agree_terms');

            if ($this->hasElement('submit')) {
                $this->getElement('submit')
                    ->setValue($translate->_('Proceed'));
            }
        }


        return $this;
    }

    ## -- START :: ADD -- [ MOD:- PICKUP LOCATIONS ]
    /**
     *
     * validate form after adding geolocation validator
     *
     * @return bool
     */
    public function isValid()
    {
        $valid = parent::isValid();

        $geoLocationValidator = new GeoLocationValidator();
        $geoLocationValidator->setAddress($this->_data);

        if (!$geoLocationValidator->isValid()) {
            $valid = false;
            $this->setMessage(
                $geoLocationValidator->getMessage());
        }

        return $valid;
    }
    ## -- END :: ADD -- [ MOD:- PICKUP LOCATIONS ]
}

