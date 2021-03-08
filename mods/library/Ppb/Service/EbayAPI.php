<?php

/**
 *
 * PHP Pro Bid
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2015 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.7
 */
/**
 * ebay api service class
 */
/**
 * MOD:- EBAY IMPORTER
 *
 * @version 3.2
 */

namespace Ppb\Service;

use Cube\Config\Xml;

class EbayAPI extends AbstractService
{
    /**
     * api credentials keys
     */
    const DEV_ID = 'ebay_importer_dev_id';
    const APP_ID = 'ebay_importer_app_id';
    const CERT_ID = 'ebay_importer_cert_id';
    const RUNAME = 'ebay_importer_runame';

    /**
     * URLS
     */
    const TRADING_API_URL = 'https://api.ebay.com/ws/api.dll';
    const SIGN_IN_URL = 'https://signin.ebay.com/ws/eBayISAPI.dll?SignIn&';

    /**
     *
     * ebay AppID
     *
     * @var string
     */
    protected $_appId;

    /**
     *
     * ebay DevID
     *
     * @var string
     */
    protected $_devId;

    /**
     *
     * ebay CertID
     *
     * @var string
     */
    protected $_certId;

    /**
     *
     * ebay app RuName
     *
     * @var string
     */
    protected $_ruName;

    public function __construct()
    {
        $settings = $this->getSettings();

        if (array_key_exists(self::DEV_ID, $settings)) {
            $this->setDevId($settings[self::DEV_ID]);
        }
        if (array_key_exists(self::APP_ID, $settings)) {
            $this->setAppId($settings[self::APP_ID]);
        }
        if (array_key_exists(self::CERT_ID, $settings)) {
            $this->setCertId($settings[self::CERT_ID]);
        }
        if (array_key_exists(self::RUNAME, $settings)) {
            $this->setRuName($settings[self::RUNAME]);
        }
    }

    /**
     *
     * get ebay AppID
     *
     * @return string
     */
    public function getAppId()
    {
        return $this->_appId;
    }

    /**
     *
     * set ebay AppID
     *
     * @param string $appId
     *
     * @return $this
     */
    public function setAppId($appId)
    {
        $this->_appId = $appId;

        return $this;
    }

    /**
     *
     * get ebay DevID
     *
     * @return string
     */
    public function getDevId()
    {
        return $this->_devId;
    }

    /**
     *
     * set ebay DevID
     *
     * @param string $devId
     *
     * @return $this
     */
    public function setDevId($devId)
    {
        $this->_devId = $devId;

        return $this;
    }

    /**
     *
     * get ebay CertID
     *
     * @return string
     */
    public function getCertId()
    {
        return $this->_certId;
    }

    /**
     *
     * set ebay CertID
     *
     * @param string $certId
     *
     * @return $this
     */
    public function setCertId($certId)
    {
        $this->_certId = $certId;

        return $this;
    }

    /**
     *
     * get ebay RuName
     *
     * @return string
     */
    public function getRuName()
    {
        return $this->_ruName;
    }

    /**
     *
     * set ebay RuName
     *
     * @param string $ruName
     *
     * @return $this
     */
    public function setRuName($ruName)
    {
        $this->_ruName = $ruName;

        return $this;
    }

    /**
     *
     * ebay shopping api - GetSingleItem method call
     *
     * @param int $itemId
     *
     * @return array|\SimpleXMLElement
     */
    public function getSingleItemResponse($itemId)
    {
        $settings = $this->getSettings();

        $response = array();

        if ($settings['enable_ebay_importer']) {

            $url = 'https://open.api.ebay.com/shopping?'
                . 'callname=GetSingleItem&'
                . 'responseencoding=XML&'
                . 'appid=' . $this->getAppId() . '&'
                . 'siteid=0&'
                . 'version=515&'
                . 'IncludeSelector=Details,Description,ItemSpecifics&'
                . 'ItemID=' . $itemId;

            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

            $res = curl_exec($ch);

            curl_close($ch);

            $response = new \SimpleXMLElement($res);
        }

        return $response;
    }

    /**
     *
     * call ebay finding api and return response in array format
     *
     * @param string $username
     * @param string $marketplace   GLOBAL-ID variable
     * @param string $operationName finding service operation name (findItemsAdvanced or findCompletedItems currently supported)
     * @param int    $entriesPerPage
     * @param int    $pageNumber
     *
     * @return array
     */
    public function getFindingServiceResponse($username, $marketplace = null, $operationName = 'findItemsAdvanced', $entriesPerPage = 10, $pageNumber = 1)
    {
        $settings = $this->getSettings();

        $response = array();

        if ($settings['enable_ebay_importer']) {

            $url = 'https://svcs.ebay.com/services/search/FindingService/v1?'
                . 'OPERATION-NAME=' . $operationName . '&'
                . 'SECURITY-APPNAME=' . $this->getAppId() . '&'
                . 'RESPONSE-DATA-FORMAT=XML&'
                . 'REST-PAYLOAD&'
                . 'itemFilter(0).name=Seller&'
                . 'itemFilter(0).value=' . $username . '&'
                . 'itemFilter(1).name=ListingType&'
                . 'itemFilter(1).value(0)=Auction&'
                . 'itemFilter(1).value(1)=AuctionWithBIN&'
                . 'itemFilter(1).value(1)=FixedPrice&';
//                . 'itemFilter(2).name=HideDuplicateItems&'
//                . 'itemFilter(2).value=false';


            if ($operationName == 'findCompletedItems') {
                $url .= 'itemFilter(3).name=SoldItemsOnly&'
                    . 'itemFilter(3).value=true&';
            }

            $url .= 'paginationInput.entriesPerPage=' . $entriesPerPage . '&'
                . 'paginationInput.pageNumber=' . $pageNumber . '&'
                . 'keywords=';


            if ($marketplace !== null) {
                $url .= '&'
                    . 'GLOBAL-ID=' . $marketplace;
            }
            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

            $res = curl_exec($ch);

            curl_close($ch);

            $xmlObject = new Xml();
            $xmlObject->setData($res);

            $response = $xmlObject->getData();
        }

        return $response;
    }

    /**
     *
     * ebay trading api call
     *
     * @param string $call
     * @param string $body
     * @param string $field
     *
     * @return string
     */
    public function callTradeAPI($call, $body, $field = null)
    {
        if (($response = @file_get_contents(self::TRADING_API_URL, 'r',
                stream_context_create(array('http' => array(
                    'method' => 'POST',

                    'header' =>
                        "Content-Type: text/xml; charset=utf-8\r\n"
                        . "X-EBAY-API-SITEID: 0\r\n"
                        . "X-EBAY-API-COMPATIBILITY-LEVEL: 689\r\n"
                        . "X-EBAY-API-CALL-NAME: {$call}\r\n"

                        // these headers are only required for GetSessionID and FetchToken
                        . "X-EBAY-API-DEV-NAME: {$this->getDevId()}\r\n"
                        . "X-EBAY-API-APP-NAME: {$this->getAppId()}\r\n"
                        . "X-EBAY-API-CERT-NAME: {$this->getCertId()}\r\n",

                    'content' => $request =
                        "<?xml version='1.0' encoding='utf-8'?>\n"
                        . "<{$call} xmlns='urn:ebay:apis:eBLBaseComponents'>{$body}</{$call}>"
                ))))) !== false
        ) {
            if ($field !== null) {
                if (($begin = strpos($response, "<{$field}>")) !== false) {
                    // skip open tag
                    $begin += strlen($field) + 2;

                    // found close tag?
                    if (($end = strpos($response, "</{$field}>", $begin)) !== false) {
                        return substr($response, $begin, $end - $begin);
                    }
                }

                return false;
            }
        }

        return $response;
    }

    /**
     *
     * get the item weight for an ebay item using the trading api / GetItemShipping call
     *
     * @param int                $ebayItemId             ebay item id
     * @param \Cube\Db\Table\Row $ebayUser               ebay user object
     * @param string             $destinationCountryCode destination country code
     * @param string             $destinationPostalCode  destination postal code
     *
     * @return float|false
     */
    public function getItemWeight($ebayItemId, $ebayUser, $destinationCountryCode = 'US', $destinationPostalCode = '10001')
    {
        if (!empty($ebayUser['ebay_token'])) {
            $body = "\n"
                . "<RequesterCredentials> \n"
                . " <eBayAuthToken>{$ebayUser['ebay_token']}</eBayAuthToken> \n"
                . "</RequesterCredentials> \n"
                . "<ItemID>{$ebayItemId}</ItemID> \n"
                . "<QuantitySold>1</QuantitySold> \n"
                . "<DestinationPostalCode>{$destinationPostalCode}</DestinationPostalCode> \n"
                . "<DestinationCountryCode>{$destinationCountryCode}</DestinationCountryCode>\n";

            $res = $this->callTradeAPI('GetItemShipping', $body);

            $xmlObject = new Xml();
            $xmlObject->setData($res);

            $response = $xmlObject->getData();

            if (isset($response['Ack'])) {
                if ($response['Ack'] == 'Success') {
                    $weightMajor = (int)$response['ShippingDetails']['CalculatedShippingRate']['WeightMajor'];
                    $weightMinor = (int)$response['ShippingDetails']['CalculatedShippingRate']['WeightMinor'];

                    return $weightMajor . '.' . $weightMinor;
                }
            }
        }

        return false;
    }

    /**
     *
     * get total number of ebay listings for a user on a certain marketplace
     *
     * @param string $username
     * @param string $marketplace
     * @param string $operationName
     *
     * @return int
     */
    public function getTotalListings($username, $marketplace = null, $operationName = 'findItemsAdvanced')
    {
        $response = $this->getFindingServiceResponse($username, $marketplace, $operationName);

        if (isset($response['ack'])) {
            if ($response['ack'] == 'Success') {
                return $response['paginationOutput']['totalEntries'];
            }
        }

        return null;
    }
}

