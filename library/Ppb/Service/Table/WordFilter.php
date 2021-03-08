<?php

/**
 * 
 * PHP Pro Bid
 * 
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2018 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 * 
 * @version     8.0 [rev.8.0.01]
 */
/**
 * word filter table service class
 */

namespace Ppb\Service\Table;

use Ppb\Db\Table\WordFilter as WordFilterTable;

class WordFilter extends AbstractServiceTable
{

    public function __construct()
    {
        parent::__construct();

        $this->setTable(
                new WordFilterTable());
    }

    /**
     * 
     * get all table columns needed to generate the 
     * word filter management table in the admin area
     * 
     * @return array
     */
    public function getColumns()
    {
        return array(
            array(
                'label' => $this->_('Word'),
                'element_id' => 'word',
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
     * word filter management table in the admin area
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
                'id' => 'word',
                'element' => 'text',                
                'attributes' => array(
                    'class' => 'form-control input-large',
                    'placeholder' => $this->_('String / regex format')
                ),
            ),
            array(
                'id' => 'delete',
                'element' => 'checkbox',
            ),
        );
    }

}

