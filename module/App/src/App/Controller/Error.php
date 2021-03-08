<?php

/**
 *
 * PHP Pro Bid
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2018 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     8.0 [rev.8.0.01]
 */

/**
 * error controller
 */

namespace App\Controller;

use Ppb\Controller\Action\AbstractAction;

class Error extends AbstractAction
{

    public function init()
    {
        $this->getResponse()
            ->setHeader(' ')
            ->setResponseCode(404);
    }

    public function NotFound()
    {

        return array(
            'headline' => $this->_('404 Error')
        );
    }

    public function Gone()
    {
        $this->getResponse()
            ->setResponseCode(410);

        return array(
            'headline' => $this->_('410 Gone')
        );
    }

}

