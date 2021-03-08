<?php

/**
 *
 * PHP Pro Bid
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2020 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     8.2 [rev.8.2.02]
 */

namespace Ppb\Model\Elements;

use Cube\Controller\Front,
    Listings\Form\Purchase as PurchaseForm,
    Ppb\Db\Table\Row\User as UserModel,
    Ppb\Service\UsersAddressBook as UsersAddressBookService,
    Ppb\Model\Shipping as ShippingModel,
    Cube\Validate;

class Purchase extends AbstractElements
{

    /**
     *
     * listing
     *
     * @var \Ppb\Db\Table\Row\Listing
     */
    protected $_listing;

    /**
     *
     * buyer
     *
     * @var \Ppb\Db\Table\Row\User
     */
    protected $_buyer;

    /**
     *
     * class constructor
     *
     * @param mixed $formId
     */
    public function __construct($formId = null)
    {
        parent::__construct();

        $this->setFormId($formId);
    }

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
     * get buyer
     *
     * @return \Ppb\Db\Table\Row\User
     */
    public function getBuyer()
    {
        return $this->_buyer;
    }

    /**
     *
     * set buyer
     *
     * @param \Ppb\Db\Table\Row\User $buyer
     *
     * @return $this
     */
    public function setBuyer($buyer)
    {
        $this->_buyer = $buyer;

        return $this;
    }

    /**
     *
     * generate custom field creation form elements
     *
     * @return array
     */
    public function getElements()
    {
        $view = $this->getView();

        $settings = $this->getSettings();
        $translate = $this->getTranslate();

        $listing = $this->getListing();
        $buyer = $this->getBuyer();

        /** @var \Ppb\Db\Table\Row\User $seller */
        $seller = $listing->findParentRow('\Ppb\Db\Table\Users');

        $offerAmountValidators = array(
            'Numeric',
            array('GreaterThan', array($listing['make_offer_min'], ($listing['make_offer_min'] > 0) ? true : false, true)),
            array('\\Ppb\\Validate\\DuplicateOffer', array($listing['id'], $buyer['id']))
        );

        if ($listing['make_offer_max'] > 0) {
            $offerAmountValidators[] = array('LessThan', array($listing['make_offer_max'], true));
        }

        $elements = array(
            array(
                'form_id'     => array('bid'),
                'id'          => 'bid_amount',
                'element'     => '\\Ppb\\Form\\Element\\LocalizedNumeric',
                'label'       => $this->_('Bid Amount'),
                'description' => sprintf($translate->_('Minimum Bid: %s'), $view->amount($listing->minimumBid(), $listing['currency'])),
                'prefix'      => $listing['currency'],
                'attributes'  => array(
                    'class' => 'form-control input-mini',
                ),
                'filters'     => array(
                    '\\Ppb\\Filter\\LocalizedNumeric',
                ),
                'validators'  => array(
                    'Numeric',
                    array('GreaterThan', array($listing->minimumBid(), true, true)),
                ),
            ),
            array(
                'form_id'    => array('bid'),
                'id'         => PurchaseForm::BTN_PLACE_BID,
                'element'    => 'button',
                'type'       => 'submit',
                'value'      => $this->_('Place Bid'),
                'attributes' => array(
                    'class' => 'btn btn-primary btn-lg',
                ),
            ),
        );


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
                                    $array['id'] = $listing['id'];
                                    $array['options'] = \Ppb\Utility::unserialize($array['options']);
                                    $array['quantity'] = $listing->getAvailableQuantity(null, $array['options']);
                                    $array['priceDisplay'] = $view->amount($price, $listing['currency']);
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
                                            var id = 0;
                                            
                                            if (typeof name !== 'undefined') { 
                                                id = parseInt(name.replace('product_attributes[', '').replace(']', ''));
                                            }

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

                                        function updateQuantityPriceDisplay(elements, container) {
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

                                                    if (quantityDescription) {
                                                        if ((availableQuantity > lowStockThreshold) || (availableQuantity === -1)) {
                                                            availableQuantity = msg.inStock;
                                                        }
                                                        else if (availableQuantity > 0) {
                                                            availableQuantity = msg.lowStock;
                                                        }
                                                        else {
                                                            availableQuantity = msg.outOfStock;
                                                        }
                                                    }

                                                    $(container).find('.current-price-' + array[i].id).text(priceDisplay);
                                                    $('#quantity-available').text(availableQuantity);
                                                }
                                            }
                                        }
                                        
                                        function productAttributesUpdates(container) {
                                            var elements = $(container).find('[name^=\"product_attributes\"]');
                                            
                                            setDisabledOptions(elements.first(), {});
                                            updateQuantityPriceDisplay(elements, container);

                                            elements.on('change', function () {
                                                setDisabledOptions($(this), {});
                                                updateQuantityPriceDisplay(elements, container);
                                            });
                                        } 
                                        
                                        $(document).ready(function () {
                                            productAttributesUpdates($('#details')); // listing details page
                                            productAttributesUpdates($('.bootbox-body')); // modal confirm form
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

        if ($settings['enable_shipping'] && $buyer instanceof UserModel) {
            $usersAddressBook = new UsersAddressBookService();
            $multiOptions = $usersAddressBook->getMultiOptions($buyer);

            $elements[] = array(
                'form_id'      => array('buy'),
                'id'           => 'shipping_address_id',
                'element'      => 'select',
                'label'        => $this->_('Shipping Address'),
                'attributes'   => array(
                    'class'    => 'form-control input-default field-changeable',
                    'onchange' => ($this->getData('modal')) ? '' : 'this.form.submit();',
                ),
                'multiOptions' => $multiOptions,
                'required'     => true,
                'hideDefault'  => true,
            );

            $elements[] = array(
                'form_id'            => array('buy'),
                'id'                 => 'postage_id',
                'element'            => '\\Ppb\\Form\\Element\\PostageMethod',
                'label'              => $this->_('Postage Method'),
                'attributes'         => array(
                    'class' => 'form-control input-medium'
                ),
                'shippingModelArray' => array(
                    'input'      => array($listing['id'] => $this->getData('quantity')),
                    'locationId' => $buyer['country'],
                    'postCode'   => $buyer['zip_code']
                ),
                'required'           => true,
            );

            if ($listing[ShippingModel::FLD_INSURANCE] > 0) {
                $elements[] = array(
                    'form_id'      => array('buy'),
                    'id'           => 'apply_insurance',
                    'element'      => 'checkbox',
                    'label'        => $this->_('Apply Insurance'),
                    'multiOptions' => array(
                        1 => $view->amount($listing[ShippingModel::FLD_INSURANCE], $listing['currency']),
                    ),
                );
            }
        }

        $availableQuantity = $listing->getAvailableQuantity(null, $this->getData('product_attributes'));

        $quantityValidators = array(
            'Digits',
            array('LessThan', array($availableQuantity, true)),
        );

        if ($availableQuantity == 0) {
            $quantityOutOfStockValidator = new Validate\NotEmpty();
            $quantityOutOfStockValidator->setMessage('This product is out of stock');
            $quantityValidators[] = $quantityOutOfStockValidator;
        }

        $elements = array_merge($elements,
            array(
                array(
                    'form_id'    => array('buy', 'offer', 'cart'),
                    'id'         => 'quantity',
                    'element'    => ($listing->isProduct()) ? '\\Ppb\\Form\\Element\\Quantity' : false,
                    'label'      => $this->_('Quantity'),
                    'attributes' => array(
                        'class' => 'form-control input-small',
                    ),
                    'validators' => $quantityValidators,
                ),
                array(
                    'form_id'    => array('buy'),
                    'id'         => PurchaseForm::BTN_BUY_OUT,
                    'element'    => 'button',
                    'type'       => 'submit',
                    'value'      => $this->_('Buy Out'),
                    'attributes' => array(
                        'class' => 'btn btn-primary btn-lg',
                    ),
                ),
                array(
                    'form_id'    => array('cart'),
                    'id'         => PurchaseForm::BTN_ADD_TO_CART,
                    'element'    => 'button',
                    'type'       => 'submit',
                    'value'      => $this->_('Add to Cart'),
                    'attributes' => array(
                        'class' => 'btn btn-primary btn-lg',
                    ),
                ),
                array(
                    'form_id'     => array('offer'),
                    'id'          => 'offer_amount',
                    'element'     => '\\Ppb\\Form\\Element\\LocalizedNumeric',
                    'label'       => $this->_('Offer Amount'),
                    'description' => ($seller->displayMakeOfferRanges()) ? $view->offerRanges($listing) : null,
                    'prefix'      => $listing['currency'],
                    'suffix'      => $this->_('/ item'),
                    'attributes'  => array(
                        'class' => 'form-control input-mini',
                    ),
                    'filters'     => array(
                        '\\Ppb\\Filter\\LocalizedNumeric',
                    ),
                    'validators'  => $offerAmountValidators,
                ),
                array(
                    'form_id'    => array('offer'),
                    'id'         => PurchaseForm::BTN_MAKE_OFFER,
                    'element'    => 'button',
                    'type'       => 'submit',
                    'value'      => $this->_('Make Offer'),
                    'attributes' => array(
                        'class' => 'btn btn-primary btn-lg',
                    ),
                ),
                array(
                    'form_id' => array('global'),
                    'id'      => 'modal',
                    'element' => 'hidden',
                ),
                array(
                    'form_id' => array('global'),
                    'id'      => 'summary',
                    'element' => 'hidden',
                ),
                array(
                    'form_id' => array('buy'),
                    'id'      => 'voucher_code',
                    'element' => 'hidden',
                ),
            )
        );

        return $this->_arrayMergeOrdering($elements, parent::getRelatedElements());
    }

}

