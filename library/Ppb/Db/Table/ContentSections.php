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
 * content sections table
 */

namespace Ppb\Db\Table;

use Cube\Db\Table\AbstractTable;

class ContentSections extends AbstractTable
{

    /**
     *
     * table name
     * 
     * @var string
     */
    protected $_name = 'content_sections';

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
    protected $_rowClass = '\Ppb\Db\Table\Row\ContentSection';

    /**
     * class name for rowset
     *
     * @var string
     */
    protected $_rowsetClass = '\Ppb\Db\Table\Rowset\ContentSections';

    /**
     *
     * reference map
     * 
     * @var array
     */
    protected $_referenceMap = array(
        'Parent' => array(
            self::COLUMNS => 'parent_id',
            self::REF_TABLE_CLASS => '\Ppb\Db\Table\ContentSections',
            self::REF_COLUMNS => 'id',
        ),
    );

    /**
     *
     * dependent tables
     * 
     * @var array
     */
    protected $_dependentTables = array(
        '\Ppb\Db\Table\ContentSections',
        '\Ppb\Db\Table\ContentEntries',
    );

}