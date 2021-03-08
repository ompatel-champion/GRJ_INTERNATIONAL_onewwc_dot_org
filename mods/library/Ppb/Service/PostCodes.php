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
 * UK post codes table service class
 */
/**
 * MOD:- PICKUP LOCATIONS
 */

namespace Ppb\Service;

use Ppb\Db\Table\PostCodes as PostCodesTable;

class PostCodes extends AbstractService
{

    /**
     * 
     * class constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->setTable(
                new PostCodesTable());
    }

}

