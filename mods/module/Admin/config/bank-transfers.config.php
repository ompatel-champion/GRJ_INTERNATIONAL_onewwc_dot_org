<?php
/**
 * @version 7.6
 */
/**
 * MOD:- BANK TRANSFER
 */

return array(
    'routes' => array(
        'admin-add-bank-account'    => array(
            'admin/fees/add-bank-account',
            array(
                'controller' => 'fees',
                'action'     => 'add-bank-account',
            ),
        ),
        'admin-edit-bank-account'   => array(
            'admin/fees/edit-bank-account',
            array(
                'controller' => 'fees',
                'action'     => 'edit-bank-account',
            ),
        ),
        'admin-delete-bank-account' => array(
            'admin/fees/delete-bank-account',
            array(
                'controller' => 'fees',
                'action'     => 'delete-bank-account',
            ),
        ),
    ),
);
