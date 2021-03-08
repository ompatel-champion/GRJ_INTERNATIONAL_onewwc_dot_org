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
 * advert display view helper class
 */

namespace App\View\Helper;

use Ppb\View\Helper\AbstractHelper,
    Cube\Controller\Front,
    Ppb\Service\Advertising as AdvertisingService,
    Ppb\Service\Table\Relational\Categories as CategoriesService,
    Cube\Db\Select,
    Cube\Db\Expr;

class Advert extends AbstractHelper
{

    /**
     *
     * the view partial to be used
     *
     * @var string
     */
    protected $_partial = 'partials/advert-single.phtml';

    /**
     *
     * advertising service
     *
     * @var \Ppb\Service\Advertising
     */
    protected $_advertising;

    /**
     *
     * adverts rowset
     *
     * @var \Ppb\Db\Table\Rowset\Adverts|null
     */
    protected $_adverts = null;

    /**
     *
     * get adverts rowset
     *
     * @return \Ppb\Db\Table\Rowset\Adverts|null
     * @throws \InvalidArgumentException
     */
    public function getAdverts()
    {
        return $this->_adverts;
    }

    /**
     *
     * set adverts rowset
     *
     * @param mixed $adverts
     *
     * @return $this
     */
    public function setAdverts($adverts)
    {
        $this->_adverts = $adverts;

        return $this;
    }


    /**
     *
     * get content sections table service
     *
     * @return \Ppb\Service\Advertising
     */
    public function getAdvertising()
    {
        if (!$this->_advertising instanceof AdvertisingService) {
            $this->setAdvertising(
                new AdvertisingService());
        }

        return $this->_advertising;
    }

    /**
     *
     * set advertising service
     *
     * @param \Ppb\Service\Advertising $advertising
     *
     * @return $this
     */
    public function setAdvertising(AdvertisingService $advertising)
    {
        $this->_advertising = $advertising;

        return $this;
    }

    /**
     *
     * get advert url
     *
     * @param \Ppb\Db\Table\Row\Advert $advert
     *
     * @return string
     */
    public function url($advert)
    {
        $addBaseUrl = ($advert['direct_link']) ? false : true;

        return $this->getView()->url($advert->link(), null, false, null, $addBaseUrl);
    }

    /**
     *
     * advert helper main method
     *
     * @param string $partial
     *
     * @return $this
     */
    public function advert($partial = null)
    {
        if ($partial !== null) {
            $this->setPartial($partial);
        }

        return $this;
    }

    /**
     *
     *
     * return one or an array of advert objects
     *
     * @param string $section
     * @param bool   $limit if true, return all adverts from the requested section
     * @param array  $categoryIds
     * @param bool   $rand  randomize result(s)
     *
     * @return $this
     */
    public function findBySection($section, $limit = false, $categoryIds = array(), $rand = true)
    {
        $select = $this->getAdvertising()->getTable()
            ->select(array('nb_rows' => new Expr('count(*)')))
            ->where('section = ?', $section)
            ->where('active = ?', 1);

        $categoriesFilter = array(0);

        $categoryIds = array_filter($categoryIds);

        if (count($categoryIds) > 0) {
            $categoriesService = new CategoriesService();

            foreach ($categoryIds as $categoryId) {
                $categoriesFilter = array_merge($categoriesFilter, array_keys(
                    $categoriesService->getBreadcrumbs($categoryId)));
            }
        }

        $select->where("category_ids REGEXP '\"" . implode('"|"',
                array_unique($categoriesFilter)) . "\"' OR category_ids = ''");

        $locale = Front::getInstance()->getBootstrap()->getResource('locale')->getLocale();

        $select->where("language = ? OR language IS NULL", $locale);

        $stmt = $select->query();

        $nbAdverts = (integer)$stmt->fetchColumn('nb_rows');

        if (!$nbAdverts) {
            $this->setAdverts(null);
        }

        $select->reset(Select::COLUMNS)
            ->columns('*');

        if ($rand === true) {
            $select->order(new Expr('rand()'));
        }

        if ($limit !== true) {
            $limit = ($limit === false) ? 1 : $limit;
            $select->limit($limit);
        }

        $this->setAdverts(
            $this->getAdvertising()->fetchAll($select));

        return $this;
    }

    /**
     *
     * legacy method
     *
     * return string
     */
    public function display()
    {
        return $this->render();
    }

    /**
     *
     * render partial
     *
     * @return string
     */
    public function render()
    {
        $adverts = $this->getAdverts();

        if ($adverts !== null) {
            if (count($adverts) > 0) {
                $view = $this->getView();

                $adverts->addView();

                $view->setVariables(array(
                    'adverts' => $adverts,
                ));

                return $view->process(
                    $this->getPartial(), true);
            }
        }

        return '';
    }
}

