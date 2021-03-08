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
 * blocked users table service class
 */

namespace Ppb\Service;

use Ppb\Db\Table\BlockedUsers as BlockedUsersTable,
    Ppb\Db\Table\Row\BlockedUser as BlockedUserModel;

class BlockedUsers extends AbstractService
{

    /**
     *
     * class constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->setTable(
            new BlockedUsersTable());
    }

    /**
     *
     * check if a user is blocked on a certain action
     * if true, then return the first matching blocked user object for display purposes
     *
     * @param string   $blockedAction (register, messaging, purchase)
     * @param array    $dataToBlock
     * @param int|null $blockerId
     *
     * @return \Ppb\Db\Table\Row\BlockedUser|null
     */
    public function check($blockedAction, $dataToBlock = array(), $blockerId = null)
    {
        $select = $this->_table->select();
        $adapter = $this->_table->getAdapter();

        if ($blockerId !== null) {
            $select->where('user_id = ? or user_id is null', $blockerId);
        }
        else {
            $select->where('user_id is null');
        }

        $where = array();
        foreach ($dataToBlock as $key => $value) {
            if (array_key_exists($key, BlockedUserModel::$blockTypes)) {
                $where[] = '(' . implode(' AND ', array(
                        $adapter->quoteInto('type = ?', $key),
                        $adapter->quoteInto("value = ?", $value)
                    )) . ')';
            }
        }

        if (count($where) > 0) {
            $select->where(implode(' OR ', $where));
        }

        $select->where("blocked_actions REGEXP '\"" . $blockedAction . "\"'");

        return $this->fetchAll($select)->getRow(0);
    }

    /**
     *
     * delete a blocked user row from the table
     *
     * @param int $id     the id of the row to be deleted
     * @param int $userId the id of owner of the row
     *
     * @return int     returns the number of affected rows
     */
    public function delete($id, $userId = null)
    {
        $adapter = $this->_table->getAdapter();

        $where[] = $adapter->quoteInto('id = ?', $id);

        if ($userId !== null) {
            $where[] = $adapter->quoteInto('user_id = ?', $userId);
        }

        return $this->_table->delete($where);
    }
}

