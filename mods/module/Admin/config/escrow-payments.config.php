<?php
/**
 * MOD:- ESCROW PAYMENTS
 */
return array(
    'routes'     => array(        
        'admin-accounting-pay-escrow'       => array(
            'admin/tools/pay-escrow',
            array(
                'controller' => 'tools',
                'action'     => 'pay-escrow',
            ),
        ),
    ),    
);
