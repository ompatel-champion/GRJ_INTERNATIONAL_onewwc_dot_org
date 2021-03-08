<?php

/**
 *
 * PHP Pro Bid
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2020 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     8.2 [rev.8.2.04]
 */

/**
 * bids table service class
 */

namespace Ppb\Service;

use Ppb\Db\Table\Bids as BidsTable,
    Ppb\Service,
    Cube\Db\Expr,
    Cube\Controller\Front;

class Bids extends AbstractService
{

    /**
     *
     * bid increments table service
     *
     * @var \Ppb\Service\Table\BidIncrements
     */
    protected $_bidIncrements;

    /**
     *
     * the message output after a bid is placed
     * array(msg, class)
     *
     * @var array
     */
    protected $_message;

    /**
     *
     * class constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->setTable(
            new BidsTable());
    }

    /**
     *
     * get bid increments table service
     *
     * @return \Ppb\Service\Table\BidIncrements
     */
    public function getBidIncrements()
    {
        if (!$this->_bidIncrements instanceof Service\Table\BidIncrements) {
            $this->setBidIncrements(
                new Service\Table\BidIncrements());
        }

        return $this->_bidIncrements;
    }

    /**
     *
     * set bid increments table service
     *
     * @param \Ppb\Service\Table\BidIncrements $bidIncrements
     *
     * @return $this
     */
    public function setBidIncrements(Service\Table\BidIncrements $bidIncrements)
    {
        $this->_bidIncrements = $bidIncrements;

        return $this;
    }

    /**
     *
     * set bid post output message
     *
     * @param string $message
     * @param string $class
     *
     * @return $this
     */
    public function setMessage($message, $class = 'alert-success')
    {
        $this->_message = array(
            'msg'   => $message,
            'class' => $class,
        );

        return $this;
    }

    /**
     *
     * get bid post output message
     *
     * @return array
     */
    public function getMessage()
    {
        return $this->_message;
    }

    /**
     *
     * process that posts a new bid on an auction
     * the listing id will always result from a "select for update" query, so that two bids cannot be posted at the same time.
     *
     * @8.0 the sniping feature will not come into effect when updating the proxy bid amount
     * or when a bidder has placed a bid which is below the proxy bid amount
     *
     * @param array $data
     *
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function save($data)
    {
        if (!isset($data['listing_id'])) {
            throw new \InvalidArgumentException("No listing selected for the post bid operation.");
        }

        $extendAuctionDuration = true;

        $listingsService = new Service\Listings();
        /** @var \Ppb\Db\Table\Row\Listing $listing */
        $listing = $listingsService->findBy('id', $data['listing_id']);

        $view = Front::getInstance()->getBootstrap()->getResource('view');

        $settings = $this->getSettings();

        $translate = $this->getTranslate();

        if ($listing->countDependentRowset('\Ppb\Db\Table\Bids') > 0) {
            $bids = $listing->getBids();

            /** @var \Ppb\Db\Table\Row\Bid $hBid */
            $hBid = $bids->getRow(0);
            $highBid = $hBid->toArray();

            if ($listing->isProxyBidding() &&
                $data['amount'] > $listing['reserve_price'] &&
                $highBid['amount'] >= $listing['reserve_price'] &&
                $highBid['user_id'] == $data['user_id']
            ) { // update maximum bid
                $adapter = $this->_table->getAdapter();
                $this->_table->update(
                    array(
                        'maximum_bid' => $data['amount'],
                        'updated_at'  => new Expr('now()')
                    ),
                    array(
                        $adapter->quoteInto('listing_id = ?', $listing['id']),
                        $adapter->quoteInto('user_id = ?', $data['user_id'])
                    ));

                $hBid->save(array(
                    'outbid' => 0,
                ));

                $this->setMessage(
                    sprintf($translate->_('Your maximum bid has been updated to %s.'),
                        $view->amount($data['amount'], $listing['currency'])),
                    'alert-info');

                $extendAuctionDuration = false;
            }
            else {
                // set to outbid all previous bids
                $this->_setOutbidBids($listing['id']);
                unset($highBid['id']);

                if ($data['amount'] > $highBid['maximum_bid']) {
                    if ($highBid['maximum_bid'] > $highBid['amount']) {
                        $highBid['amount'] = $highBid['maximum_bid'];
                        $this->_saveBid($highBid);
                    }

                    $this->_setOutbidBids($listing['id']);

                    $data['maximum_bid'] = $data['amount'];
                    $data['amount'] = $listing->minimumBid($data['amount']);
                    $this->_saveBid($data);

                    if ($highBid['user_id'] != $data['user_id']) {
                        // MAIL OUTBID BIDDER NOTIFICATION
                        $mail = new \Listings\Model\Mail\BuyerNotification();
                        $mail->outbid($hBid)->send();
                    }

                    $message = sprintf($translate->_('Your maximum bid, in the amount of %s, has been posted successfully. You are now the highest bidder on this item, with a bid of %s.'),
                        $view->amount($data['maximum_bid'], $listing['currency']),
                        $view->amount(min(array($data['amount'], $data['maximum_bid'])), $listing['currency']));

                    $class = 'alert-success';

                    if ($data['maximum_bid'] < $listing['reserve_price']) {
                        $message .= '<hr>'
                            . $translate->_('This auction has a reserve. Your bid did not meet the minimum reserve requirement.');
                        $class = 'alert-warning';
                    }

                    $this->setMessage($message, $class);
                }
                else {
                    $data['maximum_bid'] = $data['amount'];
                    $this->_saveBid($data);

                    $this->_setOutbidBids($listing['id']);

                    $highBid['amount'] = $listing->minimumBid($highBid['amount']);
                    $this->_saveBid($highBid);

                    $this->setMessage(
                        sprintf($translate->_('Your maximum bid, in the amount of %s, has been posted, but is lower than the current high bidder\'s maximum bid.'),
                            $view->amount($data['maximum_bid'], $listing['currency'])),
                        'alert-danger');

                    $extendAuctionDuration = false;
                }
            }
        }
        else {
            $data['maximum_bid'] = $data['amount'];
            $data['amount'] = $listing->minimumBid($data['amount']);

            $this->_saveBid($data);

            $message= sprintf($translate->_('Your maximum bid, in the amount of %s, has been posted successfully. You are now the highest bidder on this item, with a bid of %s.'),
                $view->amount($data['maximum_bid'], $listing['currency']),
                $view->amount($data['amount'], $listing['currency']));

            $class = 'alert-success';

            if ($data['maximum_bid'] < $listing['reserve_price']) {
                $message .= '<hr>'
                    . $translate->_('This auction has a reserve. Your bid did not meet the minimum reserve requirement.');
                $class = 'alert-warning';
            }

            $this->setMessage($message, $class);
        }

        if ($settings['enable_auctions_sniping'] && $extendAuctionDuration) {
            if ($listing->getTimeLeft() < $settings['auctions_sniping_minutes'] * 60) {
                $listing->save(array(
                    'end_time' => new Expr('now() + interval ' . intval($settings['auctions_sniping_minutes']) . ' minute'),
                ));
            }
        }

        if ($settings['enable_change_duration'] && $settings['change_duration_days'] > 0) {
            if ($listing->getTimeLeft() > $settings['change_duration_days'] * 86400) {
                $listing->save(array(
                    'end_time' => new Expr('now() + interval ' . intval($settings['change_duration_days']) . ' day'),
                ));
            }
        }

        return $this;
    }

    /**
     *
     * insert a prepared bid row into the bids table
     *
     * @param array $data
     *
     * @return $this
     */
    protected function _saveBid(array $data)
    {
        $row = null;

        $table = $this->getTable();

        $data = $this->_prepareSaveData($data);

        if (array_key_exists('id', $data)) {
            $select = $table->select()
                ->where("id = ?", $data['id']);

            unset($data['id']);

            $row = $table->fetchRow($select);
        }

        if ($row !== null) {
            $data['updated_at'] = new Expr('now()');
            $table->update($data,
                $table->getAdapter()->quoteInto('id = ?', $row['id']));
        }
        else {
            $data['created_at'] = new Expr('now()');
            $table->insert($data);
        }

        return $this;
    }

    /**
     *
     * set outbid flag to certain bids
     *
     * @param int $listingId
     *
     * @return $this
     */
    protected function _setOutbidBids($listingId)
    {
        $table = $this->getTable();

        $table->update(
            array(
                'outbid'     => 1,
                'updated_at' => new Expr('now()')
            ),
            $table->getAdapter()->quoteInto('listing_id = ?', intval($listingId)));

        return $this;
    }

    /**
     *
     * prepare custom field data for when saving to the table
     *
     * @param array $data
     *
     * @return array
     */
    protected function _prepareSaveData($data = array())
    {
        if (array_key_exists('amount', $data) &&
            array_key_exists('maximum_bid', $data)
        ) {
            if ($data['amount'] > $data['maximum_bid']) {
                $data['amount'] = $data['maximum_bid'];
            }
        }

        $data = parent::_prepareSaveData($data);

        return $data;
    }

}

