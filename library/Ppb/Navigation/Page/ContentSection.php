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
 * content section page class - used by location navigation container
 */

namespace Ppb\Navigation\Page;

use Cube\Navigation\Page\AbstractPage,
    Ppb\Service\Table\Relational\ContentSections as ContentSectionsService,
    Ppb\Db\Table\Row\ContentSection as ContentSectionModel,
    Ppb\Db\Table\Row\ContentEntry as ContentEntryModel;

class ContentSection extends AbstractPage
{
    /**
     *
     * active section id
     *
     * @var int
     */
    protected $_activeSectionId;

    /**
     *
     * sluggable value
     *
     * @var string
     */
    protected $_slug;

    /**
     *
     * content section model
     *
     * @var \Ppb\Db\Table\Row\ContentSection
     */
    protected $_section;

    /**
     *
     * content entry model
     *
     * @var \Ppb\Db\Table\Row\ContentEntry
     */
    protected $_entry;

    /**
     *
     * get label / do not translate
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->_label;
    }

    /**
     *
     * get active section id
     *
     * @return int
     */
    public function getActiveSectionId()
    {
        return $this->_activeSectionId;
    }

    /**
     *
     * set active section id
     *
     * @param int $sectionId
     *
     * @return $this
     */
    public function setActiveSectionId($sectionId)
    {
        $this->_activeSectionId = $sectionId;

        return $this;
    }

    /**
     *
     * get slug
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->_slug;
    }

    /**
     *
     * set slug
     *
     * @param string $slug
     *
     * @return $this
     */
    public function setSlug($slug)
    {
        $this->_slug = $slug;

        return $this;
    }

    /**
     *
     * get section model
     *
     * @return \Ppb\Db\Table\Row\ContentSection
     */
    public function getSection()
    {
        if (!$this->_section instanceof ContentSectionModel) {
            $sectionsService = new ContentSectionsService();
            /** @var \Ppb\Db\Table\Row\ContentSection $section */
            $section = $sectionsService->findBy('id', $this->getId());

            $this->setSection($section);
        }

        return $this->_section;
    }

    /**
     *
     * set section model
     *
     * @param \Ppb\Db\Table\Row\ContentSection $section
     *
     * @return $this
     */
    public function setSection($section)
    {
        $this->_section = $section;

        return $this;
    }

    /**
     *
     * get entry model
     *
     * @return \Ppb\Db\Table\Row\ContentEntry
     */
    public function getEntry()
    {
        if (!$this->_entry instanceof ContentEntryModel) {
            $section = $this->getSection();
            $this->setEntry(
                $section->getEntry());
        }

        return $this->_entry;
    }

    /**
     *
     * set entry model
     *
     * @param \Ppb\Db\Table\Row\ContentEntry $entry
     *
     * @return $this
     */
    public function setEntry($entry)
    {
        $this->_entry = $entry;

        return $this;
    }

    /**
     *
     * override get method to use the slug if available for the url
     *
     * @param string $name
     *
     * @return mixed|null|string
     */
    public function get($name)
    {
        if ($name == 'params' && !empty($this->_slug)) {
            return $this->getSlug();
        }

        return parent::get($name);
    }

    /**
     *
     * check if a section is active
     *
     * @param bool $recursive check in sub-sections as well, and if a sub-section is active, return the current page as active
     *
     * @return bool              returns active status
     */
    public function isActive($recursive = false)
    {
        if (!$this->_active) {
            if ($this->getActiveSectionId() == $this->_id) {
                $this->_active = true;

                return true;
            }
        }

        return parent::isActive($recursive);
    }
}

