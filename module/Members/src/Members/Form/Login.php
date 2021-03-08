<?php

/**
 *
 * PHP Pro Bid
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2018 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     8.0 [rev.8.0.03]
 */

/**
 * members module login form
 */

namespace Members\Form;

use Ppb\Form\AbstractBaseForm;

class Login extends AbstractBaseForm
{

    const BTN_SUBMIT = 'login';

    /**
     *
     * submit buttons values
     *
     * @var array
     */
    protected $_buttons = array(
        self::BTN_SUBMIT => 'Sign In',
    );

    public function __construct($action = null)
    {
        parent::__construct($action);
        $this->setMethod(self::METHOD_POST);

        $translate = $this->getTranslate();

        $redirect = $this->createElement('hidden', 'redirect');
        $redirect->setBodyCode('<script type="text/javascript">
                $(document).ready(function () {
                    var noRedirectParent = $(\'[name="no_redirect_parent"]\').val();
                    if (!noRedirectParent) {
                        var redirect = location.pathname;
                        if (location.search) {
                            redirect += location.search;
                        }
                        $(\'[name="redirect"]\').val(redirect);
                    }
                });
            </script>');
        $this->addElement($redirect);

        $noRedirectParent = $this->createElement('hidden', 'no_redirect_parent');
        $this->addElement($noRedirectParent);

        /* username field */
        $username = $this->createElement('text', 'username');
        $username->setLabel('Username / Email Address');
        $username->setAttributes(array(
            'id'          => 'username',
            'autofocus'   => 'autofocus',
            'required'    => 'required',
            'class'       => 'form-control',
            'placeholder' => $translate->_('Username / Email Address'),
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

        $rememberMe = $this->createElement('checkbox', 'remember_me');
        $rememberMe->setMultiOptions(array(
            1 => $translate->_('Remember me'),
        ));
        $this->addElement($rememberMe);

        /* submit button */
        $submit = $this->createElement('submit', self::BTN_SUBMIT);
        $submit->setAttributes(array(
            'class' => 'btn btn-lg btn-primary btn-block',
        ))
            ->setValue($this->_buttons[self::BTN_SUBMIT]);
        $this->addElement($submit);

        $this->setPartial('forms/login.phtml');
    }

}

