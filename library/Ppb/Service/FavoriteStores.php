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
 * favorite stores table service class
 */

namespace Ppb\Service;

use Ppb\Db\Table\FavoriteStores as FavoriteStoresTable;

class FavoriteStores extends AbstractService
{

    /**
     *
     * class constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->setTable(
            new FavoriteStoresTable());
    }

    /**
     *
     * delete a favorite store row from the table
     *
     * @param int $id     the id of the row to be deleted
     * @param int $userId the id of owner of the row
     *
     * @return int     returns the number of affected rows
     */
    public function delete($id, $userId = null)
    {
        $table = $this->getTable();

        $adapter = $table->getAdapter();

        $where[] = $adapter->quoteInto('id = ?', $id);

        if ($userId !== null) {
            $where[] = $adapter->quoteInto('user_id = ?', $userId);
        }

        return $table->delete($where);
    }
}

