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
 * settings table service class
 */

namespace Ppb\Service;

use Ppb\Db\Table\Settings as SettingsTable,
    Cube\Controller\Front;

class Settings extends AbstractService
{


    /**
     * media types
     */
    const TYPE_FAVICON = 'favicon';

    /**
     *
     * class constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->setTable(
            new SettingsTable());
    }

    /**
     *
     * save data in the settings table
     *
     * @param array $data
     *
     * @return $this
     */
    public function save($data)
    {
        $table = $this->getTable();

        foreach ($data as $key => $value) {
            $row = $table->fetchRow(
                $table->getAdapter()->quoteInto('name = ?', $key)
            );

            if (is_array($value)) {
                $value = serialize($value);
            }

            if ($row !== null) {
                $table->update(array('value' => $value), $table->getAdapter()->quoteInto('name = ?', $key));
            }
            else {
                $table->insert(array('name' => $key, 'value' => $value));
            }
        }

        return $this;
    }

    /**
     *
     * get one or all settings table keys
     *
     * @param string $key
     * @param bool   $force whether to force the sql query or try to fetch the settings array from the front controller
     *
     * @return array
     */
    public function get($key = null, $force = false)
    {
        $data = array();

        $table = $this->getTable();

        if ($key !== null) {
            $rowset = array($table->fetchRow(
                $table->getAdapter()->quoteInto('name = ?', $key)
            ));
        }
        else {
            if ($force === false) {
                $rowset = Front::getInstance()->getBootstrap()->getResource('settings');
            }

            if (empty($rowset)) {
                $select = $table->select(array('name', 'value'));

                $rowset = $table->fetchAll($select)->toArray();
            }
            else {
                return $rowset;
            }
        }

        foreach ($rowset as $row) {
            $data[(string)$row['name']] = $row['value'];
        }

        return $data;
    }


}

