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
 * MOD:- PICKUP LOCATIONS
 */

namespace Ppb\Model\Elements\AdminSettings;

use Ppb\Model\Elements\AbstractElements;

class PickupLocations extends AbstractElements
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
            array(
                'form_id'     => 'google_api',
                'id'          => 'google_api_key',
                'element'     => 'text',
                'label'       => $this->_('Google API Key'),
                'description' => $this->_('To use the location search related functions, you will need to create a new Google Project at the '
                    . '<a href="https://console.developers.google.com" target="_blank">Google API Console</a> and enable the the Geocoding API.'),
                'validators'  => array(
                    'NoHtml',
                ),
                'attributes'  => array(
                    'class' => 'form-control input-xlarge',
                ),
            ),
        );
    }
}

