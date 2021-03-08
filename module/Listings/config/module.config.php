<?php
/**
 * @version 8.1 [rev.8.1.01]
 */

return array(
    'routes' => array(
        'listings-create'           => array(
            'sell',
            array(
                'controller' => 'listing',
                'action'     => 'create',
            ),
        ),
        'listings-creation-confirm' => array(
            'sell/confirm',
            array(
                'controller' => 'listing',
                'action'     => 'confirm',
            ),
        ),
        'listings-create-similar'   => array(
            'sell/similar/:id',
            array(
                'controller' => 'listing',
                'action'     => 'create',
                'option'     => 'similar',
            ),
            array(
                'id' => '[\d]+',
            ),
        ),
        'listings-edit'             => array(
            'edit/:id',
            array(
                'controller' => 'listing',
                'action'     => 'create',
                'option'     => 'edit',
            ),
            array(
                'id' => '[\d]+',
            ),
        ),
        'listings-list-draft'       => array(
            'list-draft/:id',
            array(
                'controller' => 'listing',
                'action'     => 'create',
                'option'     => 'list-draft',
            ),
            array(
                'id' => '[\d]+',
            ),
        ),
        'listings-search'           => array(
            'search',
            array(
                'controller' => 'browse',
                'action'     => 'index',
            ),
        ),
        'listings-search-filtered-type'    => array(
            'search/:filter',
            array(
                'controller' => 'browse',
                'action'     => 'index',
            ),
        ),

        'listings-categories'                  => array(
            'categories',
            array(
                'controller' => 'categories',
                'action'     => 'browse',
            ),
        ),
        'listings-categories-browse'           => array(
            'categories/:category_name/:parent_id',
            array(
                'controller' => 'categories',
                'action'     => 'browse',
            ),
            array(
                'parent_id' => '[\d]+',
            ),
        ),
        'listings-categories-browse-sluggable' => array(
            'categories/:category_slug',
            array(
                'controller' => 'categories',
                'action'     => 'browse',
            ),
            array(
                'category_slug' => '[a-zA-Z0-9_\-]+',
            ),
        ),

        'listings-browse-category'                => array(
            'category/:category_name/:parent_id',
            array(
                'controller' => 'browse',
                'action'     => 'index',
            ),
            array(
                'parent_id' => '[\d]+',
            ),
        ),
        'listings-browse-category-sluggable'      => array(
            'category/:category_slug',
            array(
                'controller' => 'browse',
                'action'     => 'index',
            ),
            array(
                'category_slug' => '[a-zA-Z0-9_\-]+',
            ),
        ),
        'listings-browse-store'                   => array(
            'store/:name/:store_id',
            array(
                'controller' => 'browse',
                'action'     => 'store',
            ),
            array(
                'user_id' => '[\d]+',
            ),
        ),
        'listings-browse-store-sluggable'         => array(
            'store/:store_slug',
            array(
                'controller' => 'browse',
                'action'     => 'store',
            ),
        ),
        'listings-browse-other-items'             => array(
            'other-items/:username/:user_id',
            array(
                'controller' => 'browse',
                'action'     => 'index',
                'filter'     => 'other-items',
            ),
            array(
                'user_id' => '[\d]+',
            ),
        ),
        'listings-listing-details'                => array(
            'listing/:name/:id',
            array(
                'controller' => 'listing',
                'action'     => 'details',
            ),
            array(
                'id' => '[\d]+',
            ),
        ),
        'listings-advanced-search'                => array(
            'advanced-search',
            array(
                'controller' => 'search',
                'action'     => 'advanced',
            ),
        ),
        'listings-cart'                           => array(
            'cart',
            array(
                'controller' => 'cart',
                'action'     => 'index',
            ),
        ),
        'listings-cart-checkout'                  => array(
            'checkout',
            array(
                'controller' => 'cart',
                'action'     => 'checkout',
            ),
        ),
        'listings-purchase-actions'               => array(
            'purchase/:action/:type/:id',
            array(
                'controller' => 'purchase',
                'action'     => 'confirm',
            ),
            array(
                'id' => '[\d]+',
            ),
        ),
        'listings-purchase-actions-modal'         => array(
            'purchase-modal/:action/:type/:id',
            array(
                'controller' => 'purchase',
                'action'     => 'confirm',
                'modal'      => 1,
            ),
            array(
                'id' => '[\d]+',
            ),
        ),
        'listings-purchase-actions-modal-summary' => array(
            'purchase-modal-summary/:action/:type/:id',
            array(
                'controller' => 'purchase',
                'action'     => 'confirm',
                'modal'      => 1,
                'summary'    => 1,
            ),
            array(
                'id' => '[\d]+',
            ),
        ),
    ),
    'view'   => array(
        'layouts_path' => __DIR__ . '/../view/layout',
        'views_path'   => __DIR__ . '/../view',
        'layout_file'  => 'layout.phtml',
    ),
);
