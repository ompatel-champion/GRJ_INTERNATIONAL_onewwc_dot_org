<?php

/**
 *
 * PHP Pro Bid
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.0
 */
/**
 * address geolocation service using google maps
 * - accepts an address and returns latitude and longitude
 */
/**
 * MOD:- PICKUP LOCATIONS
 */

namespace Ppb\Service;

use Ppb\Service\Table\Relational\Locations as LocationsService;

class GeoLocation extends AbstractService
{

    const API_URL = 'https://maps.googleapis.com/maps/api/geocode/json';

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
        if (is_array($address)) {
            $locationsService = new LocationsService();

            $data = array();
            if (!empty($address['address'])) {
                $data['address'] = $address['address'];
            }

            if (!empty($address['city'])) {
                $data['city'] = $address['city'];
            }
            if (!empty($address['zip_code'])) {
                $data['zip_code'] = $address['zip_code'];
            }

            if (!empty($address['state'])) {
                if (is_numeric($address['state'])) {
                    $row = $locationsService->findBy('id', (int)$address['state']);
                    if ($row != null) {
                        $data['state'] = $row->getData('name');
                    }
                }
                else {
                    $data['state'] = $address['state'];
                }
            }
            if (!empty($address['country'])) {
                if (is_numeric($address['country'])) {
                    $row = $locationsService->findBy('id', (int)$address['country']);
                    if ($row != null) {
                        $data['country'] = $row->getData('name');
                    }
                }
                else {
                    $data['country'] = $address['country'];
                }
            }

            $address = $data;
        }

        $this->_address = $address;

        return $this;
    }

    /**
     *
     * get coordinates
     *
     * @return array
     */
    public function getCoordinates()
    {
        $address = $this->getAddress();

        if (!$address) {
            throw new \InvalidArgumentException("An address is required for the geolocation service.");
        }

        if (is_array($address)) {
            $address = implode(', ', $address);
        }

        $response = $this->_get(array(
            'address' => $address
        ));

        $result =(array_key_exists('results', $response)) ? $response['results'] : array();

        // return coordinates latitude/longitude
        return array(
            'error_message' => array_key_exists('error_message', $response) ? $response['error_message'] : null,
            'latitude'      => array_key_exists(0, $result) ? (float)$result[0]['geometry']['location']['lat'] : null,
            'longitude'     => array_key_exists(0, $result) ? (float)$result[0]['geometry']['location']['lng'] : null
        );
    }


    /**
     * Submits an HTTP POST to a reCAPTCHA server
     *
     * @param array $data
     *
     * @return array response
     */
    protected function _get(array $data)
    {
        $settings = $this->getSettings();

        $data['key'] = $settings['google_api_key'];

        $url = self::API_URL . '?' . http_build_query($data, '', '&');

        return json_decode(
            file_get_contents($url), true);
    }
}

