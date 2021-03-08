<?php

/**
 *
 * PHP Pro Bid
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2018 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     8.1 [rev.8.1.01]
 */

/**
 * index controller
 */

namespace App\Controller;

use Ppb\Controller\Action\AbstractAction,
    Cube\Controller\Front,
    Cube\View,
    Cube\Validate\Url as UrlValidator,
    Ppb\Service,
    Ppb\Db\Table\Row\Advert as AdvertModel;

class Index extends AbstractAction
{

    /**
     *
     * this action doesn't do anything, all content is generated in the view helper
     * in order for it to be theme specific
     *
     * @return array
     */
    public function Index()
    {
        $view = Front::getInstance()->getBootstrap()->getResource('view');


        if (!empty($this->_settings['meta_title'])) {
            $view->headTitle()->set($this->_settings['meta_title']);
            $view->headMeta()->setProperty('og:type', 'website')
                ->setProperty('og:title', $this->_settings['meta_title'])
                ->setProperty('og:url', $this->_settings['site_path']);
        }

        if (!empty($this->_settings['meta_description'])) {
            $view->headMeta()->setName('description', $this->_settings['meta_description']);

            $view->headMeta()->setProperty('og:description', $this->_settings['meta_description']);
        }

        if (!empty($this->_settings['site_logo_path'])) {
            $siteLogo = $this->_settings['site_path'] . '/' . \Ppb\Utility::getFolder('uploads') . '/' . $this->_settings['site_logo_path'];
            $view->headMeta()
                ->setProperty('og:image', $siteLogo)
                ->setProperty('og:image:width', '300')
                ->setProperty('og:image:height', '300');
        }

        // Twitter cards
        $view->headMeta()
            ->setName('twitter:card', 'summary');

        return array(
            'indexPage' => true,
        );
    }

    /**
     *
     * this action will count the click for an advert and redirect to the advert's url
     */
    public function AdvertRedirect()
    {
        $id = $this->getRequest()->getParam('id');

        $advertsService = new Service\Advertising();

        /** @var \Ppb\Db\Table\Row\Advert $advert */
        $advert = $advertsService->findBy('id', $id);

        if ($advert instanceof AdvertModel) {
            $advert->addClick();
            $this->_helper->redirector()->gotoUrl($advert['url']);
        }
        else {
            $this->_helper->redirector()->notFound();
        }
    }

    public function PlayVideo()
    {
        $id = $this->getRequest()->getParam('id');
        $listingsMediaService = new Service\ListingsMedia();

        /** @var \Ppb\Db\Table\Row\ListingMedia $video */
        $video = $listingsMediaService->findBy('id', $id);

        $this->_setNoLayout();

        return array(
            'video' => $video,
        );
    }

    public function Sitemap()
    {
        $this->getResponse()->setHeader('Content-Type: text/xml; charset=utf-8');

        /** @var \Ppb\View\Helper\Url $urlHelper */
        $urlHelper = Front::getInstance()->getBootstrap()->getResource('view')->getHelper('url');

        $view = new View();
        $pages = array();

        $pages[] = array(
            'loc'        => $urlHelper->url(null, 'app-home'),
            'changefreq' => 'daily',
            'priority'   => '1.0'
        );

        $categoriesService = new Service\Table\Relational\Categories();
        $categories = $categoriesService->fetchAll(
            $categoriesService->getTable()
                ->select(array('id', 'name', 'slug'))
                ->where('parent_id is null')
                ->where('enable_auctions = ?', 1)
                ->where('user_id IS NULL')
        );

        /** @var \Ppb\Db\Table\Row\Category $category */
        foreach ($categories as $category) {
            $pages[] = array(
                'loc'        => $urlHelper->url($category->link()),
                'changefreq' => 'daily',
                'priority'   => '0.5',
            );
        }

        $listingsService = new Service\Listings();
        $listings = $listingsService->fetchAll(
            $listingsService->select(Service\Listings::SELECT_LISTINGS)
        );

        /** @var \Ppb\Db\Table\Row\Listing $listing */
        foreach ($listings as $listing) {
            $pages[] = array(
                'loc'        => $urlHelper->url($listing->link()),
                'changefreq' => 'daily',
                'priority'   => '1.0',
            );
        }

        $contentSectionsService = new Service\Table\Relational\ContentSections();
        $contentSections = $contentSectionsService->fetchAll(
            $contentSectionsService->getTable()->select()
                ->where('active = ?', 1)
        );

        $urlValidator = new UrlValidator();
        /** @var \Ppb\Db\Table\Row\ContentSection $contentSection */
        foreach ($contentSections as $contentSection) {
            $urlValidator->setValue($contentSection['slug']);
            if (!$urlValidator->isValid()) {
                $pages[] = array(
                    'loc'        => $urlHelper->url($contentSection->link()),
                    'changefreq' => 'weekly',
                    'priority'   => '0.4'
                );
            }
        }

        $content = '<?xml version="1.0" encoding="UTF-8"?>
            <urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        foreach ($pages as $page) {
            $content .= '<url>
		        <loc>' . htmlentities($page['loc']) . '</loc>
		        <changefreq>' . $page['changefreq'] . '</changefreq>
		        <priority>' . $page['priority'] . '</priority>
	        </url>';
        }

        $content .= '</urlset>';

        $view->setContent($content);

        return $view;
    }

    public function MaintenanceMode()
    {
        $this->_setNoLayout();

        return array();
    }
}

