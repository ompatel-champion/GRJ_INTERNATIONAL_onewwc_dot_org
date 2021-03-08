<?php

/**
 *
 * PHP Pro Bid
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2018 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     8.0 [rev.8.0.02]
 */
/**
 * listing owner notifications emails generator class
 */

namespace Listings\Model\Mail;

use Ppb\Model\BaseMail,
    Ppb\Db\Table\Row\Listing as ListingModel,
    Ppb\Db\Table\Row\Bid as BidModel,
    Ppb\Db\Table\Row\User as UserModel;

class BuyerNotification extends BaseMail
{

    /**
     *
     * outbid bidder notification
     *
     * @param BidModel     $bid
     *
     * @return $this
     */
    public function outbid(BidModel $bid)
    {
        $user = $bid->findParentRow('\Ppb\Db\Table\Users');

        $listing = $bid->findParentRow('\Ppb\Db\Table\Listings');

        $this->setData(array(
            'listing' => $listing,
            'bid'     => $bid,
            'link'    => array('module' => 'members', 'controller' => 'buying', 'action' => 'bids'),
        ));

        $translate = $this->_mail->getTranslate();

        $this->_mail->setFrom($this->_settings['admin_email'], $this->_settings['email_admin_title'])
            ->setTo($user->getData('email'))
            ->setSubject(
                sprintf($translate->_('%s - Outbid Notification'), $listing['name']));

        $this->_view->headerMessage = $this->_('Outbid Notification');
        $this->_view->clearContent()
            ->process('emails/buyer-outbid.phtml');

        return $this;
    }

    /**
     *
     *
     * @param ListingModel $listing
     * @param UserModel    $user
     * @param string       $email
     * @param string       $message
     *
     * @return $this
     */
    public function emailFriend(ListingModel $listing, UserModel $user, $email, $message)
    {
        $this->setData(array(
            'listing' => $listing,
            'user'    => $user,
            'message' => nl2br($message),
        ));

        $translate = $this->_mail->getTranslate();

        $this->_mail->setFrom($this->_settings['admin_email'], $this->_settings['email_admin_title'])
            ->setReplyTo($user->getData('email'))
            ->setTo($email)
            ->setSubject(
                sprintf($translate->_('%s has sent you this listing from %s'), $user->getData('username'), $this->_settings['sitename']));

        $this->_view->headerMessage = $this->_('Check Out this Listing');
        $this->_view->clearContent()
            ->process('emails/email-friend.phtml');

        return $this;
    }
}