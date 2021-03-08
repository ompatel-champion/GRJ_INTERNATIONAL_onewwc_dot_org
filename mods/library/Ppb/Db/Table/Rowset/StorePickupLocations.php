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
 * pickup locations table rowset class
 */
/**
 * MOD:- PICKUP LOCATIONS
 */
namespace Ppb\Db\Table\Rowset;

class StorePickupLocations extends AbstractRowset
{

    /**
     *
     * row object class
     *
     * @var string
     */
    protected $_rowClass = '\Ppb\Db\Table\Row\StorePickupLocation';

    public function toArray()
    {
        /** @var \Cube\Db\Table\Row\AbstractRow $row */
        foreach ($this->_rows as $i => $row) {
            $this->_data[$i] = $row;
        }

        return $this->_data;
    }
}

