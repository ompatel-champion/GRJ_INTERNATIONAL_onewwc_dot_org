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
 * make offer ranges view helper class
 */

namespace Listings\View\Helper;

use Ppb\View\Helper\AbstractHelper,
    Cube\Controller\Front,
    Cube\Paginator,
    Ppb\Model\Elements\Search as SearchElementsModel,
    Ppb\Form\Element\Range,
    Ppb\Service;

class BrowsePageTitle extends AbstractHelper
{

    /**
     *
     * parameter types
     */
    const PRIMARY = 'primary';
    const PAGINATION = 'pagination';

    /**
     *
     * the view partial to be used
     *
     * @var string
     */
    protected $_partial = 'partials/browse-page-title.phtml';

    /**
     *
     * categories service object
     *
     * @var \Ppb\Service\Table\Relational\Categories
     */
    protected $_categories;

    /**
     *
     * output array, generated only once, when the helper is called initially
     *
     * @var array
     */
    protected $_output;

    /**
     *
     * param types
     *
     * @var array
     */
    public static $paramTypes = array(
        self::PRIMARY    => array('keywords', 'parent_id', 'filter', 'listing_type'),
        self::PAGINATION => array('page', 'limit', 'sort'),
    );

    /**
     *
     * get categories table service
     *
     * @return \Ppb\Service\Table\Relational\Categories
     */
    public function getCategories()
    {
        if (!$this->_categories instanceof Service\Table\Relational\Categories) {
            $this->setCategories(
                new Service\Table\Relational\Categories());
        }

        return $this->_categories;
    }

    /**
     *
     * set categories table service
     *
     * @param \Ppb\Service\Table\Relational\Categories $categories
     *
     * @return $this
     */
    public function setCategories(Service\Table\Relational\Categories $categories)
    {
        $this->_categories = $categories;

        return $this;
    }

    /**
     *
     * get output
     *
     * @return array
     */
    public function getOutput()
    {
        return $this->_output;
    }

    /**
     *
     * set output
     *
     * @param array $output
     *
     * @return $this
     */
    public function setOutput($output)
    {
        $this->_output = $output;

        return $this;
    }

    /**
     *
     * browse page title view helper
     *
     * @return string
     */
    public function browsePageTitle($partial = null)
    {
        if ($partial !== null) {
            $this->setPartial($partial);
        }

        if (empty($output)) {
            $this->_initializeOutput();
        }

        return $this;
    }

    protected function _initializeOutput()
    {
        $output = array();

        $request = Front::getInstance()->getRequest();

        $view = $this->getView();

        $params = (array)$request->getParams();

        $filters = (array)$request->getParam('filter');
        $filters = array_unique($filters);

        $keywords = $request->getParam('keywords');

        $translate = $this->getTranslate();


        ## LISTING TYPE
        $listingTypesKeywords = array();
        $listingTypes = array_filter((array)$request->getParam('listing_type'));
        if (count($listingTypes) > 0) {
            if (in_array('auction', $listingTypes)) {
                $listingTypesKeywords[] = $translate->_('Auctions');
            }
            if (in_array('product', $listingTypes)) {
                $listingTypesKeywords[] = $translate->_('Products');
            }
            if (in_array('classified', $listingTypes)) {
                $listingTypesKeywords[] = $translate->_('Classifieds');
            }

            if (count($listingTypesKeywords) == 3) {
                $listingTypesKeywords = array();
            }
        }

        if (count($listingTypesKeywords) > 0) {
            $listingTypesKeywords = implode(', ', $listingTypesKeywords);
        }
        else {
            $listingTypesKeywords = $translate->_('Listings');
        }
        ## /LISTING TYPE


        ## FILTER
        $filterOutput = null;
        if (in_array('hpfeat', $filters)) {
            $filterOutput = sprintf($translate->_('Featured %s'), $listingTypesKeywords);
        }
        else if (in_array('recent', $filters)) {
            $filterOutput = sprintf($translate->_('Recently Listed %s'), $listingTypesKeywords);
        }
        else if (in_array('ending-soon', $filters)) {
            $filterOutput = sprintf($translate->_('Ending Soon %s'), $listingTypesKeywords);
        }
        else if (in_array('popular', $filters)) {
            $filterOutput = sprintf($translate->_('Popular %s'), $listingTypesKeywords);
        }
        else if (in_array('other-items', $filters)) {
            $otherItemsOutput = $translate->_($listingTypesKeywords);
            $username = $request->getParam('username');
            if (!empty($username)) {
                $otherItemsOutput .= ' ' . sprintf($translate->_("from '%s'"), $username);
            }

            $filterOutput = $otherItemsOutput;
        }
        else if (!empty($keywords)) {
            $filterOutput = sprintf($translate->_('Search %s'), $listingTypesKeywords);
        }
        else {
            $filterOutput = sprintf($translate->_('Browse %s'), $listingTypesKeywords);
        }
        ## /FILTER


        ## KEYWORDS
        $keywordsOutput = null;
        if (!empty($keywords)) {
            $keywordsOutput = sprintf($translate->_('%s matching "%s"'), $filterOutput, $keywords);
        }
        else {
            $keywordsOutput = $filterOutput;
        }
        ## /KEYWORDS


        ## CATEGORY
        $categoryOutput = null;
        if ($parentId = $request->getParam('parent_id')) {
            $breadcrumbs = array();

            $categoriesService = $this->getCategories();

            $skipParams = array('parent_id', 'category_name', 'category_slug', 'page', 'submit', 'submit_search');

            $categories = $categoriesService->getBreadcrumbs($parentId, true, true);

            $action = $request->getAction();

            /** @var \Ppb\Db\Table\Row\Category $category */
            foreach ($categories as $id => $category) {
                if ($action == 'Store') {
                    $categoryLink = array('parent_id' => $category['id']);
                    $paramsToSkip = $skipParams;
                }
                else {
                    $categoryLink = $category->link();
                    $paramsToSkip = array_diff($skipParams, array_keys($categoryLink));
                }

                $breadcrumbs[] = '<a href="' . $view->url($categoryLink, null, true, $paramsToSkip) . '">' . $translate->_($category['name']) . '</a>';
            }

            $categoryOutput = implode(' > ', $breadcrumbs);
        }
        ## /CATEGORY


        $glue = ' ' . $translate->_('in') . ' ';
        $output[self::PRIMARY] = implode($glue, array_filter(array($keywordsOutput, $categoryOutput)));


        ## OTHER SEARCH VARIABLES
        $searchElementsModel = new SearchElementsModel();
        $searchElementsModel->setData($params);
        $searchElements = $searchElementsModel->getElements();

        $searchElementsParams = array_diff_key($params, array_flip(array_merge(self::$paramTypes[self::PRIMARY], self::$paramTypes[self::PAGINATION])));
        $searchElementsParamsKeys = array_keys($searchElementsParams);

        foreach ($searchElements as $searchElement) {
            if (($key = array_search($searchElement['id'], $searchElementsParamsKeys)) !== false) {
                if ($searchElement['element'] == '\\Ppb\\Form\\Element\\Range') {
                    $prefix = (!empty($searchElement['prefix'])) ? $searchElement['prefix'] . ' ' : null;
                    $suffix = (!empty($searchElement['suffix'])) ? ' ' . $searchElement['suffix'] : null;
                    $rangeFrom = !empty($searchElementsParams[$searchElement['id']][Range::RANGE_FROM]) ?
                        sprintf($translate->_('From %s'), $prefix . $searchElementsParams[$searchElement['id']][Range::RANGE_FROM] . $suffix) : null;
                    $rangeTo = !empty($searchElementsParams[$searchElement['id']][Range::RANGE_TO]) ?
                        sprintf($translate->_('To %s'), $prefix . $searchElementsParams[$searchElement['id']][Range::RANGE_TO] . $suffix) : null;

                    if ($rangeTo || $rangeFrom) {
                        $output[] = $translate->_($searchElement['label']) . ': ' . $rangeFrom . ' ' . $rangeTo;
                    }
                }
                else if (array_key_exists('multiOptions', $searchElement)) {
                    $multiOptions = array();

                    $searchElementMultiOptions = \Ppb\Utility::unserialize($searchElement['multiOptions']);

                    if ($searchElementMultiOptions !== $searchElement['multiOptions']) {
                        $keys = (isset($searchElementMultiOptions['key'])) ? array_values($searchElementMultiOptions['key']) : array();
                        $values = (isset($searchElementMultiOptions['value'])) ? array_values($searchElementMultiOptions['value']) : array();

                        $searchElementMultiOptions = array_filter(
                            array_combine($keys, $values));
                    }

                    if (is_array($searchElementMultiOptions)) {
                        foreach ($searchElementMultiOptions as $k => $v) {
                            if (!empty($k) && in_array($k, (array)$searchElementsParams[$searchElement['id']])) {
                                $multiOptions[] = $translate->_($v);
                            }
                        }

                        if (count($multiOptions) > 0) {
                            $output[] = sprintf($translate->_('%s: %s'), $translate->_($searchElement['label']), implode(', ', $multiOptions));
                        }
                    }
                }

                unset($searchElementsParamsKeys[$key]);
            }
        }
        ## /OTHER SEARCH VARIABLES


        ## PAGINATION
        $page = $request->getParam('page');
        if ($page > 1) {
            /** @var \Cube\Paginator $paginator */
            $paginator = $view->listings()->fetchPaginator($params)->getPaginator();
            $output['page'] = sprintf($translate->_('Page %s of %s'), $page, $paginator->getPages()->last); // ???
        }
        ## /PAGINATION


        ## ITEMS PER PAGE
        $defaultLimit = Paginator::ITEM_COUNT_PER_PAGE;
        $limit = $request->getParam('limit', $defaultLimit);
        if ($limit != $defaultLimit) {
            $output['limit'] = sprintf($translate->_('%s items per page'), $limit);
        }
        ## /ITEMS PER PAGE


        ## SORT
        $sort = $request->getParam('sort');
        if (!empty($sort)) {
            /** @var \Cube\Navigation\Page\AbstractPage $container */
            $container = $view->navigation()->getInitialContainer()->findOneBy('sort', $sort);
            if ($container !== null) {
                $output['sort'] = sprintf($translate->_('%s'), $container->getLabel());
            }
        }

        ## /SORT

        $this->setOutput($output);

        return $this;
    }

    /**
     *
     * render partial
     *
     * @return string
     */
    public function render()
    {
        $view = $this->getView();

        $view->setVariables(array(
            'output' => $this->getOutput()
        ));

        return $view->process(
            $this->getPartial(), true);
    }
}

