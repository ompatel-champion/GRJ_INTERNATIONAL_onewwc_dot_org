<?php

/**
 *
 * PHP Pro Bid
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2017 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.9 [rev.7.9.02]
 */
/**
 * shipping carrier model abstract class
 *
 * IMPORTANT: all methods that extend this class must have the name identical with the
 * 'name' field in the shipping_carriers table.
 */

namespace Ppb\Model\Shipping\Carrier;

use Cube\Db\Table\Row\AbstractRow,
    Ppb\Model\Shipping as ShippingModel,
    Ppb\Service;

abstract class AbstractCarrier extends AbstractRow
{
    /**
     * methods array constants
     */

    const DOM = 'domestic';
    const INTL = 'international';

    /**
     * dimensions constants;
     */
    const W = 'width';
    const L = 'length';
    const H = 'height';

    /**
     * weight conversion rate
     * 1 lbs = 0.4536 kg
     */
    const LBS_TO_KG = 0.4536;

    /**
     * dimensions conversion rate
     * 1 cm = 0.3937 inches
     */
    const CM_TO_INCHES = 0.3937;

    /**
     *
     * shipping carrier description
     * (to be used in the admin area - functionality description)
     *
     * @var string
     */
    protected $_description;

    /**
     *
     * carrier methods array - defined by each carrier class
     *
     * @var array
     */
    protected $_methods = array(
        self::DOM  => array(),
        self::INTL => array(),
    );

    /**
     *
     * package weight
     * (lbs or kg, depending on shipping carrier)
     *
     * @var int
     */
    protected $_weight;

    /**
     *
     * package weight uom (accepted values: kg, lbs)
     *
     * @var string
     */
    protected $_weightUom;

    /**
     *
     * carrier weight uom
     *
     * @var string
     */
    protected static $_carrierWeightUom = ShippingModel::UOM_LBS;

    /**
     *
     * carrier dimensions uom
     *
     * @var string
     */
    protected static $_carrierDimensionsUom = ShippingModel::UOM_INCHES;

    /**
     *
     * error message resulted from a getPrice operation
     *
     * @var string
     */
    protected $_error = null;

    /**
     *
     * package dimensions
     *
     * @var array
     */
    protected $_dimensions = array(
        self::W => null,
        self::H => null,
        self::L => null,
    );

    /**
     *
     * package dimensions uom (accepted values: inches, cm)
     *
     * @var string
     */
    protected $_dimensionsUom;

    /**
     *
     * carrier currency
     *
     * @var string
     */
    protected $_currency;

    /**
     *
     * source zip code
     *
     * @var string
     */
    protected $_sourceZip;

    /**
     *
     * source country
     *
     * @var string
     */
    protected $_sourceCountry;

    /**
     *
     * destination zip code
     *
     * @var string
     */
    protected $_destZip;

    /**
     *
     * destination country
     *
     * @var string
     */
    protected $_destCountry;

    /**
     *
     * class constructor
     *
     * @param string $carrierName carrier name
     * @param string $currency
     *
     * @throws \RuntimeException
     */
    public function __construct($carrierName, $currency)
    {
        $carriersService = new Service\Table\ShippingCarriers();
        $carrier = $carriersService->findBy('name', $carrierName);

        if (!$carrier['id']) {
            $translate = $this->getTranslate();

            throw new \RuntimeException(
                sprintf($translate->_("The shipping carrier you are trying to use, '%s', does not exist."), $carrierName));
        }

        $data = array(
            'table' => $carriersService->getTable(),
            'data'  => $carriersService->getData($carrier['id']),
        );

        parent::__construct($data);

        $this->setCurrency($currency);
    }

    /**
     *
     * get carrier description string
     *
     * @return string
     */
    public function getDescription()
    {
        $translate = $this->getTranslate();

        if (null !== $translate) {
            return $translate->_($this->_description);
        }

        return $this->_description;
    }

    /**
     *
     * set carrier description string
     *
     * @param string $description
     *
     * @return $this
     */
    public function setDescription($description)
    {
        $this->_description = (string)$description;

        return $this;
    }

    /**
     *
     * get package weight
     *
     * @return float|int
     */
    public function getWeight()
    {
        return $this->_weight;
    }

    /**
     *
     * set package weight
     *
     * @param float $weight
     *
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function setWeight($weight)
    {
        if (empty($this->_weightUom)) {
            throw new \InvalidArgumentException("Please set the weight UOM before setting the weight value.");
        }

        if ($this->_weightUom != self::$_carrierWeightUom) {
            $weight = ($this->_weightUom == ShippingModel::UOM_KG) ?
                ($weight / self::LBS_TO_KG) : ($weight * self::LBS_TO_KG);
        }

        $this->_weight = round($weight, 1);

        return $this;
    }

    /**
     *
     * get weight uom
     *
     * @return string
     */
    public function getWeightUom()
    {
        return $this->_weightUom;
    }

    /**
     *
     * set weight uom
     *
     * @param string $weightUom
     *
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function setWeightUom($weightUom)
    {
        $weightUom = strtolower($weightUom);

        if (!in_array($weightUom, array(ShippingModel::UOM_KG, ShippingModel::UOM_LBS))) {
            throw new \InvalidArgumentException("Invalid weight UOM submitted.");
        }

        $this->_weightUom = $weightUom;

        return $this;
    }

    /**
     *
     * get error message
     *
     * @return string
     */
    public function getError()
    {
        $translate = $this->getTranslate();

        if (null !== $translate) {
            return $translate->_($this->_error);
        }

        return $this->_error;
    }

    /**
     *
     * set error message
     *
     * @param string $error
     *
     * @return $this
     */
    public function setError($error)
    {
        $this->_error = (string)$error;

        return $this;
    }

    /**
     *
     * get dimensions array
     *
     * @return array
     */
    public function getDimensions()
    {
        return $this->_dimensions;
    }

    /**
     *
     * set dimensions array
     *
     * @param array $dimensions
     *
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function setDimensions($dimensions)
    {
        if (empty($this->_dimensionsUom)) {
            throw new \InvalidArgumentException("Please set the dimensions UOM before setting the dimensions.");
        }

        if (is_array($dimensions)) {
            if (
                array_key_exists(self::L, $dimensions) &&
                array_key_exists(self::W, $dimensions) &&
                array_key_exists(self::H, $dimensions)
            ) {
                if ($this->_dimensionsUom != self::$_carrierDimensionsUom) {
                    if ($this->_dimensionsUom == ShippingModel::UOM_CM) {
                        $dimensions = array(
                            self::L => $dimensions[self::L] * self::CM_TO_INCHES,
                            self::W => $dimensions[self::W] * self::CM_TO_INCHES,
                            self::H => $dimensions[self::H] * self::CM_TO_INCHES,
                        );
                    }
                    else {
                        $dimensions = array(
                            self::L => $dimensions[self::L] / self::CM_TO_INCHES,
                            self::W => $dimensions[self::W] / self::CM_TO_INCHES,
                            self::H => $dimensions[self::H] / self::CM_TO_INCHES,
                        );
                    }
                }

                $this->_dimensions = $dimensions;
            }
        }


        return $this;
    }

    /**
     *
     * get dimensions uom
     *
     * @return string
     */
    public function getDimensionsUom()
    {
        return $this->_dimensionsUom;
    }

    /**
     *
     * set dimensions uom
     *
     * @param string $dimensionsUom
     *
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function setDimensionsUom($dimensionsUom)
    {
        $dimensionsUom = strtolower($dimensionsUom);

        if (!in_array($dimensionsUom, array(ShippingModel::UOM_INCHES, ShippingModel::UOM_CM))) {
            throw new \InvalidArgumentException("Invalid dimensions UOM submitted.");
        }

        $this->_dimensionsUom = $dimensionsUom;

        return $this;
    }

    /**
     *
     * get carrier currency iso code
     *
     * @return string
     */
    public function getCurrency()
    {
        return $this->_currency;
    }

    /**
     *
     * set carrier currency iso code
     *
     * @param string $currency
     *
     * @return $this
     */
    public function setCurrency($currency)
    {
        $this->_currency = (string)$currency;

        return $this;
    }

    /**
     *
     * get source zip code
     *
     * @return string
     */
    public function getSourceZip()
    {
        return $this->_sourceZip;
    }

    /**
     *
     * set source zip code
     *
     * @param string $sourceZip
     *
     * @return $this
     */
    public function setSourceZip($sourceZip)
    {
        $this->_sourceZip = (string)$sourceZip;

        return $this;
    }

    /**
     *
     * get source country
     *
     * @return string
     */
    public function getSourceCountry()
    {
        return $this->_sourceCountry;
    }

    /**
     *
     * set source country
     *
     * @param string $sourceCountry
     *
     * @return $this
     */
    public function setSourceCountry($sourceCountry)
    {
        $this->_sourceCountry = (string)$sourceCountry;

        return $this;
    }

    /**
     *
     * get destination zip code
     *
     * @return string
     */
    public function getDestZip()
    {
        return $this->_destZip;
    }

    /**
     *
     * set destination zip code
     *
     * @param string $destZip
     *
     * @return $this
     */
    public function setDestZip($destZip)
    {
        $this->_destZip = (string)$destZip;

        return $this;
    }

    /**
     *
     * get destination country
     *
     * @return string
     */
    public function getDestCountry()
    {
        return $this->_destCountry;
    }

    /**
     *
     * set destination country
     *
     * @param string $destCountry
     *
     * @return $this
     */
    public function setDestCountry($destCountry)
    {
        $this->_destCountry = (string)$destCountry;

        return $this;
    }

    /**
     *
     * get form elements, used to create the form needed to add the shipping carrier settings
     *
     * @return array
     */
    public function getElements()
    {
        return array();
    }

    /**
     *
     * dummy function used as a placeholder for translatable sentences
     *
     * @param $string
     *
     * @return string
     */
    protected function _($string)
    {
        return $string;
    }

    abstract public function getPrice($methodName = null);
}

