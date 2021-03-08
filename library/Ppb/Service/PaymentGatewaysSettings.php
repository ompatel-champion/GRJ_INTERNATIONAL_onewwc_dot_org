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
 * custom fields data table service class
 *
 * IMPORTANT:
 * search serialized custom fields:
 * select * from probid_custom_fields_data where value REGEXP '"x"|"y"|"z"';
 * (maybe we will serialize all saved data)
 */

namespace Ppb\Service;

use Ppb\Db\Table;

class PaymentGatewaysSettings extends AbstractService
{

    /**
     *
     * class constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->setTable(
            new Table\PaymentGatewaysSettings());
    }

    /**
     *
     * save a row in the payment gateways settings table
     *
     * @param array $post
     *
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function save($post)
    {
        $row = null;

        if (empty($post['gateway_id'])) {
            throw new \InvalidArgumentException("The 'gateway_id' key is required when saving a payment gateways settings row.");
        }

        $table = $this->getTable();

        $data = $this->_prepareSaveData($post);

        if (array_key_exists('id', $data)) {
            $row = $this->findBy('id', $data['id']);
            unset($data['id']);
        }
        else {
            $select = $this->getTable()->select()
                ->where('name = ?', $data['name'])
                ->where('gateway_id = ?', $data['gateway_id']);

            if (isset($data['user_id'])) {
                $select->where('user_id = ?', $data['user_id']);
            }
            else {
                $select->where('user_id IS NULL');
            }

            $row = $this->getTable()->fetchRow($select);
        }

        if ($row !== null) {
            $table->update($data,
                $table->getAdapter()->quoteInto('id = ?', $row['id']));
        }
        else {
            $table->insert($data);
        }

        return $this;
    }
}

