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
 * bank accounts table service class
 */
/**
 * MOD:- BANK TRANSFER
 */

namespace Ppb\Service;

use Ppb\Db\Table\BankAccounts as BankAccountsTable,
    Ppb\Model\Elements;

class BankAccounts extends AbstractService
{

    /**
     *
     * columns to be saved in the serializable bank account field
     *
     * @var array
     */
    protected $_bankAccountFields = array();

    /**
     *
     * class constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->setTable(
            new BankAccountsTable());
    }

    /**
     *
     * set bank account fields (by default from user elements model)
     *
     * @param array $bankAccountFields
     *
     * @return $this
     */
    public function setBankAccountFields($bankAccountFields = null)
    {
        if ($bankAccountFields === null) {
            $bankAccountModel = new Elements\BankAccount();
            $elements = $bankAccountModel->getElements();

            foreach ($elements as $element) {
                if (array_intersect((array)$element['form_id'], array('bank_account')) && $element['element'] != 'hidden') {
                    $bankAccountFields[$element['id']] = $element['label'];
                }
            }
        }
        $this->_bankAccountFields = $bankAccountFields;

        return $this;
    }

    /**
     *
     * get bank account fields
     *
     * @return array
     */
    public function getBankAccountFields()
    {
        if (!$this->_bankAccountFields) {
            $this->setBankAccountFields();
        }

        return $this->_bankAccountFields;
    }


    /**
     *
     * save a user's bank account in the bank accounts table
     *
     * @param array  $data   bank account data (serializable)
     * @param int    $userId user id
     * @param string $prefix the prefix of the input fields (optional)
     *
     * @return int  the id of the saved column
     * @throws \InvalidArgumentException
     */
    public function save($data, $userId = null, $prefix = null)
    {
        $row = array();

        if ($prefix !== null) {
            foreach ($data as $key => $value) {
                if (strstr($key, $prefix) !== false) {
                    unset($data[$key]);
                    $key = str_replace($prefix, '', $key);
                    $data[$key] = $value;
                }
            }
        }

        if (isset($data['id'])) {
            $select = $this->_table->select()
                ->where("id = ?", $data['id']);

            if ($userId !== null) {
                $select->where('user_id = ?', $userId);
            }

            $row = $this->_table->fetchRow($select);
        }

        $bankAccount = array();

        $bankAccountFields = $this->getBankAccountFields();

        foreach ($bankAccountFields as $field => $label) {
            $bankAccount[$field] = (isset($data[$field])) ? $data[$field] : null;
        }

        if (count($row) > 0) {
            $this->_table->update(array(
                'account' => serialize($bankAccount),
            ), "id='{$row['id']}'");

            $id = $row['id'];
        }
        else {
            $this->_table->insert(array(
                'user_id' => $userId,
                'account' => serialize($bankAccount),
            ));

            $id = $this->_table->lastInsertId();
        }

        return $id;
    }

    /**
     *
     * get the bank accounts of a user in a key => value (bank account row object) format
     *
     * @param int|null $userId if admin we have null
     *
     * @return array
     */
    public function getMultiOptions($userId = null)
    {
        $data = array();

        $select = $this->getTable()->select()
            ->order('id DESC');

        if ($userId) {
            $select->where('user_id = ?', $userId);
        }
        else {
            $select->where('user_id is null');
        }

        $bankAccounts = $this->fetchAll($select);

        foreach ($bankAccounts as $bankAccount) {
            $data[(string)$bankAccount['id']] = $bankAccount;
        }

        return $data;
    }

}

