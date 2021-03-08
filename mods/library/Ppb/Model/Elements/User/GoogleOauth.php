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
 * MOD:- GOOGLE OAUTH LOGIN
 */

namespace Ppb\Model\Elements\User;

use Ppb\Model\Elements\AbstractElements;

class GoogleOauth extends AbstractElements
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
                'form_id' => array('basic', 'admin'),
                'id'      => 'google_oauth_id',
                'element' => 'hidden',
            ),
        );
    }
}

