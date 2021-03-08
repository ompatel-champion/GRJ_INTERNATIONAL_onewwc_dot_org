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
 * listing details view helper class
 *
 * DEPRECATED [@version 8.0]
 */
/**
 * MOD:- EBAY IMPORTER
 * MOD:- PRODUCT BUNDLES
 */

namespace Listings\View\Helper;

use Ppb\View\Helper\AbstractHelper,
    Cube\Locale\Format as LocaleFormat,
    Ppb\Service,
    Ppb\Db\Table\Row\Listing,
    Ppb\Model\Shipping as ShippingModel,
    Ppb\Db\Table\Row\User as UserModel;

class ListingDetails extends AbstractHelper
{

    /**
     *
     * listing model
     *
     * @var \Ppb\Db\Table\Row\Listing
     */
    protected $_listing;

    /**
     *
     * main method, only returns object instance
     *
     * @param \Ppb\Db\Table\Row\Listing $listing
     *
     * @return $this
     */
    public function listingDetails(Listing $listing = null)
    {
        if ($listing !== null) {
            $this->setListing($listing);
        }

        return $this;
    }

    public function getListing()
    {
        if (!$this->_listing instanceof Listing) {
            throw new \InvalidArgumentException("The listing model has not been instantiated");
        }

        return $this->_listing;
    }

    /**
     *
     * set listing model
     *
     * @param \Ppb\Db\Table\Row\Listing $listing
     *
     * @throws \InvalidArgumentException
     * @return $this
     */
    public function setListing(Listing $listing)
    {
        if (!$listing instanceof Listing) {
            throw new \InvalidArgumentException("The listing model must be an instance of \Ppb\Db\Table\Row\Listing");
        }

        $this->_listing = $listing;

        return $this;
    }

    /**
     *
     * display listing location
     *
     * @param bool $detailed display state as well
     *
     * @return string
     */
    public function location($detailed = true)
    {
        $location = array();

        $listing = $this->getListing();
        $translate = $this->getTranslate();

        $country = $this->_getLocationName($listing->getData('country'));
        if ($country !== null) {
            $location[] = $translate->_($country);
        }

        if ($detailed === true) {
            $state = $this->_getLocationName($listing->getData('state'));
            if ($state !== null) {
                $location[] = $translate->_($state);
            }

            $address = $listing->getData('address');
            if ($address !== null) {
                $location[] = trim(preg_replace('/\s\s+/', ' ', $address));
            }
        }

        return (($output = implode(', ', array_reverse($location))) != '') ? $output : null;
    }

    /**
     *
     * list in badge
     *
     * @return string
     */
    public function listIn()
    {
        $output = array();

        $listing = $this->getListing();

        $translate = $this->getTranslate();

        switch ($listing['list_in']) {
            case 'site':
                $output[] = '<span class="badge badge-listin-site">' . $translate->_('Listed in Site') . '</span>';
                break;
            case 'store':
                $output[] = '<span class="badge badge-listin-store">' . $translate->_('Listed in Store') . '</span>';
                break;
            case 'both':
                $output[] = '<span class="badge badge-listin-both">' . $translate->_('Listed in Both') . '</span>';
                break;
        }

        return implode(' ', $output);
    }

    /**
     *
     * listing type badge
     *
     * @return string
     */
    public function listingType()
    {
        $output = array();

        $listing = $this->getListing();

        $translate = $this->getTranslate();

        switch ($listing['listing_type']) {
            case 'auction':
                $output[] = '<span class="badge badge-auction">' . $translate->_('Auction') . '</span>';
                break;
            case 'product':
                $output[] = '<span class="badge badge-product">' . $translate->_('Product') . '</span>';
                break;
            case 'classified':
                $output[] = '<span class="badge badge-classified">' . $translate->_('Classified') . '</span>';
                break;
        }

        return implode(' ', $output);
    }

    /**
     *
     * get number of bids message
     *
     * @return string
     */
    public function nbBids()
    {
        $listing = $this->getListing();

        if ($listing->isAuction()) {
            $translate = $this->getTranslate();

            return sprintf(
                $this->getView()->pluralize(
                    $count = $listing->countBids(),
                    $translate->_('%s bid'),
                    $translate->_('%s bids')
                ), $count);
        }

        return '';
    }

    /**
     *
     * get number of offers message
     *
     * @return string
     */
    public function nbOffers()
    {
        $listing = $this->getListing();

        if (($countOffers = $listing->countOffers()) > 0) {
            $translate = $this->getTranslate();

            return sprintf(
                $this->getView()->pluralize(
                    $countOffers,
                    $translate->_('%s offer'),
                    $translate->_('%s offers')
                ), $countOffers);
        }

        return '';
    }

    /**
     *
     * get number of sales message
     *
     * @return string
     */
    public function nbSales()
    {
        $listing = $this->getListing();

        if (($countSales = $listing->countSales()) > 0) {
            $translate = $this->getTranslate();

            if ($listing->isAuction()) {
                return $translate->_('Sold');
            }

            return sprintf(
                $this->getView()->pluralize(
                    $countSales,
                    $translate->_('%s sale'),
                    $translate->_('%s sales')
                ), $countSales);
        }

        return '';
    }

    /**
     *
     * display activity badges (bids, offers, sales)
     *
     * @return string
     */
    public function activity()
    {
        $output = array();

        if (($nbBids = $this->nbBids()) != '') {
            $output[] = '<span class="badge badge-activity-bids">' . $nbBids . '</span>';
        }

        if (($nbOffers = $this->nbOffers()) != '') {
            $output[] = '<span class="badge badge-activity-offers">' . $nbOffers . '</span>';
        }

        if (($nbSales = $this->nbSales()) != '') {
            $output[] = '<span class="badge badge-activity-sales">' . $nbSales . '</span>';
        }

        return implode(' ', $output);
    }
    
    /**
     *
     * display listing status
     *
     * @param bool $detailed
     *
     * @return string
     */
    public function status($detailed = true)
    {
        $output = array();

        $listing = $this->getListing();
        $translate = $this->getTranslate();

        if ($listing['draft']) {
            ## -- START :: CHANGE -- [ MOD:- EBAY IMPORTER ]
            $output[] = '<span class="badge badge-draft">' . $translate->_('Draft / Bulk') . '</span>';
            ## -- END :: CHANGE -- [ MOD:- EBAY IMPORTER ]
        }
        else {
            if ($detailed === true) {
                $output[] = $this->listIn();

                if ($listing['hpfeat']) {
                    $output[] = '<span class="badge badge-homepage-featured">' . $translate->_('Homepage Featured') . '</span>';
                }

                if ($listing['catfeat']) {
                    $output[] = '<span class="badge badge-category-featured">' . $translate->_('Category Featured') . '</span>';
                }

                if (!$listing['approved']) {
                    $output[] = '<span class="badge badge-orange">' . $translate->_('Unapproved') . '</span>';
                }
                else if ($listing['active'] == -1) {
                    $output[] = '<span class="badge badge-danger">' . $translate->_('Admin Suspended') . '</span>';
                }
                else if ($listing['active'] == 0) {
                    $output[] = '<span class="badge badge-warning">' . $translate->_('Suspended') . '</span>';
                }
                else {
                    $output[] = '<span class="badge badge-success">' . $translate->_('Active') . '</span>';
                }
            }

            switch ($listing->getStatus()) {
                case Listing::SCHEDULED:
                    $output[] = '<span class="badge badge-scheduled">' . $translate->_('Scheduled') . '</span>';
                    break;
                case Listing::CLOSED:
                    $output[] = '<span class="badge badge-closed">' . $translate->_('Closed') . '</span>';
                    break;
                case Listing::OPEN:
                    $output[] = '<span class="badge badge-open">' . $translate->_('Open') . '</span>';
                    break;
            }

            if ($detailed === true) {
                ## -- ADD -- [ MOD:- PRODUCT BUNDLES ]
                if ($listing['hidden_product']) {
                    $output[] = '<span class="badge badge-danger">' . $translate->_('Hidden') . '</span>';
                }
                else {
                    $output[] = '<span class="badge badge-info">' . $translate->_('Visible') . '</span>';
                }
                ## -- ./ADD -- [ MOD:- PRODUCT BUNDLES ]

                if ($listing['deleted']) {
                    $output[] = '<span class="badge badge-dark">' . $translate->_('Deleted') . '</span>';
                }
            }
        }


        return implode('', $output);
    }

    /**
     *
     * listing countdown display
     *
     * @param bool $live generate a live countdown
     *
     * @return string
     */
    public function countdown($live = false)
    {
        $output = '';

        $listing = $this->getListing();
        $translate = $this->getTranslate();
        $view = $this->getView();

        if ($listing->isClosed()) {
            $output = '<span class="text-danger">' . $translate->_('Closed') . '</span>';
        }
        else if ($listing->isScheduled()) {
            $countdown = ($live) ?
                $view->countdown($listing['start_time'])->display() :
                $view->countdown($listing['start_time'])->timeLeft();

            $output = '<span class="text-primary">'
                . '<em>' . $translate->_('Starts in') . '</em>'
                . ' ' . $countdown . '</span>';
        }
        else if ($listing->isOpen()) {
            $output = ($live) ?
                $view->countdown($listing['end_time'])->display() :
                $view->countdown($listing['end_time'])->timeLeft();
        }

        return $output;
    }

    /**
     *
     * reserve status message
     *
     * @return string
     */
    public function reserveMessage()
    {
        $output = '';

        $listing = $this->getListing();
        $translate = $this->getTranslate();

        if ($listing->isAuction() && $listing['reserve_price'] > 0) {
            if ($listing->currentBid() < $listing['reserve_price']) {
                $output = '<small class="text-danger">' . $translate->_('Reserve Not Met') . '</small>';
            }
            else {
                $output = '<small class="text-success">' . $translate->_('Reserve Met') . '</small>';
            }
        }

        return $output;
    }

    /**
     *
     * your bid box display
     *
     * @return string
     */
    public function yourBid()
    {
        $output = '';

        $user = $this->getUser();

        if ($user instanceof UserModel) {
            $listing = $this->getListing();
            $translate = $this->getTranslate();
            if (($yourBid = $listing->yourBid($user['id'])) !== null) {
                $settings = $this->getSettings();
                $view = $this->getView();

                $output = '<div class="text-primary">'
                    . $translate->_('Your Bid:')
                    . ' '
                    . $view->amount($yourBid['amount'], $listing['currency'])
                    . ' '
                    . $view->bidStatus($yourBid);

                if ($settings['proxy_bidding']) {
                    $output .= '<span class="badge badge-light">'
                        . $translate->_('Proxy Bid:')
                        . ' '
                        . $view->amount($yourBid['maximum_bid'],
                            $listing['currency'])
                        . '</span>';
                }

                $output .= '</div>';
            }
        }

        return $output;
    }

    /**
     *
     * this method will display the locations where the listing will ship to
     * we will always insert the listing in the shipping model, to get the item's location when using
     * domestic shipping
     *
     *
     * @param \Ppb\Model\Shipping $shipping
     *
     * @return string|null
     */
    public function shipsTo(ShippingModel $shipping)
    {
        $shipping->addData(
            $this->getListing());

        if ($shipping->getPostageSettings(ShippingModel::SETUP_SHIPPING_LOCATIONS) === ShippingModel::POSTAGE_LOCATION_WORLDWIDE) {
            return $this->getTranslate()->_('Worldwide');
        }

        $shippableLocations = array_filter(
            array_values($shipping->getShippableLocations()));

        if (count($shippableLocations) > 0) {
            $locationsService = new Service\Table\Relational\Locations();

            return implode(', ', $locationsService->getMultiOptions(array_values($shippableLocations)));
        }


        return null;
    }

    /**
     *
     * display available quantity - can be based on selected product attributes as well
     *
     * @param array|null $productAttributes
     *
     * @return int|string|true
     */
    public function availableQuantity($productAttributes = null)
    {
        $listing = $this->getListing();

        $translate = $this->getTranslate();

        $quantity = $listing->getAvailableQuantity(null, $productAttributes);

        if ($listing['listing_type'] == 'product') {
            /** @var \Ppb\Db\Table\Row\User $user */
            $user = $listing->findParentRow('\Ppb\Db\Table\Users');


            if ($user->getGlobalSettings('quantity_description')) {
                $lowStockThreshold = $user->getGlobalSettings('quantity_low_stock');
                $lowStockThreshold = ($lowStockThreshold > 0) ? $lowStockThreshold : 1;

                if ($quantity > $lowStockThreshold || $quantity === true) {
                    $quantity = $translate->_('In Stock');
                }
                else if ($quantity > 0) {
                    $quantity = $translate->_('Low Stock');
                }
                else {
                    $quantity = $translate->_('Out of Stock');
                }
            }
        }

        return ($quantity === true) ? $translate->_('In Stock') : $quantity;
    }

    /**
     *
     * item weight
     *
     * @return string
     */
    public function weight()
    {
        $listing = $this->getListing();
        $seller = $listing->getOwner();

        $weight = $listing->getData(ShippingModel::FLD_ITEM_WEIGHT);
        $weightUom = $seller->getShipping()->getWeightUom();

        return LocaleFormat::getInstance()->numericToLocalized(
                $weight, true) . ' ' . $weightUom;
    }

    public function dimensions()
    {
        $listing = $this->getListing();
        $seller = $listing->getOwner();

        $dimensions = $listing->getData(ShippingModel::FLD_DIMENSIONS);
        $dimensionsUom = $seller->getShipping()->getDimensionsUom(false);

        if (!empty($dimensions)) {
            $length = LocaleFormat::getInstance()->numericToLocalized(
                $dimensions[ShippingModel::DIMENSION_LENGTH], true);
            $width = LocaleFormat::getInstance()->numericToLocalized(
                $dimensions[ShippingModel::DIMENSION_WIDTH], true);
            $height = LocaleFormat::getInstance()->numericToLocalized(
                $dimensions[ShippingModel::DIMENSION_HEIGHT], true);

            return $length . ' x ' . $width . ' x ' . $height . ' ' . $dimensionsUom;
        }

        return null;
    }

    /**
     *
     * get the name of a location based on its id
     *
     * @param int|string $location
     * @param string     $key
     *
     * @return array|null|string
     */
    protected function _getLocationName($location, $key = 'name')
    {
        if (empty($location)) {
            return null;
        }
        if (is_numeric($location)) {
            $locations = new Service\Table\Relational\Locations();
            $row = $locations->findBy('id', (int)$location);
            if ($row != null) {
                $location = $row->getData($key);
            }
        }

        return $location;
    }

}

