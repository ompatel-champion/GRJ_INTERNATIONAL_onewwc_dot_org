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
 * newsletters table service class
 */

namespace Ppb\Service;

use Ppb\Db\Table;

class Newsletters extends AbstractService
{

    /**
     *
     * newsletters recipients array
     *
     * @var array
     */
    protected $_recipients = array(
        'all'         => array(
            'name'  => 'All Users',
            'query' => '1',
            'table' => '\\Ppb\\Db\\Table\\Users',
        ),
        'active'      => array(
            'name'  => 'Active Users',
            'query' => 'active = 1',
            'table' => '\\Ppb\\Db\\Table\\Users',
        ),
        'suspended'   => array(
            'name'  => 'Suspended Users',
            'query' => 'active = 0',
            'table' => '\\Ppb\\Db\\Table\\Users',
        ),
        'subscribers' => array(
            'name'  => 'Newsletter Subscribers',
            'query' => 'confirmed = 1',
            'table' => '\\Ppb\\Db\\Table\\NewslettersSubscribers',
        ),
        'store'       => array(
            'name'  => 'Store Owners',
            'query' => 'active = 1 AND store_active = 1',
            'table' => '\\Ppb\\Db\\Table\\Users',
        ),
    );

    /**
     *
     * class constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->setTable(
            new Table\Newsletters());
    }

    /**
     *
     * set recipients array
     *
     * @param array $recipients
     *
     * @return $this
     */
    public function setRecipients(array $recipients)
    {
        $this->_recipients = $recipients;

        return $this;
    }

    /**
     *
     * get recipients array
     *
     * @return array
     */
    public function getRecipients()
    {
        return $this->_recipients;
    }

    /**
     *
     * get recipient by key
     *
     * @param string $key
     *
     * @return array|false
     */
    public function getRecipient($key)
    {
        if (array_key_exists($key, $this->_recipients)) {
            return $this->_recipients[$key];
        }

        return false;
    }


    /**
     *
     * save newsletter recipients in the recipients table
     * return the number recipients saved or false
     *
     * @param string $key recipients to send to
     * @param int    $id  newsletter id
     *
     * @return int|false
     */
    public function saveRecipients($key, $id)
    {
        if (($recipient = $this->getRecipient($key)) !== false) {
            $newslettersRecipients = new Table\NewslettersRecipients();
            $tableClassName = $recipient['table'];

            /** @var \Cube\Db\Table\AbstractTable $tableClass */
            $tableClass = new $tableClassName();

            $newslettersRecipients->getAdapter()
                ->query("INSERT INTO " . $newslettersRecipients->getPrefix() . $newslettersRecipients->getName() . "
                    (newsletter_id, email)
                    SELECT {$id}, email
                    FROM " . $tableClass->getPrefix() . $tableClass->getName() . "
                    WHERE {$recipient['query']}");

            return true;
        }

        return false;
    }
}

