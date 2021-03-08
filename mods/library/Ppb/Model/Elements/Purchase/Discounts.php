<?php

/**
 *
 * PHP Pro Bid
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2018 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     8.0 [rev.8.0.01]
 */
/**
 * MOD:- DISCOUNT RULES
 */

/**
 * class that will generate extra elements for the admin elements model
 * we can have a multiple number of such classes, they just need to have a different name
 * any elements in this class will override original elements
 */

namespace Ppb\Model\Elements\Purchase;

use Ppb\Model\Elements\AbstractElements,
    Cube\Controller\Front;

class Discounts extends AbstractElements
{
    /**
     *
     * related class
     *
     * @var bool
     */
    protected $_relatedClass = true;

    /**
     *
     * listing
     *
     * @var \Ppb\Db\Table\Row\Listing
     */
    protected $_listing;

    /**
     *
     * get listing
     *
     * @return \Ppb\Db\Table\Row\Listing
     */
    public function getListing()
    {
        return $this->_listing;
    }

    /**
     *
     * set listing
     *
     * @param \Ppb\Db\Table\Row\Listing $listing
     *
     * @return $this
     */
    public function setListing($listing)
    {
        $this->_listing = $listing;

        return $this;
    }

    /**
     *
     * get model elements
     *
     * @return array
     */
    public function getElements()
    {
        $translate = $this->getTranslate();

        $listing = $this->getListing();

        /** @var \Ppb\Db\Table\Row\User $seller */
        $seller = $listing->findParentRow('\Ppb\Db\Table\Users');

        $elements = array();

        if ($listing->isProduct()) {
            $customFields = $listing->getCustomFields();

            $addBodyCode = false;

            if (count($customFields) > 0) {
                foreach ($customFields as $customField) {
                    if ($customField['product_attribute']) {
                        $multiOptions = array();
                        if (!empty($customField['multiOptions'])) {
                            $multiOptions = \Ppb\Utility::unserialize($customField['multiOptions']);
                            $multiOptions = array_filter(array_combine($multiOptions['key'], $multiOptions['value']));
                            $multiOptions = array_intersect_key($multiOptions, array_flip((array)$customField['value']));
                        }

                        if (count($multiOptions) > 0) {

                            /** @var \Cube\Form\Element\Select $productAttributeElement */
                            $bodyCode = null;

                            if (!empty($listing['stock_levels']) && !$addBodyCode) {
                                $addBodyCode = true;
                                $stockLevels = \Ppb\Utility::unserialize($listing['stock_levels']);

                                $view = $this->getView();
                                array_walk($stockLevels, function (&$array) use (&$listing, &$view) {
                                    $price = $listing['buyout_price'] + floatval($array['price']);
                                    $array['options'] = \Ppb\Utility::unserialize($array['options']);
                                    $array['quantity'] = $listing->getAvailableQuantity(null, $array['options']);
                                    $array['priceDisplay'] = $view->amount($price, $listing['currency']);

                                    ## -- START :: ADD -- [ MOD:- DISCOUNT RULES @version 1.0 ]
                                    $array['originalPrice'] = null;

                                    $discountedPrice = $listing->discountedPrice($array['options']);

                                    if ($discountedPrice > 0) {
                                        $array['priceDisplay'] = $view->amount($discountedPrice, $listing['currency']);
                                        $array['originalPrice'] = $view->amount($price, $listing['currency']);
                                    }
                                    ## -- END :: ADD -- [ MOD:- DISCOUNT RULES @version 1.0 ]
                                });

                                $stockLevels = array_filter($stockLevels, function (&$array) {
                                    return ($array['quantity'] > 0);
                                });

                                $lowStockThreshold = (($lowStockThreshold = $seller->getGlobalSettings('quantity_low_stock')) > 0) ?
                                    $lowStockThreshold : 1;

                                $baseUrl = Front::getInstance()->getRequest()->getBaseUrl();

                                $bodyCode = '<script type="text/javascript" src="' . $baseUrl . '/js/phpjs/array_intersect_assoc.js"></script>' . "\n"
                                    . "<script type=\"text/javascript\">
                                        var array = $.parseJSON('" . json_encode($stockLevels) . "');

                                        function setDisabledOptions(element, selected) {
                                            var value = element.val();
                                            var name = element.prop('name');
                                            var id = parseInt($(element).prop('name').replace('product_attributes[', '').replace(']', ''));

                                            var search = selected;

                                            var newSelection = false;

                                            $('option', element).each(function () {
                                                search[id] = $(this).val();

                                                var exists = arrayCheck(search);

                                                if (!exists) {
                                                    if ($(this).prop('selected')) {
                                                        newSelection = true;
                                                    }
                                                    $(this).prop('disabled', true).removeProp('selected');
                                                }
                                                else {
                                                    $(this).prop('disabled', false);
                                                }
                                            });


                                            if (newSelection) {
                                                var enabledOption = $('option:not([disabled])', element).first();
                                                enabledOption.prop('selected', true);
                                                value = enabledOption.val();
                                            }

                                            selected[id] = value;

                                            var nextElement = element.closest('.form-group').next().find('select[name^=\"product_attributes\"]');

                                            if (nextElement.length > 0) {
                                                setDisabledOptions(nextElement, selected);
                                            }
                                        }

                                        function arrayCheck(search) {
                                            var exists = false;

                                            for (var i in array) {
                                                var src = array_intersect_assoc(search, array[i].options);

                                                if (JSON.stringify(src) === JSON.stringify(search)) {
                                                    exists = true;
                                                }
                                            }

                                            return exists;
                                        }

                                        function updateQuantityPriceDisplay(elements) {
                                            var msg = {
                                                inStock: '" . $translate->_('In Stock') . "',
                                                lowStock: '" . $translate->_('Low Stock') . "',
                                                outOfStock: '" . $translate->_('Out of Stock') . "'
                                            };

                                            var quantityDescription = " . intval($seller->getGlobalSettings('quantity_description')) . ";
                                            var lowStockThreshold = " . $lowStockThreshold . ";

                                            selected = {};
                                            $(elements).each(function() {
                                                var id = parseInt($(this).prop('name').replace('product_attributes[', '').replace(']', ''));
                                                selected[id] = $(this).val();
                                            });

                                            for (var i in array) {
                                                if (JSON.stringify(array[i].options) === JSON.stringify(selected)) {
                                                    var availableQuantity = array[i].quantity;
                                                    var priceDisplay = array[i].priceDisplay;
                                                    /* ## -- START :: ADD -- [ MOD:- DISCOUNT RULES @version 1.0 ] */
                                                    var originalPrice = array[i].originalPrice;
                                                    /* ## -- END :: ADD -- [ MOD:- DISCOUNT RULES @version 1.0 ] */

                                                    if (quantityDescription) {
                                                        if ((availableQuantity > lowStockThreshold) || (availableQuantity = -1)) {
                                                            availableQuantity = msg.inStock;
                                                        }
                                                        else if (availableQuantity > 0) {
                                                            availableQuantity = msg.lowStock;
                                                        }
                                                        else {
                                                            availableQuantity = msg.outOfStock;
                                                        }
                                                    }

                                                    /* ## -- START :: ADD -- [ MOD:- DISCOUNT RULES @version 1.0 ] */
                                                    $('#original-price').text(originalPrice);
                                                    /* ## -- END :: ADD -- [ MOD:- DISCOUNT RULES @version 1.0 ] */
                                                    $('#product-price').text(priceDisplay);
                                                    $('#quantity-available').text(availableQuantity);
                                                }
                                            }
                                        }


                                        $(document).ready(function () {
                                            var elements = $('[name^=\"product_attributes\"]');
                                            var selected = {};

                                            var element = elements.first();
                                            setDisabledOptions(element, selected);
                                            updateQuantityPriceDisplay(elements);

                                            elements.on('change', function () {
                                                var selected = {};

                                                setDisabledOptions($(this), selected);
                                                updateQuantityPriceDisplay(elements);
                                            });
                                        });
                                    </script>";
                            }

                            $elements[] = array(
                                'form_id'      => array('buy', 'offer', 'cart'),
                                'id'           => 'product_attributes[' . $customField['id'] . ']',
                                'element'      => 'select',
                                'label'        => $customField['label'],
                                'attributes'   => array(
                                    'class' => 'form-control input-default',
                                ),
                                'required'     => true,
                                'hideDefault'  => true,
                                'multiOptions' => $multiOptions,
                                'bodyCode'     => $bodyCode,
                            );
                        }

                    }
                }
            }
        }

        return $elements;
    }
}

