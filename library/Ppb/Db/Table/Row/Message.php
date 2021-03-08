<?php

/**
 *
 * PHP Pro Bid
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2019 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     8.1 [rev.8.1.02]
 */

/**
 * messaging table row object model
 */

namespace Ppb\Db\Table\Row;

class Message extends AbstractRow
{

    /**
     *
     * returns an array used by the url view helper to generate the messaging topic display url
     *
     * @param bool $admin if in admin module, generate a different link
     *
     * @return array|false
     */
    public function link($admin = false)
    {
        $user = $this->getUser();

        if ($admin) {
            return array(
                'module'     => 'admin',
                'controller' => 'tools',
                'action'     => 'messaging-topic',
                'id'         => $this->getData('id'),
            );
        }

        if (in_array($user['id'], array($this->getData('sender_id'), $this->getData('receiver_id')))) {
            return array(
                'module'     => 'members',
                'controller' => 'messaging',
                'action'     => 'topic',
                'id'         => $this->getData('id'),
            );
        }

        return false;
    }

    /**
     *
     * return the topic of the message
     *
     * @return \Ppb\Db\Table\Row\Message
     */
    public function getTopic()
    {
        /** @var \Ppb\Db\Table\Row\Message $topic */
        $topic = $this->findParentRow('\Ppb\Db\Table\Messaging');

        return $topic;
    }

    /**
     *
     * get topic title
     *
     * @param bool $process
     *
     * @return array|string
     */
    public function getTopicTitle($process = true)
    {
        $topicTitle = $this->getTopic()->getData('topic_title');

        $topicTitle = \Ppb\Utility::unserialize($topicTitle);

        if ($process === false) {
            return $topicTitle;
        }

        if (is_array($topicTitle)) {
            $translate = $this->getTranslate();

            $string = (isset($topicTitle['msg'])) ? $topicTitle['msg'] : '';
            $args = (isset($topicTitle['args'])) ? $topicTitle['args'] : '';

            $string = (null !== $translate) ? $translate->_($string) : $string;

            $topicTitle = vsprintf($string, $args);
        }

        return $topicTitle;
    }

    /**
     *
     * get message recipients
     * will retrieve all parties in the conversation excepting the sender of the selected message
     *
     * @return array
     */
    public function getRecipients()
    {
        $recipients = array();

        $sender = $this->findParentRow('\Ppb\Db\Table\Users', 'Sender');
        $senderId = ($sender !== null) ? $sender->getData('id') : null;

        $messages = $this->findDependentRowset('\Ppb\Db\Table\Messaging');

        $ruleKeys = array('Sender', 'Receiver');

        /** @var \Ppb\Db\Table\Row\Message $message */
        foreach ($messages as $message) {
            foreach ($ruleKeys as $ruleKey) {
                $recipient = $message->findParentRow('\Ppb\Db\Table\Users', $ruleKey);
                $recipientId = ($recipient !== null) ? $recipient->getData('id') : null;

                if ($recipientId !== null && $recipientId !== $senderId && !in_array($recipient, $recipients)) {
                    array_push($recipients, $recipient);
                }
            }
        }

        return $recipients;
    }
}

