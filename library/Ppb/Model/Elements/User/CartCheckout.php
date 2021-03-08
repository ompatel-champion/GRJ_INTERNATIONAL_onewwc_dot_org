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

/**
 * this model is used for creating the cart checkout page, specifically the address
 * and user registration sections
 */

namespace Ppb\Model\Elements\User;

use Ppb\Model\Elements\User,
    Ppb\Db\Table\Row\User as UserModel,
    Ppb\Model\Shipping as ShippingModel,
    Ppb\Service\UsersAddressBook;

class CartCheckout extends User
{
    /**
     * billing address fields prefix
     */
    const PRF_BLG = 'blg_';
    /**
     * shipping address fields prefix
     */
    const PRF_SHP = 'shp_';

    /**
     *
     * sale object
     *
     * @var \Ppb\Db\Table\Row\Sale
     */
    protected $_sale;

    /**
     *
     * seller object
     *
     * @var \Ppb\Db\Table\Row\User
     */
    protected $_seller;

    /**
     *
     * populated with the available addresses from the address book
     *
     * @var array
     */
    protected $_addressMultiOptions = array();

    /**
     *
     * set user
     *
     * @param \Ppb\Db\Table\Row\User $user
     *
     * @return $this
     */
    public function setUser(UserModel $user = null)
    {
        parent::setUser($user);

        if ($this->_user instanceof UserModel) {
            $this->setAddressMultiOptions();
        }

        return $this;
    }

    /**
     *
     * get sale object
     *
     * @return \Ppb\Db\Table\Row\Sale
     */
    public function getSale()
    {
        return $this->_sale;
    }

    /**
     *
     * set sale object
     *
     * @param \Ppb\Db\Table\Row\Sale $sale
     *
     * @return $this
     */
    public function setSale($sale)
    {
        $this->_sale = $sale;

        return $this;
    }

    /**
     *
     * get seller object
     *
     * @return \Ppb\Db\Table\Row\User
     */
    public function getSeller()
    {
        return $this->_seller;
    }

    /**
     *
     * set seller object
     *
     * @param \Ppb\Db\Table\Row\User $seller
     *
     * @return $this
     */
    public function setSeller($seller)
    {
        $this->_seller = $seller;

        return $this;
    }

    /**
     *
     * get address multi options
     *
     * @return array
     */
    public function getAddressMultiOptions()
    {
        return $this->_addressMultiOptions;
    }

    /**
     *
     * set address multi options
     *
     * @return $this
     */
    public function setAddressMultiOptions()
    {
        if ($this->_user instanceof UserModel) {
            $usersAddressBook = new UsersAddressBook();
            $addressMultiOptions = $usersAddressBook->getMultiOptions($this->_user, '<br>', true);

            if (count($addressMultiOptions) > 0) {

                $addressMultiOptions[0] = array(
                    'title'      => $this->getTranslate()->_('New address'),
                    'locationId' => null,
                    'postCode'   => null,
                );
            }

            $this->_addressMultiOptions = $addressMultiOptions;
        }

        return $this;
    }

    public function getElements()
    {
        $allElements = parent::getElements();

        $settings = $this->getSettings();

        $translate = $this->getTranslate();

        $addressMultiOptions = $this->getAddressMultiOptions();

        $allElements[] = array(
            'form_id'      => 'address',
            'id'           => 'address_id',
            'element'      => (count($addressMultiOptions) > 0) ? '\Ppb\Form\Element\SelectAddress' : 'hidden',
            'label'        => $this->_('Select Address'),
            'multiOptions' => $addressMultiOptions,
            'bodyCode'     => '
                    <script type="text/javascript">
                        AddressSelect();

                        $(document).on(\'change\', \'[name="address_id"]\', function() {
                            AddressSelect();
                        });

                        function AddressSelect() {
                            var addressId = parseInt($(\'input:radio[name="address_id"]:checked\').val());
                            if (addressId > 0) {
                                $(\'[name^="prefix"]\').closest(\'.form-group\').hide();
                            }
                            else {
                                $(\'[name^="prefix"]\').closest(\'.form-group\').show();
                            }
                            $(\'[name="address_id"]\').closest(\'.form-group\').show();
                        }
                    </script>',
        );

        $sale = $this->getSale();

        // first we add the global and billing address fields, and merge with the address_id and alternate shipping checkbox fields
        $elements = array_merge(
            $this->getElementsWithFilter('form_id', array('global', 'address'), $allElements),
            array(
                array(
                    'form_id'  => 'checkout',
                    'id'       => 'sale_id',
                    'element'  => 'hidden',
                    'bodyCode' => '<script type="text/javascript">
                        CalculateOrderPostage();
                        CartCheckoutDetails();

                        $(document).on(\'change\', \'[name="alt_ship"], [name$="address_id"], [name$="zip_code"], [name$="country"]\', function() {
                            $(\'#shipping-options\').html("' . $translate->_('Please wait ...') . '");

                            CalculateOrderPostage();
                            CartCheckoutDetails();
                        });
                        
                        $(document).on("click", \'[name="payment_method_id"], [name="postage_id"], [name="apply_insurance"]\', function() {
                            CartCheckoutDetails();
                        });
                        
                        $(document).on("change", \'[name$="address"], [name$="city"], [name$="state"], [name$="name"], [name$="phone"]\', function() {
                            CartCheckoutDetails();
                        });
                
                        $(document).on("click", \'input[name = "voucher_add"]\', function(e) { 
                            e.preventDefault();                
                            CartCheckoutDetails();           
                        });
    
                        function CalculateOrderPostage() {
                            let postCode = "";
                            let locationId = "";

                            if ($(\'input:checkbox[name="alt_ship"]\').is(\':checked\')) {
                                let selectedAddress = $(\'input:radio[name="' . self::PRF_SHP . 'address_id"]:checked\');
                                let addressId = parseInt(selectedAddress.val());

                                if (addressId > 0) {
                                    postCode = selectedAddress.attr(\'data-post-code\');
                                    locationId = selectedAddress.attr(\'data-location-id\');
                                }
                                else {
                                    postCode = $(\'[name="' . self::PRF_SHP . 'zip_code"]\').val();
                                    locationId = $(\'[name="' . self::PRF_SHP . 'country"]\').val();
                                }
                            }
                            else {
                                let selectedAddress = $(\'input:radio[name="' . self::PRF_BLG . 'address_id"]:checked\');
                                let addressId = parseInt(selectedAddress.val());

                                if (addressId > 0) {
                                    postCode = selectedAddress.attr(\'data-post-code\');
                                    locationId = selectedAddress.attr(\'data-location-id\');
                                }
                                else {
                                    postCode = $(\'[name="' . self::PRF_BLG . 'zip_code"]\').val();
                                    locationId = $(\'[name="' . self::PRF_BLG . 'country"]\').val();
                                }
                            }

                            if (postCode && locationId) {
                                $(\'#shipping-options\').calculatePostage({
                                    selector: \'.form-checkout\',
                                    postUrl: paths.calculatePostage,
                                    locationId: locationId,
                                    postCode: postCode,
                                    postageId: $(\'input:radio[name="postage_id"]:checked\').val(),
                                    enableSelection: 1,
                                    formSubmit: false
                                });
                            }
                            else {
                                $(\'#shipping-options\').html("' . $translate->_('Please enter your delivery address.') . '");
                            }
                        }
                        
                        function CartCheckoutDetails()
                        {                        
                            $(document).on("click", \'input:radio[name="postage_id"]\', function() {
                                $(\'input:hidden[name="postage_id"]\').val($(\'input:radio[name="postage_id"]:checked\').val()); 
                            });
                            
                            $.ajax({
                                method: "post",
                                url: "' . $this->getView()->url(array('module' => 'listings', 'controller' => 'cart', 'action' => 'cart-checkout-details')) . '",
                                data: $(".form-checkout").serialize(),
                                dataType: "json",
                                success: function (data) {
                                    $(".au-checkout-details").html(data.cartCheckoutDetails);
                                    $(".au-voucher-message").html(data.voucherMessage);
                                    
                                    feather.replace();
                                }
                            });
                        }
                    </script > '
                ),
            )
        );

        $object = $this;
        array_walk($elements, function (&$element) use (&$object) {
            $element = $object->_prepareElementData($element, CartCheckout::PRF_BLG);
        });

        // if the user is not registered, add basic registration fields
        if (!$this->_user) {
            $elements = array_merge($elements, $this->getElementsWithFilter('form_id', array('basic'), $allElements));
        }

        if ($settings['enable_shipping']) {
            if ($sale->isPickupOnly()) {
                $elements[] = array(
                    'form_id' => 'checkout',
                    'id'      => 'postage_id',
                    'element' => 'hidden',
                    'value'   => ShippingModel::KEY_PICK_UP
                );
            }
            else {
                $elements[] = array(
                    'form_id'      => 'checkout',
                    'id'           => 'alt_ship',
                    'subtitle'     => $this->_('Delivery Address'),
                    'element'      => 'checkbox',
                    'multiOptions' => array(
                        1 => $translate->_('Deliver to a different address'),
                    ),
                );

                // now add shipping address fields
                $shippingAddressElements = $this->getElementsWithFilter('form_id', array('address'), $allElements);
                array_walk($shippingAddressElements, function (&$element) use (&$object) {
                    $element = $object->_prepareElementData($element, CartCheckout::PRF_SHP);
                });

                $elements = array_merge($elements, $shippingAddressElements);

                $elements[] = array(
                    'form_id'  => 'checkout',
                    'id'       => 'postage_id',
                    'subtitle' => $this->_('Shipping Method'),
                    'element'  => '\\Ppb\\Form\\Element\\HtmlHidden',
                    'label'    => $this->_('Select Shipping Method'),
                    'html'     => '<span id="shipping-options">' . $translate->_('Please wait...') . '</span>',
                    'bodyCode' => '<script type="text/javascript">
                        displayDeliveryAddress();                       
                        
                        $(document).on("change", \'[name="alt_ship"]\', function() {
                            displayDeliveryAddress();
                        });

                        function displayDeliveryAddress() {
                            if ($(\'input:checkbox[name="alt_ship"]\').is(\':checked\')) {
                                $("[name^=\'' . self::PRF_SHP . 'address_id\']").closest(\'.form-group\').show();
                                $("[name^=\'' . self::PRF_SHP . 'address_id\']").trigger("click");
                            }
                            else {
                                $("[name^=\'' . self::PRF_SHP . '\']").closest(\'.form-group\').hide();
                            }
                            
                        }
                    </script>',
                    'required' => true,
                );

                $shippingModel = $this->getSeller()->getShipping();

                $insuranceAmount = $shippingModel->calculateInsurance();

                $elements[] = array(
                    'form_id'      => 'checkout',
                    'id'           => 'apply_insurance',
                    'element'      => ($insuranceAmount > 0) ? 'checkbox' : 'hidden',
                    'label'        => $this->_('Apply Insurance'),
                    'multiOptions' => array(
                        1 => $this->getView()->amount($insuranceAmount, $sale['currency'], '+%s')
                    )
                );
                $elements[] = array(
                    'form_id' => 'checkout',
                    'id'      => 'insurance_amount',
                    'element' => 'hidden',
                    'value'   => $insuranceAmount,
                );
            }
        }

        // payment method radio element (offline & direct)
        $paymentMethods = $sale->getPaymentMethods();
        $paymentMethodMultiOptions = array();
        foreach ($paymentMethods as $method) {
            $id = ($method['type'] == 'direct') ? $method['id'] : (-1) * $method['id'];
            $paymentMethodMultiOptions[$id] = $method['name'];
        }

        $elements[] = array(
            'form_id'      => 'checkout',
            'id'           => 'payment_method_id',
            'subtitle'     => $this->_('Payment Method'),
            'element'      => 'radio',
            'label'        => $this->_('Select Payment Method'),
            'multiOptions' => $paymentMethodMultiOptions,
            'required'     => true,
        );

        return array_merge($elements, parent::getRelatedElements());
    }

    /**
     *
     * processes an element array item
     *
     * @param array  $element
     * @param string $prefix
     *
     * @return array
     */
    protected function _prepareElementData($element, $prefix)
    {
        // check if we are using an existing address
        $existingAddress = $this->getData($prefix . 'address_id');

        // alter body code column in all rows
        if (!empty($element['bodyCode'])) {
            $element['bodyCode'] = str_replace(
                array(
                    '[name="country"]',
                    '[name="state"]',
                    "name: 'state'",
                    'ChangeState()',
                    'AddressSelect()',
                    '[name="address_id"]',
                    '[name^="prefix"]',

                ),
                array(
                    '[name="' . $prefix . 'country"]',
                    '[name="' . $prefix . 'state"]',
                    "name: '" . $prefix . "state'",
                    $prefix . 'ChangeState()',
                    $prefix . 'AddressSelect()',
                    '[name="' . $prefix . 'address_id"]',
                    '[name^="' . $prefix . '"]',
                ), $element['bodyCode']);
        }

        // generate state field to work correctly
        if ($element['id'] == 'country') {
            $element['forceDefault'] = true;
        }

        if ($prefix == self::PRF_SHP && !$this->getData('alt_ship')) {
            $element['required'] = false;
            $element['validators'] = array();
        }

        // if we select an existing address, the address form fields will not be required and will be hidden using javascript
        if (array_intersect((array)$element['form_id'], array('address'))) {
            if ($existingAddress && $element['id'] != 'address_id') {
                $element['required'] = false;
                $element['validators'] = array();
                if (!empty($element['attributes']['class'])) {
                    $element['attributes']['class'] .= ' address-field';
                }
                else {
                    $element['attributes']['class'] = 'address-field';
                }
            }

            $element['id'] = $prefix . $element['id'];
        }

        return $element;
    }
}