<?php
/**
 * MOD:- ADVANCED CLASSIFIEDS
 */
return array(
    'routes' => array(        
        'listings-browse-classifieds' => array(
            'class',
            array(
                'controller'   => 'browse',
                'action'       => 'index',
                'listing_type' => 'classified',
            ),
        ),
        'listings-create-classified'  => array(
            'create-classified',
            array(
                'controller'   => 'listing',
                'action'       => 'create',
                'listing_type' => 'classified',
            ),
        ),
    ),
);
