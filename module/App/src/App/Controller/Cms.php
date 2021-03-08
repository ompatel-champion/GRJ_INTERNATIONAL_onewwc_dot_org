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
 * cms controller
 */

namespace App\Controller;

use Ppb\Controller\Action\AbstractAction,
    Ppb\Service,
    Ppb\Db\Table\Row\ContentSection as ContentSectionModel,
    Ppb\Db\Table\Row\ContentEntry as ContentEntryModel,
    Cube\Controller\Front,
    Cube\Paginator,
    Ppb\Navigation\Page\ContentSection as ContentSectionPage;

class Cms extends AbstractAction
{

    /**
     *
     * content sections service
     *
     * @var \Ppb\Service\Table\Relational\ContentSections
     */
    protected $_sections;

    /**
     *
     * content entries service
     *
     * @var \Ppb\Service\ContentEntries
     */
    protected $_entries;

    public function init()
    {
        $this->_sections = new Service\Table\Relational\ContentSections();
        $this->_entries = new Service\ContentEntries();
    }

    public function Index()
    {
        $section = $entry = $paginator = $currentPage = $mainPage = null;
        $sectionId = $entryId = null;

        /** @var \Cube\View $view */
        $view = Front::getInstance()->getBootstrap()->getResource('view');

        $viewFile = 'entry-no-section.phtml';

        $statusNotFound = true;
        $statusGone = false;

        $sectionTreeBranch = $sectionTreeLeaf = false;

        $id = $this->getRequest()->getParam('id');
        $type = $this->getRequest()->getParam('type');

        $params = $this->getRequest()->getParams();

        $inAdmin = $this->_loggedInAdmin(array(), true);

        $sectionsService = new Service\Table\Relational\ContentSections();
        $entriesService = new Service\ContentEntries();

        // section multiple post, no section
        if ($type == 'entry') {
            $entry = $entriesService->findBy('id', $id);

            if ($entry instanceof ContentEntryModel) {
                $sectionId = $entry->getData('section_id');
                $section = $entry->getSection();
            }
        }
        // section single, section tree branch, section tree leaf, section multiple standard
        else {
            $section = $sectionsService->findBy('id', $id);
            $sectionId = $id;

            if ($section instanceof ContentSectionModel) {
                $entry = $section->getEntry();
            }
        }

        if ($section instanceof ContentSectionModel) {
            $sectionsSelect = $sectionsService->getTable()->select()
                ->order(array('parent_id ASC', '-order_id DESC', 'name ASC'));

            if (!$inAdmin) {
                $sectionsSelect->where('active = ?', 1);
            }

            $sectionsService->setData($sectionsSelect, null, $sectionId);

            /** @var \Ppb\Navigation\Page\ContentSection $currentPage */
            $currentPage = $sectionsService->getData()->findOneBy('id', $sectionId);

            $root = $sectionsService->getRoot($sectionId);
            $mainPage = $sectionsService->getData()->findOneBy('id', $root['id']);

            $activeOnly = ($inAdmin) ? false : true;

            $sectionSingle = $section->isSingle();
            $sectionTreeLeaf = $section->isTreeLeaf($activeOnly);
            $sectionTreeBranch = $section->isTreeBranch($activeOnly);
            $sectionMultipleStandard = ($section->isMultiple() && $type == 'section');
            $sectionMultiplePost = ($section->isMultiple() && $type == 'entry');

            if ($sectionSingle) {
                $viewFile = 'section-single.phtml';
            }
            else if ($sectionTreeLeaf) {
                $viewFile = 'section-tree-leaf.phtml';
            }
            else if ($sectionTreeBranch) {
                $viewFile = 'section-tree-branch.phtml';
            }
            else if ($sectionMultipleStandard) {
                $viewFile = 'section-multiple-standard.phtml';

                // get paginated entries
                $pageNumber = $this->getRequest()->getParam('page');
                $itemsPerPage = 10;

                $entriesTable = $entriesService->getTable();
                $entriesSelect = $entriesTable->select()
                    ->where('type = ?', ContentEntryModel::TYPE_POST)
                    ->where('section_id = ?', $sectionId)
                    ->order('created_at DESC');

                $locale = $this->getTranslate()->getLocale();

                if ($locale != '') {
                    $entriesSelect->where('locale = "" OR locale = ?', $locale);
                }

                if (!$inAdmin) {
                    $entriesSelect->where('draft = ?', 0)
                        ->where('expiry_date is null or expiry_date > now()');
                }

                $paginator = new Paginator(
                    new Paginator\Adapter\DbTableSelect($entriesSelect, $entriesTable));
                $paginator->setPageRange(5)
                    ->setItemCountPerPage($itemsPerPage)
                    ->setCurrentPageNumber($pageNumber);

            }
            else if ($sectionMultiplePost) {
                $viewFile = 'section-multiple-post.phtml';
            }

            if ($section->isActive() || $inAdmin) {
                $statusNotFound = false;
            }

            if (($entryViewFile = $section->getEntryViewFile()) !== null) {
                $viewFile = $entryViewFile;
            }
        }

        if ($entry instanceof ContentEntryModel) {
            if ($inAdmin) {
                $statusNotFound = false;
            }
            else if (!$statusNotFound) {
                if ($entry->isExpired()) {
                    $statusGone = true;
                }
                else if (!$entry->isActive()) {
                    $statusNotFound = true;
                }

                if (($statusGone || $statusNotFound) && ($sectionTreeBranch || $sectionTreeLeaf)) {
                    $statusGone = $statusNotFound = false;
                    $entry = null;
                }
            }
        }

        if ($statusGone) {
            $this->_forward('gone', 'error');
        }
        else if ($statusNotFound) {
            $this->_forward('not-found', 'error');
        }
        else {
            if ($sectionTreeBranch && !$entry instanceof ContentEntryModel && $currentPage instanceof ContentSectionPage) {
                while ($currentPage->hasChildren()) {
                    $currentPage = $currentPage->getChildren();
                }

                $this->_helper->redirector()->redirect('index', null, null, array('type' => 'section', 'name' => $currentPage->getLabel(), 'id' => $currentPage->getId()));
            }

            $view->setViewFileName($viewFile);

            // META TAGS
            if (!empty($entry['meta_title'])) {
                $metaTitle = $entry['meta_title'];
            }
            else {
                $metaTitle = implode(' / ', array_reverse($view->navigation()->setContainer($currentPage)->setMinDepth(0)->getBreadcrumbs()));
            }

            if (!empty($entry['meta_description'])) {
                $metaDescription = $entry['meta_description'];
            }
            else {
                $metaDescription = null;
            }

            $view->headTitle()->prepend(strip_tags($metaTitle));
            if ($metaDescription) {
                $view->headMeta()->setName('description', strip_tags($metaDescription));
            }


            return array(
                'section'     => $section,
                'entry'       => $entry,
                'paginator'   => $paginator,
                'currentPage' => $currentPage,
                'mainPage'    => $mainPage,
                'params'      => $params,
                'inAdmin'     => $inAdmin,
            );
        }
    }

    public function Entry()
    {
        // TODO: section multiple post, no section

        $id = $this->getRequest()->getParam('id');

        $entry = $this->_entries->findBy('id', $id);

        if ($entry instanceof ContentEntryModel) {
            $sectionId = $entry->getData('section_id');
            $section = $entry->getSection();
        }


//        $page = $this
    }

}

