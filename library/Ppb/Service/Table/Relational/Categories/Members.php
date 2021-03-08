<?php

/**
 *
 * PHP Pro Bid
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2017 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.9 [rev.7.9.01]
 */
/**
 * members module specific categories table service class
 */

namespace Ppb\Service\Table\Relational\Categories;

use Ppb\Service\Table\Relational\Categories;

class Members extends Categories
{

    /**
     *
     * fields to remove from the parent table form
     *
     * @var array
     */
    private $_skipFields = array('custom_fees', 'adult');

    /**
     *
     * get all table columns needed to generate the
     * categories management table in the admin area
     *
     * @return array
     */
    public function getColumns()
    {
        $columns = parent::getColumns();

        foreach ($columns as $key => $column) {
            if (in_array($column['element_id'], $this->_skipFields)) {
                unset($columns[$key]);
            }
        }

        return $columns;
    }

}

