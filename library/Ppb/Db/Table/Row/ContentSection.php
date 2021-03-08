<?php

/**
 *
 * PHP Pro Bid
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2020 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     8.2 [rev.8.2.01]
 */

/**
 * content sections table row object model
 */

namespace Ppb\Db\Table\Row;

use Cube\Controller\Front,
    Ppb\Service,
    Ppb\Db\Table\Row\ContentEntry as ContentEntryModel,
    Ppb\Navigation\Page\ContentSection as ContentSectionPage;

class ContentSection extends AbstractRow
{

    /**
     * section types
     */
    const TYPE_SINGLE = 'single';
    const TYPE_MULTIPLE = 'multiple';
    const TYPE_TREE = 'tree';

    /**
     * view files path
     */
    const VIEW_FILES_PATH = 'app/cms';


    /**
     *
     * section types array
     *
     * @var array
     */
    public static $sectionTypes = array(
        self::TYPE_SINGLE   => 'Single',
        self::TYPE_MULTIPLE => 'Multiple',
        self::TYPE_TREE     => 'Tree',
    );

    /**
     *
     * generate link for content section
     *
     * @return array|string
     */
    public function link()
    {
        if ($uri = $this->getUri()) {
            return $uri;
        }

        return array(
            'module'     => 'app',
            'controller' => 'cms',
            'action'     => 'index',
            'type'       => 'section',
            'name'       => $this->getData('name'),
            'id'         => $this->getData('id'),
        );
    }

    /**
     *
     * get section uri
     *
     * @return string|null
     */
    public function getUri()
    {
        if ($uri = $this->getData('uri')) {
            return $uri;
        }

        return null;
    }

    /**
     *
     * get section type
     *
     * @param bool $display
     *
     * @return string
     */
    public function getType($display = false)
    {
        $type = $this->getData('type');

        if ($display) {
            return self::$sectionTypes[$type];
        }

        return $type;
    }

    /**
     *
     * section is single
     *
     * @return bool
     */
    public function isSingle()
    {
        return ($this->getType() == self::TYPE_SINGLE) ? true : false;
    }

    /**
     *
     * section is multiple
     *
     * @return bool
     */
    public function isMultiple()
    {
        return ($this->getType() == self::TYPE_MULTIPLE) ? true : false;
    }

    /**
     *
     * section is tree
     *
     * @return bool
     */
    public function isTree()
    {
        return ($this->getType() == self::TYPE_TREE) ? true : false;
    }

    /**
     *
     * section is tree branch
     *
     * @param bool $activeOnly
     *
     * @return bool
     */
    public function isTreeBranch($activeOnly = false)
    {
        if ($this->isTree()) {
            return ($this->hasBranches($activeOnly)) ? true : false;
        }

        return false;
    }

    /**
     *
     * section is tree leaf
     *
     * @param bool $activeOnly
     *
     * @return bool
     */
    public function isTreeLeaf($activeOnly = false)
    {
        if ($this->isTree()) {
            return ($this->hasBranches($activeOnly)) ? false : true;
        }

        return false;
    }

    /**
     *
     * check if section is active
     *
     * @return bool
     */
    public function isActive()
    {
        return $this->getData('active') ? true : false;
    }

    /**
     *
     * a single entry can be added on a section of type simple or tree, and multiple entries can be added for
     * a section of type multiple
     *
     * @param string $locale
     *
     * @return bool
     */
    public function canAddEntry($locale = null)
    {
        if ($this->isMultiple()) {
            return true;
        }

        if ($this->countEntries($locale) == 0) {
            return true;
        }

        return false;
    }

    /**
     *
     * can edit entries for single and tree section types
     *
     * @return bool
     */
    public function canEditEntry()
    {
        return ($this->getEntryId() !== null) ? true : false;
    }

    /**
     *
     * get all entries corresponding to the section
     *
     * @param string|null $entryType select by entry type (multiple section only)
     * @param bool        $drafts    select drafts too
     * @param bool        $expired   select expired entries too
     *
     * @return \Ppb\Db\Table\Rowset\ContentEntries
     */
    public function getEntries($entryType = null, $drafts = true, $expired = true)
    {
        $select = $this->getTable()->select();

        $locale = $this->getTranslate()->getLocale();
        if ($locale) {
            $select->where('locale = ? OR locale = ""', $locale)
                ->order('locale DESC');
        }

        if (!$drafts) {
            $select->where('draft = ?', 0);
        }

        if (!$expired) {
            $select->where('expiry_date is null or expiry_date > now()');
        }

        if (array_key_exists($entryType, ContentEntry::$entryTypes)) {
            $select->where('type = ?', $entryType);
        }

        /** @var \Ppb\Db\Table\Rowset\ContentEntries $entries */
        $entries = $this->findDependentRowset('\Ppb\Db\Table\ContentEntries', null, $select);

        return $entries;
    }

    /**
     *
     * count all entries corresponding to the section
     *
     * @param string $locale
     *
     * @return int
     */
    public function countEntries($locale = null)
    {
        $select = null;

        if ($locale !== null) {
            $select = $this->getTable()->select()
                ->where('locale = ?', $locale);
        }

        return $this->countDependentRowset('\Ppb\Db\Table\ContentEntries', null, $select);
    }

    /**
     *
     * check if section has branches
     *
     * @param bool $activeOnly
     *
     * @return bool|int
     */
    public function hasBranches($activeOnly = false)
    {
        if (!$this->isTree()) {
            return false;
        }

        $select = null;

        if ($activeOnly) {
            $select = $this->getTable()->select()
                ->where('active = ?', 1);
        }

        $countBranches = $this->countDependentRowset('\Ppb\Db\Table\ContentSections', null, $select);

        return ($countBranches > 0) ? true : false;
    }

    /**
     *
     * get entry corresponding to the section (entry type standard)
     *
     * @return \Ppb\Db\Table\Row\ContentEntry|null
     */
    public function getEntry()
    {
        /** @var \Ppb\Db\Table\Rowset\ContentEntries $entries */
        $entries = $this->getEntries(ContentEntry::TYPE_STANDARD);

        if (count($entries) > 0) {
            return $entries->getRow(0);
        }

        return null;
    }

    /**
     *
     * get entry id if it exists
     *
     * @return int|null
     */
    public function getEntryId()
    {
        $entry = $this->getEntry();

        if ($entry instanceof ContentEntry) {
            return $entry->getData('id');
        }

        return null;
    }

    /**
     *
     * get entry view file
     *
     * @return string|null
     */
    public function getEntryViewFile()
    {
        $entryViewFile = null;

        $section = $this;

        do {
            $entryViewFile = $section->getData('entry_view_file');
            $section = $section->findParentRow('\Ppb\Db\Table\ContentSections');
        } while ($entryViewFile == null && $section !== null);

        if ($entryViewFile != null) {
            /** @var \Cube\View $view */
            $view = Front::getInstance()->getBootstrap()->getResource('view');
            $location = $view->getFileLocation(self::VIEW_FILES_PATH . DIRECTORY_SEPARATOR . $entryViewFile);

            if ($location === null) {
                $entryViewFile = null;
            }
        }

        return empty($entryViewFile) ? null : $entryViewFile;
    }

    /**
     *
     * get current page
     *
     * @return \Ppb\Navigation\Page\ContentSection
     */
    public function getCurrentPage()
    {
        $request = Front::getInstance()->getRequest();

        $id = $request->getParam('id');
        $type = $request->getParam('type');

        $sectionsService = new Service\Table\Relational\ContentSections();
        $entriesService = new Service\ContentEntries();

        $activeSectionId = null;

        // section multiple post, no section
        if ($type == 'entry') {
            $entry = $entriesService->findBy('id', $id);

            if ($entry instanceof ContentEntryModel) {
                $activeSectionId = $entry->getData('section_id');
            }
        }
        else if ($type == 'section') {
            $activeSectionId = $id;
        }

        $sectionsService->setData(null, null, $activeSectionId);

        /** @var \Ppb\Navigation\Page\ContentSection $currentPage */
        $currentPage = $sectionsService->getData()->findOneBy('id', $this->getData('id'));

        return $currentPage;
    }

    /**
     *
     * check if current page is active
     *
     * @param bool $recursive
     *
     * @return bool
     */
    public function isActiveCurrentPage($recursive = false)
    {
        $currentPage = $this->getCurrentPage();

        if ($currentPage instanceof ContentSectionPage) {
            return $currentPage->isActive($recursive);
        }

        return false;
    }
}

