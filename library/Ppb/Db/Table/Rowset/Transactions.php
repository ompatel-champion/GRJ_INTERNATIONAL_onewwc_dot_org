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
 * transactions table rowset class
 */

namespace Ppb\Db\Table\Rowset;

class Transactions extends AbstractAccounting
{

    /**
     *
     * row object class
     * 
     * @var string
     */
    protected $_rowClass = '\Ppb\Db\Table\Row\Transaction';

}

