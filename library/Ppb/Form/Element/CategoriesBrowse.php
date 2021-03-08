<?php

/**
 *
 * PHP Pro Bid
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2018 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     8.0 [rev.8.0.05]
 */

/**
 * categories browse custom form element
 */

namespace Ppb\Form\Element;

use Cube\Form\Element,
    Cube\Controller\Front,
    Ppb\Service\Table\Relational\Categories as CategoriesService;

class CategoriesBrowse extends Element
{

    const ACTIVE_CATEGORY = 'active-category';
    const STORES_CATEGORIES = 'stores-categories';
    const COUNTER_FILTER = 'counter-filter';

    /**
     *
     * type of element - override the variable from the parent class
     *
     * @var string
     */
    protected $_element = 'CategoriesBrowse';

    /**
     *
     * view object
     *
     * @var \Cube\View
     */
    protected $_view;

    /**
     *
     * active request
     *
     * @var \Cube\Controller\Request\AbstractRequest
     */
    protected $_request;

    /**
     *
     * class constructor
     *
     * @param string $name
     */
    public function __construct($name)
    {
        parent::__construct('text', $name);

        $frontController = Front::getInstance();

        $this->_view = $frontController->getBootstrap()->getResource('view');
        $this->_request = $frontController->getRequest();
    }

    public function render()
    {
        $output = null;

        $translate = $this->getTranslate();
        $settings = Front::getInstance()->getBootstrap()->getResource('settings');

        $categoriesService = new CategoriesService();

        $activeCategory = (isset($this->_attributes[self::ACTIVE_CATEGORY])) ? $this->_attributes[self::ACTIVE_CATEGORY] : null;
        $storesCategories = (isset($this->_attributes[self::STORES_CATEGORIES])) ? $this->_attributes[self::STORES_CATEGORIES] : false;
        $counterFilter = (isset($this->_attributes[self::COUNTER_FILTER])) ? $this->_attributes[self::COUNTER_FILTER] : null;

        $skipParams = array('parent_id', 'category_name', 'category_slug', 'page', 'submit', 'submit_search');

        $params = $this->_request->getParams();

        $params = array_filter(
            $params, function (&$element) {
            if (is_array($element)) {
                return array_filter($element) ? true : false;
            }

            return (!empty($element));
        });

        $action = (!empty($params['action'])) ? $params['action'] : null;

        if ($activeCategory !== null) {
            $output .= '<div class="category-breadcrumbs">';
            $breadcrumbs = array();

            foreach ($activeCategory as $key => $value) {
                if ($storesCategories || $action == 'store') {
                    $categoryLink = array('parent_id' => $key);
                    $paramsToSkip = $skipParams;
                }
                else {
                    /** @var \Ppb\Db\Table\Row\Category $category */
                    $category = $categoriesService->findBy('id', $key);
                    $categoryLink = $category->link();
                    $paramsToSkip = array_diff($skipParams, array_keys($categoryLink));
                }

                $breadcrumbs[] = '<a href="' . $this->_view->url($categoryLink, null, true, $paramsToSkip) . '">' . $translate->_($value) . '</a> ';
            }
            $output .= implode(' > ', $breadcrumbs)
                . '[ <a href="' . $this->_view->url(null, null, true, $skipParams) . '">' . $translate->_('Reset') . '</a> ]'
                . '</div>';
        }

        /** @var \Ppb\Db\Table\Row\Category $category */
        foreach ($this->_customData['rowset'] as $category) {
            $counter = $category->getCounter($counterFilter);

            if ($counter > 0 || !$settings['hide_empty_categories'] || count($params) > 0 || $storesCategories) {
                if ($storesCategories || $action == 'store') {
                    $categoryLink = array('parent_id' => $category['id']);
                    $paramsToSkip = $skipParams;
                }
                else {
                    $categoryLink = $category->link();
                    $paramsToSkip = array_diff($skipParams, array_keys($categoryLink));
                }

                $output .= '<div><a href="' . $this->_view->url($categoryLink, null, true, $paramsToSkip) . '">'
                    . $translate->_($category['name'])
                    . (($settings['category_counters'] && !count($params) && !$storesCategories) ? ' (' . $counter . ')' : '')
                    . '</a></div>';

            }
        }

        $output .= '<input type="hidden" name="' . $this->_name . '" value="' . $this->getValue() . '" '
            . $this->_endTag;

        return $output;
    }

}

