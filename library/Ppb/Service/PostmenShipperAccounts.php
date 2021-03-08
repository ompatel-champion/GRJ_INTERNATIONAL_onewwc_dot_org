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
 * postmen shipper accounts table service class
 */

namespace Ppb\Service;

use Ppb\Db\Table\PostmenShipperAccounts as PostmenShipperAccountsTable;

class PostmenShipperAccounts extends AbstractService
{

    /**
     *
     * class constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->setTable(
            new PostmenShipperAccountsTable());
    }

    /**
     *
     * delete all of a user's shipper accounts
     *
     * @param int $userId
     *
     * @return int
     */
    public function deleteByUserId($userId)
    {
        $table = $this->getTable();
        $adapter = $table->getAdapter();

        $where[] = $adapter->quoteInto('user_id = ?', $userId);

        return $table->delete($where);
    }

}

