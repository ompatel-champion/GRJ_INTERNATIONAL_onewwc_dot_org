<?php

/**
 *
 * PHP Pro Bid
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2020 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     8.2 [rev.8.2.02]
 */

namespace Members\Controller;

use Cube\Controller\Front,
    Members\Controller\Action\AbstractAction,
    Cube\Authentication\Authentication,
    Ppb\Authentication\Adapter,
    Ppb\Service\Users as UsersService,
    Ppb\Service\UsersAddressBook as UsersAddressBookService,
    Ppb\Db\Table\Row\User as UserModel,
    Members\Form,
    Members\Model\Mail,
    Cube\Crypt,
    Ppb\Service\NewslettersSubscribers as NewslettersSubscribersService;

class User extends AbstractAction
{

    public function Index()
    {
        $this->_helper->redirector()->redirect('index', 'summary');
    }

    public function Register()
    {
        $type = $this->getRequest()->getParam('type');

        $view = Front::getInstance()->getBootstrap()->getResource('view');

        $id = $userId = null;
        $user = null;
        $formId = array();
        $controller = 'User';
        $formTitle = null;
        $displaySubtitles = true;
        $data = array();

        $isMembersModule = false;

        if (!empty($this->_user['id'])) {
            // edit form
            $isMembersModule = true;
            $id = $this->_user['id'];
            $user = $this->_users->findBy('id', $id, true);
            $data = $user->toArray();

            switch ($type) {
                case 'account-settings':
                    $formId = array('user', 'payment-gateways');
                    $controller = 'My Account';
                    $formTitle = $this->_('Account Settings');
                    break;
                case 'payment-gateways':
                    $formId = array('payment-gateways');
                    break;
                case 'manage-address':
                    $formId = array('address');
                    $controller = 'My Account';
                    $formTitle = $this->_('Add Address');
                    $displaySubtitles = false;

                    $addressId = $this->getRequest()->getParam('address_id');
                    $address = $user->getAddress($addressId);

                    if ($addressId && $address !== null) {
                        $formTitle = $this->_('Edit Address');


                        if (($result = $address->canEdit()) !== true) {
                            $this->_flashMessenger->setMessage(array(
                                'msg'   => $result,
                                'class' => 'alert-danger',
                            ));

                            $this->_helper->redirector()->redirect('address-book', 'account', 'members', array());
                        }


                        $data = array_merge($data, $address->getData());
                    }
                    else {
                        $addressBookService = new UsersAddressBookService();
                        $data = array_merge($data,
                            array_map(function () {
                            }, array_flip($addressBookService->getAddressFields())));
                    }


                    break;
                default:
                    $formId = array('basic', 'advanced', 'address');
                    $controller = 'My Account';
                    $formTitle = $this->_('Personal Information');
                    break;
            }
        }
        else {
            switch ($type) {
                case 'forgot-username':
                    $formId = array('forgot-username');
                    $formTitle = $this->_('Retrieve Username');
                    break;
                case 'forgot-password':
                    $formId = array('forgot-password');
                    $formTitle = $this->_('Reset Password');
                    break;
                default:
                    $formId[] = 'basic';
                    $displaySubtitles = false;

                    if ($this->_settings['registration_type'] == 'full') {
                        $formId[] = 'advanced';
                        $formId[] = 'address';
                        $displaySubtitles = true;
                    }
                    if ($this->_settings['payment_methods_registration']) {
                        $formId[] = 'payment-gateways';
                    }
                    break;
            }
        }

        $form = new Form\Register($formId, null, $user, $displaySubtitles);

        if ($this->getRequest()->getParam('popup')) {
            $this->_setNoLayout();
            $view->script()
                ->clearHeaderCode()
                ->clearBodyCode();
            $form->setPartial('forms/popup-form.phtml');
        }

        if ($id) {
            $form->setData($data)
                ->generateEditForm($id);
        }

        if ($formTitle) {
            $form->setTitle($formTitle);
        }

        $form->setDisplaySubtitles($displaySubtitles);

        if ($this->getRequest()->isPost()) {
            $params = $this->getRequest()->getParams();
            $form->setData($params);
            $params = $form->getData();

            if ($form->isValid() === true) {
                if ($type == 'forgot-username') {
                    $email = $this->getRequest()->getParam('email');
                    $user = $this->_users->findBy('email', $email);

                    if ($user) {
                        $this->_flashMessenger->setMessage(array(
                            'msg'   => $this->_('An email containing the username associated with your email address has been sent.'),
                            'class' => 'alert-success',
                        ));

                        $mail = new Mail\User();
                        $mail->forgotUsername($user)->send();
                    }
                    else {
                        $this->_flashMessenger->setMessage(array(
                            'msg'   => $this->_('The email address you have submitted is not associated to any account.'),
                            'class' => 'alert-danger',
                        ));
                    }
                }
                else if ($type == 'forgot-password') {
                    $email = $this->getRequest()->getParam('email');
                    $username = $this->getRequest()->getParam('username');

                    /** @var \Ppb\Db\Table\Row\User $user */
                    $user = $this->_users->fetchAll(
                        $this->_users->getTable()->select()
                            ->where('username = ?', $username)
                            ->where('email = ?', $email)
                    )->getRow(0);

                    if ($user) {
                        $password = substr(md5(rand(0, 100000)), 0, 8);
                        $this->_users->savePassword($user, $password);

                        $this->_flashMessenger->setMessage(array(
                            'msg'   => $this->_('An email containing your updated login details has been sent.'),
                            'class' => 'alert-success',
                        ));

                        $mail = new Mail\User();
                        $mail->forgotPassword($user, $password)->send();
                    }
                    else {
                        $this->_flashMessenger->setMessage(array(
                            'msg'   => $this->_('The username / email address combination you have submitted doesnt exist.'),
                            'class' => 'alert-danger',
                        ));
                    }
                }
                else {
                    $userId = $this->_users->save($params, $id);

                    if ($id === null) { // new user registration related actions
                        $user = $this->_users->findBy('id', $userId);
                        $messages = $user->processRegistration();

                        foreach ($messages as $message) {
                            $this->_flashMessenger->setMessage($message);
                        }

                        $authentication = Authentication::getInstance();

                        // log user in
                        $authentication->authenticate(
                            new Adapter(array(), $userId));

                        if ($authentication->hasIdentity()) {
                            /** @var \Cube\View $view */
                            $view = Front::getInstance()->getBootstrap()->getResource('view');

                            $user = $authentication->getStorage()->read();
                            $view->set('loggedInUser', $user);
                        }
                    }
                    else {
                        if ($type == 'manage-address') {
                            $this->_flashMessenger->setMessage(array(
                                'msg'   => $this->_('The address has been saved successfully.'),
                                'class' => 'alert-success',
                            ));

                            $this->_helper->redirector()->redirect('address-book', 'account', null, array());
                        }
                        else {
                            $this->_flashMessenger->setMessage(array(
                                'msg'   => $this->_('Your details have been edited successfully.'),
                                'class' => 'alert-success',
                            ));

                            if (array_key_exists('email', $params) && $params['email'] != $this->_user['email'] && $this->_settings['email_address_change_confirmation']) {
                                $messages = $user->processRegistration(false, true);

                                foreach ($messages as $message) {
                                    $this->_flashMessenger->setMessage($message);
                                }

                                $this->_helper->redirector()->redirect('index', 'summary');
                            }
                        }
                    }
                }

                $form->clearElements();
            }
            else {
                $this->_flashMessenger->setMessage(array(
                    'msg'   => $form->getMessages(),
                    'class' => 'alert-danger',
                ));
            }
        }

        return array(
            'headline'        => $form->getTitle(),
            'form'            => $form,
            'userId'          => $userId,
            'messages'        => $this->_flashMessenger->getMessages(),
            'isMembersModule' => $isMembersModule,
            'controller'      => $controller,
        );
    }

    public function Login()
    {
        /** @var \Cube\View $view */
        $view = Front::getInstance()->getBootstrap()->getResource('view');

        $action = $view->url(null, 'members-sign-in');

        $form = new Form\Login($action);

        $username = $email = $this->getRequest()->getParam('username');
        $password = $this->getRequest()->getParam('password');
        $redirectUrl = $this->getRequest()->getParam('redirect');

        if (empty($redirectUrl)) {
            $redirectUrl = $this->getRequest()->getBaseUrl() .
                $this->getRequest()->getRequestUri();
        }

        $form->setRedirectUrl($redirectUrl);

        if ($async = $this->getRequest()->getParam('async')) {
            $this->_setNoLayout();
            $view->script()
                ->clearHeaderCode()
                ->clearBodyCode();

            $form->setAsync();
        }

        $form->setData($this->getRequest()->getParams());

        if ($form->isPost(
            $this->getRequest())
        ) {

            /** @var \Ppb\Authentication\Adapter $adapter */
            $adapter = new Adapter(array(
                'username' => $username,
                'email'    => $email,
                'password' => $password
            ), null, array(), UsersService::getAdminRoles());

            $authentication = Authentication::getInstance();
            $result = $authentication->authenticate($adapter);

            if ($authentication->hasIdentity()) {
                if ($this->getRequest()->getParam('remember_me')) {
                    /** @var \Cube\Session $session */
                    $session = Front::getInstance()->getBootstrap()->getResource('session');
                    $user = $authentication->getStorage()->read();
                    $session->setCookie(UserModel::REMEMBER_ME, $user['id']);
                }

                if (!$this->getRequest()->getParam('no_redirect_parent')) {
                    $form->setRedirectParent();
                }

                if (!$form->isAsync() || !$form->isRedirectParent()) {
                    $this->_helper->redirector()->gotoUrl($redirectUrl);
                }
            }
            else {

                $this->_flashMessenger->setMessage(array(
                    'msg'   => $result->getMessages(),
                    'class' => 'alert-danger',
                    'local' => true
                ));
            }
        }

        return array(
            'headline'        => $this->_('Sign In'),
            'form'            => $form,
            'messages'        => $this->_flashMessenger->getMessages(),
            'isMembersModule' => false,
        );
    }

    public function Logout()
    {
        Authentication::getInstance()->clearIdentity();

        /** @var \Cube\Session $session */
        $session = Front::getInstance()->getBootstrap()->getResource('session');
        $session->unsetCookie(UserModel::REMEMBER_ME);

        $url = $this->_settings['site_path'];
        $this->_helper->redirector()->gotoUrl($url);
    }

    public function Activate()
    {
        if ($this->getRequest()->getParam('resend_email')) {
            $this->_resendActivationEmail();
        }

        return array(
            'headline'        => $this->_('Activate Account'),
            'messages'        => $this->_flashMessenger->getMessages(),
            'user'            => $this->_user,
            'isMembersModule' => false,
        );
    }

    public function ConfirmRegistration()
    {
        $key = $this->getRequest()->getParam('key');
        $verified = $this->_users->verifyEmailAddress($key);

        if ($verified !== true) {
            $this->_flashMessenger->setMessage(array(
                'msg'   => $this->_('Email verification failed. The activation key is invalid, or the email for this account has already been verified.'),
                'class' => 'alert-danger',
            ));
        }

        return array(
            'headline'        => $this->_('Confirm Registration'),
            'verified'        => $verified,
            'messages'        => $this->_flashMessenger->getMessages(),
            'isMembersModule' => false,
        );
    }

    public function Verification()
    {
        if (!$this->_settings['user_verification']) {
            $this->_flashMessenger->setMessage(array(
                'msg'   => $this->_('User verification is disabled.'),
                'class' => 'alert-danger',
            ));

            $this->_helper->redirector()->redirect('index', 'summary');
        }

        $form = new Form\Register(array('advanced', 'address'), null, $this->_user, false);

        $form->setData($this->_user->getData())
            ->generateEditForm($this->_user['id']);

        $form->addSubmitElement($this->_('Get Verified'));

        if ($this->getRequest()->isPost()) {
            $params = $this->getRequest()->getParams();

            $form->setData($params);

            if ($form->isValid() === true) {
                $this->_users->save($params, $this->_user['id']);
                $this->_helper->redirector()->redirect('user-verification', 'payment', 'app');
            }
            else {
                $this->_flashMessenger->setMessage(array(
                    'msg'   => $form->getMessages(),
                    'class' => 'alert-danger',
                ));
            }
        }

        return array(
            'headline'        => $this->_('User Verification'),
            'form'            => $form,
            'messages'        => $this->_flashMessenger->getMessages(),
            'user'            => $this->_user,
            'isMembersModule' => false,
        );
    }

    public function NewsletterUnsubscribe()
    {
        $options = Front::getInstance()->getOption('session');

        $crypt = new Crypt();
        // key crypt is the admin key so we get that when decrypting
        $crypt->setKey($options['secret']);

        $encryptionKey = str_replace(' ', '+', $_REQUEST['unsubscribe']);

        $email = $crypt->decrypt($encryptionKey);

        $newslettersSubscribersService = new NewslettersSubscribersService();
        $unsubscribed = (bool)$newslettersSubscribersService->deleteOne('email', $email);

        if ($unsubscribed === true) {
            $this->_flashMessenger->setMessage(array(
                'msg'   => $this->_('You have been successfully unsubscribed from our newsletter.'),
                'class' => 'alert-success',
            ));
        }
        else {
            $this->_flashMessenger->setMessage(array(
                'msg'   => $this->_('Unsubscription failed. Your account could not be found, or your newsletter subscription is not active.'),
                'class' => 'alert-danger',
            ));
        }

        return array(
            'headline'        => $this->_('Newsletter Subscription'),
            'messages'        => $this->_flashMessenger->getMessages(),
            'isMembersModule' => false,
        );
    }

    public function NewsletterSubscriptionConfirmation()
    {
        $options = Front::getInstance()->getOption('session');

        $crypt = new Crypt();
        // key crypt is the admin key so we get that when decrypting
        $crypt->setKey($options['secret']);

        $encryptionKey = (!empty($_REQUEST['subscription-confirmation'])) ?
            str_replace(' ', '+', $_REQUEST['subscription-confirmation']) : null;

        $email = $crypt->decrypt($encryptionKey);

        $newslettersSubscribersService = new NewslettersSubscribersService();

        $newsletterSubscriber = $newslettersSubscribersService->findBy('email', $email);

        if ($newsletterSubscriber !== null) {
            $newsletterSubscriber->save(array(
                'confirmed' => 1,
            ));

            $this->_flashMessenger->setMessage(array(
                'msg'   => $this->_('Thank you for subscribing to our newsletter. Your email address has been confirmed.'),
                'class' => 'alert-success',
            ));
        }
        else {
            $this->_flashMessenger->setMessage(array(
                'msg'   => $this->_('Email address not found.'),
                'class' => 'alert-danger',
            ));
        }

        return array(
            'headline'        => $this->_('Newsletter Subscription Confirmation'),
            'messages'        => $this->_flashMessenger->getMessages(),
            'isMembersModule' => false,
        );
    }

    public function ForgotUsername()
    {
        $this->_forward('register', null, null, array('type' => 'forgot-username'));
    }

    public function ForgotPassword()
    {
        $this->_forward('register', null, null, array('type' => 'forgot-password'));
    }

    public function EditPaymentGateway()
    {
        $this->_forward('register', null, null, array('type' => 'payment-gateways'));
    }


    public function RegisterModal()
    {
        $this->_setNoLayout();

        return array();
    }

    /**
     *
     * @8.0 DEPRECATED
     *
     * @return array
     */
    public function LoginModal()
    {
        $this->_setNoLayout();

        $action = Front::getInstance()->getBootstrap()->getResource('view')
            ->url(null, 'members-sign-in');

        $form = new Form\Login($action);

        return array(
            'form' => $form,
        );
    }

    protected function _resendActivationEmail()
    {
        $identity = Authentication::getInstance()->getIdentity();

        if (isset($identity['id'])) {
            $user = $this->_users->findBy('id', $identity['id']);

            $mail = new Mail\Register($user->toArray());

            switch ($this->_settings['signup_settings']) {
                case '1':
                    $this->_flashMessenger->setMessage(array(
                        'msg'   => $this->_('The verification email has been sent.'),
                        'class' => 'alert-info',
                    ));

                    $mail->registerConfirm()->send();

                    break;
                case '2':
                    $this->_flashMessenger->setMessage(array(
                        'msg'   => $this->_('The verification email has been sent.'),
                        'class' => 'alert-info',
                    ));

                    $mail->registerApprovalUser()->send();

                    break;
                default:
                    $this->_flashMessenger->setMessage(array(
                        'msg'   => $this->_('The email could not be sent, because no email verification is necessary.'),
                        'class' => 'alert-danger',
                    ));
                    break;
            }
        }
    }


}

