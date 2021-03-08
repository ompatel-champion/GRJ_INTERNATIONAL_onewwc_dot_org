<?php
/**
 * @version 8.0 [rev.8.0.06]
 */

return array(
    'routes' => array(
        'members-register'                => array(
            'sign-up',
            array(
                'controller' => 'user',
                'action'     => 'register',
            ),
        ),
        'members-sign-in'                 => array(
            'sign-in',
            array(
                'controller' => 'user',
                'action'     => 'login',
            ),
        ),
        'members-sign-in-modal'           => array(
            'sign-in-modal',
            array(
                'controller' => 'user',
                'action'     => 'login',
                'async'      => 1,
            ),
        ),
        'members-logout'                  => array(
            'logout',
            array(
                'controller' => 'user',
                'action'     => 'logout',
            ),
        ),
        'members-forgot-username'         => array(
            'forgot-username',
            array(
                'controller' => 'user',
                'action'     => 'forgot-username',
            ),
        ),
        'members-forgot-password'         => array(
            'forgot-password',
            array(
                'controller' => 'user',
                'action'     => 'forgot-password',
            ),
        ),
        'members-index'                   => array(
            'members-area',
            array(
                'controller' => 'summary',
                'action'     => 'index',
            ),
        ),
        'members-messaging-view-topic'    => array(
            'members/messaging/topic',
            array(
                'controller' => 'messaging',
                'action'     => 'topic',
            ),
        ),
        'members-messaging-create'        => array(
            'members/messaging/create',
            array(
                'controller' => 'messaging',
                'action'     => 'create',
            ),
        ),
        'members-register-confirm'        => array(
            'confirm-registration/:key',
            array(
                'controller' => 'user',
                'action'     => 'confirm-registration',
            ),
        ),
        'members-feedback-details'        => array(
            'display-feedback/:username',
            array(
                'controller' => 'reputation',
                'action'     => 'details',
            ),
        ),
        'members-feedback-details-filter' => array(
            'display-feedback/:username/:filter',
            array(
                'controller' => 'reputation',
                'action'     => 'details',
            ),
        ),

        // members area page routes (for pages that work similar for more than one action)
        'members-purchases'               => array(
            'members/buying/purchases',
            array(
                'controller' => 'invoices',
                'action'     => 'browse',
                'type'       => 'bought',
            ),
        ),
        'members-sales'                   => array(
            'members/selling/sales',
            array(
                'controller' => 'invoices',
                'action'     => 'browse',
                'type'       => 'sold',
            ),
        ),

        'members-buying-offers'  => array(
            'members/buying/offers',
            array(
                'controller' => 'offers',
                'action'     => 'browse',
                'type'       => 'buying',
            ),
        ),
        'members-selling-offers' => array(
            'members/selling/offers',
            array(
                'controller' => 'offers',
                'action'     => 'browse',
                'type'       => 'selling',
            ),
        ),

        'members-selling-browse' => array(
            'members/selling/browse/:filter',
            array(
                'controller' => 'selling',
                'action'     => 'browse',
            ),
        ),

        'members-account-history'                      => array(
            'members/account/history',
            array(
                'controller' => 'account',
                'action'     => 'history',
            ),
        ),
        'members-stores'                               => array(
            'all-stores',
            array(

                'controller' => 'stores',
                'action'     => 'index',
            ),
        ),
        'members-stores-browse'                        => array(
            'stores/browse',
            array(
                'controller' => 'stores',
                'action'     => 'browse',
            ),
        ),
        'members-tools-favorite-stores'                => array(
            'members/tools/favorite-stores',
            array(
                'controller' => 'tools',
                'action'     => 'favorite-stores',
            ),
        ),
        'members-tools-listings-watch'                 => array(
            'members/tools/watched-items',
            array(
                'controller' => 'tools',
                'action'     => 'watched-items',
            ),
        ),
        'members-tools-bulk-lister'                    => array(
            'members/tools/bulk-lister',
            array(
                'controller' => 'tools',
                'action'     => 'bulk-lister',
            ),
        ),
        'members-store-manage-categories'              => array(
            'members/store/categories',
            array(
                'controller' => 'store',
                'action'     => 'categories',
            ),
        ),
        'members-digital-download-link'                => array(
            'download',
            array(
                'controller' => 'buying',
                'action'     => 'download',
            ),
        ),
        'members-newsletter-unsubscribe'               => array(
            'unsubscribe',
            array(
                'controller' => 'user',
                'action'     => 'newsletter-unsubscribe',
            ),
        ),
        'members-newsletter-subscription-confirmation' => array(
            'members/user/newsletter-subscription-confirmation',
            array(
                'controller' => 'user',
                'action'     => 'newsletter-subscription-confirmation',
            ),
        ),
        'members-wish-list'                            => array(
            'wish-list',
            array(
                'controller' => 'tools',
                'action'     => 'watched-items',
            ),
        ),
        'members-tools-edit-blocked-user'              => array(
            'members/tools/edit-blocked-user',
            array(
                'controller' => 'tools',
                'action'     => 'edit-blocked-user',
            ),
        ),
        'members-tools-edit-voucher'                   => array(
            'members/tools/edit-voucher',
            array(
                'controller' => 'tools',
                'action'     => 'edit-voucher',
            ),
        ),
        'members-account-edit-address'                 => array(
            'members/account/edit-address',
            array(
                'controller' => 'account',
                'action'     => 'edit-address',
            ),
        ),
        'members-offers-details'                       => array(
            'members/offers/details/:type',
            array(
                'controller' => 'offers',
                'action'     => 'details',
            ),
        ),
        'members-offers-counter'                       => array(
            'members/offers/counter/:type/:id',
            array(
                'controller' => 'offers',
                'action'     => 'counter',
            ),
        ),
    ),
    'view'   => array(
        'layouts_path' => __DIR__ . '/../view/layout',
        'views_path'   => __DIR__ . '/../view',
        'layout_file'  => 'layout.phtml',
    ),
);
