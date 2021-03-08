<?php

/**
 *
 * PHP Pro Bid
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2018 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     8.0 [rev.8.0.03]
 */

/**
 * this plugin will populate automatically the meta tags view helpers (headTitle and headMeta)
 */

namespace App\Controller\Plugin;

use Cube\Controller\Plugin\AbstractPlugin,
    Cube\Controller\Front,
    Cube\Navigation;

class MetaTags extends AbstractPlugin
{

    /**
     *
     * set up meta tags automatically based on the active request
     * this plugin will only set default/generic meta tags;
     *
     * specific meta tags will be set in the following actions:
     *
     * - App / Index / Index
     * - App / Cms / Index
     * - Listings / Categories / Browse
     * - Listings / Browse / Index
     * - Listings / Browse / Store
     * - Listings / Listing / Details
     *
     * @return void
     */
    public function preDispatcher()
    {
        $bootstrap = Front::getInstance()->getBootstrap();
        $view = $bootstrap->getResource('view');
        $settings = $bootstrap->getResource('settings');
        $navigation = $bootstrap->getResource('navigation');

        $module = $this->getRequest()->getModule();

        $view->headMeta()->setCharset('utf-8');

        $minDepth = 1;

        if ($module == 'Admin') {
            $minDepth = 2;
            $view->headTitle()->set('PHP Pro Bid Admin Control Panel');
            $view->headMeta()->setName('robots', 'noindex, nofollow');
        }
        else {
            $view->headTitle()->set($settings['sitename']);

            if (!empty($settings['meta_data'])) {
                $metaData = \Ppb\Utility::unserialize($settings['meta_data']);
                if (isset($metaData['key'])) {
                    foreach ($metaData['key'] as $key => $value) {
                        if (!empty($value)) {
                            $view->headMeta()->appendName($value, $metaData['value'][$key]);
                        }
                    }
                }
            }
        }

        if ($navigation instanceof Navigation) {
            /** @var \Cube\View\Helper\Navigation $navigationHelper */
            $navigationHelper = $view->navigation()->setMinDepth($minDepth);
            $breadcrumbs = $navigationHelper->getBreadcrumbs();

            if (count($breadcrumbs) > 0) {
                $headTitle = array();

                foreach ($breadcrumbs as $breadcrumb) {
                    $headTitle[] = $breadcrumb->label;
                }

                $view->headTitle()->prepend(implode(' / ', array_filter(array_reverse($headTitle))));
            }
        }
    }

    public function preDispatch()
    {
        $response = $this->getResponse();
        if ($response->getResponseCode() == 404) {
            $bootstrap = Front::getInstance()->getBootstrap();
            $view = $bootstrap->getResource('view');
            $settings = $bootstrap->getResource('settings');

            $view->headTitle()->set($settings['sitename']);
            $view->headTitle()->prepend('Page Not Found');
        }
    }
}

