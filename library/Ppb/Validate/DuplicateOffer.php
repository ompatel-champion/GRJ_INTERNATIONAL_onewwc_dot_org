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
 * duplicate offer validator class
 */

namespace Ppb\Validate;

use Cube\Validate\AbstractValidate,
    Cube\Controller\Front,
    Ppb\Service,
    Ppb\Db\Table\Row\Offer as OfferModel;

class DuplicateOffer extends AbstractValidate
{

    protected $_message = "You have already posted an offer with this amount.";

    /**
     *
     * listing id
     *
     * @var int
     */
    protected $_listingId;

    /**
     *
     * logged in user id
     *
     * @var int
     */
    protected $_userId;

    /**
     *
     * class constructor
     *
     * initialize the minimum value allowed and the equal check
     *
     * @param array $data data[0] -> listing id;
     *                    data[1] -> user id;
     */
    public function __construct(array $data = null)
    {
        if (isset($data[0])) {
            $this->setListingId($data[0]);
        }

        if (isset($data[1])) {
            $this->setUserId($data[1]);
        }
    }

    /**
     *
     * get listing id
     *
     * @return int
     */
    public function getListingId()
    {
        return $this->_listingId;
    }

    /**
     *
     * set listing id
     *
     * @param int $listingId
     *
     * @return $this
     */
    public function setListingId($listingId)
    {
        $this->_listingId = $listingId;

        return $this;
    }

    /**
     *
     * get user id
     *
     * @return int
     */
    public function getUserId()
    {
        return $this->_userId;
    }

    /**
     *
     * set user id
     *
     * @param int $userId
     *
     * @return $this
     */
    public function setUserId($userId)
    {
        $this->_userId = $userId;

        return $this;
    }

    /**
     *
     * checks if the offer has been posted before
     *
     * @return bool          return true if the offer is unique
     */
    public function isValid()
    {
        $request = Front::getInstance()->getRequest();

        $offersService = new Service\Offers();

        $listingId = $this->getListingId();
        $userId = $this->getUserId();

        if (!$listingId) {
            return false;
        }

        if (!$userId) {
            return false;
        }

        $quantity = $request->getParam('quantity');
        $quantity = ($quantity <= 1) ? 1 : $quantity;

        $select = $offersService->getTable()->select()
            ->where('listing_id = ?', $listingId)
            ->where('user_id = ? OR receiver_id = ?', $userId)
            ->where('quantity = ?', $quantity)
            ->where('amount = ?', $this->getValue())
            ->where('status != ?', OfferModel::STATUS_WITHDRAWN);

        $productAttributes = $request->getParam('product_attributes');

        if (!empty($productAttributes)) {
            $select->where('product_attributes = ?', serialize($productAttributes));
        }
        else {
            $select->where('product_attributes is null');
        }

        $rowset = $offersService->fetchAll($select);

        if (count($rowset) == 0) {
            return true;
        }

        return false;
    }

}

