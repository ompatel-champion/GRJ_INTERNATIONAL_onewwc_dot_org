<?php

/**
 *
 * GLOBAL CONFIG
 * ===============
 * global configuration file
 *
 * - initialize modules
 * - initialize global resources (db, session, [cache] etc)
 * - module settings will override global settings
 *
 */
/**
 * @version 8.0 [rev.8.0.01]
 */

return array(
    'modules'    => array(
        'App',
        'Admin',
        'Members',
        'Listings',
        'Install',
    ),
    'locale'     => array(
        'default' => 'en_US',
    ),
    'db'         => array(
        'adapter'  => '\\Cube\\Db\\Adapter\\PDO\\Mysql',
        'host'     => 'localhost',
        'dbname'   => 'onewwc_v8',
        'username' => 'onewwc_v8',
        'password' => ']1ZPkvC?=Xg%',
        'prefix'   => 'ppb_',
        'charset'  => 'utf8'
    ),
    'cache'      => array(
        'folder'   => __DIR__ . '/../cache',
        'queries'  => false,
        'metadata' => true,
    ),
    /* mail is global for all modules */
    'mail'       => array(
        'transport'    => 'mail',
        'layouts_path' => __DIR__ . '/../themes/eight',
        'views_path'   => __DIR__ . '/../module/App/view/emails',
        'layout_file'  => 'email.phtml',
    ),
    /* navigation is global for all modules except Admin */
    'navigation' => array(
        'data_type'  => 'xml',
        'data_file'  => __DIR__ . '/../module/App/config/data/navigation/navigation.xml',
        'views_path' => __DIR__ . '/../module/App/view',
    ),
    /* session is global for all modules except Admin */
    'session'    => array(
        'namespace' => 'PeOSxVUt',
        'secret'    => 'ayILpjuF',
    ),
    /* set folders used by the application (relative paths) */
    'folders'    => array(
        'themes'  => 'themes', // themes folder (relative path)
        'img'     => 'img', // global images folder (relative path)
        'uploads' => 'uploads', // media uploads folder
        'cache'   => 'uploads/cache', // media uploads folder
    ),
    /* set paths used by the application (absolute) */
    'paths'      => array(
        'base'      => __DIR__ . '/..', // base path of the application
        'languages' => __DIR__ . '/data/language', // languages folder
        'themes'    => __DIR__ . '/../themes',
        'img'       => __DIR__ . '/../img', // global images folder
        'uploads'   => __DIR__ . '/../uploads', // media uploads folder
        'cache'     => __DIR__ . '/../uploads/cache', // cached images folder
    ),
    'translate'  => array(
        'adapter'      => '\\Ppb\\Translate\\Adapter\\Composite',
        'translations' => array(
            array(
                'locale'  => 'en_US',
                'path'    => __DIR__ . '/data/language/en_US',
                'img'     => 'flags/en_US.png',
                'desc'    => 'English',
                'sources' => array(
                    array(
                        'adapter'   => '\\Cube\\Translate\\Adapter\\Gettext',
                        'extension' => 'mo',
                    ),
                    array(
                        'adapter'   => '\\Cube\\Translate\\Adapter\\ArrayAdapter',
                        'extension' => 'php',
                    ),
                ),
            ),
        ),
    ),
);
