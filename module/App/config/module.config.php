<?php

/**
 * @version 8.0 [rev.8.0.02]
 */

return array(
    'routes' => array(
        ## HOME PAGE
        'app-home'                             => array(
            '/',
            array(
                'controller' => 'index',
                'action'     => 'index',
            ),
        ),
        ## /HOME PAGE

        ## IPN
        'app-ipn'                              => array(
            'payment/ipn/:gateway',
            array(
                'controller' => 'payment',
                'action'     => 'ipn',
            ),
        ),
        ## /IPN

        ## ADVERT REDIRECT
        'app-advert-redirect'                  => array(
            'aredir/:id',
            array(
                'controller' => 'index',
                'action'     => 'advert-redirect',
            ),
        ),
        ## /ADVERT REDIRECT

        ## SECTION DEFAULT
        'app-section-name-id'                  => array(
            'section/:name/:id',
            array(
                'controller' => 'cms',
                'action'     => 'index',
                'type'       => 'section',
            ),
        ),
        ## /SECTION DEFAULT

        ## SECTION MULTIPLE PAGINATION
        'app-section-name-id-page'             => array(
            'section/:name/:id/page/:page',
            array(
                'controller' => 'cms',
                'action'     => 'index',
                'type'       => 'section',
            ),
        ),
        ## /SECTION MULTIPLE PAGINATION

        ## SECTION MULTIPLE POST OR ENTRY W/O SECTION
        'app-entry-title-id'                   => array(
            'entry/:title/:id',
            array(
                'controller' => 'cms',
                'action'     => 'index',
                'type'       => 'entry',
            ),
        ),
        ## /SECTION MULTIPLE POST OR ENTRY W/O SECTION

        ## RSS
        'app-rss-index'                        => array(
            'rss',
            array(
                'controller' => 'rss',
                'action'     => 'index',
            ),
        ),
        'app-rss-feed'                         => array(
            'rss/feed/:type',
            array(
                'controller' => 'rss',
                'action'     => 'feed',
            ),
        ),
        ## /RSS

        ## SITEMAP
        'app-sitemap'                          => array(
            'sitemap.xml',
            array(
                'controller' => 'index',
                'action'     => 'sitemap',
            ),
        ),
        ## /SITEMAP

        ## PLAY VIDEO LINK
        'link-play-video'                      => array(
            'play-video/:id',
            array(
                'controller' => 'index',
                'action'     => 'play-video',
            ),
        ),
        ## /PLAY VIDEO LINK

        ## PAYMENT ROUTES
        'app-payment-completed'                => array(
            'payment/completed',
            array(
                'controller' => 'payment',
                'action'     => 'completed',
            ),
        ),
        'app-payment-failed'                   => array(
            'payment/failed',
            array(
                'controller' => 'payment',
                'action'     => 'failed',
            ),
        ),
        'app-payment-completed-transaction-id' => array(
            'payment/completed/:transaction_id',
            array(
                'controller' => 'payment',
                'action'     => 'completed',
            ),
        ),
        'app-payment-failed-transaction-id'    => array(
            'payment/failed/:transaction_id',
            array(
                'controller' => 'payment',
                'action'     => 'failed',
            ),
        ),
        ## /PAYMENT ROUTES

        ## NOT FOUND
        'app-error-notfound'                   => array(
            'not-found',
            array(
                'controller' => 'error',
                'action'     => 'not-found',
            ),
        ),
        ## /NOT FOUND
    ),
    'view'   => array(
        'layouts_path' => __DIR__ . '/../view/layout',
        'views_path'   => __DIR__ . '/../view',
        'layout_file'  => 'layout.phtml',
    ),
);
