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
 * edit/combine invoices form
 *
 */

namespace Members\Form;

use App\Form\Tables as TablesForm,
    Ppb\Service\Table\SalesListings,
    Ppb\Db\Table\Row\User as UserModel,
    Ppb\Db\Table\Row\Listing as ListingModel,
    Ppb\Service\UsersAddressBook,
    Ppb\Db\Table\Rowset\Sales as SalesRowset,
    Ppb\Model\Shipping;

class Invoices extends TablesForm
{

    /**
     *
     * buyer user model
     * needed to create the postage method drop down
     *
     * @var \Ppb\Db\Table\Row\User
     */
    protected $_buyer;

    /**
     *
     * sales rowset
     *
     * @var \Ppb\Db\Table\Rowset\Sales
     */
    protected $_sales;

    /**
     *
     * seller / admin uses the form
     *
     * @var bool
     */
    protected $_isSeller;

    /**
     *
     * form constructor
     *
     * @param \Ppb\Service\Table\SalesListings $serviceTable
     * @param \Ppb\Db\Table\Rowset\Sales       $sales  sales rowset
     * @param string                           $action form action
     */
    public function __construct(SalesListings $serviceTable, SalesRowset $sales, $action = null)
    {
        parent::__construct($serviceTable, $action);

        $settings = $this->getSettings();

        $translate = $this->getTranslate();

        $elements = $this->getElements();

        $this->setSales($sales);

        $inAdmin = $sales->getAdmin();

        /** @var \Ppb\Db\Table\Row\Sale $sale */
        $sale = $sales->getRow(0);

        if (count($sales) == 1) {
            $saleId = $sale['id'];
        }
        else {
            $saleId = array();

            foreach ($sales as $sale) {
                $saleId[] = $sale['id'];
            }
        }

        $listingsData = array();

        $ids = array();
        $listingsQty = array();

        $insuranceAmt = 0;

        /** @var \Ppb\Db\Table\Row\Sale $sale */
        foreach ($sales as $sale) {
            /** @var \Ppb\Db\Table\Rowset\SalesListings $salesListings */
            $salesListings = $sale->findDependentRowset('\Ppb\Db\Table\SalesListings');

            /** @var \Ppb\Db\Table\Row\SaleListing $saleListing */
            foreach ($salesListings as $saleListing) {
                /** @var \Ppb\Db\Table\Row\Listing $listing */
                $listing = $saleListing->findParentRow('\Ppb\Db\Table\Listings');

                if ($listing instanceof ListingModel) {
                    $listingId = $listing['id'];

                    $listingsData[] = array(
                        'id'         => $saleListing['id'],
                        'listing_id' => $listingId,
                        'name'       => $listing['name'],
                        'price'      => $saleListing['price'],
                        'quantity'   => $saleListing['quantity'],
                        'currency'   => $sale['currency'],
                    );

                    if (array_key_exists($listingId, $ids)) {
                        $listingsQty[$listingId] += $saleListing['quantity'];
                    }
                    else {
                        $ids[$listingId] = $listingId;
                        $listingsQty[$listingId] = $saleListing['quantity'];
                    }

                    if ($settings['enable_shipping']) {
                        $insuranceAmt += floatval($listing->getData(Shipping::FLD_INSURANCE)) * $saleListing['quantity'];
                    }
                }
            }
        }

        $saleData = array(
            'sale_id'             => $saleId,
            'shipping_address_id' => $sale['shipping_address_id'],
            'postage_id'          => $sale['postage_id'],
            'postage_amount'      => $sale['postage_amount'],
            'apply_insurance'     => $sale['apply_insurance'],
            'insurance_amount'    => $insuranceAmt,
            'tax_rate'            => $sale['tax_rate'],
            'edit_locked'         => $sale['edit_locked'],
        );

        $this->setIsSeller(
            $sale->isSeller($inAdmin));

        $isSeller = $this->isSeller();

        /** @var \Ppb\Db\Table\Row\User $buyer */
        $buyer = $sale->findParentRow('\Ppb\Db\Table\Users', 'Buyer');
        $buyer->setAddress($sale['shipping_address_id']);

        $this->setBuyer($buyer);

        $elementSaleId = $this->createElement('hidden', 'sale_id');
        $elementSaleId->setBodyCode('
            <script type="text/javascript">
                function InvoiceTotalsBox(updatePostageAmount = false)
                {
                    $(document).on("click", \'input:radio[name="postage_id"]\', function() {
                        $(\'input:hidden[name="postage_id"]\').val($(\'input:radio[name="postage_id"]:checked\').val()); 
                    });                                      
                    
                    if (updatePostageAmount) {
                        var postageAmount = $(\'input:radio[name="postage_id"]:checked\').data("price");
                        $(\'[name="postage_amount"]\').val(postageAmount);
                    }
                    
                    $.ajax({
                        method: "post",
                        url: "' . $this->getView()->url(array('module' => 'members', 'controller' => 'invoices', 'action' => 'invoice-totals')) . '",
                        data: $(".form-invoices").serialize(),
                        dataType: "json",
                        success: function (data) {
                            $(".au-invoice-totals").html(data.invoiceTotals);
                            
                            feather.replace();
                        }
                    });                  
                }
                
                var updatePostageAmount = false;

                if ($(\'input[name*="sale_id"]\').length > 1) {
                    updatePostageAmount = true; // update postage amount if we combine sales
                }

                InvoiceTotalsBox(updatePostageAmount);

                $(document).on("change", \'[name^="price"],  [name="postage_amount"], [name="apply_insurance"], [name="insurance_amount"], [name="tax_rate"]\', function() {
                    InvoiceTotalsBox();
                });
                
                $(document).on("click", \'[name="postage_id"]\', function() {
                    InvoiceTotalsBox(true);
                });
            </script>');
        $this->addElement($elementSaleId);

        /** @var \Cube\Form\Element $element */
        foreach ($elements as $element) {
            $elementName = $element->getName();

            if ($elementName == 'quantity') {
                $data = $element->getValue();
                $this->removeElement($elementName);

                $quantity = $this->createElement('\Ppb\Form\Element\DescriptionHidden', 'quantity');
                $quantity->setMultiple()
                    ->setValue($data);

                $this->addElement($quantity);
            }

            if ($isSeller === false) {
                if ($elementName == 'price') {
                    $data = $element->getValue();
                    $this->removeElement($elementName);

                    $price = $this->createElement('\Ppb\Form\Element\PriceDescription', 'price');
                    $price->setMultiple()
                        ->setValue($data);

                    $this->addElement($price);
                }
            }
        }

        if ($settings['enable_shipping'] && !$sale->isPickupOnly()) {
            $usersAddressBook = new UsersAddressBook();
            $multiOptions = $usersAddressBook->getMultiOptions($buyer, '<br>', true);

            $deliveryAddress = $this->createElement('\Ppb\Form\Element\SelectAddress', 'shipping_address_id');
            $deliveryAddress->setLabel('Delivery Address')
                ->setBodyCode('
                    <script type="text/javascript">
                        var initialLoad = true;
    
                        if ($(\'input[name*="sale_id"]\').length > 1) {
                            initialLoad = false; // update postage amount if we combine sales
                        }
                
                        CalculateOrderPostage(initialLoad);

                        $(document).on(\'change\', \'[name="shipping_address_id"]\', function() {
                            $(\'#shipping-options\').html("' . $translate->_('Please wait ...') . '");
                            CalculateOrderPostage();
                        });
                        
                        function CalculateOrderPostage(initialLoad = false) {
                            var postCode = "";
                            var locationId = "";
                           
                            var selectedAddress = $(\'input:radio[name="shipping_address_id"]:checked\');
                            var addressId = parseInt(selectedAddress.val());
                            
                            if (addressId > 0) {
                                postCode = selectedAddress.attr(\'data-post-code\');
                                locationId = selectedAddress.attr(\'data-location-id\');
                            }                               

                            if (postCode && locationId) {
                                var postageId = $(\'input:hidden[name="postage_id"]\').val();
                                if (typeof postageId === "undefined" || postageId === "") {
                                    postageId = 0;                                   
                                }
                                
                                $(\'#shipping-options\').calculatePostage({
                                    selector: \'.form-invoices\',
                                    postUrl: paths.calculatePostage,
                                    locationId: locationId,
                                    postCode: postCode,
                                    postageId: postageId,
                                    initialLoad: initialLoad,
                                    postageAmountField: $(\'[name="postage_amount"]\'),
                                    enableSelection: 1,
                                    invoiceTotalsBox: true,
                                    formSubmit: false
                                });      
                            }
                            else {
                                $(\'#shipping-options\').html("' . $translate->_('Please select a delivery address.') . '");
                            }
                        }
                    </script>')
                ->setMultiOptions($multiOptions)
                ->setRequired();
            $this->addElement($deliveryAddress);


            /** @var \Ppb\Form\Element\HtmlHidden $shippingMethod */
            $shippingMethod = $this->createElement('\Ppb\Form\Element\HtmlHidden', 'postage_id');
            $shippingMethod->setLabel('Shipping Method')
                ->setHtml('<span id="shipping-options">' . $translate->_('Please wait...') . '</span>')
                ->setRequired();
            $this->addElement($shippingMethod);


            $postageAmount = $this->createElement(($isSeller) ? 'text' : 'hidden',
                'postage_amount')
                ->setLabel('Postage')
                ->setPrefix($sale['currency'])
                ->setAttributes(array(
                    'class' => 'form-control input-mini',
                ));
            $this->addElement($postageAmount);

            if ($insuranceAmt > 0) {
                $insuranceCheckbox = $this->createElement('checkbox', 'apply_insurance');
                $insuranceCheckbox->setLabel('Apply Insurance')
                    ->setMultiOptions(
                        array(1 => ($isSeller) ? null : $this->getView()->amount($insuranceAmt, $sale['currency'], '+%s')));
                $this->addElement($insuranceCheckbox);

                $insuranceAmount = $this->createElement(($isSeller) ? 'text' : 'hidden',
                    'insurance_amount')
                    ->setPrefix($sale['currency'])
                    ->setAttributes(array(
                        'class' => 'form-control input-mini',
                    ));
                $this->addElement($insuranceAmount);
            }
        }

        if ($sale->getData('apply_tax')) {
            $taxRate = $this->createElement(($isSeller) ? 'text' : 'hidden',
                'tax_rate')
                ->setLabel('Tax Rate')
                ->setSuffix('%')
                ->setAttributes(array(
                    'class' => 'form-control input-mini',
                ));
            $this->addElement($taxRate);
        }

        if ($isSeller) {
            $editLocked = $this->createElement('checkbox', 'edit_locked');
            $editLocked->setLabel('Lock Editing')
                ->setDescription('Check above to prevent the buyer from making any changes to the invoice.')
                ->setMultiOptions(
                    array(1 => null));
            $this->addElement($editLocked);
        }

        // the ids and qty fields are needed to return the available postage options.
        $elementIds = $this->createElement('hidden', 'ids')
            ->setAttributes(
                array('class' => 'ids'))
            ->setValue($ids);
        $this->addElement($elementIds);

        $qty = $this->createElement('hidden', 'qty')
            ->setAttributes(
                array('class' => 'qty'))
            ->setValue($listingsQty);
        $this->addElement($qty);

        $this->setData($saleData + $listingsData);

        $this->setPartial('forms/invoices.phtml');
    }

    /**
     *
     * set buyer
     *
     * @param \Ppb\Db\Table\Row\User $buyer
     *
     * @return $this
     */
    public function setBuyer(UserModel $buyer)
    {
        $this->_buyer = $buyer;

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
     * get sales rowset
     *
     * @return \Ppb\Db\Table\Rowset\Sales
     */
    public function getSales()
    {
        return $this->_sales;
    }

    /**
     *
     * set sales rowset
     *
     * @param \Ppb\Db\Table\Rowset\Sales $sales
     *
     * @return $this
     */
    public function setSales($sales)
    {
        $this->_sales = $sales;

        return $this;
    }

    /**
     *
     * get is seller flag
     *
     * @return bool
     */
    public function isSeller()
    {
        return $this->_isSeller;
    }

    /**
     *
     * set is seller flag
     *
     * @param bool $isSeller
     *
     * @return $this
     */
    public function setIsSeller($isSeller)
    {
        $this->_isSeller = $isSeller;

        return $this;
    }

}
