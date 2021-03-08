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
 * blocked user table row object model
 */

namespace Ppb\Db\Table\Row;

class BlockedUser extends AbstractRow
{
    /**
     * types of variables/values accepted for blocking
     */
    const TYPE_IP = 'ip';
    const TYPE_EMAIL = 'email';
    const TYPE_USERNAME = 'username';

    /**
     * types of actions that can be blocked
     */
    const ACTION_REGISTER = 'register';
    const ACTION_PURCHASE = 'purchase';
    const ACTION_MESSAGING = 'messaging';

    /**
     *
     * block types
     *
     * @var array
     */
    public static $blockTypes = array(
        self::TYPE_IP       => 'IP Address',
        self::TYPE_EMAIL    => 'Email Address',
        self::TYPE_USERNAME => 'Username',
    );

    /**
     *
     * blocked actions
     *
     * @var array
     */
    public static $blockedActions = array(
        self::ACTION_REGISTER  => 'Registering / Logging-in',
        self::ACTION_PURCHASE  => 'Purchasing',
        self::ACTION_MESSAGING => 'Messaging',
    );

    /**
     *
     * get blocked actions as an array
     * or for display purposes
     *
     * @param bool|string $separator
     *
     * @return array
     */
    public function getBlockedActions($separator = false)
    {
        $blockedActions = array_filter(\Ppb\Utility::unserialize($this->getData('blocked_actions'), array()));

        if ($separator !== false) {
            $output = array();
            $translate = $this->getTranslate();

            foreach ($blockedActions as $blockedAction) {
                $output[] = $translate->_(self::$blockedActions[$blockedAction]);
            }

            return implode($separator, $output);
        }

        return $blockedActions;
    }

    /**
     *
     * return the block message that the blocked user sees
     *
     * @return string
     */
    public function blockMessage()
    {
        $translate = $this->getTranslate();

        $blocker = $this->findParentRow('\Ppb\Db\Table\Users');

        $type = $this->getData('type');
        $value = $this->getData('value');
        $blockReason = $this->getData('block_reason');
        $showReason = $this->getData('show_reason');

        $message = sprintf(
            $translate->_('Your %s (%s) has been blocked from %s by %s.'),
            self::$blockTypes[$type],
            $value,
            $this->getBlockedActions(', '),
            ($blocker) ? $blocker['username'] : $translate->_('the administrator'));

        if ($showReason && !empty($blockReason)) {
            $message .= '<br>' . sprintf($translate->_('Block Reason: %s'), $blockReason);
        }

        return $message;
    }
}

