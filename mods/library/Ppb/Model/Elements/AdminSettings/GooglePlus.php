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
 * MOD:- GOOGLE PLUS LOGIN
 */

namespace Ppb\Model\Elements\AdminSettings;

use Ppb\Model\Elements\AbstractElements;

class GooglePlus extends AbstractElements
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
        return array(
            /**
             * MOD:- GOOGLE PLUS LOGIN
             */
            array(
                'form_id'      => 'google_plus_login',
                'id'           => 'enable_google_plus_login',
                'element'      => 'checkbox',
                'label'        => $this->_('Enable Google+ Login'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check the above checkbox in order to enable the Google+ login option on your website.'),
            ),
            array(
                'form_id'    => 'google_plus_login',
                'id'         => 'google_plus_client_id',
                'element'    => 'text',
                'label'      => $this->_('Client ID'),
                'attributes' => array(
                    'class' => 'form-control input-xlarge',
                ),
            ),
            array(
                'form_id'     => 'google_plus_login',
                'id'          => 'google_plus_client_secret',
                'element'     => 'text',
                'label'       => $this->_('Client Secret'),
                'attributes'  => array(
                    'class' => 'form-control input-xlarge',
                ),
                'description' => $this->_('In order to use the Google+ Login option, you will first need to create a new project at: <br>'
                    . '<a href="https://console.developers.google.com/" target="_blank">https://console.developers.google.com</a><br>'
                    . 'For more information on how to enable the Google+ API, '
                    . ' <a href="https://developers.google.com/+/web/samples/php#step_1_enable_the_google_api" target="_blank">click here</a>.'),
            ),
        );
    }
}

