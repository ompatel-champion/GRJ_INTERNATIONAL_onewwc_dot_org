<?php

/**
 *
 * PHP Pro Bid
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2018 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     8.0 [rev.8.0.01]
 */

/**
 * category page class - used by category navigation container
 */

namespace Ppb\Navigation\Page;

use Cube\Navigation\Page\AbstractPage;

class Category extends AbstractPage
{

    /**
     *
     * active category id
     *
     * @var int
     */
    protected $_activeCategoryId;

    /**
     *
     * sluggable value
     *
     * @var string
     */
    protected $_slug;

    /**
     *
     * custom fees flag
     *
     * @var bool
     */
    protected $_customFees;


    /**
     *
     * get active category id
     *
     * @return int
     */
    public function getActiveCategoryId()
    {
        return $this->_activeCategoryId;
    }

    /**
     *
     * set active category id
     *
     * @param integer $categoryId
     *
     * @return $this
     */
    public function setActiveCategoryId($categoryId)
    {
        $this->_activeCategoryId = $categoryId;

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
     * get custom fees flag
     *
     * @return bool
     */
    public function getCustomFees()
    {
        return $this->_customFees;
    }

    /**
     *
     * set custom fees flag
     *
     * @param int $customFees
     *
     * @return \Ppb\Navigation\Page\Category
     */
    public function setCustomFees($customFees)
    {
        $this->_customFees = (bool)$customFees;

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
     * check if a page is active
     *
     * @param bool $recursive check in sub-pages as well, and if a sub-page is active, return the current page as active
     *
     * @return bool              returns active status
     */
    public function isActive($recursive = false)
    {
        if (!$this->_active) {
            if ($this->getActiveCategoryId() == $this->_id) {
                $this->_active = true;

                return true;
            }
        }

        return parent::isActive($recursive);
    }
}

