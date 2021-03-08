<?php
/**
 * MOD:- DISCOUNT RULES
 *
 * @version 3.0
 */

return array(
    'routes' => array(
        'listings-discounts' => array(
            'discounted-listings',
            array(
                'controller'   => 'browse',
                'action'       => 'index',
                'filter'       => 'discounted',
                'listing_type' => 'product',
            ),
        ),
    ),
);
