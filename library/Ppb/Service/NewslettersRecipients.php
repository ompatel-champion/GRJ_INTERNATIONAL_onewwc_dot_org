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
 * newsletters recipients table service class
 */

namespace Ppb\Service;

use Ppb\Db\Table\NewslettersRecipients as NewslettersRecipientsTable;

class NewslettersRecipients extends AbstractService
{

    /**
     *
     * class constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->setTable(
            new NewslettersRecipientsTable());
    }

}

