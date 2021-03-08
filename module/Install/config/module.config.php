<?php
/**
 * @version 8.1 [rev.8.1.01]
 */

return array(
    'routes'     => array(
        'install-index'  => array(
            'install',
            array(
                'controller' => 'index',
                'action'     => 'index',
            ),
        ),
        'install-action' => array(
            'install/:action',
            array(
                'controller' => 'index',
            ),
        ),
    ),
    'view'       => array(
        'layouts_path' => __DIR__ . '/../view/layout',
        'views_path'   => __DIR__ . '/../view',
        'layout_file'  => 'layout.phtml',
    ),
    'navigation' => array(
        'data_type'  => 'xml',
        'data_file'  => APPLICATION_PATH . '/module/Install/config/data/navigation/navigation.xml', // format like this for mods navigation files
        'views_path' => __DIR__ . '/../view',
    ),
    'session'    => array(
        'namespace' => 'INSTALL_NAMESPACE',
        'secret'    => 'INSTALL_SECRET',
    ),
);
