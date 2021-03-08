<?php

/**
 * 
 * PHP Pro Bid
 * 
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2019 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     8.1 [rev.8.1.01]
 */
/**
 * tax types table
 */

namespace Ppb\Db\Table;

use Cube\Db\Table\AbstractTable;

class TaxTypes extends AbstractTable
{

    /**
     *
     * table name
     * 
     * @var string
     */
    protected $_name = 'tax_types';

    /**
     *
     * primary key
     * 
     * @var string
     */
    protected $_primary = 'id';

    /**
     *
     * class name for row
     *
     * @var string
     */
    protected $_rowClass = '\Ppb\Db\Table\Row\TaxType';

    /**
     * class name for rowset
     *
     * @var string
     */
    protected $_rowsetClass = '\Ppb\Db\Table\Rowset\TaxTypes';

    /**
     *
     * dependent tables
     *
     * @var array
     */
    protected $_dependentTables = array(
        '\Ppb\Db\Table\Listings',
    );

}