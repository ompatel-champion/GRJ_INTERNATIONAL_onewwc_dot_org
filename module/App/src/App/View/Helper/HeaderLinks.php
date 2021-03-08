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
 * header links widget view helper class
 */

namespace App\View\Helper;

use Ppb\View\Helper\AbstractHelper;

class HeaderLinks extends AbstractHelper
{

    /**
     *
     * header links helper initialization class
     * always provide view partial to use
     *
     * @param string $partial
     *
     * @return $this
     */
    public function headerLinks($partial)
    {
        $this->setPartial($partial);

        return $this;
    }

    /**
     *
     * render partial
     *
     * @return string
     */
    public function render()
    {
        $partial = $this->getPartial();

        if (!empty($partial)) {
            return $this->getView()->process(
                $partial, true);
        }

        return '';
    }

}

