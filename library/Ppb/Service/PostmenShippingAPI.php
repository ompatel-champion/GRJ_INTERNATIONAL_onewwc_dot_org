<?php

/**
 *
 * PHP Pro Bid
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2019 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     8.1 [rev.8.1.02]
 */

/**
 * Postmen Shipping API model class
 */

namespace Ppb\Service;

use Ppb\Db\Table\Row\PostmenShipperAccount as PostmenShipperAccountModel;

class PostmenShippingAPI extends AbstractService
{

    /**
     * postmen setup variables
     */
    const API_KEY = 'postmen_api_key';
    const API_MODE = 'postmen_api_mode';

    /**
     * api modes
     */
    const API_MODE_TESTING = 'testing';
    const API_MODE_PRODUCTION = 'production';

    /**
     * api urls
     */
    const SANDBOX_API_URL = 'https://sandbox-api.postmen.com/v3';
    const PRODUCTION_API_URL = 'https://production-api.postmen.com/v3';

    /**
     *
     * sandbox flag
     *
     * @var bool
     */
    protected $_sandbox = true;

    /**
     *
     * api key
     *
     * @var string
     */
    protected $_apiKey;

    /**
     *
     * api mode
     *
     * @var string
     */
    protected $_mode;

    /**
     *
     * class constructor.
     *
     * @param string $apiKey
     * @param string $mode
     */
    public function __construct($apiKey, $mode = null)
    {
        parent::__construct();

        $this->setApiKey($apiKey);

        if ($mode !== null) {
            $this->setMode($mode);
        }
    }

    /**
     *
     * get api key
     *
     * @return string
     */
    public function getApiKey()
    {
        return $this->_apiKey;
    }

    /**
     *
     * set api key
     *
     * @param string $apiKey
     *
     * @return $this
     */
    public function setApiKey($apiKey)
    {
        $this->_apiKey = $apiKey;

        return $this;
    }

    /**
     *
     * get api mode
     *
     * @return string
     */
    public function getMode()
    {
        return $this->_mode;
    }

    /**
     *
     * set api mode
     *
     * @param string $mode
     *
     * @return $this
     */
    public function setMode($mode)
    {
        $this->_mode = $mode;

        if ($mode == self::API_MODE_TESTING) {
            $this->_sandbox = true;
        }
        else if ($mode == self::API_MODE_PRODUCTION) {
            $this->_sandbox = false;
        }

        return $this;
    }

    /**
     *
     * check if api is enabled
     *
     * @return bool
     */
    public function isEnabled()
    {
        $settings = $this->getSettings();

        if (!$settings['enable_postmen']) {
            return false;
        }

        return ($this->getApiKey()) ? true : false;
    }


    /**
     *
     * retrieve shipper accounts
     *
     * @return array
     * @throws \Exception
     */
    public function retrieveShipperAccounts()
    {
        if ($this->isEnabled()) {
            $url = $this->_getApiUrl('/shipper-accounts');
            $method = 'GET';
            $headers = array(
                "content-type: application/json",
                "postmen-api-key: " . $this->getApiKey(),
            );

            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_URL            => $url,
                CURLOPT_CUSTOMREQUEST  => $method,
                CURLOPT_HTTPHEADER     => $headers,
            ));

            $res = curl_exec($curl);
            $err = curl_error($curl);

            curl_close($curl);

            if ($err) {
                throw new \Exception("cURL Error #:" . $err);
            }

            $response = $this->_jsonDecode($res);

            if (!empty($response['data']['shipper_accounts'])) {
                return $response['data']['shipper_accounts'];
            }
        }
        else {
            throw new \Exception('The Postmen API is not enabled.');
        }

        return array();
    }

    /**
     * @param PostmenShipperAccountModel $shipperAccount
     * @param array                      $parcel
     * @param array                      $shipTo
     *
     * @return array
     * @throws \Exception
     */
    public function calculateRates(PostmenShipperAccountModel $shipperAccount, $parcel = array(), $shipTo = array())
    {
        if ($this->isEnabled()) {
            $url = $this->_getApiUrl('/rates');
            $method = 'POST';
            $headers = array(
                "content-type: application/json",
                "postmen-api-key: " . $this->getApiKey(),
            );

            $body = json_encode(array(
                'async'            => false,
                'shipper_accounts' => array(
                    array(
                        'id' => $shipperAccount->getId(),
                    )
                ),
                'shipment'         => array(
                    'parcels'   => array(
                        $parcel,
                    ),
                    'ship_from' => $shipperAccount->getAddress(),
                    'ship_to'   => $shipTo,
                ),
            ));

            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_URL            => $url,
                CURLOPT_CUSTOMREQUEST  => $method,
                CURLOPT_HTTPHEADER     => $headers,
                CURLOPT_POSTFIELDS     => $body
            ));

            $res = curl_exec($curl);
            $err = curl_error($curl);

            if ($err) {
                throw new \Exception("cURL Error #:" . $err);
            }

            $response = $this->_jsonDecode($res);


            if (!empty($response['data']['status']) && !empty($response['data']['rates']) && $response['data']['status'] == 'calculated') {
                return $response['data']['rates'];
            }
            else if (isset($response['meta']['message'])) {
                throw new \Exception($response['meta']['message']);
            }
            else {
                throw new \Exception('Unknown Postmen API error.');
            }
        }
        else {
            throw new \Exception('The Postmen API is not enabled.');
        }
    }

    /**
     *
     * get api url
     *
     * @param string $path
     *
     * @return string
     */
    protected function _getApiUrl($path = null)
    {
        return (($this->_sandbox) ? self::SANDBOX_API_URL : self::PRODUCTION_API_URL) . $path;
    }

    /**
     *
     * decode json or return the original string if it cannot be decoded
     *
     * @param $string
     *
     * @return mixed
     */
    protected function _jsonDecode($string)
    {
        $array = json_decode($string, true);

        return ($array !== null) ? $array : $string;
    }

}

