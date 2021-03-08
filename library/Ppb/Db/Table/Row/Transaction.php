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
 * transactions table row object model
 */

namespace Ppb\Db\Table\Row;

class Transaction extends AbstractAccounting
{


    /**
     *
     * transactions row invoice details link
     *
     * @return array
     */
    public function link()
    {
        return array(
            'module'     => 'members',
            'controller' => 'account',
            'action'     => 'invoice',
            'type'       => 'transactions',
            'id'         => $this->getData('id')
        );
    }

    /**
     *
     * invoice details page caption
     *
     * @return string
     */
    public function caption()
    {
        return $this->getTranslate()->_('Receipt');
    }

    /**
     *
     * check if the transaction was marked as paid
     *
     * @return bool
     */
    public function isPaid()
    {
        return $this->getData('paid') ? true : false;
    }
}

