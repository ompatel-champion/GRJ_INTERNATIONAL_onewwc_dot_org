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
 * users statistics table service class
 */

namespace Ppb\Service;

use Ppb\Db\Table\UsersStatistics as UsersStatisticsTable,
    Ppb\Model\Elements,
    Cube\Db\Expr;

class UsersStatistics extends AbstractService
{

    /**
     *
     * class constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->setTable(
            new UsersStatisticsTable());
    }

    /**
     *
     * create or update a stat row
     * if a row that matches the ip, user agent and accept language exists in the table, update the row
     * otherwise insert a new row
     *
     * @param array $data
     *
     * @return $this
     */
    public function save($data)
    {
        $row = null;

        $table = $this->getTable();

        $data = $this->_prepareSaveData($data);

        $select = $table->select()
            ->where('remote_addr = ?', $data['remote_addr'])
            ->where('http_user_agent = ?', $data['http_user_agent']);

        $row = $table->fetchRow($select);

        $data['updated_at'] = new Expr('now()');

        if ($row !== null) {
            if (isset($data['http_referrer'])) {
                unset($data['http_referrer']);
            }

            $table->update($data,
                $table->getAdapter()->quoteInto('id = ?', $row['id']));
        }
        else {
            $data['created_at'] = new Expr('now()');
            $table->insert($data);
        }

        return $this;
    }

}

