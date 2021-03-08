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
 * dimensions (L x W x H) form element
 */

namespace Ppb\Form\Element;

use Cube\Form\Element,
    Cube\Controller\Front,
    Cube\Validate\NotEmpty,
    Cube\Locale\Format as LocaleFormat,
    Ppb\Model\Shipping as ShippingModel;

class Dimensions extends Element
{

    /**
     *
     * type of element - override the variable from the parent class
     *
     * @var string
     */
    protected $_element = 'dimensions';

    /**
     *
     * request object
     *
     * @var \Cube\Controller\Request\AbstractRequest
     */
    protected $_request;

    /**
     *
     * the labels for each of the two fields
     *
     * @var array
     */
    protected $_fieldLabels = array();

    /**
     *
     * class constructor
     *
     * @param string $name
     */
    public function __construct($name)
    {
        parent::__construct('text', $name);

        $this->_request = Front::getInstance()->getRequest();
    }

    /**
     *
     * set field labels
     *
     * @param array $fieldLabels
     *
     * @return $this
     */
    public function setFieldLabels($fieldLabels)
    {
        $this->_fieldLabels = $fieldLabels;

        return $this;
    }

    /**
     *
     * get field labels
     *
     * @return array
     */
    public function getFieldLabels()
    {
        return $this->_fieldLabels;
    }

    /**
     *
     * get individual field label
     *
     * @param $key
     *
     * @return string
     */
    public function getFieldLabel($key)
    {
        if (isset($this->_fieldLabels[$key])) {
            return $this->_fieldLabels[$key];
        }

        return $this->_label;
    }

    /**
     *
     * return the value(s) of the element, either the element's data or default value(s)
     *
     * @param string $key
     *
     * @return mixed
     */
    public function getValue($key = null)
    {
        $value = parent::getValue();

        if ($key !== null) {
            if (array_key_exists($key, (array)$value)) {
                return $value[$key];
            }
            else {
                return null;
            }
        }

        return $value;
    }

    /**
     *
     * render element attributes
     *
     * @param string $type
     *
     * @return string
     */
    public function renderAttributes($type = null)
    {
        $attributes = null;

        foreach ($this->_attributes as $key => $value) {
            $attributes .= $key . '="' . ((is_array($value)) ? $value[$type] : $value) . '" ';
        }

        return $attributes;
    }

    /**
     *
     * check if the composite element is valid
     *
     * @return bool
     */
    public function isValid()
    {
        $valid = true;

        if (!$this->_request->isPost()) {
            return true;
        }

        if ($this->_required === true) {
            $this->addValidator(
                new NotEmpty());
        }

        $lengthLabel = $this->getFieldLabel(ShippingModel::DIMENSION_LENGTH);
        $widthLabel = $this->getFieldLabel(ShippingModel::DIMENSION_WIDTH);
        $heightLabel = $this->getFieldLabel(ShippingModel::DIMENSION_HEIGHT);

        $lengthValue = $this->getValue(ShippingModel::DIMENSION_LENGTH);
        $widthValue = $this->getValue(ShippingModel::DIMENSION_WIDTH);
        $heightValue = $this->getValue(ShippingModel::DIMENSION_HEIGHT);

        // get original values
        $label = $this->getLabel();
        $data = $this->_data;

        foreach ($this->getValidators() as $validator) {
            // check length
            $this->setLabel($lengthLabel);
            $this->setData($lengthValue);
            $valid = ($this->_checkValidator($validator) === true) ? $valid : false;

            // check width
            $this->setLabel($widthLabel);
            $this->setData($widthValue);
            $valid = ($this->_checkValidator($validator) === true) ? $valid : false;

            // check width
            $this->setLabel($heightLabel);
            $this->setData($heightValue);
            $valid = ($this->_checkValidator($validator) === true) ? $valid : false;
        }

        // restore values
        $this->setLabel($label);
        $this->setData($data);

        return (bool)$valid;
    }

    /**
     *
     * render composite element
     *
     * @return string
     */
    public function render()
    {
        $localizedLength = LocaleFormat::getInstance()->numericToLocalized(
            $this->getValue(ShippingModel::DIMENSION_LENGTH), true);
        $localizedWidth = LocaleFormat::getInstance()->numericToLocalized(
            $this->getValue(ShippingModel::DIMENSION_WIDTH), true);
        $localizedHeight = LocaleFormat::getInstance()->numericToLocalized(
            $this->getValue(ShippingModel::DIMENSION_HEIGHT), true);

        return $this->getPrefix() . ' '
            . '<input type="' . $this->_type . '" '
            . 'name="' . $this->_name . '[' . ShippingModel::DIMENSION_LENGTH . ']" '
            . $this->renderAttributes(ShippingModel::DIMENSION_LENGTH)
            . 'value="' . $localizedLength . '" '
            . $this->_endTag . ' '
            . ' x '
            . '<input type="' . $this->_type . '" '
            . 'name="' . $this->_name . '[' . ShippingModel::DIMENSION_WIDTH . ']" '
            . $this->renderAttributes(ShippingModel::DIMENSION_WIDTH)
            . 'value="' . $localizedWidth . '" '
            . $this->_endTag . ' '
            . ' x '
            . '<input type="' . $this->_type . '" '
            . 'name="' . $this->_name . '[' . ShippingModel::DIMENSION_HEIGHT . ']" '
            . $this->renderAttributes(ShippingModel::DIMENSION_HEIGHT)
            . 'value="' . $localizedHeight . '" '
            . $this->_endTag . ' '
            . $this->getSuffix();
    }

}

