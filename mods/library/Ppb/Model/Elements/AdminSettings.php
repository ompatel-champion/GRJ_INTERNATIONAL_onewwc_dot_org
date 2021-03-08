<?php

/**
 *
 * PHP Pro Bid
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2020 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     8.2 [rev.8.2.07]
 */

/**
 * admin form elements collection
 */

namespace Ppb\Model\Elements;

use Cube\Controller\Front,
    Cube\Db\Select,
    Cube\Db\Expr,
    Cube\Cache\Adapter as CacheAdapter,
    Ppb\Service\Table\Currencies as CurrenciesService,
    Ppb\Service\Timezones as TimezonesService,
    Ppb\Service\Listings as ListingsService,
    Ppb\Service\Fees,
    Ppb\Form\Element\Selectize,
    Ppb\Service\Reputation as ReputationService;

class AdminSettings extends AbstractElements
{

    /**
     *
     * timezones table service
     *
     * @var \Ppb\Service\Timezones
     */
    protected $_timezones;

    /**
     *
     * currencies table service
     *
     * @var \Ppb\Service\Table\Currencies
     */
    protected $_currencies;

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
     * get timezones table service
     *
     * @return \Ppb\Service\Timezones
     */
    public function getTimezones()
    {
        if (!$this->_timezones instanceof TimezonesService) {
            $this->setTimezones(
                new TimezonesService());
        }

        return $this->_timezones;
    }

    /**
     *
     * set timezones table service
     *
     * @param \Ppb\Service\Timezones $timezones
     *
     * @return $this
     */
    public function setTimezones(TimezonesService $timezones)
    {

        $this->_timezones = $timezones;

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
        if (!$this->_currencies instanceof CurrenciesService) {
            $this->setCurrencies(
                new CurrenciesService());
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
    public function setCurrencies(CurrenciesService $currencies)
    {
        $this->_currencies = $currencies;

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
        $settings = $this->getSettings();
        $translate = $this->getTranslate();

        $basePath = Front::getInstance()->getRequest()->getBasePath();

        $totalListings = 0;
        if (in_array('listings_counters', $this->_formId)) {
            $listingsService = new ListingsService();

            $select = $listingsService->select(ListingsService::SELECT_LISTINGS);

            $select->reset(Select::COLUMNS);
            $select->columns(array('nb_rows' => new Expr('count(*)')));

            $stmt = $select->query();

            $totalListings = (integer)$stmt->fetchColumn('nb_rows');
        }

        $maximumFileUploadSize = \Ppb\Utility::getMaximumFileUploadSize('M')
            . ' ' . $translate->_('MB');

        $itemsRowDesktop = array(
            1 => 1,
            2 => 2,
            3 => 3,
            4 => 4,
            6 => 6,
        );

        $itemsRowPhone = array(
            1 => 1,
            2 => 2
        );

        $elements = array(
            /**
             * --------------
             * SITE SETUP
             * --------------
             */
            array(
                'form_id'     => 'site_setup',
                'id'          => 'sitename',
                'element'     => 'text',
                'label'       => $this->_('Site Name'),
                'description' => $this->_('Enter the site\'s name. It will be used for generating dynamic meta titles and will be displayed in all the emails sent by and through the site.'),
                'attributes'  => array(
                    'class' => 'form-control input-medium',
                ),
                'required'    => true,
                'validators'  => array(
                    'NoHtml'
                ),
            ),
            array(
                'form_id'     => 'site_setup',
                'id'          => 'site_path',
                'element'     => 'text',
                'label'       => $this->_('Site URL'),
                'description' => $this->_('Enter your site\'s URL. <br>'
                    . 'The URL must have the following format: http://www.yoursite.com<br>'
                    . '(Optional) If SSL is available you can set your URL using https:// rather than http://'),
                'attributes'  => array(
                    'class' => 'form-control input-medium',
                ),
                'required'    => true,
                'validators'  => array(
                    'Url',
                ),
            ),
            array(
                'form_id'     => 'site_setup',
                'id'          => 'site_logo_path',
                'element'     => '\\Ppb\\Form\\Element\\MultiUpload',
                'label'       => $this->_('Site Logo'),
                'description' => $this->_('Upload a logo for your website.'),
                'required'    => true,
                'customData'  => array(
                    'buttonText'      => $translate->_('Select Logo'),
                    'acceptFileTypes' => '/(\.|\/)(gif|jpe?g|png)$/i',
                    'formData'        => array(
                        'fileSizeLimit' => 10000000, // approx 10MB
                        'uploadLimit'   => 1,
                    ),
                ),
            ),
            array(
                'form_id'     => 'site_setup',
                'id'          => 'favicon',
                'element'     => '\\Ppb\\Form\\Element\\MultiUpload',
                'label'       => $this->_('Favicon'),
                'description' => $this->_('Upload a favicon for your website.'),
                'required'    => true,
                'customData'  => array(
                    'buttonText'      => $translate->_('Select Favicon'),
                    'acceptFileTypes' => '/(\.|\/)(ico|png)$/i',
                    'formData'        => array(
                        'fileSizeLimit' => 100000, // approx 100KB
                        'uploadLimit'   => 1,
                    ),
                ),
            ),

            array(
                'form_id'     => 'site_setup',
                'id'          => 'admin_security_key',
                'element'     => 'text',
                'label'       => $this->_('Admin Area Security Key'),
                'description' => $this->_('(Optional) You can add a security key that will be required to be added to the admin path in order to be able to access it. <br>'
                        . 'Current Admin Path:')
                    . '<div class="text-info"><strong>'
                    . $this->getView()->url(array('skey' => $this->getData('admin_security_key')), 'admin-index-index') . '</strong></div>',
                'attributes'  => array(
                    'class'        => 'form-control input-medium alert-box',
                    'data-message' => $translate->_('Warning! If adding a security key, please save the admin path in a safe place because you will '
                        . 'not be able to access the admin area without adding the security key to the url.'),
                ),
            ),
            array(
                'form_id'     => 'site_setup',
                'id'          => 'admin_email',
                'element'     => 'text',
                'label'       => $this->_('Admin Email Address'),
                'description' => $this->_('Enter your admin email address. This address will be used in the "From" field by all system emails.'),
                'attributes'  => array(
                    'class' => 'form-control input-medium',
                ),
                'required'    => true,
                'validators'  => array(
                    'Email'
                ),
            ),
            array(
                'form_id'     => 'site_setup',
                'id'          => 'email_admin_title',
                'element'     => 'text',
                'label'       => $this->_('Admin Email From Name'),
                'description' => $this->_('Enter the "From" name which will appear on all emails sent by the site on behalf of the administrator.'),
                'attributes'  => array(
                    'class' => 'form-control input-medium',
                ),
                'required'    => true,
                'validators'  => array(
                    'NoHtml'
                ),
            ),
            array(
                'form_id'      => 'site_setup',
                'id'           => 'mailer',
                'element'      => 'select',
                'label'        => $this->_('Choose Mailer'),
                'multiOptions' => array(
                    'mail'     => 'PHP mail()',
                    'sendmail' => 'Sendmail',
                    'smtp'     => 'SMTP',
                ),
                'description'  => $this->_('Available methods: php mail() function, unix sendmail app, SMTP protocol.<br>'
                    . 'SMTP recommended (if available on your server)'),
                'required'     => true,
                'attributes'   => array(
                    'id'       => 'mailer',
                    'class'    => 'form-control input-medium',
                    'onchange' => 'javascript:checkMailFields()',
                ),
                'bodyCode'     => "
                    <script type=\"text/javascript\">
                        function checkMailFields() {
                            var mailerSendmail = $('.mailer-sendmail');
                            var mailerSmtp = $('.mailer-smtp');
                            switch ($('#mailer').val()) {
                                case 'sendmail':
                                    mailerSendmail.closest('.form-group').show();
                                    mailerSmtp.closest('.form-group').hide();
                                    break;
                                case 'smtp':
                                    mailerSendmail.closest('.form-group').hide();
                                    mailerSmtp.closest('.form-group').show();
                                    break;
                                default:
                                    mailerSendmail.closest('.form-group').hide();
                                    mailerSmtp.closest('.form-group').hide();
                                    break;                    
                            }
                        }

                        $(document).ready(function() {             
                            checkMailFields();
                        });
                    </script>",
            ),
            /* site setup => sendmail path */
            array(
                'form_id'     => 'site_setup',
                'id'          => 'sendmail_path',
                'element'     => 'text',
                'label'       => $this->_('Sendmail Path'),
                'description' => $this->_('Enter the unix path for the sendmail app (available in phpinfo())'),
                'required'    => array('mailer', 'sendmail', true),
                'attributes'  => array(
                    'class' => 'mailer-sendmail form-control input-medium',
                ),
            ),
            /* site setup => smtp related fields */
            array(
                'form_id'    => 'site_setup',
                'id'         => 'smtp_host',
                'element'    => 'text',
                'label'      => $this->_('SMTP Host'),
                'attributes' => array(
                    'class' => 'mailer-smtp form-control input-medium',
                ),
            ),
            array(
                'form_id'    => 'site_setup',
                'id'         => 'smtp_port',
                'element'    => 'text',
                'label'      => $this->_('SMTP Port'),
                'attributes' => array(
                    'class' => 'mailer-smtp form-control input-medium',
                ),
            ),
            array(
                'form_id'      => 'site_setup',
                'id'           => 'smtp_protocol',
                'element'      => 'select',
                'label'        => $this->_('SMTP Protocol'),
                'attributes'   => array(
                    'class' => 'mailer-smtp form-control input-medium',
                ),
                'multiOptions' => array(
                    'TLS', 'SSL'
                ),
            ),
            array(
                'form_id'    => 'site_setup',
                'id'         => 'smtp_username',
                'element'    => 'text',
                'label'      => $this->_('SMTP Username'),
                'attributes' => array(
                    'class' => 'mailer-smtp form-control input-medium',
                ),
            ),
            array(
                'form_id'     => 'site_setup',
                'id'          => 'smtp_password',
                'element'     => 'text',
                'label'       => $this->_('SMTP Password'),
                'description' => $this->_('Enter your SMTP login details in case you choose to use SMTP as the system emails handler.<br>'
                    . '<strong>Important</strong>: you only need enter a username and a password if you SMTP server requires authentication.'
                    . 'If the server doesn\'t require authentication, please leave these fields empty because otherwise the SMTP server can return an error and no emails will be sent.<br>'
                    . 'If you are unsure of your SMTP server\'s host name and port, please leave the Host and Port fields empty and the software will try to retrieve them for you.'),
                'attributes'  => array(
                    'class' => 'mailer-smtp form-control input-medium',
                ),
            ),
            array(
                'form_id'      => 'site_setup',
                'id'           => 'disable_installer',
                'element'      => 'checkbox',
                'label'        => $this->_('Disable Installer'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check above to disable the installer module. '
                    . 'Enable only when upgrading the software.'),
            ),
            /**
             * ++++++++++++++
             * THEMES & CUSTOMIZATIONS
             * ++++++++++++++
             */
            array(
                'form_id'      => 'themes_customizations',
                'id'           => 'default_theme',
                'element'      => 'select',
                'label'        => $this->_('Site Theme'),
                'multiOptions' => \Ppb\Utility::getThemes(),
                'description'  => $this->_('Select a theme for your website.'),
                'attributes'   => array(
                    'class' => 'form-control input-medium',
                ),
                'required'     => true,
                'bodyCode'     => "
                    <script type=\"text/javascript\">
                        function checkFormFields()
                        {
                            if ($('input:radio[name=\"registration_type\"]:checked').val() === 'full') {
                                $('.full-registration-field').closest('.form-group').show();
                            }
                            else {
                                $('input[name=\"min_reg_age\"]').val('');
                                $('.full-registration-field').closest('.form-group').hide();
                            }
                        }

                        $(document).ready(function() {
                            checkFormFields();
                        });

                        $(document).on('change', '.field-changeable', function() {
                            checkFormFields();
                        });

                    </script>"
            ),
            array(
                'form_id'      => 'themes_customizations',
                'subtitle'     => $this->_('Home Page Advert Carousel'),
                'id'           => 'enable_home_page_advert_carousel',
                'element'      => 'checkbox',
                'label'        => $this->_('Enable Home Page Advert Carousel'),
                'multiOptions' => array(
                    1 => null,
                ),
            ),
            array(
                'form_id'      => 'themes_customizations',
                'id'           => 'carousel_type',
                'element'      => 'radio',
                'label'        => $this->_('Carousel Width'),
                'value'        => 'standard',
                'multiOptions' => array(
                    'standard' => $translate->_('Standard'),
                    'fluid'    => array(
                        $translate->_('Fluid'),
                        $translate->_('Choose this option for a full width container.'),
                    ),
                ),
                'description'  => $this->_('Choose the home page slider width.'),
            ),
            array(
                'form_id'      => 'themes_customizations',
                'id'           => 'home_page_advert_carousel_autoplay',
                'element'      => 'checkbox',
                'label'        => $this->_('Carousel Autoplay'),
                'multiOptions' => array(
                    1 => null,
                ),
            ),
            array(
                'form_id'     => 'themes_customizations',
                'id'          => 'home_page_advert_carousel_speed',
                'element'     => 'text',
                'label'       => $this->_('Carousel Autoplay Speed'),
                'suffix'      => $this->_('ms'),
                'description' => $this->_('Enter the autoplay speed of the milliseconds (1000ms = 1s).'),
                'attributes'  => array(
                    'class' => 'form-control input-mini',
                ),
                'validators'  => array(
                    'Digits',
                ),
            ),
            /* HOME PAGE FEATURED */
            array(
                'form_id'     => 'themes_customizations',
                'subtitle'    => $this->_('Home Page Featured'),
                'id'          => 'hpfeat_nb',
                'element'     => 'text',
                'label'       => $this->_('Listings'),
                'description' => $this->_('Enter the maximum number of home page featured listings that will be displayed. Leave empty to disable.'),
                'required'    => false,
                'validators'  => array(
                    'Digits',
                    array('LessThan', array(24, true)),
                ),
                'attributes'  => array(
                    'class' => 'form-control input-mini',
                ),
            ),
            array(
                'form_id'      => 'themes_customizations',
                'id'           => 'hpfeat_tabbed',
                'element'      => 'checkbox',
                'label'        => $this->_('Tabbed Display'),
                'required'     => false,
                'multiOptions' => array(
                    1 => null,
                ),
            ),
            array(
                'form_id'      => 'themes_customizations',
                'id'           => 'hpfeat_carousel',
                'element'      => 'checkbox',
                'label'        => $this->_('Carousel'),
                'required'     => false,
                'multiOptions' => array(
                    1 => null,
                ),
            ),
            array(
                'form_id'      => 'themes_customizations',
                'id'           => 'hpfeat_box',
                'element'      => 'radio',
                'label'        => $this->_('Box Type'),
                'required'     => false,
                'multiOptions' => array(
                    'list' => 'List',
                    'grid' => 'Grid',
                ),
            ),
            array(
                'form_id'      => 'themes_customizations',
                'id'           => 'hpfeat_items_row_desktop',
                'element'      => 'select',
                'label'        => $this->_('Grid Items / Row [Desktop]'),
                'multiOptions' => $itemsRowDesktop,
                'attributes'   => array(
                    'class' => 'form-control input-mini',
                ),
            ),
            array(
                'form_id'      => 'themes_customizations',
                'id'           => 'hpfeat_items_row_phone',
                'element'      => 'select',
                'label'        => $this->_('Grid Items / Row [Phone]'),
                'required'     => false,
                'multiOptions' => $itemsRowPhone,
                'attributes'   => array(
                    'class' => 'form-control input-mini',
                ),
            ),

            /* STORES */
            array(
                'form_id'     => 'themes_customizations',
                'subtitle'    => $this->_('Stores'),
                'id'          => 'stores_nb',
                'element'     => 'text',
                'label'       => $this->_('Number of Stores'),
                'description' => $this->_('Enter the maximum number of featured stores that will be displayed. Leave empty to disable.'),
                'required'    => false,
                'validators'  => array(
                    'Digits',
                    array('LessThan', array(24, true)),
                ),
                'attributes'  => array(
                    'class' => 'form-control input-mini',
                ),
            ),
            array(
                'form_id'      => 'themes_customizations',
                'id'           => 'stores_featured_only',
                'element'      => 'checkbox',
                'label'        => $this->_('Display Featured only'),
                'required'     => false,
                'multiOptions' => array(
                    1 => null,
                ),
            ),
            array(
                'form_id'      => 'themes_customizations',
                'id'           => 'stores_tabbed',
                'element'      => 'checkbox',
                'label'        => $this->_('Tabbed Display'),
                'required'     => false,
                'multiOptions' => array(
                    1 => null,
                ),
            ),
            array(
                'form_id'      => 'themes_customizations',
                'id'           => 'stores_carousel',
                'element'      => 'checkbox',
                'label'        => $this->_('Carousel'),
                'required'     => false,
                'multiOptions' => array(
                    1 => null,
                ),
            ),
            array(
                'form_id'      => 'themes_customizations',
                'id'           => 'stores_box',
                'element'      => 'radio',
                'label'        => $this->_('Box Type'),
                'required'     => false,
                'multiOptions' => array(
                    'list' => 'List',
                    'grid' => 'Grid',
                ),
            ),
            array(
                'form_id'      => 'themes_customizations',
                'id'           => 'stores_items_row_desktop',
                'element'      => 'select',
                'label'        => $this->_('Grid Items / Row [Desktop]'),
                'multiOptions' => $itemsRowDesktop,
                'attributes'   => array(
                    'class' => 'form-control input-mini',
                ),
            ),
            array(
                'form_id'      => 'themes_customizations',
                'id'           => 'stores_items_row_phone',
                'element'      => 'select',
                'label'        => $this->_('Grid Items / Row [Phone]'),
                'required'     => false,
                'multiOptions' => $itemsRowPhone,
                'attributes'   => array(
                    'class' => 'form-control input-mini',
                ),
            ),

            /* RECENTLY LISTED */
            array(
                'form_id'    => 'themes_customizations',
                'subtitle'   => $this->_('Recently Listed'),
                'id'         => 'recent_nb',
                'element'    => 'text',
                'label'      => $this->_('Listings'),
                'required'   => false,
                'validators' => array(
                    'Digits',
                    array('LessThan', array(32, true)),
                ),
                'attributes' => array(
                    'class' => 'form-control input-mini',
                ),

            ),
            array(
                'form_id'      => 'themes_customizations',
                'id'           => 'recent_tabbed',
                'element'      => 'checkbox',
                'label'        => $this->_('Tabbed Display'),
                'required'     => false,
                'multiOptions' => array(
                    1 => null,
                ),
            ),
            array(
                'form_id'      => 'themes_customizations',
                'id'           => 'recent_carousel',
                'element'      => 'checkbox',
                'label'        => $this->_('Carousel'),
                'required'     => false,
                'multiOptions' => array(
                    1 => null,
                ),
            ),
            array(
                'form_id'      => 'themes_customizations',
                'id'           => 'recent_box',
                'element'      => 'radio',
                'label'        => $this->_('Box Type'),
                'required'     => false,
                'multiOptions' => array(
                    'list' => 'List',
                    'grid' => 'Grid',
                ),
            ),
            array(
                'form_id'      => 'themes_customizations',
                'id'           => 'recent_items_row_desktop',
                'element'      => 'select',
                'label'        => $this->_('Grid Items / Row [Desktop]'),
                'multiOptions' => $itemsRowDesktop,
                'attributes'   => array(
                    'class' => 'form-control input-mini',
                ),
            ),
            array(
                'form_id'      => 'themes_customizations',
                'id'           => 'recent_items_row_phone',
                'element'      => 'select',
                'label'        => $this->_('Grid Items / Row [Phone]'),
                'required'     => false,
                'multiOptions' => $itemsRowPhone,
                'attributes'   => array(
                    'class' => 'form-control input-mini',
                ),
            ),

            /* ENDING SOON */
            array(
                'form_id'    => 'themes_customizations',
                'subtitle'   => $this->_('Ending Soon'),
                'id'         => 'ending_nb',
                'element'    => 'text',
                'label'      => $this->_('Listings'),
                'required'   => false,
                'validators' => array(
                    'Digits',
                    array('LessThan', array(32, true)),
                ),
                'attributes' => array(
                    'class' => 'form-control input-mini',
                ),

            ),
            array(
                'form_id'      => 'themes_customizations',
                'id'           => 'ending_tabbed',
                'element'      => 'checkbox',
                'label'        => $this->_('Tabbed Display'),
                'required'     => false,
                'multiOptions' => array(
                    1 => null,
                ),
            ),
            array(
                'form_id'      => 'themes_customizations',
                'id'           => 'ending_carousel',
                'element'      => 'checkbox',
                'label'        => $this->_('Carousel'),
                'required'     => false,
                'multiOptions' => array(
                    1 => null,
                ),
            ),
            array(
                'form_id'      => 'themes_customizations',
                'id'           => 'ending_box',
                'element'      => 'radio',
                'label'        => $this->_('Box Type'),
                'required'     => false,
                'multiOptions' => array(
                    'list' => 'List',
                    'grid' => 'Grid',
                ),
            ),
            array(
                'form_id'      => 'themes_customizations',
                'id'           => 'ending_items_row_desktop',
                'element'      => 'select',
                'label'        => $this->_('Grid Items / Row [Desktop]'),
                'multiOptions' => $itemsRowDesktop,
                'attributes'   => array(
                    'class' => 'form-control input-mini',
                ),
            ),
            array(
                'form_id'      => 'themes_customizations',
                'id'           => 'ending_items_row_phone',
                'element'      => 'select',
                'label'        => $this->_('Grid Items / Row [Phone]'),
                'required'     => false,
                'multiOptions' => $itemsRowPhone,
                'attributes'   => array(
                    'class' => 'form-control input-mini',
                ),
            ),

            /* POPULAR */
            array(
                'form_id'    => 'themes_customizations',
                'subtitle'   => $this->_('Popular'),
                'id'         => 'popular_nb',
                'element'    => 'text',
                'label'      => $this->_('Listings'),
                'required'   => false,
                'validators' => array(
                    'Digits',
                    array('LessThan', array(32, true)),
                ),
                'attributes' => array(
                    'class' => 'form-control input-mini',
                ),

            ),
            array(
                'form_id'      => 'themes_customizations',
                'id'           => 'popular_tabbed',
                'element'      => 'checkbox',
                'label'        => $this->_('Tabbed Display'),
                'required'     => false,
                'multiOptions' => array(
                    1 => null,
                ),
            ),
            array(
                'form_id'      => 'themes_customizations',
                'id'           => 'popular_carousel',
                'element'      => 'checkbox',
                'label'        => $this->_('Carousel'),
                'required'     => false,
                'multiOptions' => array(
                    1 => null,
                ),
            ),
            array(
                'form_id'      => 'themes_customizations',
                'id'           => 'popular_box',
                'element'      => 'radio',
                'label'        => $this->_('Box Type'),
                'required'     => false,
                'multiOptions' => array(
                    'list' => 'List',
                    'grid' => 'Grid',
                ),
            ),
            array(
                'form_id'      => 'themes_customizations',
                'id'           => 'popular_items_row_desktop',
                'element'      => 'select',
                'label'        => $this->_('Grid Items / Row [Desktop]'),
                'multiOptions' => $itemsRowDesktop,
                'attributes'   => array(
                    'class' => 'form-control input-mini',
                ),
            ),
            array(
                'form_id'      => 'themes_customizations',
                'id'           => 'popular_items_row_phone',
                'element'      => 'select',
                'label'        => $this->_('Grid Items / Row [Phone]'),
                'required'     => false,
                'multiOptions' => $itemsRowPhone,
                'attributes'   => array(
                    'class' => 'form-control input-mini',
                ),
            ),

            array(
                'form_id'     => 'themes_customizations',
                'subtitle'    => $this->_('Home Page Custom HTML'),
                'id'          => 'home_page_html',
                'element'     => '\\Ppb\\Form\\Element\\Wysiwyg',
                'label'       => $this->_('Home Page Custom HTML'),
                'description' => $this->_('(Recommended for SEO) Add custom html to your home page. '
                    . 'You should add one <strong>h1</strong> tag that best describes your website and at least one <strong>h2</strong> tag with secondary descriptions.'),
                'attributes'  => array(
                    'rows'  => '12',
                    'class' => 'form-control',
                ),
            ),

            /* CATEGORY PAGES FEATURED */
            array(
                'form_id'     => 'themes_customizations',
                'subtitle'    => $this->_('Category Pages Featured'),
                'id'          => 'catfeat_nb',
                'element'     => 'text',
                'label'       => $this->_('Listings'),
                'description' => $this->_('Enter the maximum number of category page featured listings that will be displayed. Leave empty to disable.'),
                'required'    => false,
                'validators'  => array(
                    'Digits',
                    array('LessThan', array(12, true)),
                ),
                'attributes'  => array(
                    'class' => 'form-control input-mini',
                ),
            ),
            array(
                'form_id'      => 'themes_customizations',
                'id'           => 'catfeat_box',
                'element'      => 'radio',
                'label'        => $this->_('Box Type'),
                'required'     => false,
                'multiOptions' => array(
                    'list' => 'List',
                    'grid' => 'Grid',
                ),
            ),
            array(
                'form_id'      => 'themes_customizations',
                'id'           => 'catfeat_items_row_desktop',
                'element'      => 'select',
                'label'        => $this->_('Grid Items / Row [Desktop]'),
                'multiOptions' => $itemsRowDesktop,
                'attributes'   => array(
                    'class' => 'form-control input-mini',
                ),
            ),
            array(
                'form_id'      => 'themes_customizations',
                'id'           => 'catfeat_items_row_phone',
                'element'      => 'select',
                'label'        => $this->_('Grid Items / Row [Phone]'),
                'required'     => false,
                'multiOptions' => $itemsRowPhone,
                'attributes'   => array(
                    'class' => 'form-control input-mini',
                ),
            ),

            array(
                'form_id'      => 'themes_customizations',
                'subtitle'     => $this->_('Eight Theme Settings'),
                'id'           => 'eight_theme_color_theme',
                'element'      => 'select',
                'label'        => $this->_('Color Theme'),
                'description'  => $this->_('If using the Eight theme, you can select a color theme.'),
                'required'     => false,
                'multiOptions' => array(
                    'theme-blue'  => 'Blue',
                    'theme-red'   => 'Red',
                    'theme-green' => 'Green',
                    'theme-gray'  => 'Gray',
                ),
                'attributes'   => array(
                    'class' => 'form-control input-medium',
                ),
            ),
            array(
                'form_id'      => 'themes_customizations',
                'id'           => 'eight_theme_header_type',
                'element'      => 'select',
                'label'        => $this->_('Header Design'),
                'description'  => $this->_('If using the Eight theme, you can select a header design.'),
                'required'     => false,
                'multiOptions' => array(
                    'header.one'   => 'Type One',
                    'header.two'   => 'Type Two',
                    'header.three' => 'Type Three',
                ),
                'attributes'   => array(
                    'class' => 'form-control input-medium',
                ),
            ),
            array(
                'form_id'      => 'themes_customizations',
                'id'           => 'eight_theme_footer_type',
                'element'      => 'select',
                'label'        => $this->_('Footer Design'),
                'description'  => $this->_('If using the Eight theme, you can select a footer design.'),
                'required'     => false,
                'multiOptions' => array(
                    'footer.one' => 'Type One',
                    'footer.two' => 'Type Two',
                ),
                'attributes'   => array(
                    'class' => 'form-control input-medium',
                ),
            ),
            /**
             * --------------
             * USER SETTINGS
             * --------------
             */
            /**
             * ++++++++++++++
             * REGISTRATION & VERIFICATION
             * ++++++++++++++
             */
            array(
                'form_id'      => 'registration_verification',
                'subtitle'     => $this->_('User Registration'),
                'id'           => 'registration_type',
                'element'      => 'radio',
                'label'        => $this->_('Select Registration Type'),
                'multiOptions' => array(
                    'quick' => array(
                        $translate->_('Quick'),
                        $translate->_('Only the username, email address and password fields will appear on the registration page.'),
                    ),
                    'full'  => array(
                        $translate->_('Full'),
                        $translate->_('This form will include all registration fields, address, date of birth, phone number and any available custom fields.'),
                    ),
                ),
                'attributes'   => array(
                    'class' => 'field-changeable',
                ),
                'bodyCode'     => "
                    <script type=\"text/javascript\">
                        function checkFormFields()
                        {
                            if ($('input:radio[name=\"registration_type\"]:checked').val() === 'full') {
                                $('.full-registration-field').closest('.form-group').show();
                            }
                            else {
                                $('input[name=\"min_reg_age\"]').val('');
                                $('.full-registration-field').closest('.form-group').hide();
                            }
                        }

                        $(document).ready(function() {
                            checkFormFields();
                        });

                        $(document).on('change', '.field-changeable', function() {
                            checkFormFields();
                        });

                    </script>"
            ),
            array(
                'form_id'     => 'registration_verification',
                'id'          => 'min_reg_age',
                'element'     => 'text',
                'label'       => $this->_('Minimum Registration Age'),
                'suffix'      => $this->_('years'),
                'description' => $this->_('Enter the minimum age required for users to be able to register to your site, or leave empty to disable this functionality.'),
                'attributes'  => array(
                    'class' => 'form-control input-mini full-registration-field',
                ),
                'validators'  => array(
                    'Digits',
                ),
            ),
            array(
                'form_id'      => 'registration_verification',
                'id'           => 'payment_methods_registration',
                'element'      => 'checkbox',
                'label'        => $this->_('Direct Payment Gateways Fields'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check above if you want to display, on the registration page, the setup fields for the enabled payment gateways.'),
            ),
            /**
             * ++++++++++++++
             * USER VERIFICATION - UNIFIED SELLER/BUYER VERIFICATION
             * ++++++++++++++
             */
            array(
                'form_id'      => 'registration_verification',
                'subtitle'     => $this->_('User Verification'),
                'id'           => 'user_verification',
                'element'      => 'checkbox',
                'label'        => $this->_('Enable User Verification'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check above to enable the feature.<br>'
                    . '<strong>Note</strong>: Even if you disable User Verification, you will still be able to '
                    . 'set the status of your users to Verified from the Users Management page. '
                    . 'However, users won\'t be able to verify their accounts themselves.'),
                'attributes'   => array(
                    'class' => 'field-changeable',
                ),
                'bodyCode'     => "
                    <script type=\"text/javascript\">
                        function checkVerificationFields()
                        {
                            if ($('input:checkbox[name=\"user_verification\"]').is(':checked')) {
                                $('[name=\"seller_verification_mandatory\"]').closest('.form-group').show();
                                $('[name=\"buyer_verification_mandatory\"]').closest('.form-group').show();
                                $('[name=\"user_verification_refund\"]').closest('.form-group').show();
                                $('input:text[name=\"user_verification_fee\"]').closest('.form-group').show();
                                $('input:text[name=\"user_verification_recurring\"]').closest('.form-group').show();
                            }
                            else {
                                $('[name=\"seller_verification_mandatory\"]').prop('checked', false).closest('.form-group').hide();
                                $('[name=\"buyer_verification_mandatory\"]').prop('checked', false).closest('.form-group').hide();
                                $('[name=\"user_verification_refund\"]').prop('checked', false).closest('.form-group').hide();
                                $('input:text[name=\"user_verification_fee\"]').val('').closest('.form-group').hide();
                                $('input:text[name=\"user_verification_recurring\"]').val('').closest('.form-group').hide();
                            }
                        }

                        $(document).ready(function() {
                            checkVerificationFields();
                        });

                        $(document).on('change', '.field-changeable', function() {
                            checkVerificationFields();
                        });

                    </script>"
            ),
            array(
                'form_id'      => 'registration_verification',
                'id'           => 'seller_verification_mandatory',
                'element'      => 'checkbox',
                'label'        => $this->_('Mandatory Seller Verification'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('When enabled, sellers will have to get verified in order to list items.'),
            ),
            array(
                'form_id'      => 'registration_verification',
                'id'           => 'buyer_verification_mandatory',
                'element'      => 'checkbox',
                'label'        => $this->_('Mandatory Buyer Verification'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('When enabled, buyers will have to get verified for bidding and/or purchasing items.'),
            ),
            array(
                'form_id'    => 'registration_verification',
                'table'      => 'fees',
                'id'         => 'user_verification_fee',
                'element'    => '\\Ppb\\Form\\Element\\LocalizedNumeric',
                'label'      => $this->_('Verification Fee'),
                'prefix'     => $settings['currency'],
                'attributes' => array(
                    'class' => 'form-control input-mini',
                ),
                'required'   => ($this->getData('user_verification')) ? true : false,
                'validators' => array(
                    'Numeric',
                    array('GreaterThan', array(0, true)),
                ),
                'filters'    => array(
                    '\\Ppb\\Filter\\LocalizedNumeric',
                ),
            ),
            array(
                'form_id'     => 'registration_verification',
                'id'          => 'user_verification_recurring',
                'element'     => 'text',
                'prefix'      => $this->_('recurring every'),
                'suffix'      => $this->_('days'),
                'description' => $this->_('You can set up a one-time or a recurring verification fee. For a one-time verification fee, enter 0 in the recurring field.'),
                'attributes'  => array(
                    'class' => 'form-control input-mini',
                ),
                'validators'  => array(
                    'Digits',
                ),
            ),
            array(
                'form_id'      => 'registration_verification',
                'id'           => 'user_verification_refund',
                'element'      => 'checkbox',
                'label'        => $this->_('Refund Verification Fee'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check above for the verification fee to be credited to the user\'s account after payment. '
                    . 'The user\'s account must be set to Account Mode for the feature to apply.'),
            ),

            /**
             * ++++++++++++++
             * REGISTRATION TERMS & CONDITIONS LINK
             * ++++++++++++++
             */
            array(
                'form_id'      => 'registration_verification',
                'subtitle'     => $this->_('Terms and Conditions / Privacy Policy Link'),
                'id'           => 'enable_registration_terms',
                'element'      => 'checkbox',
                'label'        => $this->_('Show Registration Terms & Conditions Link'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check above to require users to agree to the site\'s terms and/or privacy policy when registering.'),
            ),

            array(
                'form_id'     => 'registration_verification',
                'id'          => 'registration_terms_link',
                'element'     => 'text',
                'label'       => $this->_('Terms and Conditions Link'),
                'description' => $this->_('Enter the url of the terms and conditions page (relative url).'),
                'attributes'  => array(
                    'class' => 'form-control input-large',
                ),
                'required'    => $this->getData('enable_registration_terms') ? true : false,
                'validators'  => array(
                    'NoHtml',
                ),
            ),
            array(
                'form_id'     => 'registration_verification',
                'id'          => 'registration_privacy_link',
                'element'     => 'text',
                'label'       => $this->_('Privacy Policy Link'),
                'description' => $this->_('Enter the url of the privacy policy page (relative url).'),
                'attributes'  => array(
                    'class' => 'form-control input-large',
                ),
                'required'    => $this->getData('enable_registration_terms') ? true : false,
                'validators'  => array(
                    'NoHtml',
                ),
            ),
            /**
             * ++++++++++++++
             * PASSWORD STRENGTH CONFIGURATION
             * ++++++++++++++
             */
            array(
                'form_id'     => 'password_strength',
                'id'          => 'password_min_length',
                'element'     => 'text',
                'label'       => $this->_('Minimum Length'),
                'description' => $this->_('Enter the minimum number of characters required for creating a new password.'),
                'attributes'  => array(
                    'class' => 'form-control input-small',
                ),
                'required'    => true,
                'validators'  => array(
                    'NoHtml',
                    array('GreaterThan', array(4, true))
                ),
            ),
            array(
                'form_id'      => 'password_strength',
                'id'           => 'password_strength_settings',
                'element'      => 'checkbox',
                'label'        => $this->_('Increase Password Complexity'),
                'multiOptions' => array(
                    'uppercase' => $this->_('Require at least one uppercase letter'),
                    'digit'     => $this->_('Require at least one digit'),
                    'special'   => $this->_('Require at least one special character'),
                ),
                'description'  => $this->_('Check above to require additional security layers for new passwords.'),
            ),
            /**
             * ++++++++++++++
             * USER ACCOUNT SETTINGS
             * ++++++++++++++
             */
            array(
                'form_id'      => 'account_settings',
                'id'           => 'payment_mode',
                'element'      => 'radio',
                'label'        => $this->_('Choose Payment Option'),
                'multiOptions' => array(
                    'live'    => array(
                        $translate->_('Live (Pay as You Go)'),
                        $translate->_('Users have to pay site fees immediately.'),
                    ),
                    'account' => array(
                        $translate->_('Account Mode'),
                        $translate->_('Users must pay site fees periodically. All owed fees will be added to their account balance.'),
                    ),
                ),
                'attributes'   => array(
                    'class' => 'field-changeable',
                ),
                'bodyCode'     => "
                    <script type=\"text/javascript\">
                        function checkAccountSettingsFormFields()
                        {
                            if ($('input:radio[name=\"payment_mode\"]:checked').val() === 'live' &&
                                $('input:radio[name=\"user_account_type\"]:checked').val() === 'global') {

                                $('.account-mode-field').closest('.form-group').hide();
                            }
                            else {
                                $('input[name=\"min_reg_age\"]').val('');
                                $('.account-mode-field').closest('.form-group').show();
                            }
                        }

                        $(document).ready(function() {
                            checkAccountSettingsFormFields();
                        });

                        $(document).on('change', '.field-changeable', function() {
                            checkAccountSettingsFormFields();
                        });

                    </script>"
            ),
            array(
                'form_id'      => 'account_settings',
                'id'           => 'user_account_type',
                'element'      => 'radio',
                'label'        => $this->_('User Account Type'),
                'multiOptions' => array(
                    'global'   => array(
                        $translate->_('Global'),
                        $translate->_('All accounts will run using the default payment option.'),
                    ),
                    'personal' => array(
                        $translate->_('Personal'),
                        $translate->_('You\'ll be able to select, from the Users Management page, the payment option for each account.'),
                    ),
                ),
                'attributes'   => array(
                    'class' => 'field-changeable',
                ),
            ),
            array(
                'form_id'    => 'account_settings',
                'subtitle'   => $this->_('Account Mode Settings'),
                'id'         => 'signup_credit',
                'element'    => '\\Ppb\\Form\\Element\\LocalizedNumeric',
                'label'      => $this->_('Sign-up Credit'),
                'prefix'     => $settings['currency'],
                'attributes' => array(
                    'class' => 'form-control input-mini account-mode-field',
                ),
                'validators' => array(
                    'Numeric',
                ),
                'filters'    => array(
                    '\\Ppb\\Filter\\LocalizedNumeric',
                ),
            ),
            array(
                'form_id'     => 'account_settings',
                'id'          => 'maximum_debit',
                'element'     => '\\Ppb\\Form\\Element\\LocalizedNumeric',
                'label'       => $this->_('Maximum Debit'),
                'prefix'      => $settings['currency'],
                'description' => $this->_('Enter the maximum debit an account is allowed to have before being suspended.'),
                'attributes'  => array(
                    'class' => 'form-control input-mini account-mode-field',
                ),
                'validators'  => array(
                    'Numeric',
                ),
                'filters'     => array(
                    '\\Ppb\\Filter\\LocalizedNumeric',
                ),
            ),
            array(
                'form_id'     => 'account_settings',
                'id'          => 'min_invoice_value',
                'element'     => '\\Ppb\\Form\\Element\\LocalizedNumeric',
                'label'       => $this->_('Minimum Credit Amount'),
                'prefix'      => $settings['currency'],
                'attributes'  => array(
                    'class' => 'form-control input-mini account-mode-field',
                ),
                'description' => $this->_('Enter the minimum payment amount that a user can credit his account balance with.'),
                'validators'  => array(
                    'Numeric',
                ),
                'filters'     => array(
                    '\\Ppb\\Filter\\LocalizedNumeric',
                ),
            ),
            array(
                'form_id'      => 'account_settings',
                'id'           => 'payment_reminder_email',
                'element'      => 'checkbox',
                'label'        => $this->_('Payment Notification Emails'),
                'multiOptions' => array(
                    1 => null,
                ),
                'attributes'   => array(
                    'class' => 'account-mode-field',
                ),
                'description'  => $this->_('Check above to send automatic email notifications to accounts that have exceeded their debit limit.'),
            ),
            array(
                'form_id'      => 'account_settings',
                'id'           => 'suspend_over_limit_accounts',
                'element'      => 'checkbox',
                'label'        => $this->_('Suspend Accounts over Limit'),
                'multiOptions' => array(
                    1 => null,
                ),
                'attributes'   => array(
                    'class' => 'account-mode-field',
                ),
                'description'  => $this->_('Check above to suspend accounts with the balance over the maximum debit limit.'),
            ),
            array(
                'form_id'     => 'account_settings',
                'id'          => 'suspension_days',
                'element'     => 'text',
                'label'       => $this->_('Cron Invoice Suspension'),
                'suffix'      => $this->_('days'),
                'attributes'  => array(
                    'class' => 'form-control input-mini account-mode-field',
                ),
                'description' => $this->_('(Optional) Enter the number of days after which an account that has been sent an automatic payment notification email will be suspended. '
                    . 'This setting will only apply if you have selected not to suspend accounts that have exceeded their debit limit.'),
                'validators'  => array(
                    'Digits',
                ),
            ),
            array(
                'form_id'      => 'account_settings',
                'id'           => 'rebill_expired_subscriptions',
                'element'      => 'checkbox',
                'label'        => $this->_('Re-bill Expired Subscriptions'),
                'multiOptions' => array(
                    1 => null,
                ),
                'attributes'   => array(
                    'class' => 'account-mode-field',
                ),
                'description'  => $this->_('Check above to automatically bill expired subscriptions from the users\' account balances (ONLY in Account Mode).'),
            ),
            /**
             * ++++++++++++++
             * USER SIGNUP CONFIRMATION
             * ++++++++++++++
             */
            array(
                'form_id'      => 'signup_settings',
                'id'           => 'signup_settings',
                'element'      => 'radio',
                'label'        => $this->_('User Sign-up Confirmation'),
                'multiOptions' => array(
                    0 => array(
                        $translate->_('No Confirmation Required'),
                        $translate->_('Check above for accounts to be activated immediately.'),
                    ),
                    1 => array(
                        $translate->_('Email Address Confirmation'),
                        $translate->_('Check above to enable Email Address Confirmation. If enabled, users will have to '
                            . 'click the link received in the registration confirmation email in order to activate their accounts.'),
                    ),
                    2 => array(
                        $translate->_('Admin Approval'),
                        $translate->_('Check above if you want the admin to manually activate each new user from the Users Management page.<br>'
                            . 'Users will be required to confirm their email address (See Email Address Confirmation).'),
                    ),
                ),
                'attributes'   => array(
                    'class' => 'field-changeable',
                ),
                'bodyCode'     => "
                    <script type=\"text/javascript\">
                        function checkSignupFields()
                        {
                            if ($('input:radio[name=\"signup_settings\"]:checked').val() > 0) {
                                $('[name=\"email_address_change_confirmation\"]').closest('.form-group').show();
                            }
                            else {
                                $('[name=\"email_address_change_confirmation\"]').prop('checked', false).closest('.form-group').hide();
                            }
                        }

                        $(document).ready(function() {
                            checkSignupFields();
                        });

                        $(document).on('change', '.field-changeable', function() {
                            checkSignupFields();
                        });

                    </script>"
            ),
            array(
                'form_id'      => 'signup_settings',
                'id'           => 'email_address_change_confirmation',
                'element'      => 'checkbox',
                'label'        => $this->_('Email Address Change Confirmation'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check above to require registered users to verify their email address if changing it.'),
            ),
            /**
             * ++++++++++++++
             * ENABLE PRIVATE REPUTATION COMMENTS
             * ++++++++++++++
             */
            array(
                'form_id'      => 'private_reputation_comments',
                'id'           => 'private_reputation',
                'element'      => 'checkbox',
                'label'        => $this->_('Enable Private Reputation Comments'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('When enabled, reputation comments will become private/not available for viewing. Moreover, only the reputation score will be public.'),
            ),
            /**
             * DISABLE REPUTATION
             */
            array(
                'form_id'      => 'users_reputation',
                'id'           => 'enable_reputation',
                'element'      => 'checkbox',
                'label'        => $this->_('Enable Reputation'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check above to enable the module.'),
            ),
            array(
                'form_id'      => 'users_reputation',
                'id'           => 'auto_feedback',
                'element'      => 'checkbox',
                'subtitle'     => $this->_('Auto Feedback'),
                'label'        => $this->_('Enable Auto Feedback'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check above to enable the auto feedback function.'),
            ),
            array(
                'form_id'     => 'users_reputation',
                'id'          => 'auto_feedback_days',
                'element'     => 'text',
                'label'       => $this->_('Post after'),
                'description' => $this->_('Enter the number of days after which the auto feedback is posted.'),
                'suffix'      => $this->_('days'),
                'attributes'  => array(
                    'class' => 'form-control input-mini',
                ),
                'required'    => ($this->getData('auto_feedback')) ? true : false,
                'validators'  => array(
                    'Digits',
                ),
            ),
            array(
                'form_id'      => 'users_reputation',
                'id'           => 'auto_feedback_score',
                'element'      => 'radio',
                'label'        => $this->_('Score'),
                'description'  => $this->_('Enter the score that is posted by the auto feedback plugin.'),
                'required'     => ($this->getData('auto_feedback')) ? true : false,
                'multiOptions' => ReputationService::$scores,
            ),
            array(
                'form_id'     => 'users_reputation',
                'id'          => 'auto_feedback_comments',
                'element'     => 'text',
                'label'       => $this->_('Feedback Comment'),
                'description' => $this->_('Enter the auto feedback comment to be added.'),
                'attributes'  => array(
                    'class' => 'form-control input-large',
                ),
                'required'    => ($this->getData('auto_feedback')) ? true : false,
                'validators'  => array(
                    'NoHtml',
                ),
            ),
            /**
             * ++++++++++++++
             * TIME AND DATE SETTINGS
             * ++++++++++++++
             */
            array(
                'form_id'      => 'time_date',
                'id'           => 'timezone',
                'element'      => 'select',
                'label'        => $this->_('Time Zone'),
                'multiOptions' => $this->getTimezones()->getMultiOptions(),
                'description'  => $this->_('Select your site\'s time zone.'),
                'attributes'   => array(
                    'class' => 'form-control input-medium',
                ),
                'required'     => true,
            ),
            array(
                'form_id'      => 'time_date',
                'id'           => 'date_format',
                'element'      => 'radio',
                'label'        => $this->_('Date Format'),
                'multiOptions' => array(
                    '%m/%d/%Y %H:%M:%S' => array(
                        'mm/dd/yyyy h:m:s',
                        $translate->_('Example:') . ' ' . $this->getView()->date(time(), false, '%m/%d/%Y %H:%M:%S'),
                    ),
                    '%d.%m.%Y %H:%M:%S' => array(
                        'dd.mm.yyyy h:m:s',
                        $translate->_('Example:') . ' ' . $this->getView()->date(time(), false, '%d.%m.%Y %H:%M:%S'),
                    ),
                ),
                'description'  => $this->_('Select a format for displaying dates and date/time combinations on your website.'),
                'required'     => true,
            ),
            /**
             * ++++++++++++++
             * LANGUAGES
             * ++++++++++++++
             */
            array(
                'form_id'      => 'user_languages',
                'id'           => 'site_lang',
                'element'      => 'select',
                'label'        => $this->_('Site Language'),
                'multiOptions' => \Ppb\Utility::getLanguages(),
                'description'  => $this->_('Select a default language for your website.'),
                'attributes'   => array(
                    'class' => 'form-control input-medium',
                ),
                'required'     => true,
            ),
            array(
                'form_id'      => 'user_languages',
                'id'           => 'user_languages',
                'element'      => 'checkbox',
                'label'        => $this->_('Multi Language Support'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => sprintf(
                    $this->_('When enabled, visitors will be able to select the displaying language of the site.<br>'
                        . 'The module applies only if your site is available in multiple languages (not available by default).<br>'
                        . 'To help you, we\'ve put together a free <a href="%s" target="_blank">Translations Pack</a>.<br>'
                        . 'To manage translation files, <a href="%s">click here</a>.'),
                    'https://www.phpprobid.com/features/translations-multilanguage',
                    $this->getView()->url(array('module' => 'admin', 'controller' => 'site-content', 'action' => 'translations'))),
            ),
            /**
             * ++++++++++++++
             * SEO SETTINGS
             * ++++++++++++++
             */
            array(
                'form_id'     => 'seo_settings',
                'id'          => 'meta_title',
                'element'     => 'text',
                'label'       => $this->_('Meta Title'),
                'description' => $this->_('(Highly Recommended) Add a meta title for your home page.'),
                'attributes'  => array(
                    'class' => 'form-control input-xlarge',
                ),
                'validators'  => array(
                    'NoHtml',
                ),
            ),
            array(
                'form_id'     => 'seo_settings',
                'id'          => 'meta_description',
                'element'     => 'textarea',
                'label'       => $this->_('Meta Description'),
                'description' => $this->_('(Highly Recommended) The meta description summarizes the content of your website for search engines. '
                    . 'For optimal indexing, keep your description between 200 and 300 characters.'),
                'validators'  => array(
                    'NoHtml',
                ),
                'attributes'  => array(
                    'rows'  => '4',
                    'class' => 'form-control',
                ),
            ),
            array(
                'form_id'     => 'seo_settings',
                'id'          => 'meta_data',
                'element'     => '\\Ppb\\Form\\Element\\Composite',
                'label'       => $this->_('Other Tags'),
                'description' => $this->_('(Optional) Enter any additional meta tags that you might want to add to your site.<br>'
                    . 'Format: name (keywords, robots, etc) - content (string)'),
                'elements'    => array(
                    array(
                        'id'         => 'key',
                        'element'    => 'text',
                        'attributes' => array(
                            'class'       => 'form-control input-small mr-1',
                            'placeholder' => $translate->_('Name'),
                        ),
                    ),
                    array(
                        'id'         => 'value',
                        'element'    => 'text',
                        'attributes' => array(
                            'class'       => 'form-control input-large',
                            'placeholder' => $translate->_('Content'),
                        ),
                    ),
                ),
                'arrange'     => true,
            ),
            array(
                'form_id'      => 'seo_settings',
                'id'           => 'mod_rewrite_urls',
                'element'      => 'checkbox',
                'label'        => $this->_('Enable Search Engine Friendly URLs'),
                'description'  => sprintf(
                    $this->_('The mod_rewrite apache extension should be loaded in order for search engine friendly URLs to work. '
                        . 'There are alternatives to this extension if running '
                        . '<a target="_blank" href="http://blog.martinfjordvald.com/2011/02/nginx-primer-2-from-apache-to-nginx/">ngnix</a> or '
                        . '<a target="_blank" href="http://www.micronovae.com/ModRewrite/ModRewrite.html">Microsoft IIS</a>.<br>'
                        . '<em>mod_rewrite extension status:</em> %s'),
                    ((\Ppb\Utility::checkModRewrite()) ?
                        '<span class="badge badge-success">' . $this->_('Enabled') . '</span>' :
                        '<span class="badge badge-warning">' . $this->_('Disabled / Check Failed') . '</span>')),
                'multiOptions' => array(
                    1 => null
                ),
            ),

//            array(
//                'form_id' => 'seo_settings',
//                'id' => 'enable_sitemap',
//                'element' => 'checkbox',
//                'label'        => $this->_('Enable XML Sitemap'),
//                'multiOptions' => array(
//                    1 => null,
//                ),
//                'description'  => $this->_('If this setting is enabled, visitors browsing your site will be able to select a language in which the site will be displayed.<br>'
//                        . 'This setting will only apply if your site is available in multiple languages (not available by default).'),
//            ),
            /**
             * ++++++++++++++
             * CRON JOBS
             * ++++++++++++++
             */
            array(
                'form_id'      => 'cron_jobs',
                'id'           => 'cron_job_type',
                'element'      => 'radio',
                'label'        => $this->_('Cron Jobs Setup'),
                'multiOptions' => array(
                    'server'      => array(
                        $translate->_('Run cron jobs from your server\'s control panel'),
                        sprintf($translate->_('Please add ONE of the following lines and set it to run every minute:<br>'
                            . '<code>curl -s %1$s/cron.php</code>' . ' or<br>'
                            . '<code>wget -q %1$s/cron.php</code>' . '<br><br>'
                            . 'Purge unused images - run once per hour:<br>'
                            . '<code>curl -s %1$s/cron.php?command=purge-unused-uploaded-files 2>&1</code> or' . '<br>'
                            . '<code>wget -q %1$s/cron.php?command=purge-unused-uploaded-files</code>' . '<br><br>'
                            . 'Purge cache data - run daily:<br>'
                            . '<code>curl -s %1$s/cron.php?command=purge-cache-data 2>&1</code> or' . '<br>'
                            . '<code>wget -q %1$s/cron.php?command=purge-cache-data</code>' . '<br><br>'
                            . 'Update currency exchange rates - run daily:<br>'
                            . '<code>curl -s %1$s/cron.php?command=update-currency-exchange-rates 2>&1</code> or' . '<br>'
                            . '<code>wget -q %1$s/cron.php?command=update-currency-exchange-rates</code>'
                        ), $settings['site_path']),
                    ),
                    'application' => array(
                        $translate->_('Run cron jobs from within the application'),
                        $translate->_('Cron jobs will be run automatically each time the site is accessed. Use only if you dont '
                            . 'have access to the cron tab application on your server.'),
                    )
                ),
            ),
            /**
             * ++++++++++++++
             * CACHING ENGINE
             * ++++++++++++++
             */
            array(
                'form_id'      => 'caching',
                'id'           => 'caching_engine',
                'element'      => 'radio',
                'label'        => 'Caching Engine',
                'multiOptions' => $this->_cachingEngineMultiOptions(),
            ),
            array(
                'form_id'     => 'caching',
                'id'          => 'clear_cache',
                'element'     => 'button',
                'value'       => $this->_('Clear Cache'),
                'description' => $translate->_('Click above to clear your site\'s cache.')
                    . '<div id="clear-cache-progress" class="text-success"></div>',
                'attributes'  => array(
                    'class'                => 'btn btn-secondary btn-clear-cache',
                    'data-post-url'        => $this->getView()->url(array('module' => 'admin', 'controller' => 'index', 'action' => 'clear-cache')),
                    'data-progress-bar-id' => 'clear-cache-progress',
                ),
            ),
            array(
                'form_id'     => 'caching',
                'subtitle'    => $this->_('Cached Images'),
                'id'          => 'delete_cached_images',
                'element'     => 'button',
                'value'       => $this->_('Delete Cached Images'),
                'description' => $translate->_('Click above to delete all cached images from your site.')
                    . '<div id="delete-cached-images-progress" class="text-success"></div>',
                'attributes'  => array(
                    'class'                => 'btn btn-secondary btn-clear-cache',
                    'data-post-url'        => $this->getView()->url(array('module' => 'admin', 'controller' => 'index', 'action' => 'delete-cached-images')),
                    'data-progress-bar-id' => 'delete-cached-images-progress',
                ),
            ),
            /**
             * ++++++++++++++
             * MAINTENANCE MODE
             * ++++++++++++++
             */
            array(
                'form_id'      => 'maintenance_mode',
                'id'           => 'maintenance_mode',
                'element'      => 'checkbox',
                'label'        => $this->_('Maintenance Mode'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check above to enable maintenance mode. When enabled, '
                    . 'only logged-in administrators will be able to access the front end of the site.'),
            ),
            array(
                'form_id'     => 'maintenance_mode',
                'id'          => 'maintenance_mode_content',
                'element'     => '\\Ppb\\Form\\Element\\Wysiwyg',
                'label'       => $this->_('Custom Content'),
                'description' => $this->_('Add a custom html content for maintenance mode page, or leave empty for the default content to be displayed.'),
                'attributes'  => array(
                    'rows'  => '12',
                    'class' => 'form-control',
                ),
            ),
            /**
             * ++++++++++++++
             * PRIVATE SITE/SINGLE SELLER
             * ++++++++++++++
             */
            array(
                'form_id'      => 'private_site',
                'id'           => 'private_site',
                'element'      => 'checkbox',
                'label'        => $this->_('Private Site/Single Seller'),
                'multiOptions' => array(
                    1 => null
                ),
                'description'  => $this->_('When enabled, you\'ll be able to select the users that are allowed to list on your site. '
                    . 'Choose users with selling privileges from the Users Management page.'),
            ),
            array(
                'form_id'      => 'private_site',
                'id'           => 'private_site_request_seller_privileges',
                'element'      => 'checkbox',
                'label'        => $this->_('Allow Users to Request Selling Privileges'),
                'multiOptions' => array(
                    1 => null
                ),
                'description'  => $this->_('Check above to allow users to request selling privileges from the Members Area.<br>'
                    . 'When requested, an email notification will be sent to the admin. '
                    . 'Another email notification will be sent to the user after the admin has accepted or declined the request.'),
            ),
            /**
             * ++++++++++++++
             * PREFERRED SELLERS FEATURE
             * ++++++++++++++
             */
            array(
                'form_id'      => 'preferred_sellers',
                'id'           => 'preferred_sellers',
                'element'      => 'checkbox',
                'label'        => $this->_('Preferred Sellers'),
                'multiOptions' => array(
                    1 => null
                ),
                'description'  => $this->_('When enabled, you\'ll be able to give listings/sale fee reductions to selected users.'),
            ),
            array(
                'form_id'     => 'preferred_sellers',
                'id'          => 'preferred_sellers_expiration',
                'element'     => 'text',
                'label'       => $this->_('Expires after'),
                'description' => $this->_('(Optional) Enter the number of days after which the preferred seller status will expire.'),
                'suffix'      => $this->_('days'),
                'attributes'  => array(
                    'class' => 'form-control input-mini',
                ),
                'validators'  => array(
                    'Digits',
                ),
            ),
            array(
                'form_id'     => 'preferred_sellers',
                'id'          => 'preferred_sellers_reduction',
                'element'     => '\\Ppb\\Form\\Element\\LocalizedNumeric',
                'label'       => $this->_('Reduction'),
                'description' => $this->_('Enter the reduction percentage that will be applied.'),
                'suffix'      => '%',
                'attributes'  => array(
                    'class' => 'form-control input-mini',
                ),
                'validators'  => array(
                    'Numeric',
                ),
                'filters'     => array(
                    '\\Ppb\\Filter\\LocalizedNumeric',
                ),
            ),
            array(
                'form_id'      => 'preferred_sellers',
                'id'           => 'preferred_sellers_apply_sale',
                'element'      => 'checkbox',
                'label'        => $this->_('Apply To Sale Fees'),
                'multiOptions' => array(
                    1 => null
                ),
                'description'  => $this->_('Check above if you want to apply the preferred seller reduction to sale fees.'),
            ),
            /**
             * SITE INVOICES SETTINGS [ Header - Footer ]
             */
            array(
                'form_id'     => 'site_invoices',
                'id'          => 'invoice_logo_path',
                'element'     => '\\Ppb\\Form\\Element\\MultiUpload',
                'label'       => $this->_('Invoice Logo'),
                'description' => $this->_('Upload a custom logo that will appear on site invoices, or leave empty if you wish for the site logo to be displayed.'),
                'customData'  => array(
                    'buttonText'      => $translate->_('Select Logo'),
                    'acceptFileTypes' => '/(\.|\/)(gif|jpe?g|png)$/i',
                    'formData'        => array(
                        'fileSizeLimit' => 10000000, // approx 10MB
                        'uploadLimit'   => 1,
                    ),
                ),
            ),
            array(
                'form_id'     => 'site_invoices',
                'id'          => 'invoice_address',
                'element'     => 'textarea',
                'label'       => $this->_('Invoice Address'),
                'description' => $this->_('Enter the address that will appear on site invoices.'),
                'attributes'  => array(
                    'rows'  => '8',
                    'class' => 'form-control textarea-code',
                ),
                'validators'  => array(
                    'NoHtml',
                ),
            ),
            array(
                'form_id'     => 'site_invoices',
                'id'          => 'invoice_header',
                'element'     => '\\Ppb\\Form\\Element\\Wysiwyg',
                'label'       => $this->_('Invoice Header'),
                'description' => $this->_('Add a custom html header for site invoices.'),
                'attributes'  => array(
                    'rows'  => '6',
                    'class' => 'form-control',
                ),
                'customData'  => array(
                    'formData' => array(
                        'btns' => "[['strong', 'em'],['justifyLeft', 'justifyCenter', 'justifyRight', 'justifyFull'],['unorderedList', 'orderedList'],['horizontalRule']]",
                    )
                )
            ),
            array(
                'form_id'     => 'site_invoices',
                'id'          => 'invoice_footer',
                'element'     => '\\Ppb\\Form\\Element\\Wysiwyg',
                'label'       => $this->_('Invoice Footer'),
                'description' => $this->_('Add a custom html footer for site invoices.'),
                'attributes'  => array(
                    'rows'  => '6',
                    'class' => 'form-control',
                ),
                'customData'  => array(
                    'formData' => array(
                        'btns' => "[['strong', 'em'],['justifyLeft', 'justifyCenter', 'justifyRight', 'justifyFull'],['unorderedList', 'orderedList'],['horizontalRule']]",
                    )
                )
            ),
            /**
             * ++++++++++++++
             * ADDRESS DISPLAY FORMAT
             * ++++++++++++++
             */
            array(
                'form_id'      => 'address_display_format',
                'id'           => 'address_display_format',
                'element'      => 'radio',
                'label'        => $this->_('Address Display Format'),
                'multiOptions' => array(
                    'default'   => array(
                        $translate->_('Default'),
                        $translate->_('Address, City, Post/Zip Code, County/State, Country'),
                    ),
                    'alternate' => array(
                        $translate->_('Alternate'),
                        $translate->_('Address, City, County/State, Post/Zip Code, Country'),
                    ),
                ),
                'description'  => $this->_('Choose how addresses should be displayed on the website (on sales, invoices, etc.).'),
            ),
            /**
             * ++++++++++++++
             * GOOGLE ANALYTICS
             * ++++++++++++++
             */
            array(
                'form_id'     => 'google_analytics',
                'id'          => 'google_analytics_code',
                'element'     => 'textarea',
                'label'       => $this->_('Google Analytics Code'),
                'description' => $this->_('If you have a Google Analytics account, add the tracking code in the field above.'),
                'attributes'  => array(
                    'rows'  => '16',
                    'class' => 'form-control textarea-code',
                ),
            ),
            /**
             * ++++++++++++++
             * ALLOW BUYER TO COMBINE PURCHASES
             * ++++++++++++++
             */
            array(
                'form_id'      => 'combine_purchases',
                'id'           => 'buyer_create_invoices',
                'element'      => 'checkbox',
                'label'        => $this->_('Buyer can Combine Purchases'),
                'multiOptions' => array(
                    1 => null
                ),
                'description'  => $this->_('When enabled, buyers can combine invoices from the same seller.'),
            ),
            /**
             * ++++++++++++++
             * SOCIAL MEDIA
             * ++++++++++++++
             */
            array(
                'form_id'      => 'social_media',
                'id'           => 'enable_social_media_widget',
                'element'      => 'checkbox',
                'label'        => $this->_('Enable Widget'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check above to enable the widget. If enabled, you will be able to add your website\'s corresponding social media pages.'),
            ),
            array(
                'form_id'      => 'social_media',
                'id'           => 'enable_social_media_user',
                'element'      => 'checkbox',
                'label'        => $this->_('Enable Widget for Users'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check above to allow users to use the widget to share their social media profiles.'),
                'attributes'   => array(
                    'class' => 'field-changeable',
                ),
                'bodyCode'     => "
                    <script type=\"text/javascript\">
                        function checkSocialMediaUsers()
                        {
                            if ($('input:checkbox[name=\"enable_social_media_user\"]').is(':checked')) {
                                $('[name=\"social_media_user_type\"]').closest('.form-group').show();
                            }
                            else {
                                $('[name=\"social_media_user_type\"]').closest('.form-group').hide();
                            }
                        }

                        $(document).ready(function() {
                            checkSocialMediaUsers();
                        });

                        $(document).on('change', '.field-changeable', function() {
                            checkSocialMediaUsers();
                        });

                    </script>",
            ),
            array(
                'form_id'      => 'social_media',
                'id'           => 'social_media_user_type',
                'element'      => 'radio',
                'label'        => $this->_('Available for'),
                'multiOptions' => array(
                    'all'      => $translate->_('All Users'),
                    'verified' => $translate->_('Verified Users'),
                    'store'    => $translate->_('Store Owners'),
                ),
            ),
            array(
                'form_id'    => 'social_media',
                'id'         => 'social_media_link_facebook',
                'element'    => 'text',
                'subtitle'   => $this->_('Social Media Page Links'),
                'label'      => $this->_('Facebook'),
                'validators' => array(
                    'Url',
                ),
                'attributes' => array(
                    'class' => 'form-control input-large',
                ),
            ),
            array(
                'form_id'    => 'social_media',
                'id'         => 'social_media_link_twitter',
                'element'    => 'text',
                'label'      => $this->_('Twitter'),
                'validators' => array(
                    'Url',
                ),
                'attributes' => array(
                    'class' => 'form-control input-large',
                ),
            ),
            array(
                'form_id'    => 'social_media',
                'id'         => 'social_media_link_linkedin',
                'element'    => 'text',
                'label'      => $this->_('LinkedIn'),
                'validators' => array(
                    'Url',
                ),
                'attributes' => array(
                    'class' => 'form-control input-large',
                ),
            ),
            array(
                'form_id'    => 'social_media',
                'id'         => 'social_media_link_instagram',
                'element'    => 'text',
                'label'      => $this->_('Instagram'),
                'validators' => array(
                    'Url',
                ),
                'attributes' => array(
                    'class' => 'form-control input-large',
                ),
            ),
            /**
             * ++++++++++++++
             * RSS FEED
             * ++++++++++++++
             */
            array(
                'form_id'      => 'rss',
                'id'           => 'enable_rss',
                'element'      => 'checkbox',
                'label'        => $this->_('Enable RSS'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check above to enable RSS feeds on your website.'),
            ),
            /**
             * ++++++++++++++
             * DISPLAY FREE FEES ON FRONT END
             * ++++++++++++++
             */
            array(
                'form_id'      => 'display_free_fees',
                'id'           => 'display_free_fees',
                'element'      => 'checkbox',
                'label'        => $this->_('Display Free Fees on User End'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check above if you want to display free fees in the Front End.'),
            ),
            /**
             * ++++++++++++++
             * CUSTOM START/END TIME OPTIONS
             * ++++++++++++++
             */
            array(
                'form_id'      => 'custom_start_end_times',
                'id'           => 'enable_custom_start_time',
                'element'      => 'checkbox',
                'label'        => $this->_('Enable Custom Start Time'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check above to enable the custom start time option for listings.'),
            ),
            array(
                'form_id'      => 'custom_start_end_times',
                'id'           => 'enable_custom_end_time',
                'element'      => 'checkbox',
                'label'        => $this->_('Enable Custom End Time'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check above to enable the custom end time option for listings.'),
            ),
            /**
             * ++++++++++++++
             * LISTINGS SEARCH SETTINGS
             * ++++++++++++++
             */
            array(
                'form_id'      => 'listings_search_settings',
                'id'           => 'search_title',
                'element'      => 'checkbox',
                'label'        => $this->_('By Title'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('This setting cannot be disabled, searching by listing titles is mandatory.'),
                'attributes'   => array(
                    'checked'  => true,
                    'disabled' => true,
                ),
            ),
            array(
                'form_id'      => 'listings_search_settings',
                'id'           => 'search_description',
                'element'      => 'checkbox',
                'label'        => $this->_('By Description'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check above to allow searching for keywords in listing descriptions.'),
            ),
            array(
                'form_id'      => 'listings_search_settings',
                'id'           => 'search_category_name',
                'element'      => 'checkbox',
                'label'        => $this->_('By Category Names'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check above to allow searching for keywords in category names.'),
            ),
            /**
             * ++++++++++++++
             * LISTINGS UPDATES HELPER
             * ++++++++++++++
             */
            array(
                'form_id'      => 'listing_updates',
                'id'           => 'enable_listing_updates',
                'element'      => 'checkbox',
                'label'        => $this->_('Enable Plugin'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check above to enable the listing updates plugin. When enabled, it will update displayed listings data asynchronously. Highly recommended when auctions are enabled.'),
            ),
            array(
                'form_id'     => 'listing_updates',
                'id'          => 'listing_updates_interval',
                'element'     => 'text',
                'label'       => $this->_('Update Interval'),
                'suffix'      => $this->_('seconds'),
                'description' => $this->_('The plugin will run, in the background, at regular intervals. The default setting is 10 seconds.'),
                'attributes'  => array(
                    'class' => 'form-control input-mini',
                ),
                'validators'  => array(
                    'Digits',
                    array('GreaterThan', array(0, false)),
                ),
            ),
            /**
             * ++++++++++++++
             * LISTING SETUP PROCESS
             * ++++++++++++++
             */
            array(
                'form_id'      => 'listing_setup',
                'id'           => 'listing_setup_process',
                'element'      => 'radio',
                'label'        => $this->_('Listing Setup Process'),
                'multiOptions' => array(
                    'full'  => array(
                        $translate->_('Full'),
                        $translate->_('Use the standard 4 steps form - details, settings, shipping and preview.'),
                    ),
                    'quick' => array(
                        $translate->_('Quick'),
                        $translate->_('Use the quick 2 steps form - setup and preview.'),
                    ),
                ),
                'description'  => $this->_('Choose the type of listing creation form to use in the front end.'),
            ),
            /**
             * ++++++++++++++
             * CURRENCY SETTINGS
             * ++++++++++++++
             */
            array(
                'subtitle'     => $translate->_('Current Display Format:') . ' <strong>' . $this->getView()->amount(3999) . '</strong>',
                'form_id'      => 'currency_settings',
                'id'           => 'currency',
                'element'      => 'select',
                'label'        => $this->_('Default Currency'),
                'multiOptions' => $this->getCurrencies()->getMultiOptions(),
                'description'  => sprintf(
                    $translate->_('Select the site\'s default currency.<br>'
                        . '<strong>Important</strong>: Please <a href="%s">click here</a> to define which currencies will be available on the site.'),
                    $this->_view->url(array('module' => 'admin', 'controller' => 'tables', 'action' => 'index', 'table' => 'currencies'))),
                'required'     => true,
                'attributes'   => array(
                    'class' => 'form-control input-large',
                ),
            ),
            array(
                'form_id'      => 'currency_settings',
                'id'           => 'currency_format',
                'element'      => 'radio',
                'label'        => $this->_('Amount Display Format'),
                'multiOptions' => array(
                    1 => array(
                        $translate->_('US Format: 9,999.95'),
                    ),
                    2 => array(
                        $translate->_('EU Format: 9.999,95'),
                    ),
                ),
                'description'  => $this->_('Select the amount display format that will be applied for when displaying currency amounts on your website.'),
                'required'     => true,
            ),
            array(
                'form_id'     => 'currency_settings',
                'id'          => 'currency_decimals',
                'element'     => 'text',
                'label'       => $this->_('Decimal Digits'),
                'description' => $this->_('Enter the number of decimal digits that will be shown when displaying a currency amount.'),
                'required'    => true,
                'validators'  => array(
                    'Digits',
                ),
                'attributes'  => array(
                    'class' => 'form-control input-mini',
                ),
            ),
            array(
                'form_id'      => 'currency_settings',
                'id'           => 'currency_position',
                'element'      => 'radio',
                'label'        => $this->_('Symbol Position'),
                'multiOptions' => array(
                    1 => array(
                        $translate->_('Symbol before amount:') . ' ' . $this->getCurrencies()->getSymbol($settings['currency']) . ' 199',
                    ),
                    2 => array(
                        $translate->_('Amount before symbol:') . ' 199 ' . $this->getCurrencies()->getSymbol($settings['currency']),
                    ),
                    3 => array(
                        $translate->_('Amount between symbol and ISO code:') . ' ' . $this->getCurrencies()->getSymbol($settings['currency']) . '199 ' . $settings['currency'],
                    )
                ),
                'description'  => $this->_('Select the amount display format that will be applied for when displaying currency amounts on your website.'),
                'required'     => true,
            ),
            /**
             * ++++++++++++++
             * TITLE CHARACTER LENGTH
             * ++++++++++++++
             */
            array(
                'form_id'     => 'character_length',
                'id'          => 'character_length',
                'element'     => 'text',
                'label'       => $this->_('Title Character Length'),
                'description' => $this->_('Enter the maximum character length allowed for the listing title field.'),
                'validators'  => array(
                    'Digits',
                ),
                'attributes'  => array(
                    'class' => 'form-control input-mini',
                ),
            ),
            /**
             * ++++++++++++++
             * LISTINGS APPROVAL
             * ++++++++++++++
             */
            array(
                'form_id'      => 'listings_approval',
                'id'           => 'enable_listings_approval',
                'element'      => 'checkbox',
                'label'        => $this->_('Enable Listings Approval'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check above to require admin approval for listings. '
                    . 'Moreover, edited listings will also require admin approval.'),
            ),
            /**
             * ++++++++++++++
             * SHORT DESCRIPTION
             * ++++++++++++++
             */
            array(
                'form_id'      => 'listing_short_description',
                'id'           => 'enable_short_description',
                'element'      => 'checkbox',
                'label'        => $this->_('Enable Short Listing Description'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check above to allow sellers to add short listing descriptions.'),
            ),
            array(
                'form_id'     => 'listing_short_description',
                'id'          => 'short_description_character_length',
                'element'     => 'text',
                'label'       => $this->_('Short Description Character Length'),
                'description' => $this->_('(Optional) Enter the maximum character length allowed for the short description field.'),
                'validators'  => array(
                    'Digits',
                ),
                'attributes'  => array(
                    'class' => 'form-control input-mini',
                ),
            ),
            /**
             * ++++++++++++++
             * IMAGES SETTINGS
             * ++++++++++++++
             */
            array(
                'form_id'     => 'images_settings',
                'id'          => 'images_max',
                'element'     => 'text',
                'label'       => $this->_('Number of Images'),
                'description' => $this->_('Enter the maximum number of images that can be added to a listing.'),
                'required'    => true,
                'validators'  => array(
                    'Digits',
                ),
                'attributes'  => array(
                    'class' => 'form-control input-mini',
                ),
            ),
            array(
                'form_id'      => 'images_settings',
                'id'           => 'mandatory_images',
                'element'      => 'checkbox',
                'label'        => $this->_('Mandatory Images'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check above to require users to add at least one image when creating a listing.'),
            ),
            array(
                'form_id'     => 'images_settings',
                'id'          => 'images_size',
                'element'     => 'text',
                'label'       => $this->_('Maximum Size Allowed'),
                'suffix'      => $this->_('KB'),
                'description' => sprintf($this->_('Enter the maximum size of an uploadable image.<br>'
                    . '<strong>Note</strong>: The maximum file size allowed on your server is <strong>%s</strong>. '
                    . 'To increase this limit, please contact your hosting provider.'), $maximumFileUploadSize),
                'validators'  => array(
                    'Digits',
                ),
                'attributes'  => array(
                    'class' => 'form-control input-mini',
                ),
            ),
            array(
                'form_id'      => 'images_settings',
                'id'           => 'crop_images',
                'element'      => 'checkbox',
                'label'        => $this->_('Crop to Aspect Ratio'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check above for the software to crop listings images when generating thumbnails.'),
            ),
            array(
                'form_id'      => 'images_settings',
                'id'           => 'lazy_load_images',
                'element'      => 'checkbox',
                'label'        => $this->_('Lazy Load'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check above to enable javascript lazy load when displaying listings images (improves website loading speed).'),
            ),
            array(
                'form_id'      => 'images_settings',
                'id'           => 'remote_uploads',
                'element'      => 'checkbox',
                'label'        => $this->_('Allow Remote Images'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check above to allow the addition of images from remote locations by entering the direct link.'),
            ),
            array(
                'form_id'     => 'images_settings',
                'id'          => 'images_watermark',
                'element'     => 'text',
                'label'       => $this->_('Watermark Text'),
                'description' => $this->_('Enter the text that will be displayed on uploaded images. Leave empty for no watermark.'),
                'attributes'  => array(
                    'class' => 'form-control input-medium',
                ),
                'required'    => false,
            ),

            /**
             * ++++++++++++++
             * MEDIA UPLOAD SETTINGS
             * ++++++++++++++
             */
            array(
                'form_id'     => 'media_upload',
                'id'          => 'videos_max',
                'element'     => 'text',
                'label'       => $this->_('Number of Videos'),
                'description' => $this->_('Enter the maximum number of videos that can be added to a listing. <br>'
                    . '<strong>Important</strong>: To disable this feature, enter 0 in the above field.'),
                'required'    => true,
                'validators'  => array(
                    'Digits',
                ),
                'attributes'  => array(
                    'class' => 'form-control input-mini',
                ),
            ),
            array(
                'form_id'     => 'media_upload',
                'id'          => 'videos_size',
                'element'     => 'text',
                'label'       => $this->_('Maximum Size Allowed'),
                'suffix'      => $this->_('KB'),
                'description' => sprintf($this->_('Enter the maximum size of an uploadable video.<br>'
                    . '<strong>Note</strong>: The maximum file size allowed on your server is <strong>%s</strong>. '
                    . 'To increase this limit, please contact your hosting provider.'), $maximumFileUploadSize),
                'validators'  => array(
                    'Digits',
                ),
                'attributes'  => array(
                    'class' => 'form-control input-mini',
                ),
            ),
            array(
                'form_id'      => 'media_upload',
                'id'           => 'embedded_code',
                'element'      => 'checkbox',
                'label'        => $this->_('Allow Embedded Code'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check above to allow the addition of remote videos using embedded code. <br>'
                    . '<strong>Eg</strong>: For YouTube use the code provided by accessing "share" -> "embed" on any youtube video.'),
            ),
            /**
             * ++++++++++++++
             * DIGITAL DOWNLOADS SETTINGS
             * ++++++++++++++
             */
            array(
                'form_id'     => 'digital_downloads',
                'id'          => 'digital_downloads_max',
                'element'     => 'text',
                'label'       => $this->_('Digital Downloads'),
                'description' => $this->_('Enter the maximum number of downloadable files that can be added to a listing. <br>'
                    . '<strong>Important</strong>: To disable this feature, enter 0 in the above field.'),
                'required'    => true,
                'validators'  => array(
                    'Digits',
                ),
                'attributes'  => array(
                    'class' => 'form-control input-mini',
                ),
            ),
            array(
                'form_id'     => 'digital_downloads',
                'id'          => 'digital_downloads_folder',
                'element'     => 'text',
                'label'       => $this->_('Digital Downloads Folder'),
                'description' => $translate->_('Please enter a folder relative to your document root, where the files will be stored.<br>'
                        . 'Your document root path is:') . ' ' . $basePath,
                'attributes'  => array(
                    'class' => 'form-control input-medium',
                ),
            ),

            array(
                'form_id'     => 'digital_downloads',
                'id'          => 'digital_downloads_size',
                'element'     => 'text',
                'label'       => $this->_('Maximum Size Allowed'),
                'suffix'      => $this->_('KB'),
                'description' => sprintf($this->_('Enter the maximum size of an uploadable file.<br>'
                    . '<strong>Note</strong>: The maximum file size allowed on your server is <strong>%s</strong>. '
                    . 'To increase this limit, please contact your hosting provider.'), $maximumFileUploadSize),
                'validators'  => array(
                    'Digits',
                ),
                'attributes'  => array(
                    'class' => 'form-control input-small',
                ),
            ),
            /**
             * ++++++++++++++
             * SALE TRANSACTION FEE REFUNDS
             * ++++++++++++++
             */
            array(
                'form_id'      => 'sale_fee_refunds',
                'id'           => 'enable_sale_fee_refunds',
                'element'      => 'checkbox',
                'label'        => $this->_('Enable Sale Transaction Fee Refunds'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check above to allow your users to request refunds for sale transaction fees. <br>'
                    . 'The refunded amount will be visible in the users\' accounts. '),
            ),
            array(
                'form_id'     => 'sale_fee_refunds',
                'id'          => 'sale_fee_refunds_range',
                'element'     => '\Ppb\Form\Element\Range',
                'label'       => $this->_('Interval'),
                'description' => $this->_('Enter the interval during which a user can request a refund for a sale transaction fee. Leave both fields empty if you don\'t want to set an interval.'),
                'suffix'      => $this->_('days'),
                'attributes'  => array(
                    'class' => 'form-control input-mini',
                ),
            ),
            /**
             * ++++++++++++++
             * LISTINGS FEATURING
             * ++++++++++++++
             */
            array(
                'form_id'      => 'listings_featuring',
                'id'           => 'enable_hpfeat',
                'element'      => 'checkbox',
                'label'        => $this->_('Enable Home Page Featured Listings'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check above to enable home page featured listings.'),
            ),
            array(
                'form_id'      => 'listings_featuring',
                'id'           => 'enable_catfeat',
                'element'      => 'checkbox',
                'label'        => $this->_('Enable Category Pages Featured Listings'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check above to enable category pages featured listings.'),
            ),
            array(
                'form_id'      => 'listings_featuring',
                'id'           => 'enable_highlighted',
                'element'      => 'checkbox',
                'label'        => $this->_('Enable Highlighted Listings'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check above to enable highlighted listings.'),
            ),
            /**
             * ++++++++++++++
             * LISTINGS SHARING
             * ++++++++++++++
             */
            array(
                'form_id'      => 'listings_sharing',
                'id'           => 'enable_listings_sharing',
                'element'      => 'checkbox',
                'label'        => $this->_('Enable Listings Sharing'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check above to enable listings social media sharing.'),
            ),
            array(
                'form_id'      => 'listings_sharing',
                'id'           => 'enable_email_friend',
                'element'      => 'checkbox',
                'label'        => $this->_('Email Listing to Friend'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check above to enable the email listing to friend option.'),
            ),
            /**
             * ++++++++++++++
             * COOKIE USAGE CONFIRMATION
             * ++++++++++++++
             */
            array(
                'form_id'      => 'cookie_usage',
                'id'           => 'enable_cookie_usage_confirmation',
                'element'      => 'checkbox',
                'label'        => $this->_('Enable Cookie Usage Confirmation'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check above to enable the cookie usage confirmation option.<br>'
                    . 'Users will be notified that the site uses cookies. They will have to agree in order to hide the message.'),
            ),
            array(
                'form_id'     => 'cookie_usage',
                'id'          => 'cookie_usage_message',
                'element'     => 'textarea',
                'label'       => $this->_('Cookie Usage Confirmation Message'),
                'description' => $this->_('Enter the cookie confirmation message that will be displayed.'),
                'required'    => ($this->getData('enable_cookie_usage_confirmation')) ? true : false,
                'attributes'  => array(
                    'rows'  => '3',
                    'class' => 'form-control',
                ),
            ),
            /**
             * ++++++++++++++
             * GOOGLE RECAPTCHA
             * ++++++++++++++
             */
            array(
                'form_id'      => 'google_recaptcha',
                'id'           => 'enable_recaptcha',
                'element'      => 'checkbox',
                'label'        => $this->_('Enable reCAPTCHA'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check above to enable the Google reCAPTCHA plugin. <br>'
                    . 'To use the plugin on your website, create an account '
                    . '<a href="https://www.google.com/recaptcha/intro/index.html" target="_blank">here</a>.'),
            ),
            array(
                'form_id'    => 'google_recaptcha',
                'id'         => 'recaptcha_public_key',
                'element'    => 'text',
                'label'      => $this->_('reCAPTCHA Public Key'),
                'required'   => ($this->getData('enable_recaptcha')) ? true : false,
                'validators' => array(
                    'NoHtml',
                ),
                'attributes' => array(
                    'class' => 'form-control input-xlarge',
                ),
            ),
            array(
                'form_id'    => 'google_recaptcha',
                'id'         => 'recaptcha_private_key',
                'element'    => 'text',
                'label'      => $this->_('reCAPTCHA Private Key'),
                'required'   => ($this->getData('enable_recaptcha')) ? true : false,
                'validators' => array(
                    'NoHtml',
                ),
                'attributes' => array(
                    'class' => 'form-control input-xlarge',
                ),
            ),
            array(
                'form_id'      => 'google_recaptcha',
                'subtitle'     => $this->_('reCAPTCHA Usage'),
                'id'           => 'recaptcha_registration',
                'element'      => 'checkbox',
                'label'        => $this->_('Registration Process'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check above to enable reCAPTCHA for the registration process.'),
            ),
            array(
                'form_id'      => 'google_recaptcha',
                'id'           => 'recaptcha_contact_us',
                'element'      => 'checkbox',
                'label'        => $this->_('Contact Us Page'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check above to enable reCAPTCHA for the Contact Us page.'),
            ),
            array(
                'form_id'      => 'google_recaptcha',
                'id'           => 'recaptcha_email_friend',
                'element'      => 'checkbox',
                'label'        => $this->_('Email Listing to Friend Page'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check above to enable reCAPTCHA for the Email Listing to Friend page.'),
            ),
            /**
             * ++++++++++++++
             * BCC EMAILS TO ADMIN
             * ++++++++++++++
             */
            array(
                'form_id'      => 'bcc_emails',
                'id'           => 'bcc_emails',
                'element'      => 'checkbox',
                'label'        => $this->_('Enable BCC Emails to Admin'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check above for the admin to receive emails sent between site users and sale notifications.'),
            ),
            /**
             * ++++++++++++++
             * RECENTLY VIEWED LISTINGS BOX
             * ++++++++++++++
             */
            array(
                'form_id'      => 'recently_viewed_listings',
                'id'           => 'enable_recently_viewed_listings',
                'element'      => 'checkbox',
                'label'        => $this->_('Enable Recently Viewed Listings Box'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check above to enable the Recently Viewed Listings box. It will be displayed by default in the footer of your website.'),
            ),
            array(
                'form_id'     => 'recently_viewed_listings',
                'id'          => 'enable_recently_viewed_listings_expiration',
                'element'     => 'text',
                'label'       => $this->_('Expiration Time'),
                'suffix'      => $this->_('hours'),
                'description' => $this->_('Enter the number of hours after which a listing will be removed from the recently viewed table.'),
                'required'    => $this->getData('enable_recently_viewed_listings') ? true : false,
                'validators'  => array(
                    'Digits',
                ),
                'attributes'  => array(
                    'class' => 'form-control input-mini',
                ),
            ),
            /**
             * ++++++++++++++
             * BULK LISTER
             * ++++++++++++++
             */
            array(
                'form_id'      => 'bulk_lister',
                'id'           => 'enable_bulk_lister',
                'element'      => 'checkbox',
                'label'        => $this->_('Enable Bulk Lister'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check above to enable the Bulk Lister tool. It will parse CSV files.'),
            ),
            /**
             * ++++++++++++++
             * NEWSLETTER SUBSCRIPTION BOX
             * ++++++++++++++
             */
            array(
                'form_id'      => 'newsletter_subscription_box',
                'id'           => 'newsletter_subscription_box',
                'element'      => 'checkbox',
                'label'        => $this->_('Enable Newsletter Subscription Box'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check above to enable the Newsletter Subscription Box in the footer.'),
            ),
            array(
                'form_id'      => 'newsletter_subscription_box',
                'id'           => 'newsletter_subscription_email_confirmation',
                'element'      => 'checkbox',
                'label'        => $this->_('Require Email Confirmation'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('(Recommended) Check above to require newsletter subscribers to confirm their email addresses.'),
            ),
            /**
             * ++++++++++++++
             * ADULT CATEGORIES
             * ++++++++++++++
             */
            array(
                'form_id'      => 'adult_categories',
                'id'           => 'enable_adult_categories',
                'element'      => 'checkbox',
                'label'        => $this->_('Enable Adult Categories'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check above to enable the module.'),
            ),
            array(
                'form_id'      => 'adult_categories',
                'id'           => 'adult_categories_splash_page',
                'element'      => 'checkbox',
                'label'        => $this->_('Splash Page Custom Content'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check above to use custom content for the Adult Categories splash page.'),
                'attributes'   => array(
                    'class' => 'field-changeable',
                ),
                'bodyCode'     => "
                    <script type=\"text/javascript\">
                        function checkAdultCategoriesFields()
                        {
                            if ($('input:checkbox[name=\"adult_categories_splash_page\"]').is(':checked')) {
                                $('[name=\"adult_categories_splash_page_content\"]').closest('.form-group').show();
                            }
                            else {
                                $('[name=\"adult_categories_splash_page_content\"]').closest('.form-group').hide();
                            }
                        }

                        $(document).ready(function() {
                            checkAdultCategoriesFields();
                        });

                        $(document).on('change', '.field-changeable', function() {
                            checkAdultCategoriesFields();
                        });

                    </script>"
            ),
            array(
                'form_id'     => 'adult_categories',
                'id'          => 'adult_categories_splash_page_content',
                'element'     => '\\Ppb\\Form\\Element\\Wysiwyg',
                'label'       => $this->_('Custom Content'),
                'description' => $this->_('Enter custom content for the Adult Categories splash page.'),
                'required'    => ($this->getData('adult_categories_splash_page')) ? true : false,
                'attributes'  => array(
                    'rows'  => '12',
                    'class' => 'form-control',
                ),
            ),
            /**
             * ++++++++++++++
             * AUCTIONS SETTINGS
             * ++++++++++++++
             */
            array(
                'form_id'      => 'auctions_settings',
                'id'           => 'enable_auctions',
                'element'      => 'checkbox',
                'label'        => $this->_('Enable Auctions'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check above to enable the listing of auctions on your website.'),
                'required'     => (!$settings['enable_products'] && !$settings['enable_classifieds']) ? true : false,
            ),
            array(
                'form_id'     => 'auctions_settings',
                'subtitle'    => $this->_('Auction Editing Time Limit'),
                'id'          => 'auctions_editing_hours',
                'element'     => 'text',
                'label'       => $this->_('Time Limit'),
                'suffix'      => $this->_('hours'),
                'description' => $this->_('When the remaining extent of an auction will be lower than the set limit, the seller won\'t be able to edit it.'),
                'validators'  => array(
                    'Digits',
                ),
                'attributes'  => array(
                    'class' => 'form-control input-mini',
                ),
            ),
            array(
                'form_id'      => 'auctions_settings',
                'subtitle'     => $this->_('Auction Sniping'),
                'id'           => 'enable_auctions_sniping',
                'element'      => 'checkbox',
                'label'        => $this->_('Enable Feature'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('If enabled, the duration of an auction will be automatically extended if placing a bid when the auction is about to end.'),
            ),
            array(
                'form_id'     => 'auctions_settings',
                'id'          => 'auctions_sniping_minutes',
                'element'     => 'text',
                'label'       => $this->_('Sniping Duration'),
                'suffix'      => $this->_('minutes'),
                'description' => $this->_('When the remaining duration of an auction will be less than the above set duration, the time will be extended to the above setting.'),
                'required'    => $this->getData('enable_auctions_sniping') ? true : false,
                'validators'  => array(
                    'Digits',
                ),
                'attributes'  => array(
                    'class' => 'form-control input-mini',
                ),
            ),
            array(
                'form_id'      => 'auctions_settings',
                'subtitle'     => $this->_('Bid Retraction'),
                'id'           => 'enable_bid_retraction',
                'element'      => 'checkbox',
                'label'        => $this->_('Enable Feature'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check above to allow bidders to retract bids from auctions.'),
            ),
            array(
                'form_id'     => 'auctions_settings',
                'id'          => 'bid_retraction_hours',
                'element'     => 'text',
                'label'       => $this->_('Bid Retraction Limit'),
                'suffix'      => $this->_('hours'),
                'description' => $this->_('Enter the minimum required time left on an auction to enable bid retraction.'),
                'required'    => $this->getData('enable_auctions_sniping') ? true : false,
                'validators'  => array(
                    'Digits',
                ),
                'attributes'  => array(
                    'class' => 'form-control input-mini',
                ),
            ),
            array(
                'form_id'      => 'auctions_settings',
                'subtitle'     => $this->_('Change Auction Duration when a Bid is Placed'),
                'id'           => 'enable_change_duration',
                'element'      => 'checkbox',
                'label'        => $this->_('Enable Feature'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check above to change the auction duration after the placement of the first bid.'),
            ),
            array(
                'form_id'     => 'auctions_settings',
                'id'          => 'change_duration_days',
                'element'     => 'text',
                'label'       => $this->_('New Duration'),
                'suffix'      => $this->_('days'),
                'description' => $this->_('If the duration left on the auction is over the set value, it will be automatically reset to it.'),
                'required'    => $this->getData('enable_change_duration') ? true : false,
                'validators'  => array(
                    'Digits',
                ),
                'attributes'  => array(
                    'class' => 'form-control input-mini',
                ),
            ),
            array(
                'form_id'      => 'auctions_settings',
                'subtitle'     => $this->_('Close Auctions Before End Time'),
                'id'           => 'close_auctions_end_time',
                'element'      => 'checkbox',
                'label'        => $this->_('Enable Feature'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('When enabled, sellers can end their auctions early even in the event of a high bid. <br>'
                    . 'By default, auctions can be closed ahead of the scheduled date & time only if there are no bids or the highest bid is lower than the reserve price.'),
            ),
            array(
                'form_id'      => 'auctions_settings',
                'subtitle'     => $this->_('Proxy Bidding'),
                'id'           => 'proxy_bidding',
                'element'      => 'checkbox',
                'label'        => $this->_('Enable Feature'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('When enabled, bidders can place a maximum bid on an auction. <br>'
                    . 'However, the active bid set will be the minimum amount necessary for them to become the new high bidders.<br>'
                    . '<a href="http://en.wikipedia.org/wiki/Proxy_bid" target="_blank">Click here</a> for more information on this feature.'),
            ),
            array(
                'form_id'      => 'auctions_settings',
                'subtitle'     => $this->_('Limit Number of Bids / Offers per User'),
                'id'           => 'enable_limit_bids',
                'element'      => 'checkbox',
                'label'        => $this->_('Enable Feature'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('When enabled, sellers can limit the number of bids and/or offers a bidder can place on an auction (not considering proxy bids).'),
            ),
            /**
             * ++++++++++++++
             * PRODUCTS SETTINGS
             * ++++++++++++++
             */
            array(
                'form_id'      => 'products_settings',
                'id'           => 'enable_products',
                'element'      => 'checkbox',
                'label'        => $this->_('Enable Products'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check above to enable the listing of products on your website.'),
                'required'     => (!$settings['enable_auctions'] && !$settings['enable_classifieds']) ? true : false,
            ),
            array(
                'form_id'      => 'products_settings',
                'subtitle'     => $this->_('Unlimited Duration'),
                'id'           => 'enable_unlimited_duration',
                'element'      => 'checkbox',
                'label'        => $this->_('Enable Unlimited Duration'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('When enabled, sellers can list products without a closing date.'),
            ),
            array(
                'form_id'      => 'products_settings',
                'id'           => 'force_unlimited_duration',
                'element'      => 'checkbox',
                'label'        => $this->_('Force Unlimited Duration'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('When enabled, sellers will only be able to list products with no closing date.'),
            ),
            array(
                'form_id'      => 'products_settings',
                'subtitle'     => $this->_('Shopping Cart'),
                'id'           => 'enable_shopping_cart',
                'element'      => 'checkbox',
                'label'        => $this->_('Enable Shopping Cart'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check above to enable the module.'),
            ),
            array(
                'form_id'      => 'products_settings',
                'id'           => 'shopping_cart_applies',
                'element'      => 'radio',
                'label'        => $this->_('Shopping Cart Applies'),
                'multiOptions' => array(
                    'global'         => array(
                        $translate->_('Globally'),
                        $translate->_('The shopping cart will be used for all products listed on the site.'),
                    ),
                    'store_owners'   => array(
                        $translate->_('Store Owners'),
                        $translate->_('The shopping cart will be used for products listed by store owners only.')
                    ),
                    'store_listings' => array(
                        $translate->_('Store Listings'),
                        $translate->_('The shopping cart will be used only for products listed in stores.'),
                    ),
                ),
                'description'  => $this->_('Select when the shopping cart module will be used.'),
            ),
            array(
                'form_id'     => 'products_settings',
                'id'          => 'pending_sales_listings_expire_hours',
                'element'     => 'text',
                'label'       => $this->_('Reserve Stock'),
                'suffix'      => $this->_('minutes'),
                'description' => $this->_('Enter the time period for which products added in a shopping cart have their stock reserved. Leave empty to disable.'),
                'validators'  => array(
                    'Digits',
                ),
                'attributes'  => array(
                    'class' => 'form-control input-mini',
                ),
            ),
            array(
                'form_id'      => 'products_settings',
                'subtitle'     => $this->_('Force Payment'),
                'id'           => ($settings['enable_products']) ? 'enable_force_payment' : false,
                'element'      => 'checkbox',
                'label'        => $this->_('Enable Force Payment'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('When enabled, a sale will be considered completed only when marked as paid.<br>'
                    . 'If using this feature, it is recommended to run the website in "Account Mode".'),
            ),
            array(
                'form_id'     => 'products_settings',
                'id'          => 'force_payment_limit',
                'element'     => 'text',
                'label'       => $this->_('Force Payment Time Limit'),
                'suffix'      => $this->_('minutes'),
                'description' => $this->_('Enter the time limit after which unpaid invoices are reverted.'),
                'required'    => true,
                'validators'  => array(
                    'Digits',
                ),
                'attributes'  => array(
                    'class' => 'form-control input-mini',
                ),
            ),
            /**
             * ++++++++++++++
             * CLASSIFIEDS SETTINGS
             * ++++++++++++++
             */
            array(
                'form_id'      => 'classifieds_settings',
                'id'           => 'enable_classifieds',
                'element'      => 'checkbox',
                'label'        => $this->_('Enable Classifieds'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check above to enable the listing of classifieds on your website.<br>'
                    . '<strong>Important</strong>: Classifieds wont be available when store only mode is enabled.'),
                'required'     => (!$settings['enable_auctions'] && !$settings['enable_products']) ? true : false,
            ),
            /**
             * ++++++++++++++
             * BUY OUT FEATURE
             * ++++++++++++++
             */
            array(
                'form_id'      => 'buy_out',
                'id'           => 'enable_buyout',
                'element'      => 'checkbox',
                'label'        => $this->_('Enable Buy Out'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check above to enable the Buy Out feature for auctions.'),
                'required'     => (!$settings['enable_auctions'] && !$settings['enable_make_offer']) ? true : false,
            ),
            array(
                'form_id'      => 'buy_out',
                'id'           => 'always_show_buyout',
                'element'      => 'checkbox',
                'label'        => $this->_('Always Show Buy Out Button'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('When listing an auction with buy out enabled, the option will remain active even if there are bids above the reserve price.'),
            ),
            /**
             * ++++++++++++++
             * MAKE OFFER FEATURE
             * ++++++++++++++
             */
            array(
                'form_id'      => 'make_offer',
                'id'           => 'enable_make_offer',
                'element'      => 'checkbox',
                'label'        => $this->_('Enable Make Offer'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check above to enable the Make Offer feature for auctions and products.'),
            ),
            array(
                'form_id'      => 'make_offer',
                'id'           => 'show_make_offer_ranges',
                'element'      => 'checkbox',
                'label'        => $this->_('Show Offer Ranges'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('When enabled, the allowed offer ranges set by the seller will be displayed on the Listing Details page.'),
            ),
            /**
             * ++++++++++++++
             * ITEMS SWAPPING FEATURE
             * ++++++++++++++
             */
            array(
                'form_id'      => 'items_swapping',
                'id'           => 'enable_swap',
                'element'      => 'checkbox',
                'label'        => $this->_('Enable Items Swapping Feature'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check above to enable the items swapping feature for auctions and products.'),
            ),
            /**
             * ++++++++++++++
             * SHIPPING SETTINGS
             * ++++++++++++++
             */
            array(
                'form_id'      => 'shipping_settings',
                'id'           => 'enable_shipping',
                'element'      => 'checkbox',
                'label'        => $this->_('Enable Shipping'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check above to enable the module.'),
                'attributes'   => array(
                    'id'      => 'enable_shipping',
                    'onclick' => 'javascript:checkShippingFields();',
                ),
                'bodyCode'     => "
                    <script type=\"text/javascript\">
                        function checkShippingFields() {
                            if ($('#enable_shipping').is(':checked')) {  
                                $('.shipping-options').closest('.form-group').show();
                            }
                            else {
                                $('.shipping-options').prop('checked', false).closest('.form-group').hide();
                            }
                        }

                        $(document).ready(function() {             
                            checkShippingFields();
                        });
                    </script>",
            ),
            array(
                'form_id'      => 'shipping_settings',
                'id'           => 'enable_pickups',
                'element'      => 'checkbox',
                'label'        => $this->_('Enable Pick-ups'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check above to allow sellers to use the option on their listings.'),
                'attributes'   => array(
                    'class' => 'shipping-options',
                ),
            ),
            array(
                'form_id'      => 'shipping_settings',
                'id'           => 'enable_returns',
                'element'      => 'checkbox',
                'label'        => $this->_('Enable Returns'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check above to allow sellers to specify a returns policy for their listings.'),
                'attributes'   => array(
                    'class' => 'shipping-options',
                ),
            ),
            /**
             * ++++++++++++++
             * AUTO RELISTS SETTINGS
             * ++++++++++++++
             */
            array(
                'form_id'      => 'auto_relists',
                'id'           => 'auto_relist',
                'element'      => 'checkbox',
                'label'        => $this->_('Enable Auto Relists'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check above to enable the module.'),
            ),
            array(
                'form_id'     => 'auto_relists',
                'id'          => 'max_auto_relists',
                'element'     => 'text',
                'label'       => $this->_('Maximum Auto Relists Allowed'),
                'description' => $this->_('Enter the maximum number of auto relists that can be entered when a listing is created.'),
                'validators'  => array(
                    'Digits',
                ),
                'attributes'  => array(
                    'class' => 'form-control input-mini',
                ),
            ),
            array(
                'form_id'      => 'auto_relists',
                'id'           => 'relist_method',
                'element'      => 'select',
                'label'        => $this->_('Relist Method'),
                'description'  => $this->_('Select the relisting method for your listings:<br>'
                    . '- New: a new listing will be created and the old one will be marked as deleted<br>'
                    . '- Same: the same listing will be re-opened. All sales, bids, etc. will be removed'),
                'multiOptions' => array(
                    'new'  => 'New',
                    'same' => 'Same',
                ),
                'attributes'   => array(
                    'class' => 'form-control input-small',
                ),
            ),
            /**
             * ++++++++++++++
             * MARKED DELETED LISTINGS REMOVAL
             * ++++++++++++++
             */
            array(
                'form_id'      => 'marked_deleted',
                'id'           => 'marked_deleted_listings_removal',
                'element'      => 'checkbox',
                'label'        => $this->_('Automatic Marked Deleted Listings Removal'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check above to enable the automatic removal of listings marked as deleted.<br>'
                    . '<strong>Note</strong>: The process will be run using the cron service.'),
            ),
            /**
             * ++++++++++++++
             * CLOSED LISTINGS DELETION
             * ++++++++++++++
             */
            array(
                'form_id'     => 'closed_listings_deletion',
                'id'          => 'closed_listings_deletion_days',
                'element'     => 'text',
                'label'       => $this->_('Closed Listings Deletion'),
                'suffix'      => $this->_('days'),
                'description' => $this->_('Enter a duration, in days, after which closed listings will be automatically marked as deleted. Leave empty to disable the feature.'),
                'attributes'  => array(
                    'class' => 'form-control input-mini',
                ),
                'validators'  => array(
                    'Digits',
                ),
            ),
            /**
             * ++++++++++++++
             * USERS MESSAGING
             * ++++++++++++++
             */
            array(
                'form_id'      => 'users_messaging',
                'id'           => 'enable_messaging',
                'element'      => 'checkbox',
                'label'        => $this->_('Enable Messaging'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check above to enable the module.'),
            ),
            array(
                'form_id'      => 'users_messaging',
                'id'           => 'enable_public_questions',
                'element'      => 'checkbox',
                'label'        => $this->_('Enable Public Questions'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check above to allow posting of public questions on listings. By default, only private questions will be allowed.'),
            ),
            /**
             * ++++++++++++++
             * ADDITIONAL CATEGORY LISTING
             * ++++++++++++++
             */
            array(
                'form_id'      => 'additional_category_listing',
                'id'           => 'addl_category_listing',
                'element'      => 'checkbox',
                'label'        => $this->_('Enable Feature'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check above to enable the listing of items in an additional category.'),
            ),
            /**
             * ++++++++++++++
             * LISTINGS COUNTERS
             * ++++++++++++++
             */
            array(
                'form_id'      => 'listings_counters',
                'id'           => 'category_counters',
                'element'      => 'checkbox',
                'label'        => $this->_('Category Counters'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check above to enable the displaying of category counters on selected pages.<br>'
                    . 'Counters are updated using only the cron job. If they are out of sync, use the initialization tool below.'),
            ),
            array(
                'form_id'      => 'listings_counters',
                'id'           => 'hide_empty_categories',
                'element'      => 'checkbox',
                'label'        => $this->_('Hide Empty Categories/Options'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check above to hide categories that don\'t contain listings.'),
            ),
            array(
                'form_id'     => 'listings_counters',
                'id'          => 'init_category_counters',
                'element'     => 'button',
                'label'       => $this->_('Initialize Counters'),
                'value'       => $this->_('Initialize'),
                'description' => sprintf(
                        $translate->_('Click on the button above to initialize your site\'s category counters.'
                            . '<div>There are a total of <span id="category-total-listings">%s</span> listings to be counted.</div>'), $totalListings)
                    . '<div id="category-counters-progress" class="text-success"></div>',
                'attributes'  => array(
                    'class'         => 'btn btn-secondary',
                    'data-post-url' => $this->getView()->url(array('module' => 'admin', 'controller' => 'index', 'action' => 'initialize-category-counters')),
                ),
            ),
            /**
             * ++++++++++++++
             * LISTINGS TERMS & CONDITIONS BOX
             * ++++++++++++++
             */
            array(
                'form_id'      => 'listing_terms_box',
                'id'           => 'listing_terms_box',
                'element'      => 'checkbox',
                'label'        => $this->_('Show Listing Terms & Conditions Box'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check above to display, in the listing setup process, the Listing Terms and Conditions box.'),
            ),
            array(
                'form_id'    => 'listing_terms_box',
                'id'         => 'listing_terms_content',
                'element'    => 'textarea',
                'label'      => $this->_('Content'),
                'validators' => array(
                    'NoHtml',
                ),
                'attributes' => array(
                    'rows'  => '12',
                    'class' => 'form-control',
                ),
            ),
            /**
             * ++++++++++++++
             * USERS PHONE NUMBERS ON SUCCESSFUL SALES
             * ++++++++++++++
             */
            array(
                'form_id'      => 'user_phone_numbers',
                'id'           => 'sale_phone_numbers',
                'element'      => 'checkbox',
                'label'        => $this->_('Enable Feature'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check above to show users\' phone numbers on invoices when displaying their full addresses.'),
            ),
            /**
             * ++++++++++++++
             * SELLER'S OTHER ITEMS BOX
             * ++++++++++++++
             */
            array(
                'form_id'      => 'other_items_seller',
                'id'           => 'other_items_seller',
                'element'      => 'checkbox',
                'label'        => $this->_('Enable Feature'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check above to enable the displaying of other items from the seller\'s box on the Listing Details pages.'),
            ),
            /**
             * ++++++++++++++
             * STORES SETTINGS
             * ++++++++++++++
             */
            array(
                'form_id'      => 'stores_settings',
                'id'           => 'enable_stores',
                'element'      => 'checkbox',
                'label'        => $this->_('Enable Stores'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check above to enable the module.'),
            ),
            array(
                'form_id'      => 'stores_settings',
                'id'           => 'hide_empty_stores',
                'element'      => 'checkbox',
                'label'        => $this->_('Hide Empty Stores'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check above to hide stores with no listings when accessing the Browse Stores page.'),
            ),
            array(
                'form_id'      => 'stores_settings',
                'id'           => 'store_only_mode',
                'element'      => 'checkbox',
                'label'        => $this->_('Enable Store Only Mode'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check above to enable the feature.<br>'
                    . 'When enabled, users will have to open a store to create listings.'),
                'attributes'   => array(
                    'class' => 'field-changeable',
                ),

                'bodyCode' => "
                    <script type=\"text/javascript\">
                        function checkFormFields()
                        {
                            if ($('input:checkbox[name=\"store_only_mode\"]').is(':checked')) {
                                $('[name=\"store_only_mode_disable_listings\"]').closest('.form-group').show();
                            }
                            else {
                                $('[name=\"store_only_mode_disable_listings\"]').prop('checked', false).closest('.form-group').hide();
                            }
                        }

                        $(document).ready(function() {
                            checkFormFields();
                        });

                        $(document).on('change', '.field-changeable', function() {
                            checkFormFields();
                        });

                    </script>"
            ),
            array(
                'form_id'      => 'stores_settings',
                'id'           => 'store_only_mode_disable_listings',
                'element'      => 'checkbox',
                'label'        => $this->_('Disable Listings for Inactive Stores'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check above to disable listings for inactive stores if store only mode is enabled.'),
            ),
            array(
                'form_id'      => 'stores_settings',
                'id'           => 'disable_store_categories',
                'element'      => 'checkbox',
                'label'        => $this->_('Disable Store Categories'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check above to disable the option for sellers to select a category for their store.'),
            ),
            array(
                'form_id'      => 'stores_settings',
                'id'           => 'custom_stores_categories',
                'element'      => 'checkbox',
                'label'        => $this->_('Custom Stores Categories'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check above to allow users to create custom categories.'),
            ),
            array(
                'form_id'      => 'stores_settings',
                'id'           => 'enable_auctions_in_stores',
                'element'      => 'checkbox',
                'label'        => $this->_('Allow Auctions in Stores'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check above to allow users to list auctions in stores. '
                    . 'By default, only products can be listed.'),
            ),
            array(
                'form_id'      => 'stores_settings',
                'id'           => 'stores_force_list_in_both',
                'element'      => ($settings['enable_products']) ? 'checkbox' : 'hidden',
                'label'        => $this->_('Disable List In Select Box'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check above to disable the List In selector. In this case, all listings will be listed both in site and store.'),
            ),
            array(
                'form_id'     => 'stores_settings',
                'id'          => 'force_stores',
                'element'     => 'text',
                'label'       => $this->_('Force Stores'),
                'suffix'      => $this->_('listings'),
                'description' => $this->_('Enter the number of listings a seller can list before being required to open a store. Leave empty to disable the feature.'),
                'attributes'  => array(
                    'class' => 'form-control input-mini',
                ),
                'validators'  => array(
                    'Digits',
                ),
            ),
            /**
             * ++++++++++++++
             * TAX SETTINGS
             * ++++++++++++++
             */
            array(
                'form_id'      => 'tax_settings',
                'id'           => 'enable_tax_fees',
                'element'      => 'checkbox',
                'label'        => $this->_('Apply Tax on Site Fees'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check above to apply tax on site fees.'),
            ),
            array(
                'form_id'      => 'tax_settings',
                'id'           => 'tax_fees_type',
                'element'      => '\\Ppb\\Form\\Element\\Selectize',
                'label'        => $this->_('Tax Types'),
                'description'  => $this->_('Select the tax types that will be applied to site fees.'),
                'multiOptions' => $this->getTaxTypes()->getMultiOptions(),
                'attributes'   => array(
                    'id'          => 'selectizeTaxTypes',
                    'class'       => 'form-control input-large',
                    'placeholder' => $translate->_('Choose Tax Types ...'),
                ),
                'required'     => ($this->getData('enable_tax_fees')) ? true : false,
                'multiple'     => true,
                'dataUrl'      => Selectize::NO_REMOTE,
            ),
            array(
                'form_id'      => 'tax_settings',
                'id'           => 'enable_tax_listings',
                'element'      => 'checkbox',
                'label'        => $this->_('Allow Sellers to Apply Tax'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check above to allow sellers to apply tax on their listings.'),
            ),
            /**
             * ++++++++++++++
             * POSTMEN SHIPPING API
             * ++++++++++++++
             */
            array(
                'form_id'      => 'postmen',
                'id'           => 'enable_postmen',
                'element'      => 'checkbox',
                'label'        => $this->_('Enable Postmen Shipping API'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check above to enable the Postmen Shipping API. Sellers will be able to create Postmen Shipper accounts and use their selected shipping carriers through this module.'),
            ),
            /**
             * --------------
             * SITE FEES MANAGEMENT
             * --------------
             */
            array(
                'form_id'      => 'fees_category',
                'id'           => 'category_id',
                'element'      => 'select',
                'label'        => $this->_('Select Category'),
                'multiOptions' => $this->getCategories()->getMultiOptions("parent_id IS NULL AND custom_fees='1'", null,
                    $translate->_('Default')),
                'attributes'   => array(
                    'class' => 'form-control input-medium',
                    'id'    => 'category-selector',
                ),
                'bodyCode'     => "
                    <script type=\"text/javascript\">
                        $(document).ready(function() { 
                            $('#category-selector').change(function() { 
                                var categoryId = $(this).val();
                                var action = $(this).closest('form').attr('action');
                                var url = action.replace(/(\/category_id)(\/[0-9]+)/g, '');
                                
                                $(location).attr('href', url + '/category_id/' + categoryId);
                            });
                        });
                    </script>",
            ),
            /**
             * ++++++++++++++
             * USER SIGN-UP
             * ++++++++++++++
             */
            array(
                'form_id'     => 'signup',
                'id'          => Fees::SIGNUP,
                'element'     => '\\Ppb\\Form\\Element\\LocalizedNumeric',
                'label'       => $this->_('User Sign-up Fee'),
                'prefix'      => $settings['currency'],
                'description' => $this->_('Enter an amount in the box to charge users for signing-up.'),
                'attributes'  => array(
                    'class' => 'form-control input-mini',
                ),
                'validators'  => array(
                    'Numeric',
                ),
                'filters'     => array(
                    '\\Ppb\\Filter\\LocalizedNumeric',
                ),
            ),
            /**
             * ++++++++++++++
             * SALE FEE PAYER RADIO BUTTON
             * ++++++++++++++
             */
            array(
                'form_id'      => 'sale',
                'id'           => 'sale_fee_payer',
                'element'      => 'radio',
                'label'        => $this->_('Paid By'),
                'multiOptions' => array(
                    'buyer'  => array(
                        $translate->_('Buyer'),
                    ),
                    'seller' => array(
                        $translate->_('Seller'),
                    ),
                ),
                'description'  => $this->_('Select which party would pay for the sale transaction fee.'),
            ),
            /**
             * ++++++++++++++
             * TIERS FEES TABLES
             * ++++++++++++++
             */
            array(
                'form_id'  => 'tiers',
                'id'       => 'id',
                'element'  => 'hidden',
                'multiple' => true,
            ),
            array(
                'form_id'  => 'tiers',
                'id'       => 'delete',
                'element'  => 'checkbox',
                'multiple' => true,
            ),
            array(
                'form_id'    => 'tiers',
                'id'         => 'amount',
                'element'    => '\\Ppb\\Form\\Element\\LocalizedNumeric',
                'label'      => $this->_('Fee Amount'),
                'multiple'   => true,
                'attributes' => array(
                    'class' => 'form-control input-mini',
                ),
                'validators' => array(//                    'Numeric',
                ),
                'filters'    => array(
                    '\\Ppb\\Filter\\LocalizedNumeric',
                ),
            ),
            array(
                'form_id'        => 'tiers',
                'id'             => 'calculation_type',
                'element'        => 'select',
                'label'          => $this->_('Calculation Type'),
                'simpleMultiple' => true,
                'multiOptions'   => array(
                    'flat'    => $settings['currency'],
                    'percent' => '%',
                ),
                'attributes'     => array(
                    'class' => 'form-control input-small',
                ),
            ),
            array(
                'form_id'    => 'tiers',
                'id'         => 'tier_from',
                'element'    => '\\Ppb\\Form\\Element\\LocalizedNumeric',
                'label'      => $this->_('Range From'),
                'multiple'   => true,
                'prefix'     => $settings['currency'],
                'attributes' => array(
                    'class' => 'form-control input-small',
                ),
                'validators' => array(//                    'Numeric',
                ),
                'filters'    => array(
                    '\\Ppb\\Filter\\LocalizedNumeric',
                ),
            ),
            array(
                'form_id'    => 'tiers',
                'id'         => 'tier_to',
                'element'    => '\\Ppb\\Form\\Element\\LocalizedNumeric',
                'label'      => $this->_('Range To'),
                'multiple'   => true,
                'prefix'     => $settings['currency'],
                'attributes' => array(
                    'class' => 'form-control input-small',
                ),
                'validators' => array(//                    'Numeric',
                ),
                'filters'    => array(
                    '\\Ppb\\Filter\\LocalizedNumeric',
                ),
            ),
            /**
             * ++++++++++++++
             * HOME PAGE FEATURED LISTINGS
             * ++++++++++++++
             */
            array(
                'form_id'     => 'hpfeat',
                'id'          => Fees::HPFEAT,
                'element'     => '\\Ppb\\Form\\Element\\LocalizedNumeric',
                'label'       => $this->_('Home Page Featured Fee'),
                'prefix'      => $settings['currency'],
                'description' => $this->_('Enter a fee that will apply when listing home page featured items.'),
                'attributes'  => array(
                    'class' => 'form-control input-mini',
                ),
                'validators'  => array(
                    'Numeric',
                ),
                'filters'     => array(
                    '\\Ppb\\Filter\\LocalizedNumeric',
                ),
            ),
            /**
             * ++++++++++++++
             * CATEGORY PAGES FEATURED LISTINGS
             * ++++++++++++++
             */
            array(
                'form_id'     => 'catfeat',
                'id'          => Fees::CATFEAT,
                'element'     => '\\Ppb\\Form\\Element\\LocalizedNumeric',
                'label'       => $this->_('Category Pages Featured Fee'),
                'prefix'      => $settings['currency'],
                'description' => $this->_('Enter a fee that will apply when listing category pages featured items.'),
                'attributes'  => array(
                    'class' => 'form-control input-mini',
                ),
                'validators'  => array(
                    'Numeric',
                ),
                'filters'     => array(
                    '\\Ppb\\Filter\\LocalizedNumeric',
                ),
            ),
            /**
             * ++++++++++++++
             * HIGHLIGHTED LISTINGS
             * ++++++++++++++
             */
            array(
                'form_id'     => 'highlighted',
                'id'          => Fees::HIGHLIGHTED,
                'element'     => '\\Ppb\\Form\\Element\\LocalizedNumeric',
                'label'       => $this->_('Highlighted Listing Fee'),
                'prefix'      => $settings['currency'],
                'description' => $this->_('Enter a fee that will apply when enabling the highlighted listing feature.'),
                'attributes'  => array(
                    'class' => 'form-control input-mini',
                ),
                'validators'  => array(
                    'Numeric',
                ),
                'filters'     => array(
                    '\\Ppb\\Filter\\LocalizedNumeric',
                ),
            ),
            /**
             * ++++++++++++++
             * LISTING SHORT DESCRIPTION
             * ++++++++++++++
             */
            array(
                'form_id'     => 'short_description',
                'id'          => Fees::SHORT_DESCRIPTION,
                'element'     => '\\Ppb\\Form\\Element\\LocalizedNumeric',
                'label'       => $this->_('Listing Short Description Fee'),
                'prefix'      => $settings['currency'],
                'description' => $this->_('Enter a fee that will apply when adding a short description for a listing.'),
                'attributes'  => array(
                    'class' => 'form-control input-mini',
                ),
                'validators'  => array(
                    'Numeric',
                ),
                'filters'     => array(
                    '\\Ppb\\Filter\\LocalizedNumeric',
                ),
            ),
            /**
             * ++++++++++++++
             * LISTING IMAGES
             * ++++++++++++++
             */
            array(
                'form_id'     => 'images',
                'id'          => Fees::IMAGES,
                'element'     => '\\Ppb\\Form\\Element\\LocalizedNumeric',
                'label'       => $this->_('Images Upload Fee'),
                'prefix'      => $settings['currency'],
                'description' => $this->_('Enter a fee that will be charged for each image uploaded with a listing.'),
                'attributes'  => array(
                    'class' => 'form-control input-mini',
                ),
                'validators'  => array(
                    'Numeric',
                ),
                'filters'     => array(
                    '\\Ppb\\Filter\\LocalizedNumeric',
                ),
            ),
            array(
                'form_id'     => 'images',
                'id'          => Fees::NB_FREE_IMAGES,
                'element'     => 'text',
                'label'       => $this->_('Free Images'),
                'description' => $this->_('Enter the number of free images that can be uploaded with a listing.'),
                'attributes'  => array(
                    'class' => 'form-control input-mini',
                ),
                'validators'  => array(
                    'Digits',
                ),
                'filters'     => array(
                    'Digits',
                ),
            ),
            /**
             * ++++++++++++++
             * MEDIA UPLOAD
             * ++++++++++++++
             */
            array(
                'form_id'     => 'media',
                'id'          => Fees::MEDIA,
                'element'     => '\\Ppb\\Form\\Element\\LocalizedNumeric',
                'label'       => $this->_('Media Upload Fee'),
                'prefix'      => $settings['currency'],
                'description' => $this->_('Enter a fee that will apply when adding media to a listing.'),
                'attributes'  => array(
                    'class' => 'form-control input-mini',
                ),
                'validators'  => array(
                    'Numeric',
                ),
                'filters'     => array(
                    '\\Ppb\\Filter\\LocalizedNumeric',
                ),
            ),
            array(
                'form_id'     => 'media',
                'id'          => Fees::NB_FREE_MEDIA,
                'element'     => 'text',
                'label'       => $this->_('Free Media Items'),
                'description' => $this->_('Enter the number of free media items that can be uploaded with a listing.'),
                'attributes'  => array(
                    'class' => 'form-control input-mini',
                ),
                'validators'  => array(
                    'Digits',
                ),
                'filters'     => array(
                    'Digits',
                ),
            ),
            /**
             * ++++++++++++++
             * DIGITAL DOWNLOADS
             * ++++++++++++++
             */
            array(
                'form_id'     => 'digital_downloads_fee',
                'id'          => Fees::DIGITAL_DOWNLOADS,
                'element'     => '\\Ppb\\Form\\Element\\LocalizedNumeric',
                'label'       => $this->_('Digital Downloads Fee'),
                'prefix'      => $settings['currency'],
                'description' => $this->_('Enter a fee that will apply when creating a listing having digital download option enabled.'),
                'attributes'  => array(
                    'class' => 'form-control input-mini',
                ),
                'validators'  => array(
                    'Numeric',
                ),
                'filters'     => array(
                    '\\Ppb\\Filter\\LocalizedNumeric',
                ),
            ),
            array(
                'form_id'     => 'digital_downloads_fee',
                'id'          => Fees::NB_FREE_DOWNLOADS,
                'element'     => 'text',
                'label'       => $this->_('Free Digital Downloads'),
                'description' => $this->_('Enter the number of free digital downloads that can be uploaded with a listing.'),
                'attributes'  => array(
                    'class' => 'form-control input-mini',
                ),
                'validators'  => array(
                    'Digits',
                ),
                'filters'     => array(
                    'Digits',
                ),
            ),
            /**
             * ++++++++++++++
             * ADDITIONAL CATEGORY LISTING
             * ++++++++++++++
             */
            array(
                'form_id'     => 'addl_category',
                'id'          => Fees::ADDL_CATEGORY,
                'element'     => '\\Ppb\\Form\\Element\\LocalizedNumeric',
                'label'       => $this->_('Additional Category Listing Fee'),
                'prefix'      => $settings['currency'],
                'description' => $this->_('Enter a fee that will apply when listing an item in more than one category.'),
                'attributes'  => array(
                    'class' => 'form-control input-mini',
                ),
                'validators'  => array(
                    'Numeric',
                ),
                'filters'     => array(
                    '\\Ppb\\Filter\\LocalizedNumeric',
                ),
            ),
            /**
             * ++++++++++++++
             * BUY OUT FEE
             * ++++++++++++++
             */
            array(
                'form_id'     => 'buyout',
                'id'          => Fees::BUYOUT,
                'element'     => '\\Ppb\\Form\\Element\\LocalizedNumeric',
                'label'       => $this->_('Buy Out Fee'),
                'prefix'      => $settings['currency'],
                'description' => $this->_('Enter a fee that will apply when listing an item with buy out enabled.'),
                'attributes'  => array(
                    'class' => 'form-control input-mini',
                ),
                'validators'  => array(
                    'Numeric',
                ),
                'filters'     => array(
                    '\\Ppb\\Filter\\LocalizedNumeric',
                ),
            ),
            /**
             * ++++++++++++++
             * RESERVE PRICE FEE
             * ++++++++++++++
             */
            array(
                'form_id'     => 'reserve_price',
                'id'          => Fees::RESERVE,
                'element'     => '\\Ppb\\Form\\Element\\LocalizedNumeric',
                'label'       => $this->_('Reserve Price Fee'),
                'prefix'      => $settings['currency'],
                'description' => $this->_('Enter a fee that will apply when enabling reserve price on an auction.'),
                'attributes'  => array(
                    'class' => 'form-control input-mini',
                ),
                'validators'  => array(
                    'Numeric',
                ),
                'filters'     => array(
                    '\\Ppb\\Filter\\LocalizedNumeric',
                ),
            ),
            /**
             * ++++++++++++++
             * MAKE OFFER FEE
             * ++++++++++++++
             */
            array(
                'form_id'     => 'make_offer_fee',
                'id'          => Fees::MAKE_OFFER,
                'element'     => '\\Ppb\\Form\\Element\\LocalizedNumeric',
                'label'       => $this->_('Make Offer Fee'),
                'prefix'      => $settings['currency'],
                'description' => $this->_('Enter a fee that will apply when listing an item with make offer enabled.'),
                'attributes'  => array(
                    'class' => 'form-control input-mini',
                ),
                'validators'  => array(
                    'Numeric',
                ),
                'filters'     => array(
                    '\\Ppb\\Filter\\LocalizedNumeric',
                ),
            ),
            /**
             * ==============
             * CLASSIFIEDS FEES
             * ==============
             */
            /**
             * ++++++++++++++
             * CLASSIFIED SETUP FEE
             * ++++++++++++++
             */
            array(
                'form_id'     => 'classified_setup',
                'id'          => Fees::CLASSIFIED_SETUP,
                'element'     => '\\Ppb\\Form\\Element\\LocalizedNumeric',
                'label'       => $this->_('Classified Setup Fee'),
                'prefix'      => $settings['currency'],
                'description' => $this->_('Enter a fee that will apply when listing a classified.'),
                'attributes'  => array(
                    'class' => 'form-control input-mini',
                ),
                'validators'  => array(
                    'Numeric',
                ),
                'filters'     => array(
                    '\\Ppb\\Filter\\LocalizedNumeric',
                ),
            ),
        );

        return $this->_arrayMergeOrdering($elements, parent::getRelatedElements());
    }

    protected function _cachingEngineMultiOptions()
    {
        $translate = $this->getTranslate();

        $result = array(
            ''      => array(
                $translate->_('Off'),
                $translate->_('Disables the caching engine. Metadata caching will still be enabled, using the Files adapter.')
            ),
            'Files' => array(
                $translate->_('Files'),
                $translate->_('Uses static files for caching.')
            ),
            'Table' => array(
                $translate->_('Table'),
                $translate->_('Uses the database for caching (slower than the Files adapter).'),
            ),

        );

        $result['Memcache'] = array(
            $translate->_('Memcache'),
        );

        if (!CacheAdapter\Memcache::enabled()) {
            $result['Memcache'][] = '<span class="text-danger">' . $translate->_('Memcache is not available on your server. Please contact your server admin for information on how to enable the module.') . '</span>';
            $result['Memcache'][] = array(
                'disabled' => 'disabled'
            );
        }
        else {
            $result['Memcache'][] = $translate->_('Uses the memcache module for caching (recommended).');
        }

        if (CacheAdapter\Apc::enabled()) {
            $result['Apc'] = array(
                $translate->_('Apc'),
                $translate->_('Uses the Alternative PHP Cache (APC) module for caching.'),
            );
        }

        return $result;
    }

}

