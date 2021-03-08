<?php

/**
 *
 * PHP Pro Bid
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2020 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     8.2 [rev.8.2.01]
 */

/**
 * seo link redirect controller plugin class
 * will also force redirect to the installed site path if path is different.
 *
 * (*) dynamic link redirects will only work if mod rewrite is enabled
 */

namespace App\Controller\Plugin;

use Cube\Permissions\Acl as PermissionsAcl,
    Cube\Controller\Plugin\AbstractPlugin,
    Ppb\Service;

class LinkRedirect extends AbstractPlugin
{

    /**
     *
     * acl object
     *
     * @var \Cube\Permissions\Acl
     */
    protected $_acl;

    /**
     *
     * settings array
     *
     * @var array
     */
    protected $_settings;

    /**
     *
     * class constructor
     *
     * @param \Cube\Permissions\Acl $acl      the acl to use
     * @param array                 $settings settings array
     */
    public function __construct(PermissionsAcl $acl, $settings)
    {
        $this->_acl = $acl;
        $this->_settings = $settings;
    }

    public function preDispatcher()
    {
        if ($this->_settings['mod_rewrite_urls']) {
            $link = $this->_generateLink();

            $linkRedirectsService = new Service\Table\LinkRedirects();

            $linkRedirects = $linkRedirectsService->fetchAll(
                $linkRedirectsService->getTable()->select()
                    ->order('order_id ASC')
            );

            /** @var \Cube\Db\Table\Row $linkRedirect */
            foreach ($linkRedirects as $linkRedirect) {
                if (preg_match('#' . $linkRedirect['old_link'] . '#', $link, $matches)) {
                    unset($matches[0]);
                    $redirectUri = $this->_settings['site_path'] . vsprintf($linkRedirect['new_link'], $matches);

                    $this->getResponse()
                        ->setRedirect($redirectUri, $linkRedirect['redirect_code'])
                        ->sendHeaders();

                    exit();
                }
            }
        }
    }

    /**
     *
     * generate link string to be checked against the link redirects table
     *
     * @return string
     */
    protected function _generateLink()
    {
        return (isset($_SERVER['HTTPS']) ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    }
}

