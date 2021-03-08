<?php

/**
 *
 * PHP Pro Bid
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2020 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     8.2 [rev.8.2.02]
 */

/**
 * shipping model
 *
 * the model will require a user object in order to be initialized, and to be able
 * to calculate postage, it will require either a listing owned by the user, or
 * a sale made by the user
 *
 * Important: the calculation will always take the location of the item(s) as the source location,
 * and not the location of the owner
 */

namespace Ppb\Model;

use Cube\Controller\Front,
    Cube\Translate,
    Cube\Translate\Adapter\AbstractAdapter as TranslateAdapter,
    Ppb\Db\Table\Row\User as UserModel,
    Ppb\Db\Table\Row\Listing as ListingModel,
    Ppb\Form\Element\FlatRatesLocationGroups,
    Ppb\Service;

class Shipping
{

    /**
     * weight uom
     */
    const UOM_LBS = 'lbs';
    const UOM_KG = 'kg';

    /**
     * dimensions uom
     */
    const UOM_INCHES = 'inches';
    const UOM_CM = 'cm';

    /**
     * dimension coordinates
     */
    const DIMENSION_LENGTH = 'length';
    const DIMENSION_WIDTH = 'width';
    const DIMENSION_HEIGHT = 'height';

    /**
     * min weight value
     */
    const MIN_WEIGHT = 0.1;

    /**
     * pickup options
     */
    const NO_PICKUPS = 'no_pickups';
    const CAN_PICKUP = 'can_pickup';
    const MUST_PICKUP = 'must_pickup';

    /**
     * key and value for drop downs for the pick-up option
     */
    const KEY_PICK_UP = -1;
    const VALUE_PICK_UP = 'Pick-up';

    /**
     * seller postage setup page fields
     */
    const SETUP_FREE_POSTAGE = 'free_postage';
    const SETUP_FREE_POSTAGE_AMOUNT = 'free_postage_amount';
    const SETUP_POSTAGE_TYPE = 'postage_type';
    const SETUP_POSTAGE_FLAT_FIRST = 'postage_flat_first';
    const SETUP_POSTAGE_FLAT_ADDL = 'postage_flat_addl';
    const SETUP_SHIPPING_CARRIERS = 'shipping_carriers';
    const SETUP_WEIGHT_UOM = 'weight_uom';
    const SETUP_SHIPPING_LOCATIONS = 'shipping_locations';
    const SETUP_LOCATION_GROUPS = 'location_groups';
    const SETUP_DIMENSIONS_UOM = 'dimensions_uom';

    /**
     * postage calculation types
     */
    const POSTAGE_TYPE_ITEM = 'item';
    const POSTAGE_TYPE_FLAT = 'flat';
    const POSTAGE_TYPE_CARRIERS = 'carriers';

    /**
     * shipping locations options
     */
    const POSTAGE_LOCATION_DOMESTIC = 'domestic';
    const POSTAGE_LOCATION_WORLDWIDE = 'worldwide';
    const POSTAGE_LOCATION_CUSTOM = 'custom';

    /**
     * listing setup shipping related fields
     */
    const FLD_ACCEPT_RETURNS = 'accept_returns';
    const FLD_RETURNS_POLICY = 'returns_policy';
    const FLD_PICKUP_OPTIONS = 'pickup_options';
    const FLD_SHIPPING_DETAILS = 'shipping_details';
    const FLD_POSTAGE = 'postage';
    const FLD_ITEM_WEIGHT = 'item_weight';
    const FLD_DIMENSIONS = 'dimensions';
    const FLD_INSURANCE = 'insurance';

    /**
     * listings data array keys
     */
    const DATA_LISTING = 'listing';
    const DATA_QUANTITY = 'quantity';

    /**
     * standard shipping method desc.
     */
    const MSG_STANDARD_SHIPPING = 'Standard Shipping';

    /**
     *
     * weight uom list
     *
     * @var array
     */
    public static $weightUom = array(
        self::UOM_LBS => 'Lbs',
        self::UOM_KG  => 'Kg'
    );

    /**
     *
     * dimensions uom list
     *
     * @var array
     */
    public static $dimensionsUom = array(
        self::UOM_INCHES => 'inches',
        self::UOM_CM     => 'cm',
    );

    public static $dimensionsCoordinates = array(
        self::DIMENSION_LENGTH => 'Length',
        self::DIMENSION_WIDTH  => 'Width',
        self::DIMENSION_HEIGHT => 'Height',
    );

    /**
     *
     * pick-up options array
     *
     * @var array
     */
    public static $pickupOptions = array(
        self::NO_PICKUPS  => 'No pick-ups',
        self::CAN_PICKUP  => 'Buyer can pick-up',
        self::MUST_PICKUP => 'Buyer must pick-up',
    );

    /**
     *
     * seller postage setup page fields
     *
     * @var array
     */
    public static $postageSetupFields = array(
        self::SETUP_FREE_POSTAGE        => 'Offer Free Postage',
        self::SETUP_FREE_POSTAGE_AMOUNT => 'If amount exceeds',
        self::SETUP_POSTAGE_TYPE        => 'Postage Calculation Type',
        self::SETUP_POSTAGE_FLAT_FIRST  => 'First Item',
        self::SETUP_POSTAGE_FLAT_ADDL   => 'Additional Items',
        self::SETUP_SHIPPING_CARRIERS   => 'Select Shipping Carriers',
        self::SETUP_WEIGHT_UOM          => 'Weight UOM',
        self::SETUP_SHIPPING_LOCATIONS  => 'Shipping Locations',
        self::SETUP_LOCATION_GROUPS     => 'Location Groups',
        self::SETUP_DIMENSIONS_UOM      => 'Dimensions UOM',
    );

    /**
     *
     * listing postage related fields array
     *
     * @var array
     */
    public static $postageFields = array(
        self::FLD_ACCEPT_RETURNS   => 'Accept Returns',
        self::FLD_RETURNS_POLICY   => 'Return Policy Details',
        self::FLD_PICKUP_OPTIONS   => 'Pick-ups',
        self::FLD_POSTAGE          => 'Postage',
        self::FLD_ITEM_WEIGHT      => 'Item Weight',
        self::FLD_DIMENSIONS       => 'Dimensions',
        self::FLD_INSURANCE        => 'Insurance',
        self::FLD_SHIPPING_DETAILS => 'Shipping Instructions',
    );

    /**
     *
     * default dimensions - standard rectangular box
     *
     * @var array
     */
    protected static $_defaultDimensions = array(
        self::DIMENSION_LENGTH => 12,
        self::DIMENSION_WIDTH  => 9,
        self::DIMENSION_HEIGHT => 6,
    );

    /**
     *
     * seller user model
     *
     * @var \Ppb\Db\Table\Row\User
     */
    protected $_user;

    /**
     *
     * buyer user model
     *
     * @var \Ppb\Db\Table\Row\User
     */
    protected $_buyer;

    /**
     *
     * the postage settings that the seller has set up
     *
     * @var array
     */
    protected $_postageSettings = array();

    /**
     *
     * array of data of listing object - quantity combinations for which the postage is to be calculated
     *
     * @var array
     */
    protected $_data = array();

    /**
     *
     * locations table service
     *
     * @var \Ppb\Service\Table\Relational\Locations
     */
    protected $_locations;

    /**
     *
     * currencies table service
     *
     * @var \Ppb\Service\Table\Currencies
     */
    protected $_currencies;

    /**
     *
     * translate adapter
     *
     * @var \Cube\Translate\Adapter\AbstractAdapter
     */
    protected $_translate;

    /**
     *
     * the location id of the destination (from the locations table)
     *
     * @var int
     */
    protected $_locationId;

    /**
     *
     * the zip/post code of the destination
     *
     * @var string
     */
    protected $_postCode;

    /**
     *
     * the name of the shipping method that will be used to send the items.
     * (valid for shipping carriers too)
     *
     * @var string
     */
    protected $_postageMethod;

    /**
     *
     * this flag is set from the addData() method
     *
     * @var bool
     */
    protected $_canPickUp = false;

    /**
     *
     * if one item is set to must pick-up, then all items are set to must pick-up
     *
     * @var bool
     */
    protected $_mustPickUp = false;

    /**
     *
     * the currency of the listings inserted in the model
     *
     * @var string
     */
    protected $_currency;

    /**
     *
     * class constructor
     *
     * @param \Ppb\Db\Table\Row\User $user
     */
    public function __construct(UserModel $user)
    {
        $this->_user = $user;

        $this->setPostageSettings($user['postage_settings']);
    }

    /**
     *
     * get pick-up option description
     *
     * @param string $key pick-up option key
     *
     * @return string|null
     */
    public static function getPickupOptions($key)
    {
        if (isset(self::$pickupOptions[$key])) {
            return self::$pickupOptions[$key];
        }

        return null;
    }

    /**
     *
     * get postage settings
     *
     * @param string|null $key
     *
     * @return mixed
     */
    public function getPostageSettings($key = null)
    {
        if ($key !== null) {
            return (isset($this->_postageSettings[$key])) ? $this->_postageSettings[$key] : null;
        }

        return $this->_postageSettings;
    }

    /**
     *
     * set postage settings (accepted an array or a serialized string)
     *
     * @param array|string $postageSettings
     *
     * @return \Ppb\Model\Shipping
     */
    public function setPostageSettings($postageSettings)
    {
        if (!is_array($postageSettings)) {
            $postageSettings = \Ppb\Utility::unserialize($postageSettings, array());
        }

        $this->_postageSettings = $postageSettings;

        return $this;
    }

    /**
     *
     * get buyer
     *
     * use setBuyer() to add the buyer id.
     *
     * @return UserModel
     */
    public function getBuyer()
    {
        if (!$this->_buyer instanceof UserModel) {
            $buyer = Front::getInstance()->getBootstrap()->getResource('user');

            if ($buyer instanceof UserModel) {
                $this->setBuyer(
                    $buyer);
            }
        }

        return $this->_buyer;
    }

    /**
     *
     * set buyer
     *
     * @param UserModel $buyer
     *
     * @return $this
     */
    public function setBuyer($buyer)
    {
        $this->_buyer = $buyer;

        return $this;
    }

    /**
     *
     * get currencies table service
     *
     * @return \Ppb\Service\Table\Currencies
     */
    public function getCurrencies()
    {
        if (!$this->_currencies instanceof Service\Table\Currencies) {
            $this->setCurrencies(
                new Service\Table\Currencies());
        }

        return $this->_currencies;
    }

    /**
     *
     * set currencies service
     *
     * @param \Ppb\Service\Table\Currencies $currencies
     *
     * @return $this
     */
    public function setCurrencies(Service\Table\Currencies $currencies)
    {
        $this->_currencies = $currencies;

        return $this;
    }

    /**
     *
     * set translate adapter
     *
     * @param \Cube\Translate\Adapter\AbstractAdapter $translate
     *
     * @return $this
     */
    public function setTranslate(TranslateAdapter $translate)
    {
        $this->_translate = $translate;

        return $this;
    }

    /**
     *
     * get translate adapter
     *
     * @return \Cube\Translate\Adapter\AbstractAdapter
     */
    public function getTranslate()
    {
        if (!$this->_translate instanceof TranslateAdapter) {
            $translate = Front::getInstance()->getBootstrap()->getResource('translate');
            if ($translate instanceof Translate) {
                $this->setTranslate(
                    $translate->getAdapter());
            }
        }

        return $this->_translate;
    }

    /**
     *
     * get data array
     *
     * @return array
     */
    public function getData()
    {
        return $this->_data;
    }

    /**
     *
     * set data array
     *
     * @param array $data
     *
     * @return \Ppb\Model\Shipping
     */
    public function setData(array $data)
    {
        $this->clearData();

        foreach ($data as $row) {
            $this->addData($row[self::DATA_LISTING], $row[self::DATA_QUANTITY]);
        }

        return $this;
    }

    /**
     *
     * add a data row in the array
     * all items that are added must have the same location
     *  ** and currency (for now)
     *  ** and pickup options as well
     *
     * @param \Ppb\Db\Table\Row\Listing $listing
     * @param int                       $quantity
     *
     * @return \Ppb\Model\Shipping
     * @throws \RuntimeException
     */
    public function addData(ListingModel $listing, $quantity = 1)
    {
        foreach ($this->_data as $data) {
            if (
                $data[self::DATA_LISTING]['country'] != $listing['country'] ||
                $data[self::DATA_LISTING]['state'] != $listing['state'] ||
                $data[self::DATA_LISTING]['address'] != $listing['address'] ||
                $data[self::DATA_LISTING]['currency'] != $listing['currency'] ||
                $data[self::DATA_LISTING][self::FLD_PICKUP_OPTIONS] != $listing[self::FLD_PICKUP_OPTIONS]
            ) {
                $translate = $this->getTranslate();

                throw new \RuntimeException($translate->_("All the listings added in the shipping model must have the same location "
                    . "(country, state, address), use the same currency and have the same pick-up options selected."));
            }
        }

        if ($listing[self::FLD_PICKUP_OPTIONS] !== self::NO_PICKUPS) {
            $this->_canPickUp = true;
        }

        if ($listing[self::FLD_PICKUP_OPTIONS] == self::MUST_PICKUP) {
            $this->_mustPickUp = true;
        }

        $this->_data[] = array(
            self::DATA_LISTING  => $listing,
            self::DATA_QUANTITY => ($quantity > 0) ? (int)$quantity : 1
        );

        $this->_currency = $listing['currency'];

        return $this;
    }

    /**
     *
     * clear data
     *
     * @return $this
     */
    public function clearData()
    {
        $this->_data = array();
        $this->_canPickUp = false;
        $this->_mustPickUp = false;

        return $this;
    }

    /**
     *
     * get locations service
     *
     * @return \Ppb\Service\Table\Relational\Locations
     */
    public function getLocations()
    {
        if (!$this->_locations instanceof Service\Table\Relational\Locations) {
            $this->setLocations(
                new Service\Table\Relational\Locations());
        }

        return $this->_locations;
    }

    /**
     *
     * set locations service
     *
     * @param \Ppb\Service\Table\Relational\Locations $locations
     *
     * @return \Ppb\Model\Shipping
     */
    public function setLocations(Service\Table\Relational\Locations $locations)
    {
        $this->_locations = $locations;

        return $this;
    }

    /**
     *
     * get destination location id
     *
     * @return int
     */
    public function getLocationId()
    {
        return $this->_locationId;
    }

    /**
     *
     * set destination location id
     *
     * @param int $locationId
     *
     * @return \Ppb\Model\Shipping
     */
    public function setLocationId($locationId)
    {
        $this->_locationId = (int)$locationId;

        return $this;
    }

    /**
     *
     * get destination location post code
     *
     * @return string
     */
    public function getPostCode()
    {
        return $this->_postCode;
    }

    /**
     *
     * set destination location post code
     *
     * @param string $postCode
     *
     * @return \Ppb\Model\Shipping
     */
    public function setPostCode($postCode)
    {
        $this->_postCode = (string)$postCode;

        return $this;
    }

    /**
     *
     * get postage method
     *
     * @return string
     */
    public function getPostageMethod()
    {
        $translate = $this->getTranslate();

        if (null !== $translate) {
            return $translate->_($this->_postageMethod);
        }

        return $this->_postageMethod;
    }

    /**
     *
     * set postage method
     *
     * @param string $postageMethod
     *
     * @return $this
     */
    public function setPostageMethod($postageMethod)
    {
        $this->_postageMethod = (string)$postageMethod;

        return $this;
    }

    /**
     *
     * get weight uom set by the user
     *
     * @param bool $sentence
     *
     * @return string
     */
    public function getWeightUom($sentence = true)
    {
        $weightUom = $this->getPostageSettings(self::SETUP_WEIGHT_UOM);

        if ($weightUom !== null) {
            if ($sentence !== true) {
                return $weightUom;
            }

            $translate = $this->getTranslate();

            $sentence = self::$weightUom[$weightUom];
            if (null !== $translate) {
                return $translate->_($sentence);
            }

            return $sentence;
        }

        return null;
    }

    /**
     *
     * get dimensions uom set by the user
     *
     * @param bool $sentence
     *
     * @return string
     */
    public function getDimensionsUom($sentence = true)
    {
        $dimensionsUom = $this->getPostageSettings(self::SETUP_DIMENSIONS_UOM);

        if ($dimensionsUom !== null) {
            if ($sentence !== true) {
                return $dimensionsUom;
            }

            $translate = $this->getTranslate();

            $sentence = self::$dimensionsUom[$dimensionsUom];
            if (null !== $translate) {
                return $translate->_($sentence);
            }

            return $sentence;
        }

        return null;
    }

    /**
     *
     * get location groups array
     *
     * @return array|bool    will return false if there are no locations or if the location based calculation is not set to custom
     */
    public function getLocationGroups()
    {
        if ($this->getPostageSettings(self::SETUP_SHIPPING_LOCATIONS) == self::POSTAGE_LOCATION_CUSTOM) {
            return $this->_postageSettings[self::SETUP_LOCATION_GROUPS][FlatRatesLocationGroups::FIELD_NAME];
        }

        return false;
    }

    /**
     *
     * get the postage calculation type set by the user
     *
     * @return string
     */
    public function getPostageType()
    {
        return $this->getPostageSettings(self::SETUP_POSTAGE_TYPE);
    }

    /**
     *
     * return the postage options available based on a set of input values
     *
     * required values:
     * - one or more listings from the same seller
     * - a destination location (id from the locations table)
     * - a destination zip/post code
     *
     * outputs:
     * - an array of all available postage methods
     * OR
     * - a runtime error if any error has occurred
     *
     * [OBSOLETE]
     * currencies output:
     * - item based : item currency
     * - flat rates : site's default currency
     * - carriers : carrier currency
     *
     * [ACTUAL]
     * currency output will always be in the item's currency
     *
     * @return array
     * @throws \RuntimeException
     */
    public function calculatePostage()
    {
        $result = array();
        $translate = $this->getTranslate();
        $carrierError = null;

        if (!$this->_locationId) {
            throw new \RuntimeException($translate->_("No destination location has been set."));
        }
        else if (!$this->_postCode) {
            throw new \RuntimeException($translate->_("No destination zip/post code has been set."));
        }
        else if (empty($this->_data)) {
            throw new \RuntimeException($translate->_("At least one item needs to be set in order to calculate the postage."));
        }


        if ($this->_mustPickUp === false) {
            $shippableLocations = $this->getShippableLocations();

            $shippingLocations = $this->getPostageSettings(self::SETUP_SHIPPING_LOCATIONS);

            if (in_array($this->_locationId, $shippableLocations)) {
                if ($this->_isFreePostage()) {
                    $result[] = array(
                        'currency' => $this->_currency,
                        'price'    => 0,
                        'carrier'  => $translate->_('N/A'),
                        'method'   => self::MSG_STANDARD_SHIPPING,
                    );
                }
                else {
                    switch ($this->getPostageType()) {
                        case self::POSTAGE_TYPE_ITEM:
                            $postageMethods = null;
                            $postageData = array();

                            foreach ($this->_data as $row) {
                                $fldPostage = $row[self::DATA_LISTING][self::FLD_POSTAGE];


                                if (isset($fldPostage['locations'])) {
                                    if (in_array($shippingLocations,
                                        array(self::POSTAGE_LOCATION_DOMESTIC, self::POSTAGE_LOCATION_WORLDWIDE))
                                    ) {
                                        // unset locations field if we have domestic or worldwide postage
                                        unset($fldPostage['locations']);
                                    }
                                    else {
                                        // set locations as countries
                                        $fldPostage['locations'] = array_filter($fldPostage['locations']);
                                        foreach ($fldPostage['locations'] as $key => $loc) {
                                            foreach ($loc as $k => $v) {
                                                $fldPostage['locations'][$key][$k] = $this->_postageSettings[self::SETUP_LOCATION_GROUPS][FlatRatesLocationGroups::FIELD_LOCATIONS][$v];
                                            }

                                            $fldPostage['locations'][$key] = call_user_func_array('array_merge',
                                                $fldPostage['locations'][$key]);
                                        }
                                    }
                                }

                                // modify the price to be calculated on the quantity requested
                                if (!empty($fldPostage['price'])) {
                                    foreach ($fldPostage['price'] as $key => $value) {
                                        $fldPostage['price'][$key] = doubleval($fldPostage['price'][$key]) * $row[self::DATA_QUANTITY];
                                    }

                                    // flip postage array
                                    $postageData[] = $this->_flipArray($fldPostage);
                                }


                                // unset price field to compare data
                                if (isset($fldPostage['price'])) {
                                    unset($fldPostage['price']);
                                }

                                // the locations and postage methods must be the same for the postage to be calculated (for multiple items)
                                if ($postageMethods !== null && $postageMethods != $fldPostage) {
                                    throw new \RuntimeException($translate->_("All listings included must have the same postage methods."));
                                }

                                $postageMethods = $fldPostage;
                            }

                            $listing = $this->_data[0][self::DATA_LISTING];


                            switch ($shippingLocations) {
                                case self::POSTAGE_LOCATION_DOMESTIC:
                                case self::POSTAGE_LOCATION_WORLDWIDE:

                                    if (!empty($postageMethods['method'])) {
                                        foreach ($postageMethods['method'] as $key => $method) {
                                            if (!empty($method)) {
                                                foreach ($postageData as $data) {
                                                    $price = (!empty($result[$key]['price'])) ? doubleval($result[$key]['price']) : 0;

                                                    $result[$key] = array(
                                                        'currency' => $listing['currency'],
                                                        'price'    => ($price + doubleval($data[$key]['price'])),
                                                        'method'   => $method,
                                                    );
                                                }
                                            }
                                        }
                                    }

                                    break;

                                case self::POSTAGE_LOCATION_CUSTOM:
                                    foreach ($postageMethods['method'] as $key => $method) {
                                        if (!empty($method)) {
                                            foreach ($postageData as $data) {
                                                if (in_array($this->_locationId, (array)$data[$key]['locations'])) {
                                                    $price = (!empty($result[$key]['price'])) ? doubleval($result[$key]['price']) : 0;

                                                    $result[$key] = array(
                                                        'currency' => $listing['currency'],
                                                        'price'    => ($price + doubleval($data[$key]['price'])),
                                                        'method'   => $method,
                                                    );
                                                }
                                            }
                                        }
                                    }

                                    break;
                            }

                            break;


                        case self::POSTAGE_TYPE_FLAT:
                            $quantity = 0;
                            foreach ($this->_data as $row) {
                                $quantity += $row[self::DATA_QUANTITY];
                            }

                            $settings = Front::getInstance()->getBootstrap()->getResource('settings');

                            switch ($shippingLocations) {
                                case self::POSTAGE_LOCATION_DOMESTIC:
                                case self::POSTAGE_LOCATION_WORLDWIDE:
                                    $price = $this->getPostageSettings(self::SETUP_POSTAGE_FLAT_FIRST) +
                                        ($this->getPostageSettings(self::SETUP_POSTAGE_FLAT_ADDL) * ($quantity - 1));

                                    $result[] = array(
                                        'currency' => $settings['currency'],
                                        'price'    => doubleval($price),
                                        'method'   => self::MSG_STANDARD_SHIPPING,
                                    );
                                    break;

                                case self::POSTAGE_LOCATION_CUSTOM:
                                    $locationGroups = $this->getPostageSettings(self::SETUP_LOCATION_GROUPS);
                                    foreach ($locationGroups[FlatRatesLocationGroups::FIELD_LOCATIONS] as $key => $val) {
                                        if (in_array($this->_locationId, array_values((array)$val))) {
                                            $price = $locationGroups[FlatRatesLocationGroups::FIELD_FIRST][$key] +
                                                ($locationGroups[FlatRatesLocationGroups::FIELD_ADDL][$key] * ($quantity - 1));
                                            $result[] = array(
                                                'currency' => $settings['currency'],
                                                'price'    => doubleval($price),
                                                'method'   => $locationGroups[FlatRatesLocationGroups::FIELD_NAME][$key],
                                            );
                                        }
                                    }
                                    break;
                            }
                            break;


                        case self::POSTAGE_TYPE_CARRIERS:
                            $shippingCarriers = (array)$this->getPostageSettings(self::SETUP_SHIPPING_CARRIERS);

                            $postmenShipperAccountsService = new Service\PostmenShipperAccounts();

                            $postmenShippingApi = new Service\PostmenShippingAPI(
                                $this->_user->getGlobalSettings(Service\PostmenShippingAPI::API_KEY),
                                $this->_user->getGlobalSettings(Service\PostmenShippingAPI::API_MODE)
                            );

                            $postmenShipperAccounts = $postmenShipperAccountsService->fetchAll(
                                $postmenShipperAccountsService->getTable()->select()
                                    ->where('user_id = ?', $this->_user['id'])
                            );

                            $sourceCountry = $this->getLocations()->findBy('id',
                                $this->_data[0][self::DATA_LISTING]['country']);

                            $sourceCountryIsoCode = \Ppb\Utility::getCountryIsoAlpha3(strtoupper($sourceCountry['iso_code']));

                            $dimensions = $this->_calculateTotalDimensions();

                            $items = array();

                            $weightUnit = $this->_convertUnit($this->getPostageSettings(self::SETUP_WEIGHT_UOM));
                            $dimensionsUnit = $this->_convertUnit($this->getPostageSettings(self::SETUP_DIMENSIONS_UOM));

                            foreach ($this->_data as $row) {
                                /** @var \Ppb\Db\Table\Row\Listing $listing */
                                $listing = $row[self::DATA_LISTING];
                                $price = ($listing->isAuction()) ? $listing->currentBid(true) : $listing['buyout_price'];

                                $items[] = array(
                                    'description'    => $listing['name'],
                                    'origin_country' => $sourceCountryIsoCode,
                                    'quantity'       => $row[self::DATA_QUANTITY],
                                    'price'          => array(
                                        'amount'   => floatval($price),
                                        'currency' => $listing['currency'],
                                    ),
                                    'weight'         => array(
                                        'value' => floatval($listing[self::FLD_ITEM_WEIGHT]),
                                        'unit'  => $weightUnit,
                                    ),
                                );
                            }

                            $parcel = array(
                                'description' => 'Parcel',
                                'box_type'    => 'custom',
                                'weight'      => array(
                                    'value' => $this->_calculateTotalWeight(),
                                    'unit'  => $weightUnit
                                ),
                                'dimension'   => array(
                                    'width'  => floatval($dimensions[self::DIMENSION_WIDTH]),
                                    'height' => floatval($dimensions[self::DIMENSION_HEIGHT]),
                                    'depth'  => floatval($dimensions[self::DIMENSION_LENGTH]),
                                    'unit'   => $dimensionsUnit,
                                ),
                                'items'       => $items
                            );

                            $destCountry = $this->getLocations()->findBy('id', $this->getLocationId());

                            $shipTo = array(
                                'country'     => \Ppb\Utility::getCountryIsoAlpha3(strtoupper($destCountry['iso_code'])),
                                'postal_code' => $this->_postCode
                            );

                            foreach ($shippingCarriers as $rowCarrier) {
                                /** @var \Ppb\Db\Table\Row\PostmenShipperAccount $postmenShipperAccount */
                                foreach ($postmenShipperAccounts as $postmenShipperAccount) {
                                    if ($postmenShipperAccount->isEnabled() && $postmenShipperAccount->getSlug() == $rowCarrier) {
                                        try {
                                            $shippingRates = $postmenShippingApi->calculateRates($postmenShipperAccount, $parcel, $shipTo);

                                            try {
                                                foreach ($shippingRates as $shippingRate) {
                                                    if ($shippingRate['service_type'] != null) {
                                                        $result[] = array(
                                                            'currency' => $shippingRate['total_charge']['currency'],
                                                            'price'    => $shippingRate['total_charge']['amount'],
                                                            'method'   => strtoupper($rowCarrier) . ' ' . $shippingRate['service_name'],
                                                            'code'     => $shippingRate['service_type'],
                                                            'carrier'  => strtoupper($rowCarrier),
                                                            'class'    => null,
                                                        );
                                                    }
                                                }
                                            } catch (\Exception $e) {
                                                $carrierError = $e->getMessage();
                                            }
                                        } catch (\Exception $e) {
                                            $carrierError = $e->getMessage();
                                        }
                                    }
                                }

                                $className = '\\Ppb\\Model\\Shipping\\Carrier\\' . $rowCarrier;
                                if (class_exists($className)) {
                                    /** @var \Ppb\Model\Shipping\Carrier\AbstractCarrier $carrier */
                                    $carrier = new $className();

                                    if (!empty($this->_data[0][self::DATA_LISTING]['country'])) {
                                        $sourceCountry = $this->getLocations()->findBy('id',
                                            $this->_data[0][self::DATA_LISTING]['country']);
                                        $carrier->setSourceCountry($sourceCountry['iso_code']);
                                    }
                                    $destCountry = $this->getLocations()->findBy('id', $this->getLocationId());

                                    $carrier->setSourceZip($this->_data[0][self::DATA_LISTING]['address'])
                                        ->setDestCountry($destCountry['iso_code'])
                                        ->setDestZip($this->_postCode)
                                        ->setWeightUom(
                                            $this->getPostageSettings(self::SETUP_WEIGHT_UOM))
                                        ->setWeight(
                                            $this->_calculateTotalWeight())
                                        ->setDimensionsUom(
                                            $this->getPostageSettings(self::SETUP_DIMENSIONS_UOM))
                                        ->setDimensions(
                                            $this->_calculateTotalDimensions());

                                    if (($carrierResult = $carrier->getPrice()) !== false) {
                                        foreach ($carrierResult as $val) {
                                            $result[] = array(
                                                'currency' => $val['currency'],
                                                'price'    => $val['price'],
                                                'method'   => $translate->_($rowCarrier) . ' ' . $val['name'],
                                                'code'     => $val['code'],
                                                'carrier'  => $rowCarrier,
                                                'class'    => $className,
                                            );
                                        }
                                    }

                                    $carrierError = $carrier->getError();
                                }
                            }
                            break;
                    }
                }
            }
        }

        if ($this->_canPickUp === true || $this->_mustPickUp === true) {
            $result[self::KEY_PICK_UP] = array(
                'currency' => $this->_currency,
                'price'    => 0,
                'method'   => self::VALUE_PICK_UP,
                'carrier'  => '-',

            );
        }

        if (empty($result)) {
            throw new \RuntimeException(!empty($carrierError) ? $carrierError : $translate->_("The item(s) cannot be shipped to your selected destination."));
        }

        // convert currencies
        $result = $this->_convertCurrency($result);

        return $result;
    }

    /**
     *
     * calculate insurance amount for the items in a sale
     *
     * @return float
     */
    public function calculateInsurance()
    {
        $insuranceAmount = 0;
        foreach ($this->_data as $row) {
            if (!empty($row[self::DATA_LISTING][self::FLD_INSURANCE])) {
                $insuranceAmount += doubleval($row[self::DATA_LISTING][self::FLD_INSURANCE]) * $row[self::DATA_QUANTITY];
            }
        }

        return $insuranceAmount;
    }

    /**
     *
     * get all locations the item(s) can be posted to
     *
     * @param bool $dropDown
     *
     * @return array
     */
    public function getShippableLocations($dropDown = false)
    {
        $regions = false;

        if ($this->getPostageType() == self::POSTAGE_TYPE_ITEM) {
            foreach ($this->_data as $row) {
                $listingRegions = array();

                $listingPostageRegions = isset($row[self::DATA_LISTING][self::FLD_POSTAGE][FlatRatesLocationGroups::FIELD_LOCATIONS]) ?
                    $row[self::DATA_LISTING][self::FLD_POSTAGE][FlatRatesLocationGroups::FIELD_LOCATIONS] : array();

                $listingPostageRegions = array_filter($listingPostageRegions);

                if (count($listingPostageRegions) > 0) {
                    $listingRegions = array_unique(
                        call_user_func_array('array_merge', $listingPostageRegions));
                }

                $regions = (is_array($regions)) ? array_intersect($regions, $listingRegions) : $listingRegions;
            }
        }

        $locations = $this->_getShippingLocations($regions);

        if ($dropDown === true) {
            $locations = $this->getLocations()->getMultiOptions((array)$locations);
        }

        return $locations;
    }

    /**
     *
     * get an array containing the ids of the shipping locations from the user's postage settings
     * and optionally a set of regions
     *
     * by default users will be set to ship domestically, based on the location of the item(s) and not the one of the seller
     *
     * @param array|bool $regions
     *
     * @return array
     */
    protected function _getShippingLocations($regions = false)
    {
        $locations = array();

        $shippingLocations = (isset($this->_postageSettings[self::SETUP_SHIPPING_LOCATIONS])) ?
            $this->_postageSettings[self::SETUP_SHIPPING_LOCATIONS] : self::POSTAGE_LOCATION_DOMESTIC;

        switch ($shippingLocations) {
            case self::POSTAGE_LOCATION_DOMESTIC:
                $locations = array($this->_data[0][self::DATA_LISTING]['country']);
                break;
            case self::POSTAGE_LOCATION_WORLDWIDE:
                $locationsService = new Service\Table\Relational\Locations();
                $locations = array_keys($locationsService->getMultiOptions());
                break;
            case self::POSTAGE_LOCATION_CUSTOM:
                $postageLocations = array_filter($this->_postageSettings[self::SETUP_LOCATION_GROUPS][FlatRatesLocationGroups::FIELD_LOCATIONS]);

                if ($regions !== false) {
                    foreach ($postageLocations as $key => $val) {
                        if (!in_array($key, $regions)) {
                            unset($postageLocations[$key]);
                        }
                    }

                    if (count($postageLocations) > 0) {
                        $locations = $this->_mergeLocations($postageLocations);
                    }
                }
                else {
                    $locations = $this->_mergeLocations($postageLocations);
                }
                break;
        }

        return $locations;
    }

    /**
     *
     * rebuild locations array and remove duplicate locations
     *
     * @param $locations
     *
     * @return array
     */
    protected function _mergeLocations(array $locations)
    {
        if (count($locations) > 0) {
            $locations = array_unique(
                call_user_func_array('array_merge', $locations));
        }

        return $locations;
    }

    protected function _flipArray(array $array)
    {
        $output = array();
        foreach ($array['method'] as $key => $value) {
            if (!empty($value)) {
                if (isset($array['price'])) {
                    $output[$key]['price'] = $array['price'][$key];
                }

                $output[$key]['method'] = $array['method'][$key];

                if (isset($array['locations'])) {
                    $output[$key]['locations'] = (isset($array['locations'][$key])) ? $array['locations'][$key] : null;
                }
            }
        }

        return $output;
    }

    /**
     *
     * accepts an array returned by the calculatePostage() method and converts the amounts and currencies
     * to the currency of the listings in the model
     *
     * @param array $data
     *
     * @return array
     * @throws \RuntimeException
     */
    protected function _convertCurrency($data)
    {
        $translate = $this->getTranslate();

        foreach ($data as $key => $value) {
            if (!array_key_exists('currency', $value)
                && !array_key_exists('price', $value)
            ) {
                throw new \RuntimeException($translate->_("Invalid array input in the _convertCurrency() method."));
            }

            if ($value['currency'] != $this->_currency) {
                $data[$key]['currency'] = $this->_currency;
                $data[$key]['price'] = $this->getCurrencies()->convertAmount($value['price'], $value['currency'],
                    $this->_currency);
            }
        }

        return $data;
    }

    /**
     *
     * this method will calculate the total invoice amount and if the total is over the minimum
     * amount required for the postage to be free, it will return true; otherwise it will return false
     *
     * @return bool
     */
    protected function _isFreePostage()
    {
        if ($this->_postageSettings[self::SETUP_FREE_POSTAGE]) {
            $total = 0;

            foreach ($this->_data as $data) {
                $total += $data[self::DATA_LISTING]['buyout_price'] * $data[self::DATA_QUANTITY];
            }

            $settings = Front::getInstance()->getBootstrap()->getResource('settings');

            $freePostageAmount = $this->getCurrencies()->convertAmount($this->_postageSettings[self::SETUP_FREE_POSTAGE_AMOUNT],
                $settings['currency'], $this->_currency);
            if ($total >= $freePostageAmount) {
                return true;
            }
        }

        return false;
    }

    /**
     *
     * calculate the total weight of the items added in the shipping model
     *
     * @return float
     */
    protected function _calculateTotalWeight()
    {
        $weight = 0;
        foreach ($this->_data as $row) {
            $weight += floatval($row[self::DATA_LISTING][self::FLD_ITEM_WEIGHT]) * $row[self::DATA_QUANTITY];
        }

        return ($weight > 0) ? $weight : self::MIN_WEIGHT;
    }


    /**
     *
     * we first order the dimensions to have the same orientation for the items
     * the total dimension is calculated as follows:
     * max(length) & max(width) & sum(height)
     *
     *
     * @return array
     */
    protected function _calculateTotalDimensions()
    {
        $dimensions = null;

        $lengths = array();
        $widths = array();
        $heights = array();

        $counter = 0;

        foreach ($this->_data as $row) {
            $listingDimensions = (!empty($row[self::DATA_LISTING][self::FLD_DIMENSIONS])) ?
                $row[self::DATA_LISTING][self::FLD_DIMENSIONS] : null;

            if ($listingDimensions !== null) {
                $sortedDimensions = array_values($listingDimensions);
                rsort($sortedDimensions);

                for ($i = 0; $i < $row[self::DATA_QUANTITY]; $i++) {
                    $lengths[] = $sortedDimensions[0];
                    $widths[] = $sortedDimensions[1];
                    $heights[] = $sortedDimensions[2];

                    $counter++;
                }
            }
        }

        if ($counter > 0) {
            return array(
                self::DIMENSION_LENGTH => max($lengths),
                self::DIMENSION_WIDTH  => max($widths),
                self::DIMENSION_HEIGHT => array_sum($heights),
            );
        }

        return null;
    }

    /**
     *
     * convert unit to a format recognized by the postmen api
     *
     * @param $input
     *
     * @return string
     */
    protected function _convertUnit($input)
    {
        $output = $input;

        switch ($input) {
            case self::UOM_LBS:
                $output = 'lb';
                break;
            case self::UOM_KG:
                $output = 'kg';
                break;
            case self::UOM_INCHES:
                $output = 'in';
                break;
            case self::UOM_CM;
                $output = 'cm';
                break;
        }

        return $output;
    }
}
