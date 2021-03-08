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
 * banks table service class
 */
/**
 * MOD:- BANK TRANSFER
 *
 * @version 1.1
 */

namespace Ppb\Service\Table;

use Ppb\Db\Table\Banks as BanksTable;

class Banks extends AbstractServiceTable
{

    public function __construct()
    {
        parent::__construct();

        $this->setTable(
                new BanksTable());
    }

    /**
     * 
     * get all banks (translatable - optional)
     *
     * @return array
     */
    public function getMultiOptions()
    {
        $data = array();

        $translate = $this->getTranslate();

        $rows = $this->fetchAll();

        foreach ($rows as $row) {
            $data[(string) $row['name']] = $translate->_($row['name']);
        }

        return $data;
    }

    /**
     * 
     * get all table columns needed to generate the 
     * banks management table in the admin area
     * 
     * @return array
     */
    public function getColumns()
    {
        return array(
            array(
                'label' => $this->_('Name'),
                'element_id' => 'name',
            ),
            array(
                'label' => $this->_('Order ID'),
                'class' => 'size-mini',
                'element_id' => 'order_id',
            ),
            array(
                'label' => $this->_('Delete'),
                'class' => 'size-mini',
                'element_id' => array(
                    'id', 'delete'
                ),
            ),
        );
    }

    /**
     * 
     * get all form elements that are needed to generate the 
     * banks management table in the admin area
     * 
     * @return array
     */
    public function getElements()
    {
        return array(
            array(
                'id' => 'id',
                'element' => 'hidden',
            ),
            array(
                'id' => 'name',
                'element' => 'text',
                'attributes' => array(
                    'class' => 'form-control input-large',
                ),
            ),
            array(
                'id' => 'order_id',
                'element' => 'text',
                'attributes' => array(
                    'class' => 'form-control input-mini',
                ),
            ),
            array(
                'id' => 'delete',
                'element' => 'checkbox',
            ),
        );
    }

    /**
     * 
     * fetches all matched rows 
     * 
     * @param string|\Cube\Db\Select $where     SQL where clause, or a select object
     * @param string|array $order
     * @param int $count
     * @param int $offset
     * @return array
     */
    public function fetchAll($where = null, $order = null, $count = null, $offset = null)
    {
        if ($order === null) {
            $order = 'order_id ASC, name ASC';
        }

        return parent::fetchAll($where, $order, $count, $offset);
    }

}

