<?php

/**
 *
 * PHP Pro Bid
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2019 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     8.1 [rev.8.1.01]
 */

/**
 * listing setup fee class
 */
/**
 * MOD:- ADVANCED CLASSIFIEDS
 */

namespace Ppb\Service\Fees;

use Ppb\Service,
    Ppb\Db\Table\Row\Listing as ListingModel,
    Ppb\Db\Table\Row\Voucher;

class ListingSetup extends Service\Fees
{

    /**
     *
     * standard fees
     *
     * @var array
     */
    protected $_fees = array(
        self::SETUP             => 'Listing Setup',
        ## -- REMOVE 1L -- [ MOD:- ADVANCED CLASSIFIEDS ]
//        self::CLASSIFIED_SETUP  => 'Classified Setup',
        self::HPFEAT            => 'Home Page Featuring',
        self::CATFEAT           => 'Category Pages Featuring',
        self::HIGHLIGHTED       => 'Highlighted Item',
        self::SHORT_DESCRIPTION => 'Short Description',
        self::IMAGES            => 'Listing Images',
        self::MEDIA             => 'Listing Media',
        self::DIGITAL_DOWNLOADS => 'Digital Downloads',
        self::ADDL_CATEGORY     => 'Additional Category Listing',
        self::BUYOUT            => 'Buy Out',
        self::RESERVE           => 'Reserve Price',
        self::MAKE_OFFER        => 'Make Offer',
        self::ITEM_SWAP         => 'Item Swap'
    );

    /**
     *
     * media upload fees, with corresponding keys for files that can be uploaded free of charge
     *
     * @var array
     */
    protected $_mediaFees = array(
        self::IMAGES            => self::NB_FREE_IMAGES,
        self::MEDIA             => self::NB_FREE_MEDIA,
        self::DIGITAL_DOWNLOADS => self::NB_FREE_DOWNLOADS,
    );

    /**
     *
     * listing object
     *
     * @var \Ppb\Db\Table\Row\Listing
     */
    protected $_listing;

    /**
     *
     * saved listing object (used for when editing a listing)
     *
     * @var \Ppb\Db\Table\Row\Listing
     */
    protected $_savedListing;

    /**
     *
     * total amount to be paid after the calculate method is called
     *
     * @var float
     */
    protected $_totalAmount;

    /**
     *
     * payment redirect path
     *
     * @var array
     */
    protected $_redirect = array(
        'module'     => 'listings',
        'controller' => 'listing',
        'action'     => 'confirm',

    );

    /**
     *
     * class constructor
     *
     * @param \Ppb\Db\Table\Row\Listing             $listing listing object
     * @param integer|string|\Ppb\Db\Table\Row\User $user    the user that will be paying
     */
    public function __construct(ListingModel $listing = null, $user = null)
    {
        parent::__construct();

        $settings = $this->getSettings();

        $feesSettings = array(
            Service\Fees::ADDL_CATEGORY     => $settings['addl_category_listing'],
            Service\Fees::BUYOUT            => $settings['enable_buyout'],
            Service\Fees::RESERVE           => $settings['enable_auctions'],
            Service\Fees::DIGITAL_DOWNLOADS => $settings['digital_downloads_max'],
            Service\Fees::MAKE_OFFER        => $settings['enable_make_offer'],
            Service\Fees::IMAGES            => $settings['images_max'],
            Service\Fees::MEDIA             => $settings['videos_max'],
            Service\Fees::SHORT_DESCRIPTION => $settings['enable_short_description'],
        );

        foreach ($this->_fees as $feeName => $feeDescription) {
            if (array_key_exists($feeName, $feesSettings) && ($feesSettings[$feeName] == 0)) {
                unset($this->_fees[$feeName]);
            }
        }

        if ($listing !== null) {
            $amount = ($listing->isProduct() || $listing->isClassified()) ?
                $listing['buyout_price'] : max(array($listing['start_price'], $listing['reserve_price']));

            $this->setListing($listing)
                ->setAmount($amount)
                ->setCategoryId($listing['category_id'])
                ->setCurrency($listing['currency']);
        }

        if ($user !== null) {
            $this->setUser($user);
        }
    }

    /**
     *
     * set listing model
     *
     * @param \Ppb\Db\Table\Row\Listing $listing
     *
     * @return \Ppb\Service\Fees\ListingSetup
     */
    public function setListing(ListingModel $listing)
    {
        $this->_listing = $listing;

        return $this;
    }

    /**
     *
     * set saved listing model
     *
     * @param \Ppb\Db\Table\Row\Listing $savedListing
     *
     * @return \Ppb\Service\Fees\ListingSetup
     */
    public function setSavedListing(ListingModel $savedListing)
    {
        $this->_savedListing = $savedListing;

        return $this;
    }


    /**
     *
     * calculate and return an array containing all fees to be applied when creating a listing
     * also apply the voucher and add it as a separate row
     *
     * @return array
     */
    public function calculate()
    {
        $data = array();
        $this->_totalAmount = 0;

        $settings = $this->getSettings();

        foreach ($this->_fees as $key => $value) {

            if ($this->_applyFee($key)) {
                $feeAmount = $this->getFeeAmount($key);

                if ($feeAmount > 0 || $settings['display_free_fees']) {
                    $data[] = array(
                        'key'      => $key,
                        'name'     => $value,
                        'amount'   => $feeAmount,
                        'tax_rate' => $this->getTaxType()->getData('amount'),
                        'currency' => $settings['currency'],
                    );
                }

                $this->_totalAmount += $feeAmount;
            }
        }

        if (($voucher = $this->getVoucher()) instanceof Voucher) {
            $totalAmount = $this->_applyVoucher($this->_totalAmount, $settings['currency']);

            if ($totalAmount != $this->_totalAmount) {
                $voucherAmount = $totalAmount - $this->_totalAmount;
                $data[] = array(
                    'key'      => 'voucher_reduction',
                    'name'     => $voucher->description(),
                    'amount'   => $voucherAmount,
                    'tax_rate' => $this->getTaxType()->getData('amount'),
                    'currency' => $settings['currency'],
                );

                $this->_totalAmount = $totalAmount;
            }
        }

        return $data;
    }

    /**
     *
     * get redirect array, but attach listing_id variable if it is set
     *
     * @return array
     */
    public function getRedirect()
    {
        $redirect = $this->_redirect;
        if (!empty($this->_transactionDetails['data']['listing_id'])) {
            $redirect['params']['id'] = $this->_transactionDetails['data']['listing_id'];
        }

        return $redirect;
    }

    /**
     *
     * activate the affected listing
     * the callback will also process the listing in case a payment has been reversed etc.
     *
     * @param bool  $ipn  true if payment is completed, false otherwise
     * @param array $post array keys: {listing_id}
     *
     * @return \Ppb\Service\Fees\ListingSetup
     */
    public function callback($ipn, array $post)
    {
        $listingsService = new Service\Listings();
        $listing = $listingsService->findBy('id', $post['listing_id']);

        $flag = ($ipn) ? 1 : 0;
        $listing->updateActive($flag);

        return $this;
    }

    /**
     *
     * get the amount of a certain fee
     * calculates based on preferred seller feature
     * - in case of an image fee, calculate based on the number of images uploaded
     * (plus apply the free images setting as well)
     *
     * @param string $name
     * @param float  $amount
     * @param int    $categoryId
     * @param null   $currency
     *
     * @return float|null       return the fee amount or null if no fee applies for the selected action
     */
    public function getFeeAmount($name = null, $amount = null, $categoryId = null, $currency = null)
    {
        $feeAmount = parent::getFeeAmount($name, $amount, $categoryId, $currency);

        if (array_key_exists($name, $this->_mediaFees)) {
            $savedListingValue = (!empty($this->_savedListing[$name])) ? $this->_savedListing[$name] : null;
            $savedListingCategoryId = (!empty($this->_savedListing['category_id'])) ? $this->_savedListing['category_id'] : null;

            $counter = (is_numeric($this->_listing[$name])) ?
                intval($this->_listing[$name]) : count(array_filter((array)\Ppb\Utility::unserialize($this->_listing[$name])));

            $counterFree = intval($this->getFeeAmount($this->_mediaFees[$name]));
            $counterSaved = ($this->_listing['category_id'] == $savedListingCategoryId) ?
                count((array)\Ppb\Utility::unserialize($savedListingValue)) : 0;

            if ($counterSaved > $counterFree) {
                $counter -= $counterSaved;
            }
            else {
                $counter -= $counterFree;
            }

            $feeAmount *= ($counter <= 0) ? 0 : $counter;
        }

        return $feeAmount;
    }

    /**
     *
     * get total amount to be paid resulted from the calculate() method
     *
     * @return float
     */
    public function getTotalAmount()
    {
        return $this->_totalAmount;
    }

    /**
     *
     * check whether to apply the requested fee
     * do not apply any fees if the listing is a draft
     *
     * @param string $name the name of the fee
     *
     * @return bool
     */
    protected function _applyFee($name)
    {
        $savedListingValue = (!empty($this->_savedListing[$name])) ? $this->_savedListing[$name] : null;
        $savedListingCategoryId = (!empty($this->_savedListing['category_id'])) ? $this->_savedListing['category_id'] : null;

        if ($this->_listing['draft']) {
            return false;
        }
        else if ($name == self::CLASSIFIED_SETUP) {
            if ($this->_listing['listing_type'] == 'classified') {
                if ($this->_savedListing instanceof ListingModel) {
                    if ($this->_listing['listing_type'] == $this->_savedListing['listing_type']) {
                        return false;
                    }
                }

                return true;
            }

            return false;
        }
        else if (in_array($name, $this->_feesTiers) && (!$this->_savedListing instanceof ListingModel) && ($this->_listing['listing_type'] != 'classified')) {
            return true;
        }
        else if (array_key_exists($name, $this->_mediaFees)) {
            $counter = count(array_filter((array)$this->_listing[$name]));

            if ($this->_savedListing instanceof ListingModel) {
                $counterSavedListing = count(array_filter((array)$savedListingValue));

                if (
                    ($counter > $counterSavedListing) ||
                    ($this->_listing['category_id'] != $savedListingCategoryId)
                ) {
                    return true;
                }
            }
            else if ($counter > 0) {
                return true;
            }
        }
        else if (
            ($this->_listing[$name] && !$savedListingValue) ||
            ($this->_listing['category_id'] != $savedListingCategoryId)
        ) {
            $value = $this->_listing[$name];
            if (is_numeric($value)) {
                $value = floatval($value);
            }

            return (!empty($value)) ? true : false;
        }

        return false;
    }

}

