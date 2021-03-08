<?php

/**
 *
 * PHP Pro Bid
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2015 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.4
 */
/**
 * ebay import form
 */
/**
 * MOD:- EBAY IMPORTER
 *
 * @version 2.0
 */

namespace Members\Form;

use Ppb\Form\AbstractBaseForm,
    Ppb\Service\Listings\BulkLister\Ebay as EbayService;

class EbayImport extends AbstractBaseForm
{

    const BTN_SUBMIT = 'submit';

    /**
     *
     * available marketplaces to import from
     *
     * @var array
     */
    public static $marketplaces = array(
        'EBAY-US'    => 'Default (eBay United States)',
        'EBAY-GB'    => 'eBay UK',
        'EBAY-AT'    => 'eBay Austria',
        'EBAY-AU'    => 'eBay Australia',
        'EBAY-CH'    => 'eBay Switzerland',
        'EBAY-DE'    => 'eBay Germany',
        'EBAY-ENCA'  => 'eBay Canada (English)',
        'EBAY-FRCA'  => 'eBay Canada (Fench)',
        'EBAY-ES'    => 'eBay Spain',
        'EBAY-FR'    => 'eBay France',
        'EBAY-HK'    => 'eBay Hong Kong',
        'EBAY-IN'    => 'eBay India',
        'EBAY-IT'    => 'eBay Italy',
        'EBAY-MOTOR' => 'eBay Motors',
        'EBAY-NL'    => 'eBay Netherlands',
        'EBAY-SG'    => 'eBay Singapore',
        'EBAY-FRBE'  => 'eBay Belgium (French)',
        'EBAY-NLBE'  => 'eBay Belgium (Dutch)',
        'EBAY-IE'    => 'eBay Ireland',
        'EBAY-MY'    => 'eBay Malaysia',
        'EBAY-PH'    => 'eBay Philippines',
        'EBAY-PL'    => 'eBay Poland',
    );


    /**
     *
     * submit buttons values
     *
     * @var array
     */
    protected $_buttons = array(
        self::BTN_SUBMIT => 'Upload',
    );

    /**
     * @var bool
     */
    protected $_posted = false;

    /**
     *
     * class constructor
     *
     * @param string $action the form's action
     */
    public function __construct($action = null)
    {
        parent::__construct($action);


        $this->setMethod(self::METHOD_POST);

        $translate = $this->getTranslate();


        $marketplace = $this->createElement('select', 'marketplace')
            ->setLabel('Ebay Marketplace')
            ->setDescription('Select the ebay marketplace website you will be importing from.')
            ->setAttributes(array(
                'class' => 'form-control input-medium'
            ))
            ->setMultiOptions(self::$marketplaces);
        $this->addElement($marketplace);

        $username = $this->createElement('text', 'ebay_username')
            ->setLabel('Ebay Username')
            ->setDescription('Enter your Ebay username.'
                . '<div id="ebay-import-msg"></div>')
            ->setAttributes(array(
                'class' => 'form-control input-medium',
            ))
            ->setBodyCode("
                <script type=\"text/javascript\">
                    $(document).ready(function() {
                        function checkTotalListings() {
                            var username = $('input[name=\"ebay_username\"]').val();
                            var marketplace = $('select[name=\"marketplace\"]').val();

                            $.post(
                                '" . $this->getView()->url(array('module' => 'app', 'controller' => 'async', 'action' => 'ebay-import-total-listings')) . "',
                                {
                                    username: username,
                                    marketplace: marketplace
                                },
                                function (data) {
                                    $('body').removeClass('loading');
                                    if (username == '') {
                                        $('#btn-ebay-import').attr('disabled', true);
                                        $('#ebay-import-msg')
                                            .hide();
                                    }
                                    else if (data.listings > 0 && data.token == true) {

                                        $('#btn-ebay-import').attr('disabled', false);
                                        $('#ebay-import-msg')
                                            .html(data.message)
                                            .prop('class', 'text-success')
                                            .show();
                                            
                                        $('input[name=\"total_listings\"]').val(data.listings);
                                    }
                                    else {
                                        $('#btn-ebay-import').attr('disabled', true);
                                        $('#ebay-import-msg')
                                            .html(data.message)
                                            .prop('class', 'text-danger')
                                            .show();
                                    }
                                },
                                'json'
                            );
                        }
                        $('input[name=\"ebay_username\"], select[name=\"marketplace\"]').change(function() {
                            $('#ebay-import-msg')
                                .html('Please wait...')
                                .prop('class', 'text-info')
                                .show();
                            checkTotalListings();
                        });

                        checkTotalListings();
                    });
                </script>")
            ->setRequired();
        $this->addElement($username);

        $uploadAs = $this->createElement('radio', 'upload_as')
            ->setLabel('Upload As')
            ->setDescription('Select how the listings will be uploaded as.')
            ->setValue('bulk')
            ->setMultiOptions(array(
                'bulk' => $translate->_('Drafts / Bulk'),
                'live' => $translate->_('Live'),
            ));
        $this->addElement($uploadAs);

        $totalListings = $this->createElement('hidden', 'total_listings');
        $this->addElement($totalListings);

        $importType = $this->createElement('radio', 'import_type')
            ->setLabel('Import Type')
            ->setDescription('Select which items you wish to import.')
            ->setValue(EbayService::IMPORT_TYPE_ALL_INC_DUPLICATES)
            ->setMultiOptions(array(
                EbayService::IMPORT_TYPE_ALL_INC_DUPLICATES => 'All, including duplicates',
                EbayService::IMPORT_TYPE_ALL_WO_DUPLICATES  => array(
                    'All, without duplicates',
                    'Important: Importing all items will remove any items you have previously imported!',
                ),
                EbayService::IMPORT_TYPE_NEW_WO_DUPLICATES  => 'New items only, without duplicates',
            ));
        $this->addElement($importType);

//        $importType = $this->createElement('radio', 'import_type')
//            ->setLabel('Import Type')
//            ->setDescription('Select whether to import new items or to import afresh.')
//            ->setValue('default')
//            ->setMultiOptions(array(
//                'default' => 'New Items Only',
//                'afresh'  => array(
//                    'Import Afresh',
//                    '<strong>Warning:</strong> Importing afresh will delete all ebay listings you have currently imported!')
//
//            ));
//        $this->addElement($importType);

        $submit = $this->createElement('submit', self::BTN_SUBMIT)
            ->setAttributes(array(
                'class' => 'btn btn-primary btn-lg',
                'id'    => 'btn-ebay-import',
            ))
            ->setBodyCode("
                    <script type=\"text/javascript\">
                        $(document).on('click', '[name=\"" . self::BTN_SUBMIT . "\"]', function() {
                            $('body').addClass('loading');
                        });
                    </script>")
            ->setValue('Upload');
        $this->addElement($submit);

        $this->setPartial('forms/ebay-import.phtml');
    }

    /**
     * @return boolean
     */
    public function isPosted()
    {
        return $this->_posted;
    }

    /**
     * @param boolean $posted
     *
     * @return $this
     */
    public function setPosted($posted)
    {
        $this->_posted = $posted;

        return $this;
    }


    /**
     *
     * this method will create the necessary code in order for ebay items to be parsed asynchronously,
     * after we have first validated the form using a normal post
     *
     * @return $this
     */
    public function postAsync()
    {
        $this->clearElements();

        $this->setPosted(true);

        /* @var \Cube\View\Helper\Script $helper */
        $helper = $this->getView()->getHelper('script');
        $helper->addBodyCode(
            "<script type=\"text/javascript\">
                var totalListings = '" . $this->_data['total_listings'] . "'; // variable - get from ebay
        
                var entriesPerPage = 25;
                var pageNumber = 0; // always 0
                var progress = 0;
                var duplicateRows = 0;
                var validRows = 0;
        
                function parseEbayItems() {
                    if (progress == 0) {
                        $('#ebay-parser-progress').html('<div class=\"text-info\">' + progress + '/' + totalListings + ' listings parsed.</div>');
                    }
                    
                    if ((pageNumber * entriesPerPage) >= totalListings) {
                        $('#ebay-parser-info').prop('class', 'text-success').html('<strong>Parse complete.</strong>');
                        $('.progress-bar').removeClass('active');
                        return;
                    }

                    pageNumber ++;

                    $.ajax({
                        url: '" . $this->_view->url(array('module' => 'members', 'controller' => 'tools', 'action' => 'parse-ebay-items-async')) . "',
                        dataType: \"json\",
                        data: {
                            pageNumber: pageNumber,
                            entriesPerPage: entriesPerPage,
                            totalListings: totalListings,
                            uploadAs: '" . $this->_data['upload_as'] . "',
                            importType: '" . $this->_data['import_type'] . "',
                            ebayUsername: '" . $this->_data['ebay_username'] . "',
                            ebayMarketplace: '" . $this->_data['marketplace'] . "'
                        },
                        cache: false,
                        success: function (data) {
                            progress += data.counter;
                            var percent = Math.round((progress / totalListings) * 100);
                            $('.progress-bar').css('width', percent + '%').attr('aria-valuenow', percent);  
                            
                            var parserProgress = '<div class=\"text-info\">' + progress + '/' + totalListings + ' listings parsed.</div>';
                            
                            validRows += data.validRows;                           
                            if (validRows > 0) {
                                parserProgress += '<div class=\"text-success\">' + validRows + ' listings have been imported.</div>';
                            }
                            
                            duplicateRows += data.duplicateRows;                           
                            if (duplicateRows > 0) {
                                parserProgress += '<div class=\"text-danger\">' + duplicateRows + ' listings have been skipped because they have already been previously uploaded.</div>';
                            }
                            
                            $('#ebay-parser-progress').html(parserProgress);
                            
                            $.each(data.parseErrors, function(i, msg) {
                                $('#parser-messages').append('<div>' + msg + '</div>');
                            });
                            
                            $.each(data.listingMessages, function(i, msg) {
                                $('#parser-messages').append('<div>' + msg + '</div>');
                            });
                            
                            parseEbayItems();                            
                        }
                    });
                }
        
                parseEbayItems();
            </script>");

        return $this;
    }
}