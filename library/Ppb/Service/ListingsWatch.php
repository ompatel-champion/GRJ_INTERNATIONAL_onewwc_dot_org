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
 * listings watch table service class
 */

namespace Ppb\Service;

use Ppb\Db\Table\ListingsWatch as ListingsWatchTable;

class ListingsWatch extends AbstractService
{

    /**
     *
     * class constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->setTable(
            new ListingsWatchTable());
    }

    /**
     *
     * delete data from the table
     *
     * @param int|array $listingIds the id of the listing(s)
     * @param int       $userId     the id of the user that is watching the listing
     * @param string    $userToken  user token cookie
     *
     * @return int returns the number of affected rows
     */
    public function delete($listingIds, $userId = null, $userToken = null)
    {
        $table = $this->getTable();

        $adapter = $table->getAdapter();

        if (!is_array($listingIds)) {
            $listingIds = array($listingIds);
        }

        $where[] = $adapter->quoteInto('listing_id IN (?)', $listingIds);

        if ($userId !== null) {
            $where[] = 'user_token = "' . $userToken . '" OR user_id = "' . $userId . '"';
        }
        else if ($userToken !== null) {
            $where[] = $adapter->quoteInto('user_token = ?', $userToken);
        }

        return $table->delete($where);
    }
}

