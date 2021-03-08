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

namespace Admin\Controller;

use Ppb\Controller\Action\AbstractAction,
    Cube\Controller\Front,
    Cube\Authentication\Authentication,
    Ppb\Authentication\Adapter,
    Cube\View,
    Cube\Db\Expr,
    Cube\Cache,
    Ppb,
    Admin\Form;

class Index extends AbstractAction
{

    public function Index()
    {
        return array();
    }

    public function Login()
    {
        $view = Front::getInstance()->getBootstrap()->getResource('view');
        $view->setLayout('login.phtml');
        $view->headTitle()->prepend('Login');

        $loginForm = new Form\Login();

        if ($this->getRequest()->isPost()) {
            $loginForm->setData($this->getRequest()->getParams());

            $adapter = new Adapter(
                $this->getRequest()->getParams(),
                null,
                \Ppb\Service\Users::getAdminRoles()
            );

            $adapter->setCheckBlockedUser(false);

            $authentication = Authentication::getInstance();

            $result = $authentication->authenticate($adapter);

            if ($authentication->hasIdentity()) {
                $redirectUrl = $this->getRequest()->getBaseUrl() .
                    $this->getRequest()->getRequestUri();
                $this->_helper->redirector()->gotoUrl($redirectUrl);
            }
            else {
                $this->_flashMessenger->setMessage(array(
                    'msg'   => $result->getMessages(),
                    'class' => 'alert-danger',
                ));
            }
        }

        return array(
            'loginForm' => $loginForm,
            'messages'  => $this->_flashMessenger->getMessages(),
        );
    }

    public function Logout()
    {
        Authentication::getInstance()->clearIdentity();

        $this->_helper->redirector()->redirect('login', 'index', 'admin');
    }

    public function QuickNavigation()
    {
        $this->getResponse()->setHeader('Content-Type: application/json');

        $input = $this->getRequest()->getParam('input');
        $limit = $this->getRequest()->getParam('limit');

        $counter = 0;

        $view = new View();

        /** @var \Cube\Navigation $navigation */
        $navigation = Front::getInstance()->getBootstrap()->getResource('navigation');

        $container = $navigation->findOneBy('label', $this->getTranslate()->_('Home'));
        $pages = $container->findAllBy('label', $input, false);

        $data = array();

        if (strlen($input) > 0) {
            $pageHeader = null;

            // first we search for standard pages
            /** @var \Cube\Navigation\Page\AbstractPage $page */
            foreach ($pages as $page) {
                if (!$page->hidden && !$page->filter) {
                    $parentLabel = $page->getParent()->getLabel();

                    if (!$page->hasChildren() || $page->hidden_children) {
                        if ($parentLabel != $pageHeader) {
                            $data[] = array(
                                'itemType' => 'header',
                                'label'    => $parentLabel,
                                'path'     => null,
                            );
                            $pageHeader = $parentLabel;
                        }

                        $data[] = array(
                            'itemType' => 'item',
                            'label'    => $page->getLabel(),
                            'path'     => $view->url($page->getParams()),
                        );

                        $counter++;

                        if ($counter > $limit && $limit > 0) {
                            break;
                        }
                    }
                }
            }

            // if no standard pages match, we search for headlines
            if (count($data) === 0) {
                /** @var \Cube\Navigation\Page\AbstractPage $page */
                foreach ($pages as $page) {
                    if (!$page->hidden && !$page->filter) {
                        $data[] = array(
                            'itemType' => 'header',
                            'label'    => $page->getLabel(),
                            'path'     => null,
                        );

                        $children = $page->getPages();

                        /** @var \Cube\Navigation\Page\AbstractPage $child */
                        foreach ($children as $child) {
                            if (!$child->hasChildren() || $child->hidden_children) {
                                $data[] = array(
                                    'itemType' => 'item',
                                    'label'    => $child->getLabel(),
                                    'path'     => $view->url($child->getParams()),
                                );
                            }
                        }

                        $counter++;

                        if ($counter > $limit && $limit > 0) {
                            break;
                        }
                    }
                }
            }
        }

        $view->setContent(
            json_encode($data));

        return $view;
    }

    public function InitializeCategoryCounters()
    {
        $limit = $this->getRequest()->getParam('limit', 500);
        $offset = $this->getRequest()->getParam('offset', 0);

        $categoriesService = new Ppb\Service\Table\Relational\Categories();
        if ($offset == 0) {
            $categoriesService->resetCounters();
        }


        $listingsService = new Ppb\Service\Listings();
        $select = $listingsService->select(Ppb\Service\Listings::SELECT_COUNTER)
            ->limit($limit, $offset)
            ->order('id ASC');


        $listings = $listingsService->fetchAll($select);

        $counter = 0;
        /** @var \Ppb\Db\Table\Row\Listing $listing */
        foreach ($listings as $listing) {
            $counted = $listing->processCategoryCounter(true);
            if ($counted) {
                $counter++;
            }

        }

        $this->getResponse()->setHeader('Content-Type: application/json');

        $view = new View();
        $view->setContent(json_encode(array(
            'counter' => $counter,
        )));

        return $view;
    }

    public function CountListings()
    {
        $this->getResponse()->setHeader('Content-Type: application/json');

        $listingsService = new Ppb\Service\Listings();
        $select = $listingsService->select(Ppb\Service\Listings::SELECT_COUNTER);

        $select->columns(array('nb_rows' => new Expr('count(*)')));

        $stmt = $select->query();

        $view = new View();
        $view->setContent(json_encode(array(
            'counter' => (integer)$stmt->fetchColumn('nb_rows'),
        )));

        return $view;
    }

    public function ClearCache()
    {
        $cacheAdapters = array('Files', 'Table', 'Apc', 'Memcache');

        $cacheConfig = Front::getInstance()->getOption('cache');

        foreach ($cacheAdapters as $cacheAdapter) {
            /** @var \Cube\Cache\Adapter\AbstractAdapter $adapterClassName */
            $adapterClassName = '\\Cube\\Cache\\Adapter\\' . $cacheAdapter;

            if ($adapterClassName::enabled()) {
                /** @var \Cube\Cache\Adapter\AbstractAdapter $adapterClass */
                $adapterClass = new $adapterClassName($cacheConfig);

                $cache = Cache::getInstance()->setAdapter($adapterClass);

                /** @var \Cube\Cache\Adapter\Files $adapter */
                $adapter = $cache->getAdapter();

                $adapter->setExpires(0)
                    ->purge(Cache\Adapter\AbstractAdapter::METADATA)
                    ->purge(Cache\Adapter\AbstractAdapter::ROUTES)
                    ->purge(Cache\Adapter\AbstractAdapter::QUERIES);
            }
        }

        $this->getResponse()->setHeader('Content-Type: application/json');

        $view = new View();
        $view->setContent(json_encode(array(
            'message' => $this->_('Cache cleared successfully.'),
        )));

        return $view;
    }

    public function DeleteCachedImages()
    {
        $counter = 0;
        $cacheFolder = \Ppb\Utility::getPath('cache');

        // we have sorted all files by modified date
        $files = glob($cacheFolder . '/*');

        foreach ($files as $filePath) {
            if (is_file($filePath)) {
                @unlink($filePath); // delete file
                $counter++;
            }
        }

        clearstatcache();

        $this->getResponse()->setHeader('Content-Type: application/json');

        $view = new View();
        $view->setContent(json_encode(array(
            'message' => sprintf($this->_('%s cached images have been deleted.'), $counter),
        )));

        return $view;
    }
}

