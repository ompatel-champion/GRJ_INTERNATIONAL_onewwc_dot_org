<?php

/**
 *
 * PHP Pro Bid
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2018 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     8.0 [rev.8.0.09]
 */

/**
 * members module - messaging controller
 */

namespace Members\Controller;

use Members\Controller\Action\AbstractAction,
    Cube\Controller\Front,
    Cube\Paginator,
    Ppb\Service,
    Members\Form;

class Messaging extends AbstractAction
{

    /**
     *
     * messaging table service
     *
     * @var \Ppb\Service\Messaging
     */
    protected $_messaging;

    public function init()
    {
        $this->_messaging = new Service\Messaging();
    }


    public function Browse()
    {
        $inAdmin = $this->_loggedInAdmin();

        $filter = $this->getRequest()->getParam('filter', 'received');
        $archived = $this->getRequest()->getParam('archived', 0);
        $summary = $this->getRequest()->getParam('summary');
        $keywords = $this->getRequest()->getParam('keywords');

        $table = $this->_messaging->getTable();
        $select = $table->select()
            ->order('created_at DESC');

        if (!$inAdmin) {
            $filter = ($filter == 'all') ? null : $filter;
        }

        if ($this->getRequest()->isPost() &&
            $this->getRequest()->getParam('option') == 'archive'
        ) {
            $id = $this->getRequest()->getParam('id');

            $ids = array_filter(
                array_values((array)$id));

            if (count($ids) > 0) {
                $messagingService = new Service\Messaging();
                $messagingService->archive($ids, $this->_user['id'], $filter);

                $this->_flashMessenger->setMessage(array(
                    'msg'   => $this->_('The selected messages have been archived.'),
                    'class' => 'alert-success',
                ));
            }
            else {
                $this->_flashMessenger->setMessage(array(
                    'msg'   => $this->_('No messages have been selected.'),
                    'class' => 'alert-danger',
                ));
            }
        }

        if (!empty($keywords)) {
            $params = '%' . str_replace(' ', '%', $keywords) . '%';

            $select->where("title LIKE '{$params}' OR content LIKE '{$params}' OR topic_title LIKE '{$params}'");
        }

        switch ($filter) {
            case 'all':
                break;
            case 'sent':
                $select->where('sender_id = ?', $this->_user['id'])
                    ->where('sender_deleted = ?', (int)$archived);
                break;
            default: // received
                $select->where('receiver_id = ?', $this->_user['id'])
                    ->where('receiver_deleted = ?', (int)$archived);
                break;
        }

        if ($summary) {
            $select->where('flag_read = ?', 0);
        }

        $paginator = new Paginator(
            new Paginator\Adapter\DbTableSelect($select, $table));

        $pageNumber = $this->getRequest()->getParam('page');
        $paginator->setCurrentPageNumber($pageNumber)
            ->setItemCountPerPage(10);

        $array = array(
            'controller' => ($inAdmin) ? 'Tools' : (($summary) ? 'Members Area' : 'Messages'),
            'paginator'  => $paginator,
            'filter'     => $filter,
            'keywords'   => $keywords,
            'archived'   => $archived,
            'summary'    => $summary,
            'inAdmin'    => $inAdmin,
        );

        if (!$summary) {
            $array['messages'] = $this->_flashMessenger->getMessages();
        }

        return $array;
    }

    public function Create()
    {
        /** @var \Cube\View $view */
        $view = Front::getInstance()->getBootstrap()->getResource('view');

        $messageId = null;
        $canPost = true;
        $canPostMsg = null;
        $redirectUrl = null;
        $topicId = $this->getRequest()->getParam('id');

        $usersService = new Service\Users();

        $params = $this->getRequest()->getParams();
        $params['sender_id'] = $this->_user['id'];

        $inAdmin = $this->_loggedInAdmin();

        if (!empty($params['sale_id'])) {
            $salesService = new Service\Sales();
            $sale = $salesService->findBy('id', (int)$params['sale_id']);

            $params['receiver_id'] = ($params['sender_id'] == $sale['buyer_id']) ? $sale['seller_id'] : $sale['buyer_id'];

            if (!$inAdmin && !in_array($this->_user['id'], array($sale['seller_id'], $sale['buyer_id']))) {
                $canPost = false;
                $canPostMsg = $this->_('You do not have the privileges to access this messaging topic.');
            }
        }
        else if (empty($params['receiver_id'])) {
            $adminUser = $usersService->findBy('role', 'admin');
            $params['receiver_id'] = $adminUser['id'];
        }

        $form = new Form\Message();
        $form->setData($params);

        if ($inAdmin) {
            $action = $view->url(array('module' => 'admin', 'controller' => 'tools', 'action' => 'messaging-create'));
        }
        else {
            $action = $view->url(array('module' => 'members', 'controller' => 'messaging', 'action' => 'create'));
        }

        $form->setAction($action)
            ->setAsync()
            ->setAsyncWrapper();

        $isFormPost = $form->isPost($this->getRequest());

        if ($isFormPost) {
            $view->setNoLayout();

            $view->script()
                ->clearHeaderCode()
                ->clearBodyCode();

            $form->setAsyncWrapper(false);
        }

        if (!$inAdmin) {
            if (!$this->_user->isActive()) {
                $receiver = $usersService->findBy('id', $params['receiver_id']);

                if (!in_array($receiver['role'], array_keys(Service\Users::getAdminRoles()))) {
                    $canPost = false;
                    $canPostMsg = $this->_('Your account is inactive. The message posting capability is disabled.');
                }
            }
        }

        if (!empty($params['topic_type'])) {
            $topicTitle = $this->_messaging->generateTopicTitle($params);
            if (is_array($topicTitle)) {
                $topicTitle = vsprintf($topicTitle['msg'], $topicTitle['args']);
            }
            $form->setTitle($topicTitle);
        }
        else {
            $form->setTitle('Post Message');
        }

        if ($canPost === false) {
            $form->clearElements();
        }
        else if ($isFormPost) {
            if ($form->isValid() === true) {
                $messageId = $this->_messaging->save($form->getData());

                $this->_flashMessenger->setMessage(array(
                    'msg'   => $this->_('The message has been posted successfully.'),
                    'class' => 'alert-success',
                ));

                $mail = new \Members\Model\Mail\User();

                /** @var \Ppb\Db\Table\Row\Message $message */
                $message = $this->_messaging->findBy('id', $messageId);
                $topicId = $message->getData('topic_id');

                $receivers = $message->getRecipients();

                foreach ($receivers as $receiver) {
                    $mail->messageReceived($messageId, $receiver)->send();
                }

                if ($this->_settings['bcc_emails']) {
                    $subject = $mail->getMail()->getSubject();
                    $subject = '[BCC] ' . $subject;
                    $mail->getMail()
                        ->setSubject($subject)
                        ->setTo($this->_settings['admin_email'])
                        ->send();
                }

                if ($form->isAsync()) {
                    $form->setRedirectParent();
                }
            }
            else {
                $this->_flashMessenger->setMessage(array(
                    'msg'   => $form->getMessages(),
                    'class' => 'alert-danger',
                ));
            }

            if ($inAdmin) {
                $params = array(
                    'module'     => 'admin',
                    'controller' => 'tools',
                    'action'     => 'messaging-topic',
                    'id'         => $topicId,
                );

                $redirectUrl = $view->url($params);
            }
            else {
                switch ($form->getData('topic_type')) {
                    case Service\Messaging::ABUSE_REPORT_LISTING:
                    case Service\Messaging::ABUSE_REPORT_USER:
                    case Service\Messaging::REFUND_REQUEST:
                        $redirectUrl = $view->url(array('module' => 'members', 'controller' => 'messaging', 'action' => 'browse', 'filter' => 'sent'));

                        break;
                    case Service\Messaging::PRIVATE_QUESTION:
                    case Service\Messaging::PUBLIC_QUESTION:
                        $listingsService = new Service\Listings();
                        $listing = $listingsService->findBy('id', $this->getRequest()->getParam('listing_id'));

                        $redirectUrl = $view->url($listing->link());
                        break;
                    case Service\Messaging::SALE_TRANSACTION:
                    default:
                        $params = array(
                            'module'     => 'members',
                            'controller' => 'messaging',
                            'action'     => 'topic',
                        );

                        if ($messageId) {
                            $params['id'] = $this->_messaging->findBy('id', $messageId)->getData('topic_id');
                        }

                        $redirectUrl = $view->url($params);
                        break;
                }
            }

            if (!empty($redirectUrl)) {
                if ($form->isAsync()) {
                    $form->setRedirectUrl($redirectUrl);
                }
                else {
                    $this->_helper->redirector()->gotoUrl($redirectUrl);
                }
            }
        }

        $array = array(
            'form'        => $form,
            'canPost'     => $canPost,
            'canPostMsg'  => $canPostMsg,
            'redirectUrl' => $redirectUrl,
        );

        if ($canPost && !$this->getRequest()->getParam('topic_id')) {
            $array['headline'] = $form->getTitle();
        }

        if (!$form->isRedirectParent()) {
            $array['messages'] = $this->_flashMessenger->getMessages();
        }

        return $array;
    }

    public function Topic()
    {
        $inAdmin = $this->_loggedInAdmin();

        $sale = null;
        $messageId = $this->getRequest()->getParam('id', 0);

        $select = $this->_messaging->getTable()->select()
            ->where('id = ?', $messageId);

        if (!$inAdmin) {
            $select->where("sender_id = '{$this->_user['id']}' OR receiver_id = '{$this->_user['id']}'");
        }

        /** @var \Ppb\Db\Table\Row\Message $message */
        $message = $this->_messaging->fetchAll($select)->getRow(0);

        if (!$message) {
            $this->_flashMessenger->setMessage(array(
                'msg'   => $this->_('The selected topic doesnt exist or you are not allowed to view it.'),
                'class' => 'alert-danger',
            ));

            $this->_helper->redirector()->redirect('browse');
        }

        if (!empty($message['sale_id'])) {
            $sale = $message->findParentRow('\Ppb\Db\Table\Sales');
        }

        if (isset($message['topic_id'])) {
            $this->_messaging->getTable()
                ->update(array(
                    'flag_read' => 1,
                ), "topic_id = '{$message['topic_id']}' AND receiver_id='{$this->_user['id']}'");
        }

        return array(
            'headline'   => $message->getTopicTitle(),
            'controller' => ($inAdmin) ? 'Tools' : 'Messages',
            'inAdmin'    => $inAdmin,
            'messages'   => $this->_flashMessenger->getMessages(),
            'message'    => $message,
            'sale'       => $sale,
        );
    }
}

