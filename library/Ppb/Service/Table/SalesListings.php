<?php

/**
 *
 * PHP Pro Bid
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2020 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     8.2 [rev.8.2.01]
 */

/**
 * sales listings table service class
 * creates/edits/removes sales listings
 * when editing or removing a sale listing, check if the corresponding row in the
 * sales table is empty, and if it is, remove that as well
 */

namespace Ppb\Service\Table;

use Ppb\Db\Table,
    Ppb\Service\Sales,
    Cube\Db\Expr,
    Ppb\Service\ListingsWatch;

class SalesListings extends AbstractServiceTable
{
    /**
     * download encryption key separator
     */
    const KEY_SEPARATOR = '|';

    /**
     *
     * sales table service class
     *
     * @var \Ppb\Service\Sales
     */
    protected $_sales = null;


    /**
     *
     * class constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->setTable(
            new Table\SalesListings());
    }

    /**
     *
     * get all table columns needed to generate the
     * sale invoices edit/combine form
     *
     * @return array
     */
    public function getColumns()
    {
        return array(
            array(
                'label'      => '',
                'class'      => 'size-tiny',
                'element_id' => array(
                    'id', 'listing_id',
                ),
            ),
            array(
                'label'      => $this->_('Listing'),
                'element_id' => 'name',
            ),
            array(
                'label'      => $this->_('Quantity'),
                'class'      => 'size-small',
                'element_id' => 'quantity',
            ),
            array(
                'label'      => $this->_('Price'),
                'element_id' => 'price',
                'class'      => 'size-medium',
            ),

        );
    }

    /**
     *
     * get all form elements that are needed to generate the
     * sale invoices edit/combine form
     *
     * @return array
     */
    public function getElements()
    {
        return array(
            array(
                'id'      => 'id', // sale listing id
                'element' => 'hidden',
            ),
            array(
                'id'      => 'listing_id',
                'element' => 'hidden',
            ),
            array(
                'id'      => 'name',
                'element' => 'description',
            ),
            array(
                'id'         => 'price',
                'element'    => 'text',
                'attributes' => array(
                    'class' => 'form-control input-small',
                ),
            ),
            array(
                'id'         => 'quantity',
                'element'    => '\\Ppb\\Form\\Element\\Quantity',
                'attributes' => array(
                    'class' => 'form-control input-small',
                ),
            ),
        );
    }

    /**
     *
     * get sales table service class
     *
     * @return \Ppb\Service\Sales
     */
    public function getSales()
    {
        if (!$this->_sales instanceof Sales) {
            $this->setSales();
        }

        return $this->_sales;
    }

    /**
     *
     * set sales table service class
     *
     * @param \Ppb\Service\Sales $sales
     *
     * @return \Ppb\Service\Table\SalesListings
     */
    public function setSales(Sales $sales = null)
    {
        if (!$sales instanceof Sales) {
            $sales = new Sales();
        }

        $this->_sales = $sales;

        return $this;
    }

    /**
     *
     * create or edit a sale listing.
     * - when editing, if changing "sale_id", check if there are other listings
     * corresponding to that "sale_id", and if not, remove the row in the "sales" table
     *
     * @param array $data
     * @param bool  $insertSalesListings
     *
     * @return \Ppb\Service\Table\SalesListings
     * @throws \InvalidArgumentException
     */
    public function save($data, $insertSalesListings = true)
    {
        $saleListing = null;

        $data = $this->_prepareSaveData($data);

        if (empty($data['id']) && (empty($data['sale_id']) || empty($data['listing_id']))) {
            throw new \InvalidArgumentException("A sale id and a listing id are required 
                when adding/editing a row from the sales listings table.");
        }

        if (array_key_exists('id', $data)) {
            $saleListing = $this->findBy('id', $data['id']);
            unset($data['id']);
        }

        if ($saleListing !== null) {
            $saleId = $saleListing['sale_id'];

            $saleListing->save($data);

            $this->_deleteEmptySale($saleId);
        }
        else if ($insertSalesListings) {
            $data['created_at'] = new Expr('now()');
            $this->_table->insert($data);
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
     * @return \Ppb\Db\Table\Rowset\SalesListings
     */
    public function fetchAll($where = null, $order = null, $count = null, $offset = null)
    {
        if ($order === null) {
            $order = 'sale_id ASC, created_at ASC';
        }

        return parent::fetchAll($where, $order, $count, $offset);
    }

    /**
     *
     * delete data from the sales listings table
     *
     * @param int         $id
     * @param string|null $userToken
     *
     * @return integer|false     returns the number of affected rows or false if the deletion was not completed
     */
    public function deleteOne($id, $userToken = null)
    {
        if ($userToken !== null) {
            $saleListing = $this->findBy('id', $id);

            if ($saleListing !== null) {
                $select = $this->_table->select()
                    ->where('pending = ?', 1)
                    ->where('user_token = ?', $userToken);

                $sale = $saleListing->findParentRow('\Ppb\Db\Table\Sales', null, $select);

                if (!$sale) {
                    return false;
                }
            }
            else {
                return false;
            }
        }

        return $this->delete(array($id));
    }

    /**
     *
     * move an item from the cart to the wish list
     *
     * @param int         $id
     * @param string|null $userToken
     *
     * @return bool
     */
    public function moveWishList($id, $userToken = null)
    {
        if ($userToken !== null) {
            $saleListing = $this->findBy('id', $id);

            if ($saleListing !== null) {
                $select = $this->_table->select()
                    ->where('pending = ?', 1)
                    ->where('user_token = ?', $userToken);

                $sale = $saleListing->findParentRow('\Ppb\Db\Table\Sales', null, $select);

                if ($sale !== null) {
                    $listingsWatchService = new ListingsWatch();

                    $user = $this->getUser();
                    $userId = (!empty($user['id'])) ? $user['id'] : null;

                    $listingsWatchService->save(array(
                        'user_token' => $userToken,
                        'user_id'    => $userId,
                        'listing_id' => $saleListing['listing_id'],
                    ));

                    $this->delete(array($id));

                    return true;
                }
            }
        }

        return false;
    }

    /**
     *
     * delete one or more rows from the sale listings table
     *
     * if the parent sale has no more sales listings, delete the sale as well.
     *
     * @param array $data
     *
     * @return int
     */
    public function delete($data)
    {
        $result = 0;

        $data = array_filter(
            array_values((array)$data));

        if (count($data) > 0) {
            $salesListings = $this->_table->fetchAll(
                $this->_table->select()
                    ->where('id IN (?)', $data)
            );

            /** @var \Ppb\Db\Table\Row\SaleListing $saleListing */
            foreach ($salesListings as $saleListing) {
                $result += $saleListing->delete();
            }
        }

        return $result;
    }

    /**
     *
     * check if the sale having the id $saleId contains listings, and if not, delete it
     *
     * @param int $saleId
     *
     * @return $this
     */
    protected function _deleteEmptySale($saleId)
    {
        /** @var \Ppb\Db\Table\Row\Sale $sale */
        $sale = $this->getSales()
            ->findBy('id', $saleId);

        if (!$sale->countDependentRowset('\Ppb\Db\Table\SalesListings')) {
            $sale->delete(true);
        }

        return $this;
    }
}
