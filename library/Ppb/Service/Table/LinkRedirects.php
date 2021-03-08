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
 * link redirects table service class
 */

namespace Ppb\Service\Table;

use Ppb\Db\Table\LinkRedirects as LinkRedirectsTable;

class LinkRedirects extends AbstractServiceTable
{

    /**
     *
     * number of insert rows that appear at the bottom of a table form
     *
     * @var integer
     */
    protected $_insertRows = 3;

    public function __construct()
    {
        parent::__construct();

        $this->setTable(
            new LinkRedirectsTable());
    }

    /**
     *
     * get all table columns needed to generate the
     * link redirects management table in the admin area
     *
     * @return array
     */
    public function getColumns()
    {
        return array(
            array(
                'label'      => $this->_('Original Link'),
                'class'      => 'size-large',
                'element_id' => 'old_link',

            ),
            array(
                'label'      => $this->_('New Link'),
                'element_id' => 'new_link',
            ),
            array(
                'label'      => $this->_('Redirect Code'),
                'class'      => 'size-mini',
                'element_id' => 'redirect_code',
            ),
            array(
                'label'      => $this->_('Order ID'),
                'class'      => 'size-mini',
                'element_id' => 'order_id',
            ),
            array(
                'label'      => $this->_('Delete'),
                'class'      => 'size-mini',
                'element_id' => array(
                    'id', 'delete'
                ),
            ),
        );
    }

    /**
     *
     * get all form elements that are needed to generate the
     * link redirects management table in the admin area
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
                'id'         => 'old_link',
                'element'    => 'text',
                'attributes' => array(
                    'class'       => 'form-control input-block-level',
                    'placeholder' => $this->_('String / regex format'),
                ),
            ),
            array(
                'id'         => 'new_link',
                'element'    => 'text',
                'attributes' => array(
                    'class'       => 'form-control input-medium',
                    'placeholder' => $this->_('String / sprintf format'),
                ),
            ),
            array(
                'id'         => 'redirect_code',
                'element'    => 'text',
                'attributes' => array(
                    'class' => 'form-control input-mini',
                ),
            ),
            array(
                'id'         => 'order_id',
                'element'    => 'text',
                'attributes' => array(
                    'class' => 'form-control input-mini',
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
     * fetches all matched rows
     *
     * @param string|\Cube\Db\Select $where SQL where clause, or a select object
     * @param string|array           $order
     * @param int                    $count
     * @param int                    $offset
     *
     * @return \Cube\Db\Table\Rowset
     */
    public function fetchAll($where = null, $order = null, $count = null, $offset = null)
    {
        if ($order === null) {
            $order = 'order_id ASC, id ASC';
        }

        return parent::fetchAll($where, $order, $count, $offset);
    }
}

