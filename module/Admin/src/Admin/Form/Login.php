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
 * admin login form
 */

namespace Admin\Form;

use Cube\Form;

class Login extends Form
{

    public function __construct($action = null)
    {
        parent::__construct($action);
        $this->setMethod(self::METHOD_POST);

        $translate = $this->getTranslate();

        /* username field */
        $username = $this->createElement('text', 'username');
        $username->setLabel('Username');
        $username->setAttributes(array(
            'id'          => 'username',
            'autofocus'   => 'autofocus',
            'required'    => 'required',
            'class'       => 'form-control',
            'placeholder' => $translate->_('Username'),
        ));
        $username->setRequired();
        $this->addElement($username);

        /* password field */
        $password = $this->createElement('password', 'password');
        $password->setLabel('Password');
        $password->setAttributes(array(
            'id'          => 'password',
            'required'    => 'required',
            'class'       => 'form-control',
            'placeholder' => $translate->_('Password'),
        ));
        $this->addElement($password);

        /* submit button */
        $submit = $this->createElement('submit', 'submit');
        $submit->setAttributes(array(
            'class' => 'btn btn-lg btn-primary btn-block',
        ));
        $submit->setValue('Log In');
        $this->addElement($submit);

        $this->setPartial('forms/admin-login.phtml');
    }

}

