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
 * adult category check controller plugin class
 * the plugin will be called when trying to view a listing or browse an adult category
 */

namespace Listings\Controller\Plugin;

use Cube\Controller\Plugin\AbstractPlugin,
    Cube\Controller\Front,
    Ppb\Service,
    Ppb\Db\Table\Row\Listing as ListingModel,
    Ppb\Db\Table\Row\Category as CategoryModel;

class AdultCategoryCheck extends AbstractPlugin
{


    const AGREE_ADULT_COOKIE = 'AgreeAdultCookie';

    /**
     *
     * bootstrap
     *
     * @var \Cube\Application\Bootstrap
     */
    protected $_bootstrap;

    public function __construct()
    {
        $this->_bootstrap = Front::getInstance()->getBootstrap();
    }

    public function preDispatch()
    {
        $request = $this->getRequest();

        $controller = $request->getController();
        $action = $request->getAction();

        $isAdultCategory = false;

        /** @var \Cube\Session $session */
        $session = $this->_bootstrap->getResource('session');

        if ($request->getParam('agree_adult')) {
            $session->set(self::AGREE_ADULT_COOKIE, 1);
            $request->clearParam('agree_adult');
        }

        $agreeAdultCookie = $session->get(self::AGREE_ADULT_COOKIE);

        if (!$agreeAdultCookie) {
            if ($controller == 'Browse' && $action == 'Index') {
                $parentId = $this->getRequest()->getParam('parent_id');
                $slug = $this->getRequest()->getParam('category_slug');

                $categoriesService = new Service\Table\Relational\Categories();
                $category = null;

                if ($slug) {
                    $category = $categoriesService->findBy('slug', $slug);
                }
                else if ($parentId) {
                    $category = $categoriesService->findBy('id', $parentId);
                }

                if ($category instanceof CategoryModel) {
                    if ($category->getData('adult')) {
                        $isAdultCategory = true;
                    }
                }
            }
            else if ($controller == 'Listing' && $action == 'Details') {
                $listingsService = new Service\Listings();
                $listing = $listingsService->findBy('id', $request->getParam('id'));

                if ($listing instanceof ListingModel) {
                    if ($listing->isAdult()) {
                        $isAdultCategory = true;
                    }
                }
            }

            if ($isAdultCategory === true) {
                $controller = 'browse';
                $action = 'adult-categories-splash-page';

                $request->setController($controller)
                    ->setAction($action);
            }
        }
    }

}

