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
 * newsletters recipients table
 */

namespace Ppb\Db\Table;

use Cube\Db\Table\AbstractTable;

class NewslettersRecipients extends AbstractTable
{

    /**
     *
     * table name
     * 
     * @var string
     */
    protected $_name = 'newsletters_recipients';

    /**
     *
     * primary key
     * 
     * @var string
     */
    protected $_primary = 'id';

    /**
     *
     * reference map
     * 
     * @var array
     */
    protected $_referenceMap = array(
        'User' => array(
            self::COLUMNS => 'user_id',
            self::REF_TABLE_CLASS => '\Ppb\Db\Table\Users',
            self::REF_COLUMNS => 'id',
        ),
        'Subscriber' => array(
            self::COLUMNS => 'subscriber_id',
            self::REF_TABLE_CLASS => '\Ppb\Db\Table\NewslettersSubscribers',
            self::REF_COLUMNS => 'id',
        ),
        'Newsletter' => array(
            self::COLUMNS => 'newsletter_id',
            self::REF_TABLE_CLASS => '\Ppb\Db\Table\Newsletters',
            self::REF_COLUMNS => 'id',
        ),
    );

}