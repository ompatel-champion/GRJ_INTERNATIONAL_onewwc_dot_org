<?php

/**
 *
 * PHP Pro Bid
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2020 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     8.2 [rev.8.2.03]
 */
/**
 * MOD:- ADVANCED CLASSIFIEDS
 */

namespace Ppb\Model\Elements;

use Cube\Controller\Front,
    Cube\Validate,
    Ppb\Db\Table,
    Ppb\Filter,
    Ppb\Db\Table\Row\User as UserModel,
    Ppb\Model\Shipping as ShippingModel,
    Cube\Locale,
    Ppb\Service;

class Listing extends AbstractElements
{

    /**
     *
     * listing owner
     *
     * @var \Ppb\Db\Table\Row\User
     */
    protected $_user;

    /**
     *
     * listing types available
     * Default: auction, product, wanted, reverse, first_bidder
     *
     * @var array
     */
    protected $_listingTypes = array();

    /**
     *
     * list in options
     *
     * @var array
     */
    protected $_listIn = array();

    /**
     *
     * currencies table service
     *
     * @var \Ppb\Service\Table\Currencies
     */
    protected $_currencies;

    /**
     *
     * durations table service
     *
     * @var \Ppb\Service\Table\Durations
     */
    protected $_durations;

    /**
     *
     * offline payment methods table service
     *
     * @var \Ppb\Service\Table\OfflinePaymentMethods
     */
    protected $_paymentMethods;

    /**
     *
     * payment gateways table service
     *
     * @var \Ppb\Service\Table\PaymentGateways
     */
    protected $_paymentGateways;

    /**
     *
     * fees service
     *
     * @var \Ppb\Service\Fees
     */
    protected $_fees;

    /**
     *
     * admin flag
     *
     * @var bool
     */
    protected $_inAdmin = false;

    /**
     *
     * class constructor
     *
     * @param mixed $formId
     * @param bool  $inAdmin
     */
    public function __construct($formId = null, $inAdmin = false)
    {
        parent::__construct();

        $this->setFormId($formId);

        $this->_inAdmin = $inAdmin;
    }

    /**
     *
     * get current user
     *
     * @return \Ppb\Db\Table\Row\User
     */
    public function getUser()
    {
        if (!$this->_user instanceof UserModel) {
            $this->setUser(
                Front::getInstance()->getBootstrap()->getResource('user'));
        }

        return $this->_user;
    }

    /**
     *
     * set current user
     *
     * @param \Ppb\Db\Table\Row\User $user
     *
     * @return $this
     */
    public function setUser(UserModel $user)
    {
        $this->_user = $user;

        return $this;
    }

    /**
     *
     * get list in options
     *
     * @return array
     */
    public function getListIn()
    {
        if (empty($this->_listIn)) {
            $this->setListIn();
        }

        return $this->_listIn;
    }

    /**
     *
     * set list in field options
     * if store only mode is enabled, items will all be listed as in "Both"
     *
     * @param array $listIn
     *
     * @return $this
     */
    public function setListIn(array $listIn = null)
    {
        if ($listIn === null) {
            $settings = $this->getSettings();
            $user = $this->getUser();

            if (!$settings['store_only_mode']) {
                $listIn['site'] = 'Site';
            }

            $storeEnabled = false;
            if ($user['id']) {
                $storeEnabled = $user->storeStatus();
            }

            if ($storeEnabled) {
                if ($settings['stores_force_list_in_both']) {
                    $listIn = array();
                }
                else if (!$settings['store_only_mode']) {
                    $listIn['store'] = 'Store';
                }
                $listIn['both'] = 'Both';
            }
        }

        $this->_listIn = (array)$listIn;

        return $this;
    }

    /**
     *
     * get currencies table service
     *
     * @return \Ppb\Service\Table\Currencies
     */
    public function getCurrencies()
    {
        if (!$this->_currencies instanceof Service\Table\Currencies) {
            $this->setCurrencies(
                new Service\Table\Currencies());
        }

        return $this->_currencies;
    }

    /**
     *
     * set currencies service
     *
     * @param \Ppb\Service\Table\Currencies $currencies
     *
     * @return $this
     */
    public function setCurrencies(Service\Table\Currencies $currencies)
    {
        $this->_currencies = $currencies;

        return $this;
    }

    /**
     *
     * get durations table service
     *
     * @return \Ppb\Service\Table\Durations
     */
    public function getDurations()
    {
        if (!$this->_durations instanceof Service\Table\Durations) {
            $this->setDurations(
                new Service\Table\Durations());
        }

        return $this->_durations;
    }

    /**
     *
     * set durations service
     *
     * @param \Ppb\Service\Table\Durations $durations
     *
     * @return $this
     */
    public function setDurations(Service\Table\Durations $durations)
    {
        $this->_durations = $durations;

        return $this;
    }

    /**
     *
     * get offline payment methods table service
     *
     * @return \Ppb\Service\Table\OfflinePaymentMethods
     */
    public function getPaymentMethods()
    {
        if (!$this->_paymentMethods instanceof Service\Table\OfflinePaymentMethods) {
            $this->setPaymentMethods(
                new Service\Table\OfflinePaymentMethods());
        }

        return $this->_paymentMethods;
    }

    /**
     *
     * set offline payment methods table service
     *
     * @param \Ppb\Service\Table\OfflinePaymentMethods $paymentMethods
     *
     * @return $this
     */
    public function setPaymentMethods(Service\Table\OfflinePaymentMethods $paymentMethods)
    {
        $this->_paymentMethods = $paymentMethods;

        return $this;
    }

    /**
     *
     * get payment gateways table service
     *
     * @return \Ppb\Service\Table\PaymentGateways
     */
    public function getPaymentGateways()
    {
        if (!$this->_paymentGateways instanceof Service\Table\PaymentGateways) {
            $this->setPaymentGateways(
                new Service\Table\PaymentGateways());
        }

        return $this->_paymentGateways;
    }

    /**
     *
     * set payment gateways table service
     *
     * @param \Ppb\Service\Table\PaymentGateways $paymentGateways
     *
     * @return $this
     */
    public function setPaymentGateways(Service\Table\PaymentGateways $paymentGateways)
    {
        $this->_paymentGateways = $paymentGateways;

        return $this;
    }

    /**
     *
     * get fees service
     *
     * @return \Ppb\Service\Fees
     */
    public function getFees()
    {
        if (!$this->_fees instanceof Service\Fees) {
            $this->setFees(
                new Service\Fees());
        }

        return $this->_fees;
    }

    /**
     *
     * set fees service
     *
     * @param \Ppb\Service\Fees $fees
     *
     * @return $this
     */
    public function setFees(Service\Fees $fees)
    {
        $this->_fees = $fees;

        return $this;
    }

    /**
     *
     * get form data
     * for certain keys, generate preset values
     *
     * @param string $key
     *
     * @return mixed
     */
    public function getData($key = null)
    {
        $result = parent::getData($key);

        if ($result === null) {
            $settings = $this->getSettings();

            switch ($key) {
                case 'list_in':
                    $listInKeys = array_keys($this->getListIn());
                    $result = array_shift($listInKeys);
                    $this->addData('list_in', $result);
                    break;
                case 'listing_type':
                    $listIn = $this->getData('list_in');
                    if ($listIn != 'site' && !$settings['enable_auctions_in_stores'] && $settings['enable_products']) {
                        $result = 'product';
                    }
                    else {
                        $listingTypesKeys = array_keys($this->getListingTypes());
                        $result = array_shift($listingTypesKeys);
                    }
                    $this->addData('listing_type', $result);
                    break;
                case 'currency':
                    $result = $settings['currency'];
                    $this->addData('currency', $result);
                    break;
            }
        }

        switch ($key) {
            case 'start_price':
            case 'reserve_price':
            case 'buyout_price':
            case 'bid_increment':
            case 'make_offer_min':
            case 'make_offer_max':
                $filter = new Filter\LocalizedNumeric();
                $result = $filter->filter($result);
                $this->addData($key, $result);
                break;
        }

        return $result;
    }

    /**
     *
     * generate listing form elements
     *
     * @return array
     */
    public function getElements()
    {
        $listIn = $this->getListIn();
        $listingTypes = $this->getListingTypes();
        $currencies = $this->getCurrencies()->getMultiOptions('iso_code');
        $settings = $this->getSettings();
        $translate = $this->getTranslate();
        $listingType = $this->getData('listing_type');

        $durations = $this->getDurations()->getMultiOptions($listingType);
        $countries = $this->getLocations()->getMultiOptions();

        $states = array();

        $country = $this->getData('country');
        if (!empty($country)) {
            $states = $this->getLocations()->getMultiOptions($country);
        }

        $paymentGateways = $this->getPaymentGateways()->getMultiOptions($this->_user['id']);
        $paymentMethods = $this->getPaymentMethods()->getMultiOptions();


        $categoryId = $this->getData('category_id');
        $addlCategoryId = $this->getData('addl_category_id');

        $categoriesFilter = array(0);

        if ($categoryId) {
            $categoriesFilter = array_merge($categoriesFilter, array_keys(
                $this->getCategories()->getBreadcrumbs($categoryId)));
        }

        if ($addlCategoryId) {
            $categoriesFilter = array_merge($categoriesFilter, array_keys(
                $this->getCategories()->getBreadcrumbs($addlCategoryId)));
        }

//        $customFieldsType = (in_array($this->_formId, $this->getCustomFields()->getCustomFieldTypes())) ?
//            $this->_formId : 'item';
        ## -- CHANGE -- [ MOD:- ADVANCED CLASSIFIEDS ]
        $customFieldsType = ($listingType == 'classified') ?
            'classified' : 'item';

        ## -- ./CHANGE -- [ MOD:- ADVANCED CLASSIFIEDS ] 

        $customFields = $this->getCustomFields()->getFields(
            array(
                'type'         => $customFieldsType,
                'active'       => 1,
                'category_ids' => $categoriesFilter,
            ))->toArray();

        $calculationAmount = ($listingType == 'product') ?
            $this->getData('buyout_price') : max(array($this->getData('start_price'), $this->getData('reserve_price')));


        $this->getFees()->setCategoryId($this->getData('category_id'))
            ->setUser($this->getUser())
            ## -- ADD 1L -- [ MOD:- ADVANCED CLASSIFIEDS ]
            ->setType(($listingType == 'classified') ?
                'classified' : 'default')
            ->setAmount($calculationAmount);

        // images field related description
        $imagesDescription = sprintf($translate->_('You can upload up to %s images. Arrange by drag and drop.'), $settings['images_max']);

        $imagesFeeAmount = $this->getFees()->getFeeAmount(Service\Fees::IMAGES);
        $nbFreeImages = intval($this->getFees()->getFeeAmount(Service\Fees::NB_FREE_IMAGES));

        if ($imagesFeeAmount > 0) {
            if ($nbFreeImages < $settings['images_max']) {
                $imagesDescription .= '<br>';
                if ($nbFreeImages > 0) {
                    $imagesDescription .= sprintf($translate->_('Upload %s images for free.'), $nbFreeImages)
                        . ' ' . sprintf($translate->_('Each additional image costs %s.'), $this->getView()->amount($imagesFeeAmount));
                }
                else {
                    $imagesDescription .= sprintf($translate->_('Each image costs %s.'), $this->getView()->amount($imagesFeeAmount));
                }
            }
        }


        // media upload field related description
        $videosDescription = sprintf($translate->_('You can upload up to %s videos.'), $settings['videos_max']);

        $videosFeeAmount = $this->getFees()->getFeeAmount(Service\Fees::MEDIA);
        $nbFreeVideos = intval($this->getFees()->getFeeAmount(Service\Fees::NB_FREE_MEDIA));

        if ($videosFeeAmount > 0) {
            if ($nbFreeVideos < $settings['videos_max']) {
                $videosDescription .= '<br>';
                if ($nbFreeVideos > 0) {
                    $videosDescription .= sprintf($translate->_('Upload %s media for free.'), $nbFreeVideos)
                        . ' ' . sprintf($translate->_('Each additional media upload costs %s.'), $this->getView()->amount($videosFeeAmount));
                }
                else {
                    $videosDescription .= sprintf($translate->_('Each media file upload costs %s.'), $this->getView()->amount($videosFeeAmount));
                }
            }
        }

        // digital downloads field related description
        $downloadsDescription = $translate->_('If this listing contains digital downloads, please upload the files using this field.') . '<br>' .
            sprintf($translate->_('You can upload up to %s digital downloads.'),
                $settings['digital_downloads_max']);

        $downloadsFeeAmount = $this->getFees()->getFeeAmount(Service\Fees::DIGITAL_DOWNLOADS);
        $nbFreeDownloads = intval($this->getFees()->getFeeAmount(Service\Fees::NB_FREE_DOWNLOADS));

        if ($downloadsFeeAmount > 0) {
            $downloadsDescription .= '<br>';
            if ($nbFreeDownloads < $settings['digital_downloads_max']) {
                if ($nbFreeDownloads > 0) {
                    $downloadsDescription .= sprintf($translate->_('First %s uploaded files are free.'), $nbFreeDownloads)
                        . ' ' . sprintf($translate->_('Each additional upload costs %s.'), $this->getView()->amount($downloadsFeeAmount));
                }
                else {
                    $downloadsDescription .= sprintf($translate->_('Each file upload costs %s.'), $this->getView()->amount($downloadsFeeAmount));
                }
            }
        }


        $bulkArraySeparator = Service\Listings\BulkLister::ARRAY_SEPARATOR;

        $locationRecordExists = new Validate\Db\RecordExists(array(
            'table' => new Table\Locations(),
            'field' => 'id',
        ));
        $locationRecordExists->setMessage($translate->_("The country id '%value%' is invalid."));

        /**
         * @version 7.5: moved above the array initialization due to the product attributes module
         */
        $isProductAttributes = false;
        foreach ($customFields as $key => $customField) {
            $customFields[$key]['form_id'] = array($listingType, 'product_edit');
            $customFields[$key]['id'] = 'custom_field_' . $customField['id'];
            $customFields[$key]['subform'] = 'details';

            if (!empty($customField['multiOptions'])) {
                $multiOptions = \Ppb\Utility::unserialize($customField['multiOptions']);

                // display only the multi options that correspond to the categories selected
                if (!empty($multiOptions['categories'])) {
                    foreach ($multiOptions['categories'] as $k => $v) {
                        $categoriesArray = array_filter((array)$v);
                        if (count($categoriesArray) > 0) {
                            $intersect = array_intersect($categoriesArray, $categoriesFilter);
                            if (count($intersect) == 0) {
                                unset($multiOptions['key'][$k]);
                                unset($multiOptions['value'][$k]);
                                unset($multiOptions['categories'][$k]);
                            }
                        }
                    }

                    unset($multiOptions['categories']);
                }

                $customFields[$key]['multiOptions'] = serialize($multiOptions);
                $customFields[$key]['bulk']['multiOptions'] = array_flip(array_filter($multiOptions['key']));
            }

            if (array_key_exists($key, $customFields)) {
                if ($customField['product_attribute']) {
                    $isProductAttributes = true;
                    $customFields[$key]['required'] = false;
                    $customFields[$key]['productAttribute'] = true;
                    $customFields[$key]['multiple'] = true;
                }
            }
        }

        $elements = array(
            array(
                'form_id'  => array('global', 'prefilled', 'fees_calculator'),
                'id'       => 'user_id',
                'element'  => 'hidden',
                'bodyCode' => "
                    <script type=\"text/javascript\">
                        function checkListingFormFields()
                        {
                            var listIn = $('[name=\"list_in\"]');
                            var listingType = $('[name=\"listing_type\"]');
                            var startTimeTypeChecked = $('input:radio[name=\"start_time_type\"]:checked');
                            
                            if (listIn.val() !== 'site') {
                                " . (($settings['enable_auctions_in_stores']) ? '' : "listingType.val('product').closest('.form-group').hide();") . "
                                $('.btn-category').attr('data-store-id', {$this->_user['id']});
                            }
                            else {
                                listingType.closest('.form-group').show();
                                $('.btn-category').removeAttr('data-store-id');
                            }

                            if (listIn.val() === 'store') {
                                $('[name=\"hpfeat\"]').prop('checked', false).closest('.form-group').hide();
                                $('[name=\"catfeat\"]').prop('checked', false).closest('.form-group').hide();
                                $('[name=\"highlighted\"]').prop('checked', false).closest('.form-group').hide();
                            }
                            else {
                                $('[name=\"hpfeat\"]').closest('.form-group').show();
                                $('[name=\"catfeat\"]').closest('.form-group').show();
                                $('[name=\"highlighted\"]').closest('.form-group').show();
                            }

                            if (listingType.val() === 'product') {
                                var qty = $('input:text[name=\"quantity\"]');

                                if (qty.length > 0) {
                                    $('.product-attribute').on('change', function (e) {
                                        $('body').addClass('loading');
                                        displayQuantityStockLevels(qty);
                                    });

                                    displayQuantityStockLevels(qty);
                                }
                            }
                
                            if ($('input:checkbox[name=\"enable_reserve_price\"]').is(':checked')) {
                                $('input:text[name=\"reserve_price\"]').closest('.form-group').show();
                            }
                            else {
                                $('input:text[name=\"reserve_price\"]').val('').closest('.form-group').hide();
                            }
                            

                            if ($('input:checkbox[name=\"enable_buyout_price\"]').is(':checked')) {
                                $('input:text[name=\"buyout_price\"]').closest('.form-group').show();
                            }
                            else if (listingType.val() === 'auction') {
                                $('input:text[name=\"buyout_price\"]').val('').closest('.form-group').hide();
                            }
                            
                            if ($('input:checkbox[name=\"enable_make_offer\"]').is(':checked')) {
                                $('input:text[name=\"make_offer_min\"]').closest('.form-group').show();
                                $('input:text[name=\"make_offer_max\"]').closest('.form-group').show();
                            }
                            else {
                                $('input:text[name=\"make_offer_min\"]').val('').closest('.form-group').hide();
                                $('input:text[name=\"make_offer_max\"]').val('').closest('.form-group').hide();
                            }
                            
                            if ($('input:radio[name=\"bid_increment_type\"]:checked').val() === '1') {
                                $('input:text[name=\"bid_increment\"]').closest('.form-group').show();
                            }
                            else {
                                $('input:text[name=\"bid_increment\"]').val('').closest('.form-group').hide();
                            }
                            
                            if (startTimeTypeChecked.val() === '1') { 
                                $('input:text[name=\"start_time\"]').closest('.form-group').show();
                            }
                            else if (startTimeTypeChecked.val() === '0') {
                                $('input:text[name=\"start_time\"]').val('').closest('.form-group').hide();
                            }
                            
                            if ($('input:radio[name=\"end_time_type\"]:checked').val() === '1') { 
                                $('select[name=\"duration\"]').closest('.form-group').hide();
                                $('input:text[name=\"end_time\"]').closest('.form-group').show();
                            }
                            else {
                                $('input:text[name=\"end_time\"]').val('').closest('.form-group').hide();
                                $('select[name=\"duration\"]').closest('.form-group').show();
                            }
                            
                            if ($('input:checkbox[name=\"enable_auto_relist\"]').is(':checked')) {
                                $('input:text[name=\"nb_relists\"]').closest('.form-group').show();
                                $('input:checkbox[name=\"relist_until_sold\"]').closest('.form-group').show();
                                $('input:checkbox[name=\"auto_relist_sold\"]').closest('.form-group').show();
                                
                                if ($('input:checkbox[name=\"relist_until_sold\"]').is(':checked')) {
                                    $('input:text[name=\"nb_relists\"]').val('').closest('.form-group').hide();
                                    $('input:checkbox[name=\"auto_relist_sold\"]').prop('checked', false).closest('.form-group').hide();
                                }
                                else {
                                    $('input:checkbox[name=\"relist_until_sold\"]').closest('.form-group').show();
                                    $('input:checkbox[name=\"auto_relist_sold\"]').closest('.form-group').show();
                                }
                            }
                            else {
                                $('input:text[name=\"nb_relists\"]').val('').closest('.form-group').hide();
                                $('input:checkbox[name=\"relist_until_sold\"]').prop('checked', false).closest('.form-group').hide();
                                $('input:checkbox[name=\"auto_relist_sold\"]').prop('checked', false).closest('.form-group').hide();
                            }
                            
                            if ($('input:checkbox[name=\"apply_tax\"]').is(':checked')) {
                                $('input:radio[name=\"tax_type_id\"]').closest('.form-group').show();
                            }
                            else {
                                $('input:radio[name=\"tax_type_id\"]').closest('.form-group').hide();
                            }
                            
                            if ($('input:checkbox[name=\"accept_returns\"]').is(':checked')) {
                                $('textarea[name=\"returns_policy\"]').closest('.form-group').show();
                            }
                            else {
                                $('textarea[name=\"returns_policy\"]').val('').closest('.form-group').hide();
                            }                                                                                                                
                            
                            if ($('[name=\"pickup_options\"]').val() === '" . ShippingModel::MUST_PICKUP . "') { 
                                $('.field-shipping').closest('.form-group').hide();
                            }
                            else {
                                $('.field-shipping').closest('.form-group').show();
                            }
                            
                            $('.listing-currency').html($('[name=\"currency\"]').val());

                            $('.direct-payment').on('click', function() {
                                var checkbox = $(this);

                                if (checkbox.is(':checked')) {
                                    $.ajax({
                                        type: 'POST',
                                        url: '" . $this->getView()->url(array('module' => 'app', 'controller' => 'async', 'action' => 'check-direct-payment-method')) . "',
                                        data: {
                                            userId: '" . $this->_user['id'] . "',
                                            gatewayId: checkbox.val()
                                        },
                                        dataType: 'json',
                                        success: function(data) {
                                            if (!data.active) {
                                                checkbox.prop('checked', false);
                                                $('#direct-payment-form').trigger('click');
                                            }
                                        }
                                    });
                                }
                            });
                        }

                        function displayQuantityStockLevels(qty)
                        {
                            var stk = $('.stock-levels');

                            $.ajax({
                                type: 'POST',
                                url: '" . $this->getView()->url(array('module' => 'app', 'controller' => 'async', 'action' => 'update-stock-levels-element')) . "',
                                data: qty.closest('form').serialize(),
                                dataType: 'json',
                                success: function(data) {
                                    stk.html(data.output);

                                    if (!data.empty) {
                                        stk.closest('.form-group').show();
                                        qty.closest('.form-group').hide();
                                    }
                                    else {
                                        stk.closest('.form-group').hide();
                                        qty.closest('.form-group').show();
                                    }

                                    $('body').removeClass('loading');
                                    feather.replace();
                                }
                            });
                        }

                        $(document).ready(function() {
                            checkListingFormFields();
                        });
                        
                        $(document).on('change', '.field-changeable', function() {
                            checkListingFormFields();
                        });
                        
                        $(document).on('change', '.form-refresh', function() {
                            $('body').addClass('loading'); 
                            $(this).closest('form').submit();
                        });
                    </script>"
            ),
            array(
                'form_id' => 'global',
                'id'      => 'option',
                'element' => 'hidden',
            ),
            array(
                'form_id'      => array('auction', 'product', 'classified', 'product_edit', 'fees_calculator', 'bulk'),
                'subform'      => 'details',
                'id'           => 'list_in',
                'element'      => (count($listIn) > 1 && !in_array('product_edit', $this->_formId)) ? 'select' : 'hidden',
                'label'        => $this->_('List In'),
                'multiOptions' => $listIn,
                'required'     => true,
                'hideDefault'  => true,
                'value'        => $this->getFirstElement($listIn),
                'description'  => $this->_('Choose where to list your item.'),
                'attributes'   => array(
                    'class' => 'form-control input-small field-changeable form-refresh',
                ),

            ),
            array(
                'form_id'      => array('auction', 'product', 'classified', 'product_edit', 'fees_calculator', 'bulk'),
                'subform'      => 'details',
                'id'           => 'listing_type',
                'element'      => (count($listingTypes) > 1 && !in_array('product_edit', $this->_formId)) ? 'select' : 'hidden',
                'label'        => $this->_('Listing Type'),
                'multiOptions' => $listingTypes,
                'required'     => true,
                'hideDefault'  => true,
                'value'        => $this->getFirstElement($listingTypes),
                'description'  => $this->_('Choose the listing type you wish to create.'),
                'attributes'   => array(
                    'class' => 'form-control input-small field-changeable form-refresh',
                ),
            ),
            array(
                'subtitle'    => $this->_('Select Category'),
                'subform'     => 'details',
                'form_id'     => array('global', 'fees_calculator', 'bulk'),
                'id'          => 'category_id',
                'element'     => '\\Ppb\\Form\\Element\\Category',
                'label'       => $this->_('Main Category'),
                'description' => $this->_('Select a main category where the item will be listed.'),
                'required'    => true,
                'validators'  => array(
                    '\\Ppb\\Validate\\Db\\Category',
                ),
                'bulk'        => array(
                    'notes'  => $translate->_('Category IDs allowed are available in the "Categories" tab.'),
                    'type'   => $translate->_('integer'),
                    'sample' => 7425,
                ),
            ),
            array(
                'form_id'      => array('global', 'fees_calculator', 'bulk'),
                'subform'     => 'details',
                'id'          => 'addl_category_id',
                'element'      => ($settings['addl_category_listing']) ? ((in_array('fees_calculator', $this->_formId)) ? 'checkbox' : '\\Ppb\\Form\\Element\\Category') : false,
                'label'       => $this->_('Additional Category'),
                'suffix'      => $this->getView()->amount($this->getFees()->getFeeAmount(Service\Fees::ADDL_CATEGORY),
                    null,
                    '<span class="badge badge-text badge-slim">+%s</span>'),
                'description'  => ((in_array('fees_calculator', $this->_formId)) ?
                    $this->_('Check the above checkbox if you wish to list the item in an additional category.') :
                    $this->_('Select an additional category where to list your item (optional).')),
                'validators'   => ((in_array('fees_calculator', $this->_formId)) ?
                    array() :
                    $this->getData('addl_category_id') ? array(
                    '\\Ppb\\Validate\\Db\\Category',
                    array('Different', array($translate->_('Main Category'), $this->getData('category_id')))
                    ) : null),
                'bulk'        => array(
                    'type' => $translate->_('integer'),
                ),
                'multiOptions' => ((in_array('fees_calculator', $this->_formId)) ?
            array(
                    1 => $this->getView()->amount($this->getFees()->getFeeAmount(Service\Fees::ADDL_CATEGORY),
                        null,
                        '<span class="badge badge-text badge-slim">+%s</span>'),
                    ) : array()),
            ),
            array(
                'subtitle'    => $this->_('Listing Details'),
                'form_id'     => array('global', 'prefilled', 'bulk'),
                'subform'     => 'details',
                'id'          => 'name',
                'element'     => 'text',
                'label'       => $this->_('Item Title'),
                'description' => $this->_('Enter a title for your listing.'),
                'required'    => (!in_array('prefilled', $this->_formId)) ? true : false,
                'validators'  => array(
                    'NoHtml',
                    array('StringLength', array(null, min(255, $settings['character_length']))),
                ),
                'filters'     => array(
                    '\\Ppb\\Filter\\BadWords',
                ),
                'attributes'  => array(
                    'class' => 'form-control input-xlarge',
                ),
                'bulk'        => array(
                    'sample' => 'My Listing',
                ),

            ),
            array(
                'form_id'     => array('global', 'prefilled', 'bulk'),
                'subform'     => 'details',
                'id'          => 'short_description',
                'element'     => ($settings['enable_short_description']) ? '\\Ppb\\Form\\Element\\Wysiwyg' : false,
                'label'       => $this->_('Short Description'),
                'description' => $this->_('(Optional) Add a short description for your listing.'),
                'suffix'      => $this->getView()->amount(
                    $this->getFees()->getFeeAmount(Service\Fees::SHORT_DESCRIPTION),
                    null,
                    '<span class="badge badge-text badge-slim">+%s</span>'),
                'validators'  => array(
                    'NoHtml',
                    array('StringLength', array(null, min(500, $settings['short_description_character_length']))),
                ),
                'filters'     => array(
                    '\\Ppb\\Filter\\BadWords',
                ),
                'attributes'  => array(
                    'class' => 'form-control',
                    'rows'  => 5,
                ),
                'customData'  => array(
                    'formData' => array(
                        'btns' => "['strong', 'em']",
                    )
                ),
                'bulk'        => array(
                    'sample' => 'Short Description',
                ),
            ),
            array(
                'form_id'      => 'fees_calculator',
                'id'           => 'short_description',
                'element'      => ($settings['enable_short_description']) ? 'checkbox' : false,
                'label'        => $this->_('Short Description'),
                'description'  => $this->_('(Optional) Add a short description for your listing.'),
                'multiOptions' => array(
                    1 => $this->getView()->amount($this->getFees()->getFeeAmount(Service\Fees::SHORT_DESCRIPTION),
                        null,
                        '<span class="badge badge-text badge-slim">+%s</span>'),
                ),
            ),
            array(
                'form_id'     => array('global', 'prefilled', 'bulk'),
                'subform'     => 'details',
                'id'          => 'description',
                'element'     => '\\Ppb\\Form\\Element\\Wysiwyg',
                'label'       => $this->_('Description'),
                'description' => $this->_('Enter a description for your item.'),
                'required'    => (!in_array('prefilled', $this->_formId)) ? true : false,
                'filters'     => array(
                    '\\Ppb\\Filter\\BadWords',
                ),
                'attributes'  => array(
                    'class' => 'form-control',
                    'rows'  => 15,
                ),
                'bulk'        => array(
                    'sample' => 'Listing Description',
                ),
            ),
            array(
                'form_id'     => array('global', 'bulk'),
                'subform'     => 'details',
                'id'          => 'image',
                'element'     => ($settings['images_max'] > 0) ? '\\Ppb\\Form\\Element\\MultiUpload\\Sortable' : false,
                'label'       => $this->_('Images'),
                'description' => $imagesDescription,
                'required'    => ($settings['mandatory_images']) ? true : false,
                'multiple'    => true,
                'customData'  => array(
                    'buttonText'      => $translate->_('Select Images'),
                    'acceptFileTypes' => '/(\.|\/)(gif|jpe?g|png)$/i',
                    'remoteUploads'   => ($settings['remote_uploads']) ? true : false,
                    'formData'        => array(
                        'watermark'     => $settings['images_watermark'],
                        'fileSizeLimit' => ($settings['images_size'] * 1024),
                        'uploadLimit'   => $settings['images_max'],
                    ),
                ),
                'bulk'        => array(
                    'sample' => $settings['site_path'] . '/images/sample1.jpg'
                        . $bulkArraySeparator
                        . $settings['site_path'] . '/images/sample2.jpg',
                    'notes'  => sprintf($translate->_('Absolute path to images is highly recommended. '
                        . 'For local images, the "%s" folder will be used. Multiple images are to be separated by "%s"'),
                        \Ppb\Utility::getFolder('uploads'), $bulkArraySeparator),
                )
            ),
            array(
                ## -- ADD 1L -- [ MOD:- ADVANCED CLASSIFIEDS ]
                'form_id'     =>  array('fees_calculator', 'classified_fees_calculator'),
                'id'          => 'image',
                'element'     => ($settings['images_max'] > 0) ? 'text' : false,
                'label'       => $this->_('Images'),
                'description' => $translate->_('Enter the number of images you wish to upload with your listing.') . '<br>' . $imagesDescription,
                'attributes'  => array(
                    'class' => 'form-control input-mini',
                ),
            ),
            array(
                'form_id'     => 'global',
                'subform'     => 'details',
                'id'          => 'video',
                'element'     => ($settings['videos_max'] > 0) ? '\\Ppb\\Form\\Element\\MultiUpload' : false,
                'label'       => $this->_('Media'),
                'description' => $videosDescription,
                'required'    => false,
                'multiple'    => true,
                'customData'  => array(
                    'buttonText'      => $translate->_('Select Media'),
                    'acceptFileTypes' => '/(\.|\/)(mov|mp4|flv)$/i',
                    'embeddedCode'    => ($settings['embedded_code']) ? true : false,
                    'formData'        => array(
                        'fileSizeLimit' => ($settings['videos_size'] * 1024),
                        'uploadLimit'   => $settings['videos_max'],
                    ),
                ),
            ),
            array(
                ## -- ADD 1L -- [ MOD:- ADVANCED CLASSIFIEDS ]
                'form_id'     => array('fees_calculator', 'classified_fees_calculator'),
                'id'          => 'video',
                'element'     => ($settings['videos_max'] > 0) ? 'text' : false,
                'label'       => $this->_('Media'),
                'description' => $translate->_('Enter the number of videos you wish to add to your listing.') . '<br>' . $videosDescription,
                'attributes'  => array(
                    'class' => 'form-control input-mini',
                ),
            ),
            array(
                'form_id'     => array('auction', 'product', 'fees_calculator'),
                'subform'     => 'details',
                'id'          => 'download',
                'element'     => ($settings['digital_downloads_max'] > 0) ? ((in_array('fees_calculator', $this->_formId)) ? 'text' : '\\Ppb\\Form\\Element\\MultiUpload') : false,
                'label'       => $this->_('Digital Downloads'),
                'description' => $downloadsDescription,
                'required'    => false,
                'multiple'    => (in_array('fees_calculator', $this->_formId)) ? false : true,
                'attributes'  => array(
                    'class' => (in_array('fees_calculator', $this->_formId)) ? 'form-control input-mini' : '',
                ),
                'customData'  => array(
                    'buttonText'      => $translate->_('Select Files'),
                    'acceptFileTypes' => '/(\.|\/)(doc?x|xls?x|txt|zip|tar|gz|exe|pdf)$/i',
                    'formData'        => array(
                        'uploadType'    => 'download',
                        'fileSizeLimit' => (intval($settings['digital_downloads_size']) * 1024),
                        'uploadLimit'   => $settings['digital_downloads_max'],
                    ),
                ),
            ),
            array(
                'subtitle'     => $this->_('Listing Settings'),
                'form_id'      => array('auction', 'product', 'classified', 'prefilled', 'product_edit', 'bulk'),
                'subform'      => 'settings',
                'id'           => 'currency',
                'element'      => (count($currencies) > 1) ? 'select' : 'hidden',
                'label'        => $this->_('Currency'),
                'multiOptions' => $currencies,
                'value'        => $this->getData('currency'),
                'description'  => $this->_('Select your item\'s currency.'),
                'required'     => true,
                'attributes'   => array(
                    'class' => 'form-control input-small field-changeable',
                ),
            ),
            array(
                'form_id'     => array('product', 'product_edit', 'bulk'),
                'subform'     => 'settings',
                'id'          => 'quantity',
                'element'     => '\\Ppb\\Form\\Element\\Quantity',
                'label'       => $this->_('Quantity'),
                'description' => $this->_('Enter the number of items you are offering for sale.'),
                'required'    => true,
                'value'       => 1,
                'attributes'  => array(
                    'class' => 'form-control input-mini',
                ),
                'source'      => array(
                    '1'  => '1',
                    '2'  => '2',
                    '3'  => '3',
                    '4'  => '4',
                    '5'  => '5',
                    '10' => '10',
                    '-1' => $translate->_('Unlimited'),
                ),
                'validators'  => array(
                    'Digits',
                ),
                'bulk'        => array(
                    'notes' => $translate->_('Only editable for products. For auctions it will always be reset to 1.'),
                    'type'  => $translate->_('integer'),
                ),
            ),
            array(
                'form_id' => array('auction', 'classified'),
                'id'      => 'quantity',
                'element' => 'hidden',
                'value'   => 1,
                'forceValue' => true,
            ),
            array(
                'form_id'     => array('auction', 'fees_calculator', 'bulk'),
                'subform'     => 'settings',
                'id'          => 'start_price',
                'element'     => ($settings['enable_auctions']) ? '\\Ppb\\Form\\Element\\LocalizedNumeric' : false,
                'label'       => $this->_('Start Price'),
                'description' => $this->_('Enter the start price for your item.'),
                'required'    => ($listingType == 'auction') ? true : false,
                'prefix'      => '<span class="listing-currency">' . $this->getData('currency') . '</span>',
                'attributes'  => array(
                    'class' => 'form-control input-mini',
                ),
                'filters'     => array(
                    '\\Ppb\\Filter\\LocalizedNumeric',
                ),
                'validators'  => array(
                    'Numeric',
                    array('GreaterThan',
                        ($listingType == 'auction') ?
                            array(0, false, true) : array(0, true)),
                ),
                'bulk'        => array(
                    'notes'  => $translate->_('Field required for auctions.'),
                    'type'   => $translate->_('decimal'),
                    'sample' => 5,
                ),
            ),
            array(
                'form_id'      => array('auction', 'fees_calculator'),
                'subform'      => 'settings',
                'id'           => 'enable_reserve_price',
                'element'      => ($settings['enable_auctions']) ? 'checkbox' : false,
                'label'        => $this->_('Enable Reserve'),
                'multiOptions' => array(
                    1 => $this->getView()->amount($this->getFees()->getFeeAmount(Service\Fees::RESERVE),
                        null,
                        '<span class="badge badge-text badge-slim">+%s</span>'),
                ),
                'value'        => ($this->getData('reserve_price') > 0) ? 1 : null,
                'description'  => $this->_('By enabling reserve, the auction wont be awarded unless the high bid is above the reserve price. The amount is hidden.'),
                'attributes'   => array(
                    'class' => 'field-changeable',
                ),
            ),
            array(
                'form_id'     => array('auction', 'fees_calculator', 'bulk'),
                'subform'     => 'settings',
                'id'          => 'reserve_price',
                'element'     => ($settings['enable_auctions']) ? '\\Ppb\\Form\\Element\\LocalizedNumeric' : false,
                'label'       => $this->_('Reserve Price'),
                'description' => $this->_('Enter the reserve price.'),
                'required'    => ($this->getData('enable_reserve_price')) ? true : false,
                'prefix'      => '<span class="listing-currency">' . $this->getData('currency') . '</span>',
                'attributes'  => array(
                    'class' => 'form-control input-mini',
                ),
                'filters'     => array(
                    '\\Ppb\\Filter\\LocalizedNumeric',
                ),
                'validators'  => array(
                    'Numeric',
                    array('GreaterThan',
                        array((($this->getData('enable_reserve_price') || (in_array('bulk', $this->_formId))) ? $this->getData('start_price') : -0.01), false)),
                ),
                'bulk'        => array(
                    'notes' => $translate->_('Only available for auctions. If set, it needs to be greater than the start price.'),
                    'type'  => $translate->_('decimal'),
                ),
            ),
            array(
                'form_id'      => array('auction', 'fees_calculator'),
                'subform'      => 'settings',
                'id'           => 'enable_buyout_price',
                'element'      => ($settings['enable_buyout']) ? 'checkbox' : false,
                'label'        => $this->_('Enable Buy Out'),
                'multiOptions' => array(
                    1 => $this->getView()->amount($this->getFees()->getFeeAmount(Service\Fees::BUYOUT),
                        null,
                        '<span class="badge badge-text badge-slim">+%s</span>'),
                ),
                'value'        => ($this->getData('buyout_price') > 0) ? 1 : null,
                'description'  => $this->_('Enable Buy Out if you want to allow your users to purchase your item instantly.'),
                'attributes'   => array(
                    'class' => 'field-changeable',
                ),
            ),
            array(
                'form_id'     => array('auction', 'product', 'classified', 'fees_calculator', 'product_edit', 'bulk'),
                'subform'     => 'settings',
                'id'          => 'buyout_price',
                'element'     => ($settings['enable_buyout'] || $settings['enable_products'] || $settings['enable_classifieds']) ?
                    '\\Ppb\\Form\\Element\\LocalizedNumeric' : false,
                'label'       => ($listingType == 'classified') ? $this->_('Price') : $this->_('Buy Out Price'),
                'description' => ($listingType == 'classified') ?
                    $this->_('(Optional) Enter a price for your classified.') : $this->_('Enter the buy out price for your item.'),
                'required'    => (
                    $this->getData('enable_buyout_price') ||
                    $listingType == 'product'
                ) ? true : false,
                'prefix'      => '<span class="listing-currency">' . $this->getData('currency') . '</span>',
                'attributes'  => array(
                    'class' => 'form-control input-mini',
                ),
                'filters'     => array(
                    '\\Ppb\\Filter\\LocalizedNumeric',
                ),
                'validators'  => array(
                    'Numeric',
                    array(
                        'GreaterThan',
                        array(
                            ($listingType == 'product') ? 0 : max(array($this->getData('start_price'), $this->getData('reserve_price'))),
                            false,
                            ($listingType == 'product') ? true : false
                        ),
                    ),
                ),
                'bulk'        => array(
                    'notes'  => $translate->_('Required for products. For auctions, if set, it needs to be greater than the start price and reserve price.'),
                    'type'   => $translate->_('decimal'),
                    'sample' => 15,
                ),
            ),
            array(
                'form_id'      => array('product', 'product_edit', 'bulk'),
                'subform'      => 'settings',
                'id'           => 'stock_levels',
                'element'      => ($isProductAttributes) ? '\\Ppb\\Form\\Element\\StockLevels' : 'hidden',
                'label'        => $this->_('Stock Levels'),
                'description'  => $this->_('Enter the stock levels and variations from the base price corresponding to your selected product attributes combinations.'),
                'attributes'   => array(
                    'class' => 'form-control input-mini',
                ),
                'validators'   => ($listingType == 'product' && $isProductAttributes) ? array('\\Ppb\\Validate\\StockLevels') : array(),
                'customFields' => $customFields,
                'formData'     => $this->getData(),
                'bulk'         => array(),
            ),
            array(
                'form_id'      => array('auction', 'product', 'product_edit', 'fees_calculator', 'bulk'),
                'subform'      => 'settings',
                'id'           => 'enable_make_offer',
                'element'      => ($settings['enable_make_offer']) ? 'checkbox' : false,
                'label'        => $this->_('Accept Offers'),
                'multiOptions' => array(
                    1 => $this->getView()->amount($this->getFees()->getFeeAmount(Service\Fees::MAKE_OFFER),
                        null,
                        '<span class="badge badge-text badge-slim">+%s</span>'),
                ),
                'description'  => $this->_('Check the above checkbox if you wish to accept offers for your item.'),
                'attributes'   => array(
                    'class' => 'field-changeable',
                ),
                'bulk'         => array(
                    'multiOptions' => array(0, 1),
                    'type'         => $translate->_('integer'),

                ),
            ),
            array(
                'form_id'    => array('auction', 'product', 'product_edit', 'bulk'),
                'subform'    => 'settings',
                'id'         => 'make_offer_min',
                'element'    => ($settings['enable_make_offer']) ? '\\Ppb\\Form\\Element\\LocalizedNumeric' : false,
                'label'      => $this->_('Minimum Offer Range'),
                'prefix'     => '<span class="listing-currency">' . $this->getData('currency') . '</span>',
                'attributes' => array(
                    'class' => 'form-control input-mini',
                ),
                'filters'    => array(
                    '\\Ppb\\Filter\\LocalizedNumeric',
                ),
                'validators' => array(
                    'Numeric',
                    array('GreaterThan', array(0, true)),
                ),
                'bulk'       => array(
                    'type' => $translate->_('decimal'),
                ),
            ),
            array(
                'form_id'     => array('auction', 'product', 'product_edit', 'bulk'),
                'subform'     => 'settings',
                'id'          => 'make_offer_max',
                'element'     => ($settings['enable_make_offer']) ? '\\Ppb\\Form\\Element\\LocalizedNumeric' : false,
                'label'       => $this->_('Maximum Offer Range'),
                'description' => $this->_('Enter a range between which users can place offers (Optional).'),
                'prefix'      => '<span class="listing-currency">' . $this->getData('currency') . '</span>',
                'attributes'  => array(
                    'class' => 'form-control input-mini',
                ),
                'filters'     => array(
                    '\\Ppb\\Filter\\LocalizedNumeric',
                ),
                'validators'  => array(
                    'Numeric',
                    array('GreaterThan', array($this->getData('make_offer_min'), true)),
                ),
                'bulk'        => array(
                    'type' => $translate->_('decimal'),
                ),
            ),
            array(
                'form_id'      => 'auction',
                'subform'      => 'settings',
                'id'           => 'bid_increment_type',
                'element'      => ($settings['enable_auctions']) ? 'radio' : false,
                'label'        => $this->_('Bid Increment'),
                'multiOptions' => array(
                    0 => array(
                        $translate->_('Use the built-in proportional increments table'),
                    ),
                    1 => array(
                        $translate->_('Enter your custom increment amount'),
                    ),
                ),
                'value'        => ($this->getData('bid_increment') > 0) ? 1 : 0,
                'description'  => $this->_('Select how the bid increments will be calculated.'),
                'attributes'   => array(
                    'class' => 'field-changeable',
                ),
            ),
            array(
                'form_id'     => array('auction', 'bulk'),
                'subform'     => 'settings',
                'id'          => 'bid_increment',
                'element'     => ($settings['enable_auctions']) ? '\\Ppb\\Form\\Element\\LocalizedNumeric' : false,
                'label'       => $this->_('Bid Increment Amount'),
                'description' => $this->_('Enter your custom bid increment amount.'),
                'prefix'      => '<span class="listing-currency">' . $this->getData('currency') . '</span>',
                'attributes'  => array(
                    'class' => 'form-control input-mini',
                ),
                'filters'     => array(
                    '\\Ppb\\Filter\\LocalizedNumeric',
                ),
                'validators'  => array(
                    'Numeric',
                    array('GreaterThan', array(0, false)),
                ),
                'bulk'        => array(
                    'notes' => $translate->_('Only set if you wish to use a custom increment amount for your auction. '
                        . 'By default, the bid increments table will be used.'),
                    'type'  => $translate->_('decimal'),
                ),
            ),
            array(
                'form_id'      => array('global', 'bulk'),
                'subform'      => 'settings',
                'id'           => 'start_time_type',
                'element'      => ($settings['enable_custom_start_time']) ? 'radio' : 'hidden',
                'label'        => $this->_('Start Time'),
                'description'  => $this->_('Enter the start time for your listing.'),
                'value'        => 0,
                'multiOptions' => array(
                    0 => $translate->_('Now'),
                    1 => $translate->_('Custom'),
                ),
                'attributes'   => array(
                    'class' => 'field-changeable',
                ),
                'bulk'         => array(
                    'notes' => $translate->_('Allowed types: 0 => Now, 1 => Custom'),
                    'type'  => $translate->_('integer'),
                ),
            ),
            array(
                'form_id'     => array('global', 'bulk'),
                'subform'     => 'settings',
                'id'          => 'start_time',
                'element'     => ($settings['enable_custom_start_time']) ? '\\Ppb\\Form\\Element\\DateTime' : 'hidden',
                'label'       => $this->_('Custom Start Time'),
                'description' => $this->_('Choose a custom start time for the listing.'),
                'required'    => ($this->getData('start_time_type') == 1) ? true : false,
                'validators'  => ($settings['enable_custom_start_time'] && $this->getData('start_time_type') == 1) ? array(
                    array('\\Ppb\\Validate\\DateGreaterThan', array(Locale\DateTime::getInstance()->setFormat($settings['date_format'])->dateTimeToLocalized(time()), false)),
                ) : null,
                'attributes'  => array(
                    'class'    => 'form-control input-medium',
                    'readonly' => 'readonly',
                ),
                'customData'  => array(
                    'formData' => array(
                        'minDate'   => 'new Date()',
                        'stepping'  => '5',
                        'showClear' => 'true',
                    ),
                ),
                'bulk'        => array(
                    'type'  => $translate->_('datetime'),
                    'notes' => $translate->_('Accepted format: yyyy-mm-dd hh:mm:ss'),
                ),
            ),
            array(
                'form_id'      => array('global', 'bulk'),
                'subform'      => 'settings',
                'id'           => 'end_time_type',
                'element'      => (($settings['enable_unlimited_duration'] &&
                        $settings['force_unlimited_duration'] &&
                        $listingType == 'product') || !$settings['enable_custom_end_time']) ? false : 'radio',
                'label'        => $this->_('End Time'),
                'description'  => $translate->_('Enter the end time for your listing.') .
                    (($settings['enable_change_duration'] && $listingType == 'auction') ?
                        '<br>' . sprintf(
                            $translate->_('Note: If the time left is greater than %s days when the first bid is placed, the duration will be reduced to %s days.'),
                            $settings['change_duration_days'],
                            $settings['change_duration_days']) : ''),
                'value'        => 0,
                'multiOptions' => array(
                    0 => $translate->_('Duration'),
                    1 => $translate->_('Custom'),
                ),
                'attributes'   => array(
                    'class' => 'field-changeable',
                ),
                'bulk'         => array(
                    'notes' => $translate->_('Allowed types: 0 => Duration, 1 => Custom'),
                    'type'  => $translate->_('integer'),
                ),
            ),
            array(
                'form_id'      => array('global', 'prefilled', 'bulk'),
                'subform'      => 'settings',
                'id'           => 'duration',
                'element'      => (count($durations) > 1) ? 'select' : 'hidden',
                'label'        => $this->_('Duration'),
                'multiOptions' => $durations,
                'description'  => $this->_('Select a duration for your listing.'),
                'attributes'   => array(
                    'class' => 'form-control input-medium',
                ),
            ),
            array(
                'form_id'     => array('global', 'bulk'),
                'subform'     => 'settings',
                'id'          => 'end_time',
                'element'     => '\\Ppb\\Form\\Element\\DateTime',
                'label'       => $this->_('Custom End Time'),
                'description' => $this->_('Choose a custom end time for the listing.'),
                'required'    => ($this->getData('end_time_type') == 1) ? true : false,
                'validators'  => ($this->getData('end_time_type') == 1) ? array(
                    array('\\Ppb\\Validate\\DateGreaterThan', array($this->getData('start_time'), false)),
                ) : null,
                'attributes'  => array(
                    'class'    => 'form-control input-medium',
                    'readonly' => 'readonly',
                ),
                'customData'  => array(
                    'formData' => array(
                        'minDate'   => 'new Date()',
                        'stepping'  => '5',
                        'showClear' => 'true',
                    ),
                ),
                'bulk'        => array(
                    'type'  => $translate->_('datetime'),
                    'notes' => $translate->_('Accepted format: yyyy-mm-dd hh:mm:ss'),
                ),
            ),
            array(
                'form_id'      => array('auction', 'product', 'prefilled', 'product_edit', 'bulk'),
                'subform'      => 'settings',
                'id'           => 'apply_tax',
                'element'      => ($this->getUser()->canApplyTax() !== false) ? 'checkbox' : false,
                'label'        => $this->_('Apply Tax'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check the above checkbox to apply tax for this listing.'),
                'attributes'   => array(
                    'class' => 'field-changeable',
                ),
                'bulk'         => array(
                    'multiOptions' => array(0, 1),
                    'type'         => $translate->_('integer'),
                ),
            ),
            array(
                'form_id'      => array('auction', 'product', 'prefilled', 'product_edit', 'bulk'),
                'subform'      => 'settings',
                'id'           => 'tax_type_id',
                'element'      => 'radio',
                'value'        => '',
                'label'        => $this->_('Tax Type'),
                'description'  => $this->_('If wishing to force a certain tax type to apply for this listing, please select it, otherwise select "Default".'),
                'multiOptions' => array('' => $translate->_('Default')) + $this->getTaxTypes()->getMultiOptions(true, $this->_user),
            ),
            array(
                'form_id'      => array('auction', 'prefilled', 'bulk'),
                'subform'      => 'settings',
                'id'           => 'private_auction',
                'element'      => ($settings['enable_auctions']) ? 'checkbox' : false,
                'label'        => $this->_('Private Auction'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('By enabling this option, bidders on this auction will be hidden to site users, and only you '
                    . '(the owner of the auction) will be able to see the usernames of the bidders. Bid amounts will still be visible.'),
                'bulk'         => array(
                    'multiOptions' => array(0, 1),
                    'type'         => $translate->_('integer'),
                ),
            ),
            array(
                'form_id'      => array('auction', 'prefilled', 'bulk'),
                'subform'      => 'settings',
                'id'           => 'disable_sniping',
                'element'      => ($settings['enable_auctions'] && $settings['enable_auctions_sniping']) ? 'checkbox' : false,
                'label'        => $this->_('Disable Sniping Feature'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check the above checkbox if you want to disable the sniping feature for this auction.'),
                'bulk'         => array(
                    'multiOptions' => array(0, 1),
                    'type'         => $translate->_('integer'),
                ),
            ),
            array(
                'subtitle'     => $this->_('Feature Your Listing'),
                'form_id'      => array('auction', 'product', 'classified', 'fees_calculator', 'bulk'),
                'subform'      => 'settings',
                'id'           => 'hpfeat',
                'element'      => ($settings['enable_hpfeat']) ? 'checkbox' : false,
                'label'        => '',
                'multiOptions' => array(
                    1 => $translate->_('Feature on home page') . ' ' . $this->getView()->amount($this->getFees()->getFeeAmount(Service\Fees::HPFEAT),
                            null,
                            '<span class="badge badge-text badge-slim">+%s</span>'),
                ),
                'bulk'         => array(
                    'multiOptions' => array(0, 1),
                    'label'        => $translate->_('Home Page Featuring'),
                    'type'         => $translate->_('integer'),
                ),
            ),
            array(
                'form_id'      => array('auction', 'product', 'classified', 'fees_calculator', 'bulk'),
                'subform'      => 'settings',
                'id'           => 'catfeat',
                'element'      => ($settings['enable_catfeat']) ? 'checkbox' : false,
                'label'        => '',
                'multiOptions' => array(
                    1 => $translate->_('Feature on category page') . ' ' . $this->getView()->amount($this->getFees()->getFeeAmount(Service\Fees::CATFEAT),
                            null,
                            '<span class="badge badge-text badge-slim">+%s</span>'),
                ),
                'bulk'         => array(
                    'multiOptions' => array(0, 1),
                    'label'        => $translate->_('Category Page Featuring'),
                    'type'         => $translate->_('integer'),
                ),
            ),
            array(
                'form_id'      => array('auction', 'product', 'classified', 'fees_calculator', 'bulk'),
                'subform'      => 'settings',
                'id'           => 'highlighted',
                'element'      => ($settings['enable_highlighted']) ? 'checkbox' : false,
                'label'        => '',
                'multiOptions' => array(
                    1 => $translate->_('Highlight listing') . ' ' . $this->getView()->amount($this->getFees()->getFeeAmount(Service\Fees::HIGHLIGHTED),
                            null,
                            '<span class="badge badge-text badge-slim">+%s</span>'),
                ),
                'bulk'         => array(
                    'multiOptions' => array(0, 1),
                    'label'        => $translate->_('Highlighted Listing'),
                    'type'         => $translate->_('integer'),
                ),
            ),
            array(
                'form_id'      => array('auction', 'product', 'prefilled'),
                'subtitle'     => $this->_('Auto Relist'),
                'subform'      => 'settings',
                'id'           => 'enable_auto_relist',
                'element'      => ($settings['auto_relist']) ? 'checkbox' : false,
                'value'        => ($this->getData('nb_relists') > 0 || $this->getData('relist_until_sold')) ? 1 : 0,
                'label'        => $this->_('Enable Auto Relist'),
                'multiOptions' => array(
                    1 => null,
                ),
                'attributes'   => array(
                    'class' => 'field-changeable',
                ),
                'description'  => $this->_('Check the above checkbox for your listing to be relisted automatically.'),
            ),
            array(
                'form_id'      => array('auction', 'product', 'prefilled'),
                'subform'      => 'settings',
                'id'           => 'relist_until_sold',
                'element'      => ($settings['auto_relist']) ? 'checkbox' : false,
                'label'        => $this->_('Relist Until Sold'),
                'multiOptions' => array(
                    1 => null,
                ),
                'attributes'   => array(
                    'class' => 'field-changeable',
                ),
                'description'  => $this->_('Check the above checkbox for your listing to be relisted until sold.'),
            ),
            array(
                'form_id'     => array('auction', 'product', 'prefilled', 'bulk'),
                'subform'     => 'settings',
                'id'          => 'nb_relists',
                'element'     => ($settings['auto_relist']) ? 'text' : false,
                'label'       => $this->_('Number of Relists'),
                'description' => sprintf($translate->_('(Optional) Enter the number of times the item will be relisted automatically. <br>'
                    . 'The maximum number of auto relists allowed is %s.'), $settings['max_auto_relists']),
                'attributes'  => array(
                    'class' => 'form-control input-mini',
                ),
                'validators'  => array(
                    'Digits',
                    array('LessThan', array($settings['max_auto_relists'], true)),
                ),
                'bulk'        => array(
                    'type' => $translate->_('integer'),
                ),
            ),
            array(
                'form_id'      => array('auction', 'product', 'prefilled', 'bulk'),
                'subform'      => 'settings',
                'id'           => 'auto_relist_sold',
                'element'      => ($settings['auto_relist']) ? 'checkbox' : false,
                'label'        => $this->_('Auto Relist if Sold'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('By enabling this option, your item will be relisted even if sold.'),
                'bulk'         => array(
                    'multiOptions' => array(0, 1),
                    'type'         => $translate->_('integer'),
                ),
            ),
            array(
                'form_id'      => array('auction', 'product', 'classified', 'prefilled', 'product_edit', 'bulk'),
                'subtitle'     => $this->_('Location'),
                'subform'      => 'settings',
                'id'           => 'country',
                'element'      => 'select',
                'label'        => $this->_('Country'),
                'multiOptions' => $countries,
                'required'     => (!in_array('prefilled', $this->_formId)) ? true : false,
                'forceDefault' => true,
                'description'  => $this->_('Enter the country where the item is located.'),
                'attributes'   => array(
                    'class' => 'form-control input-medium',
                ),
                'bodyCode'     => "
                    <script type=\"text/javascript\">
                        $(document).on('change', '[name=\"country\"]', function() {
                            $.post(
                                '" . $this->getView()->url(array('module' => 'app', 'controller' => 'async', 'action' => 'select-location')) . "',
                                {
                                    id: $('[name=\"country\"]').val()
                                }, 
                                function (data) {
                                    var state = $('[name=\"state\"]');
                                    var div = state.closest('div');
                                    
                                    state.remove();
                                    div.prepend(data);
                                }
                            );
                        });
                    </script>",
                'validators'   => (!in_array('prefilled', $this->_formId)) ? array(
                    $locationRecordExists,
                ) : array(),
                'bulk'         => array(
                    'type'         => $translate->_('integer'),
                    'notes'        => $translate->_('Country IDs allowed are available in the "Locations" tab. '),
                    'multiOptions' => array(),
                    'sample'       => 2083,
                ),
            ),
            array(
                'form_id'      => array('auction', 'product', 'classified', 'prefilled', 'product_edit', 'bulk'),
                'subform'      => 'settings',
                'id'           => 'state',
                'element'      => (count($states) > 0) ? 'select' : 'text',
                'label'        => $this->_('State/County'),
                'multiOptions' => $states,
                'description'  => $this->_('Enter the state/county where the item is located.'),
                'attributes'   => array(
                    'class' => 'form-control input-medium',
                ),
                'filters'      => array(
                    '\\Ppb\\Filter\\BadWords',
                ),
                'bulk'         => array(
                    'multiOptions' => array(),
                    'sample'       => 2104,
                ),
            ),
            array(
                'form_id'     => array('auction', 'product', 'classified', 'prefilled', 'product_edit', 'bulk'),
                'subform'     => 'settings',
                'id'          => 'address',
                'element'     => 'text',
                'label'       => $this->_('Address/Post Code'),
                'required'    => (!in_array('prefilled', $this->_formId)) ? true : false,
                'description' => $this->_('Enter the address/post code where the item is located.'),
                'attributes'  => array(
                    'class' => 'form-control input-medium',
                ),
                'filters'     => array(
                    '\\Ppb\\Filter\\BadWords',
                ),
                'bulk'        => array(
                    'sample' => 'My Street',
                ),
            ),
            array(
                'form_id'      => array('auction', 'product', 'prefilled', 'product_edit', 'bulk'),
                'subform'      => 'shipping',
                'subtitle'     => $this->_('Shipping'),
                'id'           => ShippingModel::FLD_PICKUP_OPTIONS,
                'element'      => ($settings['enable_shipping'] && $settings['enable_pickups']) ? 'select' : 'hidden',
                'value'        => ($settings['enable_pickups']) ? $this->getData('pickups') : ShippingModel::NO_PICKUPS,
                'label'        => ShippingModel::$postageFields[ShippingModel::FLD_PICKUP_OPTIONS],
                'description'  => $this->_('Select if you wish to offer pick-up options for your listing.'),
                'multiOptions' => ShippingModel::$pickupOptions,
                'attributes'   => array(
                    'class' => 'form-control input-medium field-changeable',
                ),
            ),
            array(
                'form_id'               => array('auction', 'product', 'prefilled', 'product_edit', 'bulk'),
                'subform'               => 'shipping',
                'id'                    => ShippingModel::FLD_POSTAGE,
                'element'               => ($settings['enable_shipping'] && $this->getUser()->getShipping()->getPostageType() == ShippingModel::POSTAGE_TYPE_ITEM) ? '\\Ppb\\Form\\Element\\ListingPostageLocations' : false,
                'label'                 => ShippingModel::$postageFields[ShippingModel::FLD_POSTAGE],
                'locationsMultiOptions' => $this->getUser()->getShipping()->getLocationGroups(),
                'description'           => $this->_('Enter the postage options that apply to this item.'),
                'arrange'               => true,
                'validators'            => (
                    (!in_array('prefilled', $this->_formId)) &&
                    (!in_array('bulk', $this->_formId)) &&
                    $this->getData(ShippingModel::FLD_PICKUP_OPTIONS) != ShippingModel::MUST_PICKUP) ?
                    array(
                        '\\Ppb\\Validate\\ItemPostage',
                    ) : null,
                'filters'               => array(
                    '\\Ppb\\Filter\\LocalizedNumeric',
                ),
                'bulk'                  => array(
                    'multiOptions' => (array)$this->getUser()->getShipping()->getLocationGroups(),
                ),
            ),
            array(
                'form_id'     => array('auction', 'product', 'product_edit', 'bulk'),
                'subform'     => 'shipping',
                'id'          => ShippingModel::FLD_ITEM_WEIGHT,
                'element'     => ($settings['enable_shipping'] && $this->getUser()->getShipping()->getPostageType() == ShippingModel::POSTAGE_TYPE_CARRIERS) ? '\\Ppb\\Form\\Element\\LocalizedNumeric' : 'hidden',
                'label'       => ShippingModel::$postageFields[ShippingModel::FLD_ITEM_WEIGHT],
                'suffix'      => $this->getUser()->getShipping()->getWeightUom(),
                'description' => $this->_('Enter the weight of your item.'),
                'attributes'  => array(
                    'class' => 'form-control input-mini field-shipping',
                ),
                'required'    => (
                    $settings['enable_shipping'] &&
                    $this->getUser()->getShipping()->getPostageType() == ShippingModel::POSTAGE_TYPE_CARRIERS &&
                    $this->getData(ShippingModel::FLD_PICKUP_OPTIONS) != ShippingModel::MUST_PICKUP
                ) ? true : false,
                'filters'     => array(
                    '\\Ppb\\Filter\\LocalizedNumeric',
                ),
                'bulk'        => array(
                    'type'   => $translate->_('decimal'),
                    'notes'  => sprintf($translate->_('Weight UOM: %s'), $this->getUser()->getShipping()->getWeightUom()),
                    'sample' => 1.5,
                ),
            ),
            array(
                'form_id'     => array('auction', 'product', 'product_edit', 'bulk'),
                'subform'     => 'shipping',
                'id'          => ShippingModel::FLD_DIMENSIONS,
                'element'     => ($settings['enable_shipping'] && $this->getUser()->getShipping()->getPostageType() == ShippingModel::POSTAGE_TYPE_CARRIERS) ? '\\Ppb\\Form\\Element\\Dimensions' : 'hidden',
                'label'       => ShippingModel::$postageFields[ShippingModel::FLD_DIMENSIONS],
                'suffix'      => $this->getUser()->getShipping()->getDimensionsUom(),
                'description' => $this->_('Enter the dimensions of your item (L x W x H).'),
                'attributes'  => array(
                    'class'       => 'form-control input-mini field-shipping',
                    'placeholder' => array(
                        ShippingModel::DIMENSION_LENGTH => $translate->_('Length'),
                        ShippingModel::DIMENSION_WIDTH  => $translate->_('Width'),
                        ShippingModel::DIMENSION_HEIGHT => $translate->_('Height'),
                    ),
                ),
                'filters'     => array(
                    '\\Ppb\\Filter\\LocalizedNumeric',
                ),
                'bulk'        => array(
                    'type'   => $translate->_('string'),
                    'notes'  => sprintf($translate->_('Dimensions UOM: %s'), $this->getUser()->getShipping()->getDimensionsUom())
                        . '<br>'
                        . $this->_('The required format for this field is: Length||Width||Height'),
                    'sample' => '5' . Service\Listings\BulkLister::ARRAY_SEPARATOR . '7' . Service\Listings\BulkLister::ARRAY_SEPARATOR . '10',
                ),
            ),
            array(
                'form_id'     => array('auction', 'product', 'product_edit', 'prefilled', 'bulk'),
                'subform'     => 'shipping',
                'id'          => ShippingModel::FLD_INSURANCE,
                'element'     => ($settings['enable_shipping']) ? '\\Ppb\\Form\\Element\\LocalizedNumeric' : 'hidden',
                'label'       => ShippingModel::$postageFields[ShippingModel::FLD_INSURANCE],
                'prefix'      => '<span class="listing-currency">' . $this->getData('currency') . '</span>',
                'description' => $this->_('Enter the insurance amount that applies to this item (optional).'),
                'attributes'  => array(
                    'class' => 'form-control input-mini field-shipping',
                ),
                'filters'     => array(
                    '\\Ppb\\Filter\\LocalizedNumeric',
                ),
                'validators'  => array(
                    'Numeric',
                    array('GreaterThan', array(0, true)),
                ),
                'bulk'        => array(
                    'type' => $translate->_('decimal'),
                ),
            ),
            array(
                'form_id'     => array('auction', 'product', 'product_edit', 'prefilled', 'bulk'),
                'subform'     => 'shipping',
                'id'          => ShippingModel::FLD_SHIPPING_DETAILS,
                'element'     => ($settings['enable_shipping']) ? 'textarea' : 'hidden',
                'label'       => ShippingModel::$postageFields[ShippingModel::FLD_SHIPPING_DETAILS],
                'description' => $this->_('Enter any shipping instructions that might apply for your item (optional).'),
                'attributes'  => array(
                    'rows'  => '4',
                    'class' => 'form-control field-shipping',
                ),
                'filters'     => array(
                    '\\Ppb\\Filter\\BadWords',
                ),
                'bulk'        => array(
                    'sample' => 'Shipping Details',
                ),
            ),
            array(
                'form_id'      => array('auction', 'product', 'product_edit', 'prefilled', 'bulk'),
                'subform'      => 'shipping',
                'subtitle'     => $this->_('Returns'),
                'id'           => ShippingModel::FLD_ACCEPT_RETURNS,
                'element'      => ($settings['enable_returns']) ? 'checkbox' : false,
                'value'        => ($this->getData('returns_policy')) ? 1 : null,
                'label'        => ShippingModel::$postageFields[ShippingModel::FLD_ACCEPT_RETURNS],
                'multiOptions' => array(
                    1 => null,
                ),
                'attributes'   => array(
                    'class' => 'field-changeable',
                ),
                'description'  => $this->_('Check the above checkbox if you will be accepting returns for this item.'),
                'bulk'         => array(
                    'multiOptions' => array(0, 1),
                ),
            ),
            array(
                'form_id'     => array('auction', 'product', 'product_edit', 'prefilled', 'bulk'),
                'subform'     => 'shipping',
                'id'          => ShippingModel::FLD_RETURNS_POLICY,
                'element'     => 'textarea',
                'label'       => ShippingModel::$postageFields[ShippingModel::FLD_RETURNS_POLICY],
                'description' => $this->_('Enter any return policy details that might apply for your item (optional).'),
                'attributes'  => array(
                    'class' => 'form-control',
                ),
                'filters'     => array(
                    '\\Ppb\\Filter\\BadWords',
                ),
                'bulk'        => array(
                    'sample' => 'Returns Policy',
                ),
            ),
            array(
                'form_id'      => array('auction', 'product', 'product_edit', 'prefilled', 'bulk'),
                'subform'      => 'shipping',
                'subtitle'     => $this->_('Payment Methods'),
                'id'           => 'direct_payment',
                'element'      => (count($paymentGateways) > 0) ? 'checkbox' : 'hidden',
                'label'        => $this->_('Direct Payment'),
                'multiOptions' => $paymentGateways,
                'attributes'   => array(
                    'class' => 'direct-payment',
                ),
                'description'  => $translate->_('Select any direct payment methods that can be used to pay for this item.')
                    . ((!$this->_inAdmin) ? '<br>'
                        . sprintf($translate->_('<a id="direct-payment-form" class="jq-popup-form" href="%s" title="%s">Click here</a> to manage your payment gateways credentials / settings.'),
                            $this->getView()->url(array(
                                'module'     => 'members',
                                'controller' => 'user',
                                'action'     => 'edit-payment-gateway',
                                'popup'      => true)),
                            $translate->_('Direct Payment Gateways Settings')) : ''),
                'bulk'         => array(
                    'notes'        => sprintf(
                        $translate->_('Direct payment methods available are displayed in the "Payment Methods" tab. Multiple selections are to be separated by "%s"'),
                        $bulkArraySeparator),
                    'multiOptions' => array(),
                    'sample'       => implode($bulkArraySeparator, array_keys($paymentGateways))
                ),
            ),
            array(
                'form_id'      => array('auction', 'product', 'classified', 'product_edit', 'prefilled', 'bulk'),
                'subform'      => 'shipping',
                'id'           => 'offline_payment',
                'element'      => (count($paymentMethods) > 0) ? 'checkbox' : 'hidden',
                'label'        => $this->_('Offline Payment'),
                'multiOptions' => $paymentMethods,
                'description'  => $this->_('Select any payment methods from the above that the buyer might use to pay for the item.'
                    . 'The payment through these payment methods will be handled offline.'),
                'bulk'         => array(
                    'notes'        => sprintf(
                        $translate->_('Offline payment methods available are displayed in the "Payment Methods" tab. Multiple selections are to be separated by "||"'),
                        $bulkArraySeparator),
                    'multiOptions' => array(),
                    'sample'       => implode($bulkArraySeparator, array_keys($paymentMethods))
                ),
            ),
            array(
                'form_id'    => array('auction', 'product', 'product_edit'),
                'subform'    => 'shipping',
                'id'         => 'check_payment_methods',
                'element'    => 'hidden',
                'validators' => (count($paymentGateways) > 0 || count($paymentMethods) > 0) ?
                    array('\\Ppb\\Validate\\PaymentMethods') : null,
            )
        );


        if (!in_array('bulk', $this->_formId)) {
            array_splice($elements, 10, 0, $customFields);
        }
        else {
            $bulkListerCustomFields = $this->getCustomFields()->getFields(
                array(
                    'type'   => $customFieldsType,
                    'active' => 1,
                ))->toArray();

            foreach ($bulkListerCustomFields as $key => $customField) {
                $bulkListerCustomFields[$key]['form_id'] = array('bulk');
                $bulkListerCustomFields[$key]['id'] = (!empty($customField['alias'])) ?
                    $customField['alias'] : 'custom_field_' . $customField['id'];

                if ($customField['product_attribute']) {
                    $bulkListerCustomFields[$key]['required'] = false;
                }

                if (!empty($customField['multiOptions'])) {
                    $multiOptions = \Ppb\Utility::unserialize($customField['multiOptions']);
                    $bulkListerCustomFields[$key]['bulk']['multiOptions'] = (!empty($multiOptions['key'])) ?
                        array_flip(array_filter($multiOptions['key'])) : array();
                }
            }

            $elements = array_merge($elements, $bulkListerCustomFields);
        }

        return $this->_arrayMergeOrdering($elements, parent::getRelatedElements());
    }

}

