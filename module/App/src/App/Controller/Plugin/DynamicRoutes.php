<?php

/**
 *
 * PHP Pro Bid
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2018 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     8.0 [rev.8.0.02]
 */

/**
 * dynamic routes generator controller plugin class
 */

namespace App\Controller\Plugin;

use Cube\Controller\Plugin\AbstractPlugin,
    Cube\Controller\Router\Route,
    Cube\Controller\Front,
    Cube\Controller\Request,
    Ppb\Db\Table\Row\ContentEntry as ContentEntryModel,
    Ppb\Service;

class DynamicRoutes extends AbstractPlugin
{
    /**
     *
     * content sections table service
     *
     * @var \Ppb\Service\Table\Relational\ContentSections
     */
    protected $_sections;

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
     * @param array $settings settings array
     */
    public function __construct($settings)
    {
        $this->_settings = $settings;
    }

    /**
     *
     * set content sections table service
     *
     * @param \Ppb\Service\Table\Relational\ContentSections $sections
     *
     * @return $this
     */
    public function setSections(Service\Table\Relational\ContentSections $sections)
    {
        $this->_sections = $sections;

        return $this;
    }

    /**
     *
     * get content sections table service
     *
     * @return \Ppb\Service\Table\Relational\ContentSections
     */
    public function getSections()
    {
        if (!$this->_sections instanceof Service\Table\Relational\ContentSections) {
            $this->setSections(
                new Service\Table\Relational\ContentSections());
        }

        return $this->_sections;
    }


    /**
     * initialize dynamic routes
     */
    public function preRoute()
    {
        $router = Front::getInstance()->getRouter();

        $sections = $this->getSections()->fetchAll(
            $this->getSections()->getTable()->select()
                ->where('uri != ?', '')
                ->order(array('parent_id ASC', '-order_id DESC', 'name ASC'))
        );

        $array = array();

        /** @var \Ppb\Db\Table\Row\ContentSection $section */
        foreach ($sections as $section) {
            ## DEFAULT ROUTE FOR SINGLE / MULTIPLE / TREE
            $array[] = array(
                'name'       => 'app-cms-section-' . $section['id'],
                'module'     => 'app',
                'path'       => $section['uri'],
                'defaults'   => array(
                    'controller' => 'cms',
                    'action'     => 'index',
                    'type'       => 'section',
                    'name'       => $section['name'],
                    'id'         => $section['id'],
                ),
                'conditions' => array()
            );
            ## /DEFAULT ROUTE FOR SINGLE / MULTIPLE / TREE


            if ($section->isMultiple()) {
                ## ROUTE FOR MULTIPLE POST W/O SLUG
                $array[] = array(
                    'name'       => 'app-cms-section-entry-noslug-' . $section['id'],
                    'module'     => 'app',
                    'path'       => $section['uri'] . '/:title/:id',
                    'defaults'   => array(
                        'controller'  => 'cms',
                        'action'      => 'index',
                        'type'        => 'entry',
                        'section_uri' => $section['uri'],
                    ),
                    'conditions' => array()
                );
                ## /ROUTE FOR MULTIPLE POST W/O SLUG


                ## PAGINATION ROUTE FOR MULTIPLE STANDARD
                $array[] = array(
                    'name'       => 'app-cms-section-pagination-' . $section['id'],
                    'module'     => 'app',
                    'path'       => $section['uri'] . '/page/:page',
                    'defaults'   => array(
                        'controller' => 'cms',
                        'action'     => 'index',
                        'type'       => 'section',
                        'name'       => $section['name'],
                        'id'         => $section['id'],
                    ),
                    'conditions' => array()
                );
                ## /PAGINATION ROUTE FOR MULTIPLE STANDARD


                ## ROUTES FOR MULTIPLE POSTS W/ SLUG
                $entries = $section->findDependentRowset('\Ppb\Db\Table\ContentEntries', null,
                    $section->getTable()->select()
                        ->where('type = ?', ContentEntryModel::TYPE_POST)
                        ->where('slug != ?', '')
                );

                /** @var \Ppb\Db\Table\Row\ContentEntry $entry */
                foreach ($entries as $entry) {
                    $array[] = array(
                        'name'       => 'app-cms-entry-' . $entry['id'],
                        'module'     => 'app',
                        'path'       => $section['uri'] . '/' . $entry['slug'],
                        'defaults'   => array(
                            'controller' => 'cms',
                            'action'     => 'index',
                            'type'       => 'entry',
                            'title'      => $entry['title'],
                            'id'         => $entry['id'],
                        ),
                        'conditions' => array()
                    );
                }
                ## /ROUTES FOR MULTIPLE POSTS W/ SLUG
            }
        }

        foreach ($array as $data) {
            if ($this->_settings['mod_rewrite_urls']) {
                $route = new Route\Rewrite($data['path'], $data['defaults'], $data['conditions']);
            }
            else {
                $route = new Route\Standard($data['path'], $data['defaults'], $data['conditions']);
            }

            $route->setName($data['name'])
                ->setModule($data['module']);

            $router->addRoute($route);
        }

        $router->setRequest(
            new Request());
    }

}

