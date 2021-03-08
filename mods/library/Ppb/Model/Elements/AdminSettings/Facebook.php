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
 * MOD:- FACEBOOK LOGIN
 */

namespace Ppb\Model\Elements\AdminSettings;

use Ppb\Model\Elements\AbstractElements;

class Facebook extends AbstractElements
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
            ## -- START :: ADD -- [ MOD:- FACEBOOK LOGIN ]
            /**
             * MOD:- FACEBOOK LOGIN
             */
            array(
                'form_id'      => 'facebook_login',
                'id'           => 'enable_facebook_login',
                'element'      => 'checkbox',
                'label'        => $this->_('Enable Facebook Login'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check the above checkbox in order to enable the Facebook login option on your website.'),
            ),
            array(
                'form_id'    => 'facebook_login',
                'id'         => 'facebook_app_id',
                'element'    => 'text',
                'label'      => $this->_('Facebook App ID'),
                'attributes' => array(
                    'class' => 'form-control input-xlarge',
                ),
            ),
            array(
                'form_id'     => 'facebook_login',
                'id'          => 'facebook_app_secret',
                'element'     => 'text',
                'label'       => $this->_('Facebook App Secret'),
                'attributes'  => array(
                    'class' => 'form-control input-xlarge',
                ),
                'description' => $this->_('In order to use the Facebook Login option, you will first need to create a new website app at: <br>'
                    . '<a href="http://developers.facebook.com/setup" target="_blank">http://developers.facebook.com/setup</a><br>'
                    . 'When prompted to choose a category, select "Apps for pages".<br>'
                    . 'After you have successfully created the app, please copy the App ID and App Secret in the fields above.'),
            ),
            ## -- END :: ADD -- [ MOD:- FACEBOOK LOGIN ]
        );
    }
}

