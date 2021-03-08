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
 * content menus table
 */

namespace Ppb\Db\Table;

use Cube\Db\Table\AbstractTable;

class ContentMenus extends AbstractTable
{

    /**
     *
     * table name
     * 
     * @var string
     */
    protected $_name = 'content_menus';

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
    protected $_rowClass = '\Ppb\Db\Table\Row\ContentMenu';

    /**
     * class name for rowset
     *
     * @var string
     */
    protected $_rowsetClass = '\Ppb\Db\Table\Rowset\ContentMenus';

}