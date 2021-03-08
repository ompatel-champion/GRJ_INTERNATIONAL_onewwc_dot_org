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

namespace Listings\Controller;

use Ppb\Controller\Action\AbstractAction,
    Cube\Controller\Front,
    Ppb\Service,
    Listings\Form,
    Ppb\Db\Table\Row\User as UserModel;

class Browse extends AbstractAction
{

    public function Index()
    {
        $view = Front::getInstance()->getBootstrap()->getResource('view');

        $store = $this->_getStoreFromParams();

        $categoriesService = new Service\Table\Relational\Categories();

        $parentId = $this->getRequest()->getParam('parent_id');
        $categorySlug = $this->getRequest()->getParam('category_slug');

        $category = null;

        if (!$parentId && $categorySlug) {
            $category = $categoriesService->findBy('slug', $categorySlug);

            if ($category !== null) {
                $this->getRequest()->setParam('parent_id', $category->getData('id'));
            }
        }

        $displaySellingSortFilter = true;

        $filter = (array)$this->getRequest()->getParam('filter');
        if (in_array('ending-soon', $filter)) {
            $this->getRequest()->setParam('sort', 'ending_asc');
            $displaySellingSortFilter = false;
        }
        if (in_array('popular', $filter)) {
            $this->getRequest()->setParam('sort', 'clicks_desc');
            $displaySellingSortFilter = false;
        }
        if (in_array('recent', $filter)) {
            $this->getRequest()->setParam('sort', 'started_desc');
            $displaySellingSortFilter = false;
        }

        $basicSearchForm = new Form\Search(array('basic', 'item'), null, $store);
        $basicSearchForm->setData(
            $this->getRequest()->getParams())
            ->generateBasicForm();

        // META TAGS
        $view->headTitle()->prepend(
            strip_tags($view->browsePageTitle('partials/browse-page-title.phtml')->render())
        );

        return array(
            'parentId'        => $this->getRequest()->getParam('parent_id'),
            'messages'        => $this->_flashMessenger->getMessages(),
            'params'          => $this->getRequest()->getParams(),
            'basicSearchForm' => $basicSearchForm,
            'displaySellingSortFilter' => $displaySellingSortFilter,
        );
    }

    public function Store()
    {
        $store = $this->_getStoreFromParams();

        $showStore = false;
        if ($store instanceof UserModel) {
            if ($store->storeStatus(true)) {
                $showStore = true;

                $view = Front::getInstance()->getBootstrap()->getResource('view');

                $storeSettings = $store->getStoreSettings();
                // META TAGS
                $view->headTitle()->set(strip_tags($store->storeName()));

                if (!empty($storeSettings['store_meta_description'])) {
                    $view->headMeta()->setName('description', strip_tags($storeSettings['store_meta_description']));
                }

                $page = $this->getRequest()->getParam('page');

                switch ($page) {
                    case 'store_about':
                        $view->headTitle()->prepend($this->_('About Us'));
                        break;
                    case 'store_shipping_information':
                        $view->headTitle()->prepend($this->_('Shipping Information'));
                        break;
                    case 'store_company_policies':
                        $view->headTitle()->prepend($this->_('Company Policies'));
                        break;
                }
            }
        }
        if (!$showStore) {
            // if the store is not active, forward to the not found page
            $this->_helper->redirector()->redirect('not-found', 'error', null, array());
        }

        return array(
            'store'  => $store,
            'params' => $this->getRequest()->getParams(),
            'page'   => $this->getRequest()->getParam('page'),
        );
    }

    public function FavoriteStore()
    {
        $view = Front::getInstance()->getBootstrap()->getResource('view');

        $users = new Service\Users();
        $store = $users->findBy('id', $this->getRequest()->getParam('id'));

        $this->_flashMessenger->setMessage(array(
            'msg'   => $store->isFavoriteStore($this->_user['id']) ?
                $this->_('The store has been removed from your favorites list.') :
                $this->_('The store has been added to your favorites list.'),
            'class' => 'alert-success',
        ));

        $store->processFavoriteStore($this->_user['id']);

        $this->_helper->redirector()->gotoUrl($view->url($store->storeLink()));
    }

    public function AdultCategoriesSplashPage()
    {
        return array(
            'headline' => $this->_('Warning: Adult Content'),
        );
    }

    /**
     *
     * get store object from params and set store_id param
     *
     * @return null|\Ppb\Db\Table\Row\User
     */
    protected function _getStoreFromParams()
    {
        $usersService = new Service\Users();

        $store = null;

        if ($storeId = $this->getRequest()->getParam('store_id')) {
            $store = $usersService->findBy('id', $storeId);
        }
        else if ($storeSlug = $this->getRequest()->getParam('store_slug')) {
            $store = $usersService->findBy('store_slug', $storeSlug);
        }

        if (!empty($store['id'])) {
            $this->getRequest()->setParam('store_id', $store['id']);
        }

        return $store;
    }
}

