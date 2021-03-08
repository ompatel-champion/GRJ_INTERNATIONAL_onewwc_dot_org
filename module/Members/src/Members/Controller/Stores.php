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
 * members module - stores browse controller
 */

namespace Members\Controller;

use Members\Controller\Action\AbstractAction,
    Ppb\Service;

class Stores extends AbstractAction
{

    public function Index()
    {
        $categoriesService = new Service\Table\Relational\Categories();

        $categories = $categoriesService->fetchAll(
            $categoriesService->getTable()->select()
                ->where('parent_id is null')
                ->where('user_id is null')
                ->where('enable_auctions = ?', 1)
                ->order(array('order_id ASC', 'name ASC'))
        );

        return array(
            'headline'        => $this->_('Stores'),
            'isMembersModule' => false,
            'categories'      => $categories,
        );
    }

    public function Browse()
    {
        return array(
            'messages'        => $this->_flashMessenger->getMessages(),
            'params'          => $this->getRequest()->getParams(),
            'parentId'        => $this->getRequest()->getParam('parent_id'),
            'isMembersModule' => false,
        );
    }
}

