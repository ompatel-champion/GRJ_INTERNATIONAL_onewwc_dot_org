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
 * transactions table service class
 */

namespace Ppb\Service;

use Ppb\Db\Table\Transactions as TransactionsTable,
    Cube\Db\Expr;

class Transactions extends AbstractService
{

    /**
     *
     * class constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->setTable(
            new TransactionsTable());
    }

    /**
     *
     * create or update a transaction
     *
     * @param array $data
     *
     * @return int          the id of the inserted/updated row
     */
    public function save($data)
    {
        $row = null;

        $table = $this->getTable();

        $data = $this->_prepareSaveData($data);

        if (array_key_exists('id', $data)) {
            $select = $table->select()
                ->where("id = ?", $data['id']);

            unset($data['id']);

            $row = $table->fetchRow($select);
        }

        if ($row !== null) {
            $data['updated_at'] = new Expr('now()');
            $table->update($data,
                $table->getAdapter()->quoteInto('id = ?', $row['id']));

            $id = $row['id'];
        }
        else {
            $data['created_at'] = new Expr('now()');
            $table->insert($data);

            $id = $table->lastInsertId();
        }

        return $id;
    }

}

