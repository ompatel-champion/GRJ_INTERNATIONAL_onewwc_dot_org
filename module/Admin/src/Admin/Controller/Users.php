<?php

/**
 *
 * PHP Pro Bid
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2019 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     8.1 [rev.8.1.02]
 */

namespace Admin\Controller;

use Ppb\Controller\Action\AbstractAction,
    Cube\Authentication\Authentication,
    Cube\Authentication\Storage\Session as StorageObject,
    Cube\Controller\Front,
    Cube\Paginator,
    Cube\Session,
    Cube\View,
    Ppb\Authentication\Adapter,
    Ppb\Service,
    Ppb\Db\Table\Row\User as UserModel,
    Admin\Form;

class Users extends AbstractAction
{

    /**
     *
     * users service
     *
     * @var \Ppb\Service\Users
     */
    protected $_users;

    /**
     *
     * admin roles
     *
     * @var array
     */
    protected $_adminRoles;

    public function init()
    {
        $this->_users = new Service\Users();
        $this->_adminRoles = array_keys(Service\Users::getAdminRoles());
    }

    public function Browse()
    {
        $userId = $this->getRequest()->getParam('user_id');
        $username = $this->getRequest()->getParam('username');
        $email = $this->getRequest()->getParam('email');

        $section = $this->getRequest()->getParam('view');
        $filter = $this->getRequest()->getParam('filter');

        $table = $this->_users->getTable();

        if ($this->getRequest()->isPost()) {
            $id = $this->getRequest()->getParam('id');
            $option = $this->getRequest()->getParam('option');

            $ids = array_filter(
                array_values((array)$id));

            $counter = null;
            $messages = array();

            if (count($ids) > 0) {
                $where = $table->getAdapter()->quoteInto("id IN (?)", $ids);

                /** @var \Ppb\Db\Table\Rowset\Users $users */
                $users = $this->_users->fetchAll($where);
                $messages = $users->changeStatus($option, true);

                $counter = $users->getCounter();
            }

            if ($counter > 0) {
                $this->_flashMessenger->setMessage(array(
                    'msg'   => sprintf($this->_users->getStatusMessage($option, $counter), $counter),
                    'class' => 'alert-success',
                ));
            }
            else {
                $this->_flashMessenger->setMessage(array(
                    'msg'   => $this->_('No user accounts have been updated.'),
                    'class' => 'alert-danger',
                ));
            }

            if (count($messages) > 0) {
                $this->_flashMessenger->setMessage(array(
                    'msg'   => $messages,
                    'class' => 'alert-info',
                ));
            }
        }

        $select = $table->select();

        switch ($section) {
            case 'admin':
                $select->where('role in (?)', $this->_adminRoles);
                break;
            default:
                $select->where('role not in (?)', $this->_adminRoles);
                break;
        }

        if (!empty($userId)) {
            $select->where('id = ?', $userId);
        }
        if (!empty($username)) {
            $select->where('username LIKE ?', '%' . $username . '%');
        }
        if (!empty($email)) {
            $select->where('email LIKE ?', '%' . $email . '%');
        }

        switch ($filter) {
            case 'stores':
                $select->where('store_active = ?', 1);
                break;
            case 'verified_users':
                $select->where('user_verified = ?', 1);
                break;
            case 'private_site':
                $select->where('is_seller = ?', 1);
                break;
            case 'preferred':
                $select->where('preferred_seller = ?', 1);
                break;
            case 'active':
                $select->where('active = ?', 1)
                    ->where('approved = ?', 1);
                break;
            case 'suspended':
                $select->where('active = ?', 0)
                    ->orWhere('approved = ?', 0);
                break;
            case 'awaiting_approval':
                $select->where('approved = ?', 0);
                break;
            case 'email_not_verified':
                $select->where('mail_activated = ?', 0);
                break;
            case 'requested_selling_privileges':
                $select->where('is_seller = ?', 0)
                    ->where('request_selling_privileges = ?', 1);
                break;
            case 'with_debit':
                $select->where('balance > ?', 0)
                    ->order('balance DESC');

                if ($this->_settings['user_account_type'] == 'personal') {
                    $select->where('account_mode = ?', 'account');
                }
                break;
            case 'debit_balance_exceeded':
                $select->where('balance > ?', 0)
                    ->where('balance > max_debit')
                    ->order('balance DESC');

                if ($this->_settings['user_account_type'] == 'personal') {
                    $select->where('account_mode = ?', 'account');
                }


                break;
        }

        $select->order('created_at DESC');

        $paginator = new Paginator(
            new Paginator\Adapter\DbTableSelect($select, $table));

        $pageNumber = $this->getRequest()->getParam('page');
        $paginator->setPageRange(5)
            ->setItemCountPerPage(15)
            ->setCurrentPageNumber($pageNumber);

        return array(
            'paginator'  => $paginator,
            'messages'   => $this->_flashMessenger->getMessages(),
            'section'    => $section,
            'userId'     => $userId,
            'username'   => $username,
            'email'      => $email,
            'adminRoles' => $this->_adminRoles,
            'filter'     => $filter,
        );
    }

    public function Add()
    {
        $this->_forward('manage');
    }

    public function Manage()
    {
        $id = $this->getRequest()->getParam('id');
        $view = $this->getRequest()->getParam('view');
        $user = null;

        $formId = array('basic', 'advanced', 'user', 'address', 'payment-gateways');

        if ($id) {
            $user = $this->_users->findBy('id', $id, true);

            if (in_array($user['role'], $this->_adminRoles)) {
                $formId = array('admin');
            }
        }
        else if ($view == 'admin') {
            $formId = array('admin');
        }

        if (in_array('admin', $formId) && $this->_user['role'] != Service\Users::ADMIN_ROLE_PRIMARY) {
            $this->_flashMessenger->setMessage(array(
                'msg'   => $this->_('You do not have the privileges to create / edit admin users.'),
                'class' => 'alert-danger',
            ));
            $this->_helper->redirector()->redirect('browse', null, null,
                array('view' => $this->getRequest()->getParam('view')));
        }

        $form = new \Members\Form\Register(
            $formId, null, $user);

        if ($id) {
            $form->setData($user->toArray())
                ->generateEditForm($id);
        }

        if ($this->getRequest()->isPost()) {
            $params = $this->getRequest()->getParams();
            $form->setData($params);

            if ($form->isValid() === true) {
                // set the default signup settings and activate the user
                $userId = $this->_users->save($params);

                if (!$id) {
                    $user = $this->_users->findBy('id', $userId);
                    $user->processRegistration(true);
                }

                $this->_flashMessenger->setMessage(array(
                    'msg'   => ($id) ?
                        $this->_('The user account has been edited successfully') :
                        $this->_('The user account has been created successfully.'),
                    'class' => 'alert-success',
                ));

                $this->_helper->redirector()->redirect('browse', null, null,
                    array('view' => $this->getRequest()->getParam('view')));
            }
            else {
                $this->_flashMessenger->setMessage(array(
                    'msg'   => $form->getMessages(),
                    'class' => 'alert-danger',
                ));
            }
        }

        return array(
            'form'     => $form,
            'messages' => $this->_flashMessenger->getMessages()
        );
    }

    public function Delete()
    {
        $user = $this->_users->findBy('id', $this->getRequest()->getParam('id'));

        if ($user instanceof UserModel) {
            if ($user->canDelete()) {
                $translate = $this->getTranslate();

                $this->_flashMessenger->setMessage(array(
                    'msg'   => sprintf($translate->_("User account '%s' has been deleted."), $user['username']),
                    'class' => 'alert-success',
                ));

                $user->delete();
            }
            else {
                $this->_flashMessenger->setMessage(array(
                    'msg'   => $this->_('Deletion failed. You do not have the necessary permissions to remove this account.'),
                    'class' => 'alert-danger',
                ));
            }
        }
        else {
            $this->_flashMessenger->setMessage(array(
                'msg'   => $this->_('Deletion failed. User account not found.'),
                'class' => 'alert-danger',
            ));
        }

        $params = $this->getRequest()->getParams();
        unset($params['id']);

        $this->_helper->redirector()->redirect('browse', null, null, $params);
    }


    public function ChangeStatus()
    {
        $id = $this->getRequest()->getParam('id', 0);
        $option = $this->getRequest()->getParam('option');
        $flag = $this->getRequest()->getParam('flag');

        $user = $this->_users->findBy('id', $id);

        $translate = $this->getTranslate();

        if ($user instanceof UserModel) {
            $params = array();

            $class = 'alert-success';
            switch ($option) {
                case 'approve':
                    $params['approved'] = 1;
                    $message = sprintf($translate->_("User '%s' has been approved."), $user['username']);
                    break;
                case 'verify-email':
                    $params['mail_activated'] = 1;
                    $message = sprintf($translate->_("The email address for user '%s' has been verified."),
                        $user['username']);
                    break;
                case 'activate':
                    $user->updateActive($flag);
                    $status = ($flag == 1) ? $translate->_('activated') : $translate->_('suspended');
                    $message = sprintf($translate->_("User '%s' has been %s."), $user['username'], $status);
                    break;
                case 'is_seller':
                    $params['is_seller'] = ($flag == 1) ? 1 : 0;
                    $status = ($flag == 1) ? $translate->_('enabled') : $translate->_('disabled');
                    $message = sprintf($translate->_("The selling capability for user '%s' has been %s."),
                        $user['username'],
                        $status);

                    $user['is_seller'] = $params['is_seller'];

                    $mail = new \Members\Model\Mail\User();
                    $mail->sellingPrivilegesChange($user)->send();
                    break;
                default:
                    $message = $this->_('Invalid option selected');
                    $class = 'alert-danger';
                    break;
            }

            if (count($params) > 0) {
                $user->save($params);
            }

            $this->_flashMessenger->setMessage(array(
                'msg'   => $message,
                'class' => $class,
            ));
        }
        else {
            $this->_flashMessenger->setMessage(array(
                'msg'   => $this->_('Operation failed. The user could not be found.'),
                'class' => 'alert-danger',
            ));
        }

        $this->_helper->redirector()->redirect('browse', null, null, $this->getRequest()->getParams(array('id')));
    }


    public function LoginAs()
    {
        $userId = $this->getRequest()->getParam('id');
        $adapter = new Adapter(array(), $userId);

        $translate = $this->getTranslate();

        $config = require APPLICATION_PATH . '/config/global.config.php';
        $session = new Session($config['session']);

        $authentication = new Authentication(
            new StorageObject(null, null, $session));

        $result = $authentication->authenticate($adapter);

        if ($authentication->hasIdentity()) {
            $user = $this->_users->findBy('id', $userId);
            $view = Front::getInstance()->getBootstrap()->getResource('view');

            $this->_flashMessenger->setMessage(array(
                'msg'   => sprintf($translate->_("You were successfully logged in as '%s'."), $user['username'])
                    . ' <a class="btn btn-success" href="' . $view->url(array('module' => 'members', 'controller' => 'summary', 'action' => 'index')) . '" target="_blank">' . $translate->_('Proceed') . '</a>',
                'class' => 'alert-success',
            ));
        }
        else {
            $this->_flashMessenger->setMessage(array(
                'msg'   => sprintf($translate->_("Could not log you in as the user having the id '%s'."), $userId),
                'class' => 'alert-danger',
            ));
        }

        $this->_helper->redirector()->redirect('browse', null, null, array('view' => 'site'));
    }

    public function UserOptions()
    {
        $form = null;
        $view = Front::getInstance()->getBootstrap()->getResource('view');
        $view->setNoLayout();

        /** @var \Cube\View\Helper\Script $scriptHelper */
        $scriptHelper = $view->getHelper('script');
        $scriptHelper->clearHeaderCode()
            ->clearBodyCode();

        $userId = $this->getRequest()->getParam('id');

        /* @var \Ppb\Db\Table\Row\User $user */
        $user = $this->_users->findBy('id', $userId);

        if ($user instanceof UserModel) {
            $form = new Form\UserOptions($user);

            $form->setData($user->getData());

            if ($this->getRequest()->isPost()) {
                $params = $this->getRequest()->getParams();

                $form->setData($params);

                if ($form->isValid() === true) {
                    $messages = $user->updateSettings(
                        $form->getData());

                    $this->_flashMessenger->setMessage(array(
                        'msg'   => $this->_('The settings have been saved.'),
                        'class' => 'alert-success',
                    ));

                    if (count($messages) > 0) {
                        $this->_flashMessenger->setMessage(array(
                            'msg'   => $messages,
                            'class' => 'alert-success',
                        ));
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
        }
        else {
            $this->_flashMessenger->setMessage(array(
                'msg'   => $this->_("The user doesn't exist."),
                'class' => 'alert-danger',
            ));
        }

        return array(
            'headline' => 'User Options',
            'form'     => $form,
            'messages' => $this->_flashMessenger->getMessages()
        );
    }

    public function SendMessage()
    {
        $bootstrap = Front::getInstance()->getBootstrap();
        $user = $bootstrap->getResource('user');
        $view = $bootstrap->getResource('view');
        $view->setNoLayout();

        /** @var \Cube\View\Helper\Script $scriptHelper */
        $scriptHelper = $view->getHelper('script');
        $scriptHelper->clearHeaderCode()
            ->clearBodyCode();

        $params = $this->getRequest()->getParams();
        $params['sender_id'] = $user['id'];

        $messages = null;

        $form = new \Members\Form\Message();
        $form->setData($params)
            ->setPartial('forms/popup-form.phtml');

        if ($this->getRequest()->isPost()) {
            if ($form->isValid() === true) {
                $messagingService = new Service\Messaging();
                $messageId = $messagingService->setTopicType(Service\Messaging::ADMIN_MESSAGE)
                    ->save($params);

                $this->_flashMessenger->setMessage(array(
                    'msg'   => $this->_('The message has been successfully sent.'),
                    'class' => 'alert-success',
                ));

                $mail = new \Members\Model\Mail\User();
                $mail->messageReceived($messageId)->send();

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
            'form'     => $form,
            'messages' => $this->_flashMessenger->getMessages()
        );
    }

    public function Reputation()
    {

        if ($this->getRequest()->isPost() &&
            $this->getRequest()->getParam('option') == 'delete'
        ) {
            $id = $this->getRequest()->getParam('id');

            $ids = array_filter(
                array_values((array)$id));

            $reputationService = new Service\Reputation();
            $reputationService->delete($ids);

            $this->_flashMessenger->setMessage(array(
                'msg'   => $this->_('The selected reputation comments have been removed'),
                'class' => 'alert-success',
            ));
        }

        $this->_forward('browse', 'reputation', 'members');
    }

    public function SaveReputation()
    {
        $view = new View();

        $id = $this->getRequest()->getParam('id');
        $comments = $this->getRequest()->getParam('comments');

        $reputationService = new Service\Reputation();
        $reputation = $reputationService->findBy('id', $id);

        $message = $this->_('Operation failed.');

        if ($reputation) {
            $reputation->save(array(
                'comments' => $comments,
            ));

            $message = $this->_('The reputation comment has been updated.');
        }

        $this->getResponse()->setHeader('Content-Type: application/json');

        $view->setContent(
            json_encode(array(
                'message' => $message
            )));

        return $view;

    }

    public function PaymentReminder()
    {
        $id = $this->getRequest()->getParam('id');

        $user = $this->_users->findBy('id', $id);
        if ($user) {
            $translate = $this->getTranslate();

            $mail = new \Members\Model\Mail\User();
            $mail->balancePaymentReminder($user)->send();

            $this->_flashMessenger->setMessage(array(
                'msg'   => sprintf($translate->_("The payment reminder email has been sent to '%s'."),
                    $user['username']),
                'class' => 'alert-success',
            ));
        }

        $this->_helper->redirector()->redirect('browse', null, null,
            array('view' => 'site'));
    }

}