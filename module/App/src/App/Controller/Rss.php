<?php

/**
 *
 * PHP Pro Bid
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2018 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     8.0 [rev.8.0.02]
 */

/**
 * rss feed controller
 */

namespace App\Controller;

use Ppb\Controller\Action\AbstractAction,
    Ppb\Service,
    Cube\Controller\Front,
    Cube\Feed,
    Cube\View;

class Rss extends AbstractAction
{
    protected $_feeds = array(
        'homepage' => 'Home Page Featured',
        'recent'   => 'Recently Listed',
        'ending'   => 'Ending Soon',
        'popular'  => 'Popular Listings',
    );

    public function Index()
    {
        return array(
            'headline' => $this->_('RSS Feeds'),
            'feeds'    => $this->_feeds,
        );
    }

    public function Feed()
    {
        $feedType = $this->getRequest()->getParam('type');

        if (!array_key_exists($feedType, $this->_feeds)) {
            $this->_helper->redirector()->notFound();
        }

        $mainView = Front::getInstance()->getBootstrap()->getResource('view');

        $view = new View();

        $rss = new Feed\Rss();

        $rss->setChannels(array(
            'title'       => $this->_settings['sitename'] . ' :: ' . $this->_feeds[$feedType],
            'link'        => $this->_settings['site_path'],
            'description' => $this->_settings['meta_description'],
            'image'       => array(
                'url'   => $this->_settings['site_path'] . '/'
                    . \Ppb\Utility::getFolder('uploads') . '/'
                    . $this->_settings['site_logo_path'],
                'title' => $this->_settings['sitename'],
                'link'  => $this->_settings['site_path'],
            ),
        ));

        $params = array();
        switch ($feedType) {
            case 'homepage':
                $params['filter'] = 'hpfeat';
                $params['sort'] = 'rand';
                break;
            case 'recent':
                $params['sort'] = 'started_desc';
                break;
            case 'ending':
                $params['filter'] = 'ending-soon';
                $params['sort'] = 'ending_asc';
                break;
            case 'popular':
                $params['sort'] = 'clicks_desc';
                break;
        }

        $listingsService = new Service\Listings();
        $listings = $listingsService->fetchAll(
            $listingsService->select(Service\Listings::SELECT_LISTINGS, $params)->limit(20)
        );

        $categoriesService = new Service\Table\Relational\Categories();


        /** @var \Ppb\Db\Table\Row\Listing $listing */
        foreach ($listings as $listing) {
            $link = $this->_settings['site_path'] . $mainView->url($listing->link(), null, false, null, false);

            $category = implode(' :: ', $categoriesService->getBreadcrumbs($listing['category_id']));

            $image = $mainView->thumbnail($listing->getMainImage(), 200, true,
                array('alt' => $listing['name'], 'class' => ''));

            $description = '<![CDATA['
                . '<p>' . $image . '</p>'
                . $listing->shortDescription(500)
                . ']]>';

            $entry = new Feed\Entry();
            $entry->setElements(array(
                'title'       => $listing['name'],
                'description' => $description,
                'link'        => $link,
                'guid'        => $link,
                'category'    => $category,
                'pubDate'     => date(DATE_RFC2822, strtotime($listing['start_time'])),

            ));
            $rss->addEntry($entry);
        }

        $view->setContent($rss->generateFeed());


        return $view;
    }
}

