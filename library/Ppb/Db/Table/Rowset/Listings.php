<?php

/**
 *
 * PHP Pro Bid
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2020 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     8.2 [rev.8.2.01]
 */

/**
 * listings table rowset class
 */

namespace Ppb\Db\Table\Rowset;

use Cube\Db\Expr,
    Ppb\Service;

class Listings extends AbstractStatus
{

    /**
     * email notifications keys
     */
    const EMAIL_CLOSED = 'listingsClosed';
    const EMAIL_SUSPENDED = 'listingsSuspended';
    const EMAIL_RELISTED = 'listingsRelisted';
    const EMAIL_APPROVED = 'listingsApproved';

    /**
     *
     * row object class
     *
     * @var string
     */
    protected $_rowClass = '\Ppb\Db\Table\Row\Listing';

    /**
     *
     * listings service
     *
     * @var \Ppb\Service\Listings
     */
    protected $_listings;

    /**
     *
     * users service
     *
     * @var \Ppb\Service\Users
     */
    protected $_users;

    /**
     *
     * get listings service
     *
     * @return \Ppb\Service\Listings
     */
    public function getListings()
    {
        if (!$this->_listings instanceof Service\Listings) {
            $this->setListings(
                new Service\Listings());
        }

        return $this->_listings;
    }

    /**
     *
     * set listings service
     *
     * @param \Ppb\Service\Listings $listings
     *
     * @return $this
     */
    public function setListings(Service\Listings $listings)
    {
        $this->_listings = $listings;

        return $this;
    }

    /**
     *
     * get users service
     *
     * @return \Ppb\Service\Users
     */
    public function getUsers()
    {
        if (!$this->_users instanceof Service\Users) {
            $this->setUsers(
                new Service\Users());
        }

        return $this->_users;
    }

    /**
     *
     * set users service
     *
     * @param \Ppb\Service\Users $users
     *
     * @return $this
     */
    public function setUsers(Service\Users $users)
    {
        $this->_users = $users;

        return $this;
    }

    /**
     *
     * open listings from the selected rowset
     * only scheduled items can be opened, ended items can only be relisted
     *
     * @return $this
     */
    public function open()
    {
        $this->resetCounter();

        /** @var \Ppb\Db\Table\Row\Listing $listing */
        foreach ($this as $listing) {
            if (strtotime($listing['start_time']) > time() && $listing['closed'] == 1) {
                $params['closed'] = 0;
                $params['start_time'] = new Expr('now()');

                $listing->save($params);

                $this->incrementCounter();
            }
        }

        return $this;
    }

    /**
     *
     * close listings from the selected rowset
     * open items with end time > current time can be closed
     * send emails when listings have been closed (single email per user)
     *
     * 7.8: a listing can be closed if one of the flags: admin, automatic or canClose are true
     *
     * @return $this
     */
    public function close()
    {
        $this->resetCounter();

        $emails = array();

        /** @var \Ppb\Db\Table\Row\Listing $listing */
        foreach ($this as $listing) {
            if ($listing->canClose() || $this->_admin || $this->_automatic) {
                $listing->close($this->_automatic);

                if ($listing->getClosedFlag() === true) {
                    $emails[$listing['user_id']][] = $listing;
                }

                $this->incrementCounter();
            }
            else {
                $translate = $this->getTranslate();
                $message = sprintf($translate->_('Listing ID: #%s cannot be closed.'), $listing['id']);
                $this->addMessage($message);
            }
        }

        return $this;
    }

    /**
     *
     * relist listings from the selected rowset
     * closed ended items can be relisted
     * send emails when listings have been relisted (single email per user)
     *
     * @return $this
     */
    public function relist()
    {
        $usersService = $this->getUsers();
        $listingsService = $this->getListings();

        $inAdmin = $this->getAdmin();
        $autoRelist = $this->getAutomatic();

        $this->resetCounter();

        $emails = array();

        /** @var \Ppb\Db\Table\Row\Listing $listing */
        foreach ($this as $listing) {
            /** @var \Ppb\Db\Table\Row\User $seller */
            $seller = $listing->getOwner();

            if (($seller->canList() || $inAdmin) && (!$autoRelist || $listing->isAutoRelistPending())) {
                $listingId = $listing->relist($autoRelist);

                $newListing = $listingsService->findBy('id', $listingId, false, true);
                if (!$inAdmin) {
                    $message = $newListing->processPostSetupActions();
                    $this->addMessage($message);
                }
                else {
                    $newListing->updateActive();
                    $newListing->save(array(
                        'approved' => 1,
                    ));
                }

                $emails[$listing['user_id']][] = $newListing;

                $this->incrementCounter();
            }

            $listing->save(array(
                'auto_relist_pending' => 0
            ));
        }

        // send email notifications to listings owners
        $mail = new \Listings\Model\Mail\OwnerNotification();

        foreach ($emails as $userId => $listings) {
            $user = $usersService->findBy('id', $userId);

            $mail->setUser($user)
                ->setListings($listings)
                ->listingsRelisted()
                ->send();
        }

        return $this;
    }

    /**
     *
     * set auto relist pending flags
     *
     * @param bool $force
     *
     * @return $this
     */
    public function setAutoRelistPendingFlags()
    {
        /** @var \Ppb\Db\Table\Row\Listing $listing */
        foreach ($this as $listing) {
            $listing->setAutoRelistPendingFlag();
        }

        return $this;
    }

    /**
     *
     * list selected drafts / bulk items - it will work like relisting same
     *
     * @return $this
     */
    public function draftsList()
    {
        $listingsService = $this->getListings();

        $this->resetCounter();

        /** @var \Ppb\Db\Table\Row\Listing $listing */
        foreach ($this as $listing) {
            $listingId = $listing->setRelistMethod('same')
                ->relist();

            $newListing = $listingsService->findBy('id', $listingId, false, true);

            $newListing->save(array(
                'draft'    => 0,
                'active'   => 0,
                'approved' => 0,
            ));

            if (!$this->_admin) {
                $message = $newListing->processPostSetupActions();
                $this->addMessage($message);
            }
            else {
                $newListing->updateActive();
                $newListing->save(array(
                    'approved' => 1,
                ));
            }

            $this->incrementCounter();
        }

        return $this;
    }

    /**
     *
     * activate listings from the selected rowset
     * only suspended & approved items can be activated
     *
     * @return $this
     */
    public function activate()
    {
        $this->resetCounter();

        /** @var \Ppb\Db\Table\Row\Listing $listing */
        foreach ($this as $listing) {
            if ($listing['active'] != 1 && $listing['approved'] == 1) {
                $listing->updateActive(1);

                $this->incrementCounter();
            }
        }

        return $this;
    }

    /**
     *
     * approve listings from the selected rowset
     * unapproved items can be approved
     * send emails when listings are approved by admin (single email per user)
     *
     * @return $this
     */
    public function approve()
    {
        $usersService = $this->getUsers();

        $this->resetCounter();

        $emails = array();

        /** @var \Ppb\Db\Table\Row\Listing $listing */
        foreach ($this as $listing) {
            if ($listing['approved'] == 0) {
                $listing->updateApproved(1);
                $emails[$listing['user_id']][] = $listing;

                $this->incrementCounter();
            }
        }

        // send email notifications to listings owners
        $mail = new \Listings\Model\Mail\OwnerNotification();

        foreach ($emails as $userId => $listings) {
            $user = $usersService->findBy('id', $userId);

            $mail->setUser($user)
                ->setListings($listings)
                ->listingsApproved()
                ->send();
        }

        return $this;
    }

    /**
     *
     * suspend listings from the selected rowset
     * active items can be suspended
     * send emails when listings are suspended by admin (single email per user)
     *
     * @return $this
     */
    public function suspend()
    {
        $usersService = $this->getUsers();

        $this->resetCounter();

        $emails = array();

        /** @var \Ppb\Db\Table\Row\Listing $listing */
        foreach ($this as $listing) {
            if ($listing['active'] == 1 && $listing['approved'] == 1) {
                $listing->updateActive(-1);

                if ($this->_admin) {
                    $emails[$listing['user_id']][] = $listing;
                }

                $this->incrementCounter();
            }
        }

        // send email notifications to listings owners
        $mail = new \Listings\Model\Mail\OwnerNotification();

        foreach ($emails as $userId => $listings) {
            $user = $usersService->findBy('id', $userId);

            $mail->setUser($user)
                ->setListings($listings)
                ->listingsSuspended()
                ->send();
        }

        return $this;
    }

    /**
     *
     * remove marked deleted status from marked deleted items
     *
     * @return $this
     */
    public function undelete()
    {
        $this->save(array(
            'deleted' => 0,
        ));

        $this->setCounter(
            $this->count());

        return $this;
    }

    /**
     *
     * delete all rows from the rowset individually
     * mark deleted (if user) or delete (if admin) any item
     *
     * 7.8: a listing can be closed if one of the flags: admin, automatic or canClose are true
     *
     * @return $this
     */
    public function delete()
    {
        $this->resetCounter();

        /** @var \Ppb\Db\Table\Row\Listing $listing */
        foreach ($this as $listing) {
            if ($listing->canDelete() || $this->_admin || $this->_automatic) {
                $listing->delete($this->_admin);

                $this->incrementCounter();
            }
            else {
                $translate = $this->getTranslate();
                $message = sprintf($translate->_('Listing ID: #%s cannot be deleted.'), $listing['id']);
                $this->addMessage($message);
            }
        }

        return $this;
    }

}

