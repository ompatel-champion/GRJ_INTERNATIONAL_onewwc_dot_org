<?php

/**
 *
 * PHP Pro Bid
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.2
 */

/**
 * address geolocation validator class
 */

namespace Ppb\Validate;

use Cube\Validate\AbstractValidate,
    Ppb\Service\GeoLocation as GeoLocationService;

class GeoLocation extends AbstractValidate
{

    protected $_message = "Could not geolocate the address you have entered.";


    /**
     *
     * address
     *
     * @var mixed
     */
    protected $_address;

    /**
     *
     * get address
     *
     * @return mixed
     */
    public function getAddress()
    {
        return $this->_address;
    }

    /**
     *
     * set address
     *
     * @param mixed $address
     *
     * @return $this
     */
    public function setAddress($address)
    {
        $this->_address = $address;

        return $this;
    }

    /**
     *
     * checks if the input address can be geolocated
     *
     * @return bool          return true if the validation is successful
     */
    public function isValid()
    {
        $geoLocationService = new GeoLocationService();
        $geoLocationService->setAddress(
            $this->getAddress());

        try {
            $coordinates = $geoLocationService->getCoordinates();
            if ($coordinates['latitude'] === null && $coordinates['longitude'] === null) {
                if (!empty($coordinates['error_message'])) {
                    $this->setMessage(
                        $this->getMessage() . '<br>' . $coordinates['error_message']);
                }
                return false;
            }
        } catch (\Exception $e) {
        }

        return true;
    }

}

