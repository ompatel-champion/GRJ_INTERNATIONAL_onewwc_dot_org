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
 * MOD:- GOOGLE TRANSLATE
 */

namespace Ppb\Model\Elements\AdminSettings;

use Ppb\Model\Elements\AbstractElements;

class GoogleTranslate extends AbstractElements
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
                'form_id'      => 'user_languages',

                'id'           => 'google_translate',
                'element'      => 'checkbox',
                'label'        => $this->_('Google Translate'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check the above checkbox to enable to google translate plugin for your website.<br>'
                    . '<strong>Important</strong>: The plugin will override the manual translations module.'),
            ),
        );
    }
}

