<?php

/**
 *
 * Cube Framework
 *
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2020 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 *
 * @version     2.2 [rev.2.2.02]
 */

/**
 * curl client class
 */

namespace Cube\Http\Client;

class Curl
{
    /**
     *
     * url
     *
     * @var string
     */
    protected $_url = null;

    /**
     *
     * headers
     *
     * @var array
     */
    protected $_headers = array();

    /**
     *
     * data
     *
     * @var mixed
     */
    protected $_data;

    /**
     *
     * curl error
     *
     * @var string
     */
    protected $_error;

    /**
     *
     * curlopt variables we might need to set
     *
     * @var array
     */
    protected $_curlOpt = array();


    /**
     *
     * class constructor
     *
     * @param string|null $url
     */
    public function __construct($url = null)
    {
        $this->setUrl($url);
    }

    /**
     *
     * get url
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->_url;
    }

    /**
     *
     * set url
     *
     * @param string $url
     *
     * @return $this
     */
    public function setUrl($url)
    {
        $this->_url = $url;

        return $this;
    }

    /**
     *
     * get headers
     *
     * @return array
     */
    public function getHeaders()
    {
        return $this->_headers;
    }

    /**
     *
     * set headers
     *
     * @param array $headers
     *
     * @return $this
     */
    public function setHeaders($headers)
    {
        $this->_headers = $headers;

        return $this;
    }

    /**
     *
     * get data
     *
     * @return mixed
     */
    public function getData()
    {
        return $this->_data;
    }

    /**
     *
     * set data
     *
     * @param mixed $data
     *
     * @return $this
     */
    public function setData($data)
    {
        $this->_data = $data;

        return $this;
    }

    /**
     *
     * get error
     *
     * @return string
     */
    public function getError()
    {
        return $this->_error;
    }

    /**
     *
     * set error
     *
     * @param string $error
     *
     * @return $this
     */
    public function setError($error)
    {
        $this->_error = $error;

        return $this;
    }

    /**
     *
     * get CURLOPT extra variables
     *
     * @return array
     */
    public function getCurlOpt()
    {
        return $this->_curlOpt;
    }

    /**
     *
     * set CURLOPT extra variables
     *
     * @param array $curlOpt
     *
     * @return $this
     */
    public function setCurlOpt($curlOpt)
    {
        $this->_curlOpt = $curlOpt;

        return $this;
    }

    /**
     *
     * curl post
     *
     * @return mixed
     */
    public function post()
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $this->getUrl());
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->getHeaders());

        foreach ($this->getCurlOpt() as $key => $value) {
            curl_setopt($ch, $key, $value);
        }

        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $this->getData());

        //execute post
        $result = curl_exec($ch);

        if ($result === false) {
            $this->setError(
                curl_error($ch));
        }

        curl_close($ch);

        return $this->_jsonDecode($result);
    }

    /**
     *
     * curl get
     *
     * @return mixed
     */
    public function get()
    {
        $ch = curl_init();

        //set the url, number of POST vars, POST data
        curl_setopt($ch, CURLOPT_URL, $this->getUrl());
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->getHeaders());

        //execute post
        $result = curl_exec($ch);

        curl_close($ch);

        return $this->_jsonDecode($result);
    }

    /**
     *
     * curl getinfo
     * https://www.php.net/manual/en/function.curl-getinfo.php
     *
     * @param string $option
     *
     * @return bool|mixed
     */
    public function getInfo($option)
    {
        $ch = curl_init();

        //set the url, number of POST vars, POST data
        curl_setopt($ch, CURLOPT_URL, $this->getUrl());

        foreach ($this->getCurlOpt() as $key => $value) {
            curl_setopt($ch, $key, $value);
        }

        //do request
        $result = curl_exec($ch);

        $response = false;

        //if request did not fail
        if ($result !== false) {
            $response = curl_getinfo($ch, $option);
        }

        curl_close($ch);

        return $response;
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

