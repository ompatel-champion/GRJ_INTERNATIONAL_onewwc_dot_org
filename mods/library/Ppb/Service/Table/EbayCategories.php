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
 * ebay categories table service class
 */
/**
 * MOD:- EBAY IMPORTER
 *
 * @version 1.3
 */

namespace Ppb\Service\Table;

use Ppb\Db\Table\EbayCategories as EbayCategoriesTable,
    Cube\Db\Table\AbstractTable,
    Ppb\Service\Table\Relational\Categories as CategoriesService,
    External\ParseCSV,
    External\ForceUTF8;

class EbayCategories extends AbstractServiceTable
{

    /**
     *
     * column to check against when adding rows
     *
     * @var string
     */
    protected $_mainColumn = 'name';

    public function __construct()
    {
        parent::__construct();

        $this->setInsertRows(5)
            ->setTable(
                new EbayCategoriesTable());
    }

    /**
     *
     * get all table columns needed to generate the
     * ebay categories management table in the admin area
     *
     * @return array
     */
    public function getColumns()
    {
        return array(
            array(
                'label'      => $this->_('Ebay Category Name'),
                'element_id' => 'name',
            ),
            array(
                'label'      => $this->_('Ebay Category ID'),
                'class'      => 'size-small th-small',
                'element_id' => 'ebay_category_id',
            ),
            array(
                'label'      => $this->_('Local Category ID'),
                'class'      => 'size-small th-small',
                'element_id' => 'category_id',
            ),
            array(
                'label'      => $this->_('Delete'),
                'class'      => 'size-mini th-small',
                'element_id' => array(
                    'id', 'delete'
                ),
            ),
        );
    }

    /**
     *
     * get all form elements that are needed to generate the
     * ebay categories management table in the admin area
     *
     * @return array
     */
    public function getElements()
    {
        return array(
            array(
                'id'      => 'id',
                'element' => 'hidden',
            ),
            array(
                'id'         => 'name',
                'element'    => 'text',
                'attributes' => array(
                    'class' => 'form-control input-large',
                ),
            ),
            array(
                'id'         => 'ebay_category_id',
                'element'    => 'text',
                'attributes' => array(
                    'class' => 'form-control input-small',
                ),
            ),
            array(
                'id'         => 'category_id',
                'element'    => 'text',
                'attributes' => array(
                    'class' => 'form-control input-small',
                ),
            ),
            array(
                'id'      => 'delete',
                'element' => 'checkbox',
            ),
        );
    }


    /**
     *
     * parse csv file and return an array containing data formatted in order to saved in the ebay categories table
     *
     * @param string $fileName
     *
     * @return array
     */
    public function parseCSV($fileName)
    {
        $data = array();

        $filePath = \Ppb\Utility::getPath('uploads') . '/' . $fileName;

        $csv = new ParseCSV($filePath);

        if (count($csv->data) > 0) {
            foreach ($csv->data as $key => $row) {
                $row = array_values($row);

                $data['id'][$key] = null;
                $data['name'][$key] = implode(' :: ', array_filter(array_slice($row, 1, -1)));
                $data['category_id'][$key] = $row[7];
                $data['ebay_category_id'][$key] = $row[0];
            }
        }

        return $data;
    }


    /**
     *
     * save data in the table (update if an id exists or insert otherwise)
     *
     * TODO: PROBLEM WITH "max_input_vars" php setting for big arrays of data
     *
     * @param array $data
     *
     * @return \Ppb\Service\Table\AbstractServiceTable
     * @throws \InvalidArgumentException
     */
    public function save($data)
    {
        if (!isset($data['id'])) {
            throw new \InvalidArgumentException("The form must use an element with the name 'id'.");
        }

        $columns = array_keys($data);

        $tableColumns = array_flip(array_values($this->getTable()->info(AbstractTable::COLS)));

        $categoriesService = new CategoriesService();

        foreach ($data['id'] as $key => $value) {
            $row = $this->_table->fetchRow("id='{$value}'");

            $input = array();
            foreach ($columns as $column) {
                if (isset($data[$column][$key]) && array_key_exists($column, $tableColumns)) {
                    $input[$column] = $data[$column][$key];
                }
            }

            $input = $this->_prepareSaveData($input);

            $category = $categoriesService->findBy('id', $input['category_id']);

            if ($category !== null) {
                if ($row !== null) {
                    $this->_table->update($input, "id='{$value}'");
                }
                else if (count(array_filter($input)) > 0 && !empty($input[$this->getMainColumn($columns)])) {
                    $this->_table->insert($input);
                }
            }
        }

        return $this;
    }

    /**
     *
     * fetches all matched rows
     *
     * @param string|\Cube\Db\Select $where SQL where clause, or a select object
     * @param string|array           $order
     * @param int                    $count
     * @param int                    $offset
     *
     * @return array
     */
    public function fetchAll($where = null, $order = null, $count = null, $offset = null)
    {
        if ($order === null) {
            $order = 'name ASC';
        }

        return parent::fetchAll($where, $order, $count, $offset);
    }

    /**
     *
     * get local category id from the table based on the ebay category id
     * if there is no corresponding value, return the default category id
     *
     * @param int $ebayCategoryId
     *
     * @return int
     */
    public function getLocalCategoryId($ebayCategoryId)
    {
        $settings = $this->getSettings();
        $categoryId = $settings['ebay_default_category_id'];

        $ebayCategory = $this->findBy('ebay_category_id', $ebayCategoryId);

        $categoriesService = new CategoriesService();

        if ($ebayCategory !== null) {
            $category = $categoriesService->findBy('id', $ebayCategory['category_id']);

            if ($category !== null) {
                $categoryId = $category['id'];
            }
        }

        return $categoryId;
    }

    /**
     *
     * delete all rows from the table
     *
     * @return int    returns the number of affected rows
     */
    public function truncate()
    {
        return $this->_table->delete('');
    }
}

