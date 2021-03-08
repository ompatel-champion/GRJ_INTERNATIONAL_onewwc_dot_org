<?php

/**
 *
 * PHP Pro Bid
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2017 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.9 [rev.7.9.01]
 */
/**
 * EU VAT number validator class
 */

namespace Ppb\Validate;

use Cube\Validate\AbstractValidate;

class VatNumber extends AbstractValidate
{
    const WEBSERVICE_URL = "http://ec.europa.eu/taxation_customs/vies/checkVatService.wsdl";

    protected $_message = "'%s': '%value%' is not a valid VAT number.";

    /**
     *
     * checks if the value is a valid VAT number
     * using the VIES web service
     *
     * @return bool
     * @throws \Exception
     */
    public function isValid()
    {
        $value = $this->getValue();

        if (empty($value)) {
            return true;
        }

        $formattedValue = preg_replace('/[ \-,.]/', '', $value);

        $countryCode = substr($formattedValue, 0, 2); // get country code - first two letters of VAT ID
        $vatNumber = substr($formattedValue, 2);


        if (!class_exists('SoapClient')) {
            throw new \Exception('The Soap library has to be installed and enabled.');
        }

        try {
            $client = new \SoapClient(self::WEBSERVICE_URL, array('trace' => true));
        } catch (\Exception $e) {
            throw new \Exception('Web Service Error: ' . $e->getMessage());
        }

        try {
            $rs = $client->checkVat(array('countryCode' => $countryCode, 'vatNumber' => $vatNumber));
            if ($rs->valid) {
                return true;
            }
        } catch (\Exception $e) {
        }

        $this->setMessage(
            str_replace('%value%', $value, $this->getMessage()));


        return false;
    }

}

