<?php

/**
 * 
 * PHP Pro Bid
 * 
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 * 
 * @version     7.0
 */
/**
 * members module - preferences management controller
 */

namespace Members\Controller;

use Members\Controller\Action\AbstractAction;

class Preferences extends AbstractAction
{

    public function Email()
    {
        return array(
            'headline' => 'Mail Preferences',
        );
    }

}

