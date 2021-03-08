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
 * stores display view helper class
 */

namespace Members\View\Helper;

use Ppb\View\Helper\AbstractHelper,
    Ppb\Service\Users as UsersService,
    Cube\Cache\Adapter\AbstractAdapter as CacheAdapter,
    Cube\Paginator;

class Stores extends AbstractHelper
{

    /**
     *
     * the view partial to be used
     *
     * @var string
     */
    protected $_partial = 'partials/stores-cards.phtml';

    /**
     *
     * variables to be used by the partial
     *
     * @var array
     */
    protected $_variables;

    /**
     *
     * users service
     *
     * @var \Ppb\Service\Users
     */
    protected $_usersService;

    /**
     *
     * stores rowset
     *
     * @var \Ppb\Db\Table\Rowset\Users|null
     */
    protected $_stores = null;

    /**
     *
     * stores paginator
     *
     * @var \Cube\Paginator
     */
    protected $_paginator;

    /**
     *
     * get partial variables
     *
     * @return array
     */
    public function getVariables()
    {
        return $this->_variables;
    }

    /**
     *
     * set partial variables
     *
     * @param array $variables
     *
     * @return $this
     */
    public function setVariables($variables)
    {
        $this->_variables = $variables;

        return $this;
    }


    /**
     *
     * clear partial variables
     *
     * @return $this
     */
    public function clearVariables()
    {
        $this->_variables = array();

        return $this;
    }

    /**
     *
     * get partial variable or return null if it doesnt exist
     *
     * @param string $key
     *
     * @return mixed|null
     */
    public function getVariable($key)
    {
        if ($this->isVariable($key)) {
            return $this->_variables[$key];
        }

        return null;
    }

    /**
     *
     * set partial variable
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return $this
     */
    public function setVariable($key, $value)
    {
        $this->_variables[$key] = $value;

        return $this;
    }

    /**
     *
     * clear partial variable
     *
     * @param string $key
     *
     * @return $this
     */
    public function clearVariable($key)
    {
        if ($this->isVariable($key)) {
            unset($this->_variables[$key]);
        }

        return $this;
    }

    /**
     *
     * check if partial variable exists and is not null
     *
     * @param string $key
     *
     * @return bool
     */
    public function isVariable($key)
    {
        return (isset($this->_variables[$key])) ? true : false;
    }

    /**
     *
     * get users service
     *
     * @return \Ppb\Service\Users
     */
    public function getUsersService()
    {
        if (!$this->_usersService instanceof UsersService) {
            $this->setUsersService(
                new UsersService());
        }

        return $this->_usersService;
    }

    /**
     *
     * set users service
     *
     * @param \Ppb\Service\Users $listingsService
     *
     * @return $this
     */
    public function setUsersService(UsersService $listingsService)
    {
        $this->_usersService = $listingsService;

        return $this;
    }

    /**
     *
     * get stores rowset
     *
     * @return \Ppb\Db\Table\Rowset\Users|null
     */
    public function getStores()
    {
        return $this->_stores;
    }

    /**
     *
     * set stores rowset
     *
     * @param mixed $stores
     *
     * @return $this
     */
    public function setStores($stores)
    {
        $this->_stores = $stores;

        return $this;
    }

    /**
     *
     * get paginator
     *
     * @return \Cube\Paginator
     */
    public function getPaginator()
    {
        return $this->_paginator;
    }

    /**
     *
     * set paginator
     *
     * @param \Cube\Paginator $paginator
     *
     * @return $this
     */
    public function setPaginator($paginator)
    {
        $this->_paginator = $paginator;

        return $this;
    }

    /**
     *
     * stores helper main method
     *
     * @param string $partial
     *
     * @return $this
     */
    public function stores($partial = null)
    {
        if ($partial !== null) {
            $this->setPartial($partial);
        }

        return $this;
    }

    /**
     *
     * fetch stores, can retrieve cached data
     *
     * @param array|null $params
     * @param bool       $cache
     *
     * @return $this
     */
    public function fetchStores(array $params = null, $cache = true)
    {
        $usersService = $this->getUsersService();

        if ($cache === true) {
            // use caching
            $usersService->setCacheId(array(
                CacheAdapter::CACHE_COL   => 'u.id',
                CacheAdapter::CACHE_WHERE => 'id',
            ));
        }

        $select = $this->getUsersService()->storesSelect($params);

        if (!empty($params['limit'])) {
            $select->limit($params['limit']);
        }

        $stores = $usersService->fetchAll($select);

        if ($cache === true) {
            $usersService->setCacheId(null);
        }

        $this->setStores($stores);

        return $this;
    }

    /**
     *
     * fetch stores in a paginator object
     *
     * @param array|null $params
     *
     * @return $this
     */
    public function fetchPaginator(array $params = null)
    {
        $select = $this->getUsersService()->storesSelect($params);

        $paginator = new Paginator(
            new Paginator\Adapter\DbTableSelect($select, $this->getUsersService()->getTable()));

        $itemCountPerPage = (!empty($params['limit'])) ? $params['limit'] : 20;
        $pageNumber = (!empty($params['page'])) ? $params['page'] : 0;

        $paginator->setItemCountPerPage($itemCountPerPage)
            ->setCurrentPageNumber($pageNumber);

        $this->setPaginator($paginator);

        return $this;
    }

    /**
     *
     * render partial
     *
     * only use view variables that are explicitly provided in the helper
     *
     * @param bool $paginator
     *
     * @return string
     */
    public function render($paginator = false)
    {
        $nbStores = 0;

        if ($paginator) {
            $stores = $this->getPaginator();
            $nbStores = $stores->getPages()->totalItemCount;
        }
        else {
            $stores = $this->getStores();
            if ($stores !== null) {
                $nbStores = count($stores);
            }
        }

        if ($nbStores > 0) {
            $this->setVariable('stores', $stores);

            $view = $this->getView();

            $variables = $view->getVariables();

            $view->setVariables(
                    $this->getVariables(), true);

            $output = $view->process(
                $this->getPartial(), true);

            $view->setVariables($variables, true);

            return $output;
        }

        return '';
    }

    /**
     *
     * render paginator
     *
     * @return string
     */
    public function renderPaginator()
    {
        return $this->render(true);
    }
}

