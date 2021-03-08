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
 * custom fields table service class
 *
 * IMPORTANT:
 * search custom fields by multiple categories:
 * select * from custom_fields where category_ids REGEXP '"x"|"y"|"z"';
 */
/**
 * MOD:- ADVANCED CLASSIFIEDS
 */

namespace Ppb\Service;

use Ppb\Db\Table;

class CustomFields extends AbstractService
{

    /**
     *
     * allowed custom field types
     *
     * @var array
     */
    ## -- START :: CHANGE -- [ MOD:- ADVANCED CLASSIFIEDS ]
    protected $_customFieldTypes = array(
        'user', 'item', 'classified');
    ## -- END :: CHANGE -- [ MOD:- ADVANCED CLASSIFIEDS ]

    /**
     *
     * class constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->setTable(
            new Table\CustomFields());
    }

    /**
     *
     * get allowed custom field types
     *
     * @return array
     */
    public function getCustomFieldTypes()
    {
        return $this->_customFieldTypes;
    }

    /**
     *
     * get certain custom fields based on a set of queries
     *
     * @param array $data  the search data used to return the requested fields
     * @param mixed $order order by field(s)
     *
     * @return \Cube\Db\Table\Rowset\AbstractRowset
     */
    public function getFields(array $data = null, $order = null)
    {
        $select = $this->_table->select();

        foreach ((array)$data as $key => $value) {
            if ($key === 'category_ids') {
                $select->where("category_ids REGEXP '\"" . implode('"|"',
                        array_unique($value)) . "\"' OR category_ids = ''");
            }
            ## -- ADD -- [ MOD:- ADVANCED CLASSIFIEDS ]
            else if (is_array($value)) {
                $select->where("{$key} IN (?)", $value);
            }
            ## -- ./ADD -- [ MOD:- ADVANCED CLASSIFIEDS ]
            else {
                $select->where("{$key} = ?", $value);
            }
        }

        if ($order === null) {
            $order = array('active DESC', 'order_id ASC');
        }

        $select->order($order);

        return $this->fetchAll($select);
    }

    /**
     *
     * save custom fields settings (order etc)
     *
     * @param array $data
     *
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function saveBrowseSettings(array $data)
    {
        if (!isset($data['id'])) {
            throw new \InvalidArgumentException("The form must use an element with the name 'id'.");
        }

        $table = $this->getTable();
        $columns = array_keys($data);

        foreach ((array)$data['id'] as $key => $value) {
            $row = $table->fetchRow(
                $table->getAdapter()->quoteInto('id = ?', $value));

            $input = array();
            foreach ($columns as $column) {
                if (is_array($data[$column]) && isset($data[$column][$key])) {
                    $input[$column] = $data[$column][$key];
                }
            }

            $input = parent::_prepareSaveData($input);

            if ($row !== null) {
                $table->update($input,
                    $table->getAdapter()->quoteInto('id = ?', $value));
            }
        }

        return $this;
    }

    /**
     *
     * get custom fields aliases
     *
     * @return array
     */
    public function getAliases()
    {
        $aliases = array();


        $customFields = $this->fetchAll(
            $this->getTable()->select()
                ->where('alias != ?', '')
        )->toArray();

        foreach ($customFields as $customField) {
            $aliases[$customField['alias']] = 'custom_field_' . $customField['id'];
        }

        return $aliases;
    }

    protected function _prepareSaveData($data = array())
    {
        $data = parent::_prepareSaveData($data);
        $data['element'] = $_POST['element'];

        return $data;

    }
}

