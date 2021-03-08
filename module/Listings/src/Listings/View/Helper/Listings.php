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
 * listings display view helper class
 */

namespace Listings\View\Helper;

use Ppb\View\Helper\AbstractHelper,
    Ppb\Service\Listings as ListingsService,
    Cube\Cache\Adapter\AbstractAdapter as CacheAdapter,
    Cube\Controller\Front,
    Cube\Paginator;

class Listings extends AbstractHelper
{

    /**
     *
     * the view partial to be used
     *
     * @var string
     */
    protected $_partial = 'partials/listings-cards.phtml';

    /**
     *
     * variables to be used by the partial
     *
     * @var array
     */
    protected $_variables;

    /**
     *
     * listings service
     *
     * @var \Ppb\Service\Listings
     */
    protected $_listingsService;

    /**
     *
     * listings rowset
     *
     * @var \Ppb\Db\Table\Rowset\Listings|null
     */
    protected $_listings = null;

    /**
     *
     * listings paginator
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
     * get listings service
     *
     * @return \Ppb\Service\Listings
     */
    public function getListingsService()
    {
        if (!$this->_listingsService instanceof ListingsService) {
            $this->setListingsService(
                new ListingsService());
        }

        return $this->_listingsService;
    }

    /**
     *
     * set listings service
     *
     * @param \Ppb\Service\Listings $listingsService
     *
     * @return $this
     */
    public function setListingsService(ListingsService $listingsService)
    {
        $this->_listingsService = $listingsService;

        return $this;
    }

    /**
     *
     * get listings rowset
     *
     * @return \Ppb\Db\Table\Rowset\Listings|null
     */
    public function getListings()
    {
        return $this->_listings;
    }

    /**
     *
     * set listings rowset
     *
     * @param mixed $listings
     *
     * @return $this
     */
    public function setListings($listings)
    {
        $this->_listings = $listings;

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
     * listings helper main method
     *
     * @param string $partial
     *
     * @return $this
     */
    public function listings($partial = null)
    {
        if ($partial !== null) {
            $this->setPartial($partial);
        }

        return $this;
    }

    /**
     *
     * fetch listings, can retrieve cached data
     *
     * @param array|null $params
     * @param string     $selectType
     * @param bool       $cache
     *
     * @return $this
     */
    public function fetchListings(array $params = null, $selectType = ListingsService::SELECT_LISTINGS, $cache = true)
    {
        $listingsService = $this->getListingsService();

        if ($cache === true) {
            // use caching
            $listingsService->setCacheId(array(
                CacheAdapter::CACHE_COL   => 'l.id',
                CacheAdapter::CACHE_WHERE => 'id',
            ));
        }

        $select = $this->getListingsService()->select($selectType, $params);

        if (!empty($params['limit'])) {
            $select->limit($params['limit']);
        }

        $listings = $listingsService->fetchAll($select);

        if ($cache === true) {
            $listingsService->setCacheId(null);
        }

        $this->setListings($listings);

        return $this;
    }

    /**
     *
     * fetch listings in a paginator object
     *
     * @param array|null $params
     * @param string     $selectType
     *
     * @return $this
     */
    public function fetchPaginator(array $params = null, $selectType = ListingsService::SELECT_LISTINGS)
    {
        $select = $this->getListingsService()->select($selectType, $params);

        $paginator = new Paginator(
            new Paginator\Adapter\DbTableSelect($select, $this->getListingsService()->getTable()));

        $request = Front::getInstance()->getRequest();

        $itemCountPerPage = (!empty($params['limit'])) ? $params['limit'] : $request->getParam('limit', Paginator::ITEM_COUNT_PER_PAGE);
        $pageNumber = (!empty($params['page'])) ? $params['page'] : $request->getParam('page');

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
        if ($paginator) {
            $listings = $this->getPaginator();
            $nbListings = $listings->getPages()->totalItemCount;
        }
        else {
            $listings = $this->getListings();
            $nbListings = count($listings);
        }

        if ($nbListings > 0) {
            $this->setVariable('listings', $listings);

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

