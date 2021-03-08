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
 * listing box / details view helper class
 */

namespace Listings\View\Helper;

use Ppb\View\Helper\AbstractHelper,
    Cube\Locale\Format as LocaleFormat,
    Ppb\Service,
    Ppb\Db\Table\Row\Listing as ListingModel,
    Ppb\Db\Table\Row\User as UserModel,
    Ppb\Model\Shipping as ShippingModel;

class Listing extends AbstractHelper
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
     * display seller flag
     *
     * @var bool
     */
    protected $_displaySeller = false;

    /**
     *
     * display enhanced flag
     *
     * @var bool
     */
    protected $_displayEnhanced = false;

    /**
     *
     * get listing model
     *
     * @return \Ppb\Db\Table\Row\Listing
     */
    public function getListing()
    {
        if (!$this->_listing instanceof ListingModel) {
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
    public function setListing(ListingModel $listing)
    {
        if (!$listing instanceof ListingModel) {
            throw new \InvalidArgumentException("The listing model must be an instance of \Ppb\Db\Table\Row\Listing");
        }

        $this->_listing = $listing;

        return $this;
    }

    /**
     *
     * get display seller flag
     *
     * @return bool
     */
    public function isDisplaySeller()
    {
        return $this->_displaySeller;
    }

    /**
     *
     * set display seller flag
     *
     * @param bool $displaySeller
     *
     * @return $this
     */
    public function setDisplaySeller($displaySeller = true)
    {
        $this->_displaySeller = $displaySeller;

        return $this;
    }

    /**
     *
     * get display enhanced flag
     *
     * @return bool
     */
    public function isDisplayEnhanced()
    {
        return $this->_displayEnhanced;
    }

    /**
     *
     * set display enhanced flag
     *
     * @param bool $displayEnhanced
     *
     * @return $this
     */
    public function setDisplayEnhanced($displayEnhanced = true)
    {
        $this->_displayEnhanced = $displayEnhanced;

        return $this;
    }

    /**
     *
     * main method, only returns object instance
     * always reset local display variables
     *
     * @param \Ppb\Db\Table\Row\Listing $listing
     * @param string                    $partial
     *
     * @return $this
     */
    public function listing(ListingModel $listing = null, $partial = null)
    {
        if ($listing !== null) {
            $this->setListing($listing);
        }

        if ($partial !== null) {
            $this->setPartial($partial);
        }

        $this->setDisplaySeller(false)
            ->setDisplayEnhanced(false);

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

        $settings = $this->getSettings();

        if ($settings['enable_stores']) {
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
            $output[] = '<span class="badge badge-draft">' . $translate->_('Draft') . '</span>';
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
                case ListingModel::SCHEDULED:
                    $output[] = '<span class="badge badge-scheduled">' . $translate->_('Scheduled') . '</span>';
                    break;
                case ListingModel::CLOSED:
                    $output[] = '<span class="badge badge-closed">' . $translate->_('Closed') . '</span>';
                    break;
                case ListingModel::OPEN:
                    $output[] = '<span class="badge badge-open">' . $translate->_('Open') . '</span>';
                    break;
            }

            if ($detailed === true) {
                if ($listing['deleted']) {
                    $output[] = '<span class="badge badge-dark">' . $translate->_('Deleted') . '</span>';
                }
            }
        }


        return implode(' ', $output);
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
                    . $this->yourBidStatus();

                if ($listing->isProxyBidding()) {
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
     * your bid status badge display
     *
     * @return string
     */
    public function yourBidStatus()
    {
        $output = '';

        $user = $this->getUser();

        if ($user instanceof UserModel) {
            $listing = $this->getListing();
            if (($yourBid = $listing->yourBid($user['id'])) !== null) {
                $output = $this->getView()->bidStatus($yourBid);
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
        $shipping->clearData()
            ->addData(
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
     * output default listing postage to logged in user's location or standard domestic postage otherwise
     *
     * @param ShippingModel $shipping
     * @param bool          $postageOnly
     *
     * @return string
     */
    public function defaultPostage(ShippingModel $shipping, $postageOnly = false)
    {
        $output = null;

        $listing = $this->getListing();

        $translate = $this->getTranslate();

        if ($listing->pickUpOnly()) {
            return $translate->_('Pick-up only');
        }

        $shipping->clearData()
            ->addData($listing);

        $locationId = $listing['country'];
        $postCode = $listing['address'];

        $user = $this->getUser();

        if (!empty($user)) {
            $address = $user->getAddress();

            if (!empty($address['country']) && !empty($address['zip_code'])) {
                $locationId = $address['country'];
                $postCode = $address['zip_code'];
            }
        }

        $shipping->setLocationId($locationId)
            ->setPostCode($postCode);

        try {
            $result = $shipping->calculatePostage();
            $result = reset($result);

            $postageAmount = $this->getView()->amount($result['price'], $result['currency']);

            if ($postageOnly) {
                $output = $postageAmount;
            }
            else {
                $output = sprintf($translate->_('%s to %s'), $postageAmount, $this->_getLocationName($locationId))
                    . '<br>'
                    . '<em>' . $translate->_($result['method']) . '</em>';
            }
        } catch (\RuntimeException $e) {
            $output = ($postageOnly) ? $translate->_('N/A') : '<span class="text-danger">' . $translate->_('The seller does not ship to your location.') . '</span>';
        }

        return $output;
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

    /**
     *
     * item dimensions
     *
     * @return string|null
     */
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
     * render partial
     *
     * @param array $localVariables
     *
     * @return string
     */
    public function render($localVariables = null)
    {
        $view = $this->getView();

        $view->setVariables(array(
            'listing'         => $this->getListing(),
            'displaySeller'   => $this->isDisplaySeller(),
            'displayEnhanced' => $this->isDisplayEnhanced(),
        ));

        // set local variables
        if (is_array($localVariables)) {
            $view->setVariables($localVariables);
        }

        $output = $view->process(
            $this->getPartial(), true);

        // clear local variables
        if (is_array($localVariables)) {
            foreach ($localVariables as $key => $value) {
                $view->clearVariable($key);
            }
        }

        return $output;
    }

    /**
     *
     * get the name of a location based on its id
     *
     * @param int|string $location
     * @param string     $key
     *
     * @return string|null
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

