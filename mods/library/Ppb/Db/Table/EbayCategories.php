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
 * ebay categories table
 */
/**
 * MOD:- EBAY IMPORTER
 */

namespace Ppb\Db\Table;

use Cube\Db\Table\AbstractTable;

class EbayCategories extends AbstractTable
{

    /**
     *
     * table name
     * 
     * @var string
     */
    protected $_name = 'ebay_categories';

    /**
     *
     * primary key
     * 
     * @var string
     */
    protected $_primary = 'id';

}