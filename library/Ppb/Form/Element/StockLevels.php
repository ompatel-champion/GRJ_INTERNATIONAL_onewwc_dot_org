<?php

/**
 *
 * PHP Pro Bid
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2020 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     8.2 [rev.8.2.01]
 */

/**
 * listing stock levels form element
 * allows the creation of the composite element used if we have product attributes
 * product attributes are generated from custom fields
 */

namespace Ppb\Form\Element;

use Cube\Form\Element,
    Cube\Controller\Front,
    Ppb\View\Helper\ProductAttributes as ProductAttributesHelper;
use Cube\Locale\Format as LocaleFormat,
    Ppb\Filter\LocalizedNumeric as LocalizedNumericFilter;

class StockLevels extends TextAutocomplete
{

    const FIELD_OPTIONS = 'options';
    const FIELD_QUANTITY = 'quantity';
    const FIELD_PRICE = 'price';

    /**
     *
     * custom fields array
     *
     * @var array
     */
    protected $_customFields;

    /**
     *
     * form data - needed to display only selected product attributes
     *
     * @var array
     */
    protected $_formData;

    /**
     *
     * whether we have an empty stock levels element
     *
     * @var bool
     */
    protected $_empty = false;

    public function __construct($name)
    {
        parent::__construct($name);

        $baseUrl = Front::getInstance()->getRequest()->getBaseUrl();

        $translate = $this->getTranslate();

        $this->setBodyCode('<script type="text/javascript" src="' . $baseUrl . '/js/bootbox.min.js"></script>')
            ->setBodyCode(
                "<script type=\"text/javascript\">
                    $(document).on('click', 'button[name=\"" . $this->_name . "\"]', function(e) {
                        e.preventDefault();

                        var btn = $(this);

                        bootbox.confirm(\"" . $translate->_('Do you wish to set the same quantity value on all fields below?') . "\", function(result) {
                            if (result) {
                                var quantity = btn.closest('div').find('input[name*=\"" . self::FIELD_QUANTITY . "\"]').val();
                                btn.closest('.form-group').find('input[name*=\"" . self::FIELD_QUANTITY . "\"]').val(quantity);
                            }
                        });
                    });
                </script>")
            ->setFilters(array(
                new LocalizedNumericFilter(),
            ));
    }

    /**
     *
     * get custom fields array
     *
     * @return array
     */
    public function getCustomFields()
    {
        return $this->_customFields;
    }

    /**
     *
     * set custom fields array
     *
     * @param array $customFields
     *
     * @return $this
     */
    public function setCustomFields($customFields)
    {
        $this->_customFields = $customFields;

        return $this;
    }

    /**
     *
     * get form data
     *
     * @param string $key
     *
     * @return mixed
     */
    public function getFormData($key = null)
    {
        if ($key !== null) {
            if (!empty($this->_formData[$key])) {
                return $this->_formData[$key];
            }

            return null;
        }

        return $this->_formData;
    }

    /**
     *
     * set form data
     *
     * @param array $formData
     *
     * @return $this
     */
    public function setFormData($formData)
    {
        $this->_formData = $formData;

        return $this;
    }

    /**
     *
     * check empty flag
     *
     * @return boolean
     */
    public function isEmpty()
    {
        return $this->_empty;
    }

    /**
     *
     * set empty flag
     *
     * @param boolean $empty
     *
     * @return $this
     */
    public function setEmpty($empty)
    {
        $this->_empty = $empty;

        return $this;
    }


    public function render()
    {
        $output = null;

        $array = array();
        $customFields = $this->getCustomFields();

        foreach ($customFields as $key => $customField) {
            if ($customField['product_attribute']) {

                $customFields[$key]['multiOptions']
                    = $multiOptions
                    = \Ppb\Utility::unserialize($customField['multiOptions']);

                if (!empty($multiOptions['key'])) {
                    $id = intval(str_replace('custom_field_', '', $customField['id']));
                    $value = array_filter($multiOptions['key']);
                    $customFieldData = (array)$this->getFormData($customField['id']);

                    $array[$id] = array_intersect($customFieldData, $value);
                }
            }
        }

        $cartesian = array_filter(
            $this->_cartesian($array));

        if (count($cartesian) > 0) {
            $values = $this->getValue();
            $translate = $this->getTranslate();

            $cloneButton = true;

            $helper = new ProductAttributesHelper();
            foreach ($cartesian as $key => $row) {
                $value = str_ireplace(
                    array("'", '"'),
                    array('&#039;', '&quot;'), serialize($row));


                $price = null;
                $quantity = null;

                foreach ((array)$values as $k => $v) {
                    if (!empty($values[$k][self::FIELD_OPTIONS])) {
                        if (\Ppb\Utility::unserialize($values[$k][self::FIELD_OPTIONS]) == $row) {
                            $quantity = (!empty($values[$k][self::FIELD_QUANTITY])) ?
                                abs(intval($values[$k][self::FIELD_QUANTITY])) : null;
                            $price = (!empty($values[$k][self::FIELD_PRICE])) ?
                                abs(LocaleFormat::getInstance()->localizedToNumeric($values[$k][self::FIELD_PRICE])) : null;
                        }
                    }
                }


                $output .= '<input type="hidden" name="' . $this->_name . '[' . $key . '][' . self::FIELD_OPTIONS . ']" '
                    . 'value="' . $value . '">';

                $this->removeAttribute('placeholder')->addAttribute('placeholder', $translate->_('Qty'));

                $output .= '<div class="row mb-1">'
                    . '<label class="col-sm-4 control-label">' . $helper->productAttributes($row)->display() . '</label>'
                    . '<div class="col-sm-8">'
                    . ' <input type="text" name="' . $this->_name . '[' . $key . '][' . self::FIELD_QUANTITY . ']" '
                    . $this->renderAttributes()
                    . ' value="' . $quantity . '" '
                    . $this->_endTag;

                if ($cloneButton === true) {
                    $output .= '<button type="button" name="' . $this->_name . '" class="btn btn-link">
                            <span data-feather="copy"></span>
                        </button>';
                }

                $this->removeAttribute('placeholder')->addAttribute('placeholder', $translate->_('Price'));

                $priceElement = new LocalizedNumeric($this->_name . '[' . $key . '][' . self::FIELD_PRICE . ']');
                $priceElement->setAttributes(
                    $this->getAttributes())
                    ->setValue($price);

                $output .= ' ' . $priceElement->render()
                    . '</div>'
                    . '</div>';

                $cloneButton = false;

            }
        }

        if ($output == null) {
            $this->setEmpty(true);

            $hidden = new Element\Hidden($this->_name);
            $output = $hidden->render();
        }

        return '<div class="stock-levels">' . $output . '</div>';
    }

    /**
     *
     * create the cartesian product of the input array
     *
     * @param array $input
     *
     * @return array
     */
    protected function _cartesian($input)
    {
        // filter out empty values
        $input = array_filter($input);

        $result = array(array());

        foreach ($input as $key => $values) {
            $append = array();

            foreach ($result as $product) {
                foreach ($values as $item) {
                    $product[$key] = $item;
                    $append[] = $product;
                }
            }

            $result = $append;
        }

        return $result;
    }


}

