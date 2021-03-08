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
 * adverts table rowset class
 */

namespace Ppb\Db\Table\Rowset;

class Adverts extends AbstractRowset
{

    /**
     *
     * row object class
     * 
     * @var string
     */
    protected $_rowClass = '\Ppb\Db\Table\Row\Advert';

    /**
     *
     * count number of views
     *
     * @return $this
     */
    public function addView()
    {
        /** @var \Ppb\Db\Table\Row\Advert $advert */
        foreach ($this as $advert) {
            $advert->addView();
        }

        return $this;
    }

}

