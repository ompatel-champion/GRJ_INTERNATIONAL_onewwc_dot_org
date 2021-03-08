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
 * ebay users table service class
 */
/**
 * MOD:- EBAY IMPORTER
 */

namespace Ppb\Service;

use Cube\Db\Expr,
    Ppb\Db\Table\EbayUsers as EbayUsersTable;

class EbayUsers extends AbstractService
{

    /**
     *
     * class constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->setTable(
            new EbayUsersTable());
    }

    /**
     *
     * create or update a table row
     *
     * @param array $data
     *
     * @return int the id of the saved row
     */
    public function save($data)
    {
        $row = null;

        $data = $this->_prepareSaveData($data);

        if (array_key_exists('id', $data)) {
            $select = $this->_table->select()
                ->where("id = ?", $data['id']);

            unset($data['id']);

            $row = $this->_table->fetchRow($select);
        }

        if (count($row) > 0) {

            $data['updated_at'] = new Expr('now()');
            $this->_table->update($data, "id='{$row['id']}'");

            $id = $row['id'];
        }
        else {
            $data['created_at'] = new Expr('now()');
            $this->_table->insert($data);

            $id = $this->_table->lastInsertId();
        }

        return $id;
    }

    /**
     *
     * get ebay user row based on username (and user id)
     *
     * @param string $ebayUsername
     * @param int    $userId
     *
     * @return \Cube\Db\Table\Row|null
     */
    public function findUser($ebayUsername, $userId = null)
    {
        $select = $this->getTable()->select()
            ->where('ebay_username = ?', $ebayUsername);

        if ($userId !== null) {
            $select->where('user_id = ?', $userId);
        }

        return $this->getTable()->fetchRow($select);
    }

    /**
     *
     * delete a row from the table
     *
     * @param integer $id the id of the row
     *
     * @return integer     returns the number of affected rows
     */
    public function delete($id)
    {
        return $this->_table->delete(
            $this->_table->getAdapter()->quoteInto('id = ?', $id));
    }
}

