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
 * content entries table row object model
 */

namespace Ppb\Db\Table\Row;

class ContentEntry extends AbstractRow
{

    /**
     * entry types
     */
    const TYPE_STANDARD = 'standard';
    const TYPE_POST = 'post';

    /**
     *
     * entry types array
     *
     * @var array
     */
    public static $entryTypes = array(
        self::TYPE_STANDARD => 'Standard',
        self::TYPE_POST     => 'Multiple / Post',
    );

    /**
     *
     * entry link
     *
     * @param bool $addLocale
     *
     * @return array|string
     */
    public function link($addLocale = false)
    {
        $link = $this->_generateLink();

        if ($addLocale && $locale = $this->getData('locale')) {
            if (is_array($link)) {
                $link['lang'] = $locale;
            }
            else if (is_string($link)) {
                $link .= ((stristr($link, '?') === false) ? '?' : '&')
                    . 'lang=' . $locale;
            }
        }

        return $link;
    }

    /**
     *
     * generate entry link
     *
     * @return array|string
     */
    protected function _generateLink()
    {
        /** @var \Ppb\Db\Table\Row\ContentSection $section */
        $section = $this->findParentRow('\Ppb\Db\Table\ContentSections');

        if ($section instanceof ContentSection) {
            $sectionUri = $section->getUri();

            if ($section->isMultiple() && $this->getData('type') == self::TYPE_POST) {
                $entrySlug = $this->getData('slug');

                if ($sectionUri) {
                    if ($entrySlug) {
                        return $sectionUri . '/' . $entrySlug;
                    }
                    else {
                        return $this->_entryLink() + array('section_uri' => $sectionUri);
                    }
                }
                else {
                    return $this->_entryLink();
                }
            }

            return ($sectionUri) ? $sectionUri : $section->link();
        }

        return $this->_entryLink();
    }

    /**
     *
     * check if entry is a draft
     *
     * @return bool
     */
    public function isDraft()
    {
        return ($this->getData('draft')) ? true : false;
    }

    /**
     *
     * check if entry is expired
     *
     * @return bool
     */
    public function isExpired()
    {
        $expiryDate = $this->getData('expiry_date');

        if ($expiryDate == null) {
            return false;
        }

        $expiryDate = strtotime($expiryDate);

        if ($expiryDate > time()) {
            return false;
        }

        return true;
    }

    /**
     *
     * check if entry is active
     * (not a draft and not expired)
     *
     * @return bool
     */
    public function isActive()
    {
        return ($this->isDraft() || $this->isExpired()) ? false : true;
    }

    /**
     *
     * get corresponding section
     *
     * @return \Ppb\Db\Table\Row\ContentSection|null
     */
    public function getSection()
    {
        return $this->findParentRow('\Ppb\Db\Table\ContentSections');
    }

    /**
     *
     * default entry link
     *
     * @return array
     */
    protected function _entryLink()
    {
        return array(
            'module'     => 'app',
            'controller' => 'cms',
            'action'     => 'index',
            'type'       => 'entry',
            'title'      => $this->getData('title'),
            'id'         => $this->getData('id'),
        );
    }
}

