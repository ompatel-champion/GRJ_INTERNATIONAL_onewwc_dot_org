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
 * registration emails generator class
 */

namespace Members\Model\Mail;

use Ppb\Model\BaseMail;

class Register extends BaseMail
{

    /**
     * 
     * registration success - no confirmation
     * 
     * @return $this
     */
    public function registerDefault()
    {
        $this->_mail->setFrom($this->_settings['admin_email'], $this->_settings['email_admin_title'])
                ->setTo($this->_data['email'])
                ->setSubject('Registration Successful');

        $this->_view->headerMessage = $this->_('Registration Successful');
        $this->_view->clearContent()
                ->process('emails/register-success.phtml');

        return $this;
    }

    /**
     * 
     * email confirmation required
     *
     * @return $this
     */
    public function registerConfirm()
    {
        $this->_mail->setFrom($this->_settings['admin_email'], $this->_settings['email_admin_title'])
                ->setTo($this->_data['email'])
                ->setSubject('Confirm Registration');

        $this->_view->headerMessage = $this->_('Registration Successful');


        $this->_view->clearContent()
                ->process('emails/register-confirm.phtml');

        return $this;
    }

    /**
     * 
     * registration approval - user notification
     *
     * @return $this
     */
    public function registerApprovalUser()
    {
        $this->_mail->setFrom($this->_settings['admin_email'], $this->_settings['email_admin_title'])
                ->setTo($this->_data['email'])
                ->setSubject('Confirm Registration');

        $this->_view->headerMessage = $this->_('Registration Successful');

        $this->_view->clearContent()
                ->process('emails/register-approval-user-notification.phtml');

        return $this;
    }

    /**
     *
     * registration approved - user notification
     *
     * @return $this
     */
    public function registerApprovedUser()
    {
        $this->_mail->setFrom($this->_settings['admin_email'], $this->_settings['email_admin_title'])
            ->setTo($this->_data['email'])
            ->setSubject('Account Approved');

        $this->_view->headerMessage = $this->_('Account Approved');

        $this->_view->clearContent()
            ->process('emails/register-approved-user-notification.phtml');

        return $this;
    }

    /**
     * 
     * registration approval - user notification
     *
     * @return $this
     */
    public function registerApprovalAdmin()
    {
        $this->_mail->setFrom($this->_settings['admin_email'], $this->_settings['email_admin_title'])
                ->setTo($this->_settings['admin_email'])
                ->setSubject('User Approval Request');

        $this->_view->headerMessage = $this->_('New User Registration Approval');

        $this->_view->clearContent()
                ->process('emails/register-approval-admin-notification.phtml');

        return $this;
    }

}

