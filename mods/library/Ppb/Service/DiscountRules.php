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
 * discount rules table service class
 */
/**
 * MOD:- DISCOUNT RULES
 *
 * @version 2.1
 */

namespace Ppb\Service;

use Cube\Db\Expr,
    Ppb\Db\Table;

class DiscountRules extends AbstractService
{

    /**
     *
     * class constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->setTable(
            new Table\DiscountRules());
    }

    /**
     *
     * create or update a discount rule
     *
     * @param array $data
     *
     * @return $this
     */
    public function save($data)
    {
        $row = null;

        $data = $this->_prepareSaveData($data);

        if (!empty($data['id'])) {
            $select = $this->_table->select()
                ->where("id = ?", $data['id']);

            unset($data['id']);

            $row = $this->_table->fetchRow($select);
        }

        if (count($row) > 0) {

            $data['updated_at'] = new Expr('now()');
            $this->_table->update($data, "id='{$row['id']}'");
        }
        else {
            $data['created_at'] = new Expr('now()');
            $this->_table->insert($data);
        }

        return $this;
    }

    /**
     *
     * prepare listing data for when saving to the table
     *
     * @param array $data
     *
     * @return array
     */
    protected function _prepareSaveData($data = array())
    {

        if ($data['start_date'] == '') {
            $data['start_date'] = new Expr('null');
        }

        if ($data['expiration_date'] == '') {
            $data['expiration_date'] = new Expr('null');
        }

        return parent::_prepareSaveData($data);
    }

    /**
     *
     * delete a discount rule row from the table
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

