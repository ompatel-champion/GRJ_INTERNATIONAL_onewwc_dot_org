<?php

/**
 *
 * PHP Pro Bid
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2020 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     8.2 [rev.8.2.01]
 */

/**
 * content entries table service class
 */

namespace Ppb\Service;

use Ppb\Db\Table\ContentEntries as ContentEntriesTable;

class ContentEntries extends AbstractService
{

    /**
     *
     * class constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->setTable(
            new ContentEntriesTable());
    }
}

