<?php

/**
 *
 * Cube Framework
 *
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2019 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 *
 * @version     2.1 [rev.2.1.01]
 */

/**
 * crypt class
 */

namespace Cube;

class Crypt
{

    /**
     *
     * encryption key
     *
     * @var string
     */
    protected $_key;

    /**
     * One of the MCRYPT_cipher name constants, or the name of the algorithm as string.
     *
     * @var string
     */
    protected $_cipher = "AES-128-CBC";

    /**
     * One of the MCRYPT_MODE_modename constants,
     * or one of the following strings: "ecb", "cbc", "cfb", "ofb", "nofb" or "stream".
     *
     * @var string
     */
    protected $_mode = OPENSSL_RAW_DATA;

    /**
     *
     * set encryption key
     *
     * @param string $key
     *
     * @return $this
     */
    public function setKey($key)
    {
        $this->_key = hash('sha256', $key, true);

        return $this;
    }

    /**
     *
     * get encryption key
     *
     * @return string
     * @throws \RuntimeException
     */
    public function getKey()
    {
        if (empty($this->_key)) {
            throw new \RuntimeException("The encryption key has not been set.");
        }

        return $this->_key;
    }

    /**
     *
     * set mcrypt cipher
     *
     * @param string $cipher
     *
     * @return $this
     */
    public function setCipher($cipher)
    {
        $this->_cipher = $cipher;

        return $this;
    }

    /**
     *
     * get mcrypt cipher
     *
     * @return string
     */
    public function getCipher()
    {
        return $this->_cipher;
    }

    /**
     *
     * set mcrypt mode
     *
     * @param string $mode
     *
     * @return $this
     */
    public function setMode($mode)
    {
        $this->_mode = $mode;

        return $this;
    }

    /**
     *
     * get mcrypt mode
     *
     * @return string
     */
    public function getMode()
    {
        return $this->_mode;
    }


    /**
     *
     * encrypt a string
     *
     * @param $input
     *
     * @return string
     */
    function encrypt($input)
    {
        $cipher = $this->getCipher();
        $key = $this->getKey();
        $options = $this->getMode();

        $ivLength = openssl_cipher_iv_length($cipher);
        $iv = openssl_random_pseudo_bytes($ivLength);
        $outputRaw = openssl_encrypt($input, $cipher, $key, $options, $iv);
        $hmac = hash_hmac('sha256', $outputRaw, $key, true);

        return base64_encode($iv . $hmac . $outputRaw);
    }

    /**
     *
     * decrypt an encrypted string
     *
     * @param $input
     *
     * @return string
     */
    function decrypt($input)
    {
        $cipher = $this->getCipher();
        $key = $this->getKey();
        $options = $this->getMode();

        $cipherText = base64_decode($input);


        $ivLength = openssl_cipher_iv_length($cipher);
        $iv = substr($cipherText, 0, $ivLength);
        $hmac = substr($cipherText, $ivLength, $sha2len = 32);
        $cipherTextRaw = substr($cipherText, $ivLength + $sha2len);
        $output = openssl_decrypt($cipherTextRaw, $cipher, $key, $options, $iv);
        $calcmac = hash_hmac('sha256', $cipherTextRaw, $key, true);

        if (is_string($hmac) && hash_equals($hmac, $calcmac))//PHP 5.6+ timing attack safe comparison
        {
            return $output;
        }

        return $input;
    }


}

