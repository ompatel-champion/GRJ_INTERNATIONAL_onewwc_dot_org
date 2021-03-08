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
 * content entries table
 */

namespace Ppb\Db\Table;

use Cube\Db\Table\AbstractTable;

class ContentEntries extends AbstractTable
{

    /**
     *
     * table name
     *
     * @var string
     */
    protected $_name = 'content_entries';

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
    protected $_rowClass = '\Ppb\Db\Table\Row\ContentEntry';

    /**
     * class name for rowset
     *
     * @var string
     */
    protected $_rowsetClass = '\Ppb\Db\Table\Rowset\ContentEntries';

    /**
     *
     * reference map
     *
     * @var array
     */
    protected $_referenceMap = array(
        'Section' => array(
            self::COLUMNS         => 'section_id',
            self::REF_TABLE_CLASS => '\Ppb\Db\Table\ContentSections',
            self::REF_COLUMNS     => 'id',
        ),
        'User'    => array(
            self::COLUMNS         => 'user_id',
            self::REF_TABLE_CLASS => '\Ppb\Db\Table\Users',
            self::REF_COLUMNS     => 'id',
        ),
    );

}