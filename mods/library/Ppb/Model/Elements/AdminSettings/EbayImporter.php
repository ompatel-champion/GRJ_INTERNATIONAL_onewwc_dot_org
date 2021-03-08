<?php

/**
 *
 * PHP Pro Bid
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2018 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     8.0 [rev.8.0.07]
 */

/**
 * sample class that will generate extra elements for the admin elements model
 * we can have a multiple number of such classes, they just need to have a different name
 * any elements in this class will override original elements
 */
/**
 * MOD:- EBAY IMPORTER
 *
 * @version 4.0
 */

namespace Ppb\Model\Elements\AdminSettings;

use Ppb\Model\Elements\AbstractElements;
## -- START :: ADD -- [ MOD:- EBAY IMPORTER ]
use Ppb\Service\EbayAPI as EbayAPIService,
    Ppb\Service\Table\Durations as DurationsService;

class EbayImporter extends AbstractElements
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
     * get model elements
     *
     * @return array
     */
    public function getElements()
    {
        $durationsService = new DurationsService();

        $translate = $this->getTranslate();

        return array(
            /**
             * ++++++++++++++
             * IMPORT EBAY LISTINGS USING THE EBAY API
             * ++++++++++++++
             */
            array(
                'form_id'      => 'ebay_import_tool',
                'id'           => 'enable_ebay_importer',
                'element'      => 'checkbox',
                'label'        => $this->_('Enable Ebay Import Tool'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check the above checkbox to enable the ebay import tool.<br>'
                    . 'You obtain an Application ID by joining the <a href="https://developer.ebay.com/join/default.aspx" target="_blank">eBay Developers Program</a>'),
            ),
            array(
                'form_id'    => 'ebay_import_tool',
                'subtitle'   => $this->_('Application Settings'),
                'id'         => EbayAPIService::APP_ID,
                'element'    => 'text',
                'required'   => ($this->getData('enable_ebay_importer')) ? true : false,
                'label'      => $this->_('App ID'),
                'attributes' => array(
                    'class' => 'form-control input-large',
                ),
            ),
            array(
                'form_id'    => 'ebay_import_tool',
                'id'         => EbayAPIService::DEV_ID,
                'element'    => 'text',
                'label'      => $this->_('Dev ID'),
                'attributes' => array(
                    'class' => 'form-control input-large',
                ),
            ),
            array(
                'form_id'    => 'ebay_import_tool',
                'id'         => EbayAPIService::CERT_ID,
                'element'    => 'text',
                'label'      => $this->_('Cert ID'),
                'attributes' => array(
                    'class' => 'form-control input-large',
                ),
            ),

            array(
                'form_id'      => 'ebay_import_tool',
                'subtitle'     => $this->_('Application Level Settings'),
                'id'           => 'ebay_account_verification',
                'element'      => 'checkbox',
                'label'        => $this->_('Ebay Account Verification'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check the above checkbox to require your users to verify their ebay accounts before being able to import their ebay listings.'),
            ),
            array(
                'form_id'     => 'ebay_import_tool',
                'id'          => EbayAPIService::RUNAME,
                'element'     => 'text',
                'label'       => $this->_('Ebay App RuName'),
                'description' => sprintf($translate->_('Enter the RuName used by the ebay app. <br>'
                    . 'Required if ebay account verification is enabled. <br>'
                    . 'To get more information on generating a RuName, '
                    . '<a href="http://developer.ebay.com/DevZone/xml/docs/HowTo/Tokens/GettingTokens.html#step1" target="_blank">click here</a>.<br>'
                    . '<strong>Ebay Sign-in Settings</strong><br>'
                    . 'Your auth accepted URL: <br>%s<br>'
                    . 'Your auth declined URL: <br>%s'),
                    $this->getView()->url(array('module' => 'members', 'controller' => 'tools', 'action' => 'ebay-generate-token')),
                    $this->getView()->url(array('module' => 'members', 'controller' => 'tools', 'action' => 'ebay-import'))
                ),
                'required'    => ($this->getData('ebay_account_verification')) ? true : false,
                'attributes'  => array(
                    'class' => 'form-control input-large',
                ),
            ),
            array(
                'form_id'     => 'ebay_import_tool',
                'id'          => 'ebay_default_category_id',
                'element'     => '\\Ppb\\Form\\Element\\Category',
                'label'       => $this->_('Default Import Category'),
                'description' => sprintf(
                    $translate->_('Select a default category that will be used for all imported items that have no corresponding category on your website. <br>'
                        . 'To define ebay - local category pairings, <a href="%s" target="_blank">click here</a>.'),
                    $this->getView()->url('admin/tables/table/EbayCategories')),
                'attributes'  => array(
                    'data-no-refresh' => 'true'
                ),
                'required'    => true,
            ),
            array(
                'form_id'      => 'ebay_import_tool',
                'id'           => 'ebay_default_auction_duration',
                'element'      => 'select',
                'label'        => $this->_('Default Duration for Auctions'),
                'multiOptions' => $durationsService->getMultiOptions('auction'),
                'description'  => $this->_('Select a default duration that will be used for imported auctions.'),
                'attributes'   => array(
                    'class' => 'form-control input-medium',
                ),
            ),
            array(
                'form_id'      => 'ebay_import_tool',
                'id'           => 'ebay_strip_description_html',
                'element'      => 'checkbox',
                'label'        => $this->_('Strip Description HTML'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check above to remove HTML code from the description field.'),
            ),
        );
    }
}

