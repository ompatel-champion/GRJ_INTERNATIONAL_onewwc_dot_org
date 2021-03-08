<?php

/**
 *
 * Cube Framework
 *
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2018 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 *
 * @version     2.0 [rev.2.0.02]
 */

namespace Cube\Navigation;

use Cube\Config\AbstractConfig;

abstract class AbstractContainer implements \Countable, \RecursiveIterator
{

    /**
     *
     * will hold the navigation array
     *
     * @var array
     */
    protected $_pages = array();

    /**
     *
     * pages index array
     *
     * @var array
     */
    protected $_index = array();


    /**
     *
     * index is dirty and needs to be re-arranged
     *
     * @var bool
     */
    protected $_dirtyIndex = false;

    /**
     *
     * sorts the page index according to page order
     *
     * @return void
     */
    protected function sort()
    {
        if (!$this->_dirtyIndex) {
            return;
        }

        $newIndex = array();
        $index = 0;

        foreach ($this->_pages as $hash => $page) {
            $order = $page->getOrder();
            if ($order === null) {
                $newIndex[$hash] = $index;
                $index++;
            }
            else {
                $newIndex[$hash] = $order;
            }
        }

        asort($newIndex);
        $this->_index = $newIndex;
        $this->_dirtyIndex = false;
    }

    /**
     *
     * notifies container that the order of pages are updated
     *
     * @return void
     */
    public function notifyOrderUpdated()
    {
        $this->_dirtyIndex = true;
    }

    /**
     *
     * get the pages container
     *
     * @return array
     */
    public function getPages()
    {
        return $this->_pages;
    }

    /**
     *
     * set pages array
     *
     * @param \Cube\Config\AbstractConfig|array $pages
     *
     * @return $this
     */
    public function setPages($pages)
    {
        $this->removePages();

        $this->addPages($pages);

        return $this;
    }

    /**
     *
     * add new pages to the pages array, accepts an array or an object of type Config
     *
     * @param \Cube\Config\AbstractConfig|array $pages
     *
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function addPages($pages)
    {

        if (!is_array($pages) && !($pages instanceof AbstractConfig)) {
            throw new \InvalidArgumentException("The navigation object requires an array or an object of type \Cube\Config in order to be created.");
        }
        else {
            // need to test as i'm not sure it works as intended
            if ($pages instanceof AbstractConfig) {
                $pages = $pages->getData();
            }

            // Because adding a page to a container removes it from the original
            // (see {@link Page\AbstractPage::setParent()}), iteration of the
            // original container will break. As such, we need to iterate the
            // container into an array first.
            if ($pages instanceof AbstractContainer) {
                $pages = iterator_to_array($pages);
            }

            foreach ($pages as $page) {
                if (null === $page) {
                    continue;
                }
                $this->addPage($page);
            }
        }

        return $this;
    }

    /**
     *
     * add a page to the container
     *
     * This method will inject the container as the given page's parent by
     * calling {@link Page\AbstractPage::setParent()}.
     *
     * @param  \Cube\Navigation\Page\AbstractPage|array|\Traversable $page page to add
     *
     * @return $this
     * @throws \InvalidArgumentException if page is invalid
     */
    public function addPage($page)
    {
        if ($page === $this) {
            throw new \InvalidArgumentException('A page cannot have itself as a parent');
        }

        if (!$page instanceof Page\AbstractPage) {
            if (!is_array($page) && !$page instanceof \Traversable) {
                throw new \InvalidArgumentException(sprintf(
                    "'%s' must be an instance of Cube\Navigation\Page\AbstractPage, Traversable, or an array", $page));
            }

            $page = Page\AbstractPage::factory($page);
        }

        if ($page) {
            $hash = $page->hashCode();

            if (array_key_exists($hash, $this->_index)) {
                // page is already in container
                return $this;
            }

            $this->_pages[$hash] = $page;
            $this->_index[$hash] = $page->getOrder();
            $this->_dirtyIndex = true;

            $page->setParent($this);
        }

        return $this;
    }

    /**
     *
     * reset pages array
     *
     * @return $this
     */
    public function removePages()
    {
        $this->_pages = array();
        $this->_index = array();

        return $this;
    }

    /**
     *
     * remove the given page from the container
     *
     * @param  \Cube\Navigation\Page\AbstractPage|int $page      page to remove, either a page
     *                                                           instance or a specific page order
     * @param  bool                                   $recursive whether to remove recursively
     *
     * @return bool whether the removal was successful
     */
    public function removePage($page, $recursive = false)
    {
        if ($page instanceof Page\AbstractPage) {
            $hash = $page->hashCode();
        }
        elseif (is_int($page)) {
            $this->sort();
            if (!$hash = array_search($page, $this->_index)) {
                return false;
            }
        }
        else {
            return false;
        }

        if (isset($this->_pages[$hash])) {
            unset($this->_pages[$hash]);
            unset($this->_index[$hash]);
            $this->_dirtyIndex = true;

            return true;
        }

        if ($recursive) {
            /** @var \Cube\Navigation\Page\AbstractPage $childPage */
            foreach ($this->_pages as $childPage) {
                if ($childPage->hasPage($page, true)) {
                    $childPage->removePage($page, true);

                    return true;
                }
            }
        }

        return false;
    }

    /**
     *
     * return true if container contains any pages
     *
     * @return bool  whether container has any pages
     */
    public function hasPages()
    {
        return count($this->_index) > 0;
    }

    /**
     *
     * check if the container has the given page
     *
     * @param  \Cube\Navigation\Page\AbstractPage $page      page to look for
     * @param  bool                               $recursive whether to search recursively
     *
     * @return bool whether page is in container
     */
    public function hasPage(Page\AbstractPage $page, $recursive = false)
    {
        if (array_key_exists($page->hashCode(), $this->_index)) {
            return true;
        }
        else if ($recursive) {
            /** @var \Cube\Navigation\Page\AbstractPage $childPage */
            foreach ($this->_pages as $childPage) {
                if ($childPage->hasPage($page, true)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     *
     * get the first page matching a property
     *
     * @param string $property property name to search against
     * @param mixed  $value    property value
     * @param bool   $exact    whether to search for exact values only
     *
     * @return \Cube\Navigation\Page\AbstractPage|null
     */
    public function findOneBy($property, $value, $exact = true)
    {
        $iterator = new \RecursiveIteratorIterator($this, \RecursiveIteratorIterator::SELF_FIRST);

        /** @var \Cube\Navigation\Page\AbstractPage $page */
        foreach ($iterator as $page) {
            $pageName = $page->get($property);

            if ($exact === true) {
                if ($pageName == $value) {
                    return $page;
                }
            }
            else {
                if (stristr($pageName, $value)) {
                    return $page;
                }
            }
        }

        return null;
    }

    /**
     *
     * get all pages matching a set property
     *
     * @param string $property property name to search against
     * @param mixed  $value    property value
     * @param bool   $exact    whether to search for exact values only
     *
     * @return array
     */
    public function findAllBy($property, $value, $exact = true)
    {
        $result = array();

        $iterator = new \RecursiveIteratorIterator($this, \RecursiveIteratorIterator::SELF_FIRST);

        /** @var \Cube\Navigation\Page\AbstractPage $page */
        foreach ($iterator as $page) {
            $pageName = $page->get($property);

            if ($exact === true) {
                if ($pageName == $value) {
                    $result[] = $page;
                }
            }
            else {
                if (stristr($pageName, $value)) {
                    $result[] = $page;
                }
            }
        }

        return $result;
    }

    /**
     *
     * return an array representation of all pages in container
     *
     * @return array
     */
    public function toArray()
    {
        $this->sort();

        $pages = array();
        $indexes = array_keys($this->_index);
        foreach ($indexes as $hash) {
            $pages[] = $this->_pages[$hash]->toArray();
        }

        return $pages;
    }

    /**
     *
     * return the current page
     *
     * @return \Cube\Navigation\Page\AbstractPage current page or null
     * @throws \OutOfBoundsException
     */
    public function current()
    {
        $this->sort();

        current($this->_index);
        $hash = key($this->_index);
        if (!isset($this->_pages[$hash])) {
            throw new \OutOfBoundsException('Container corrupt: current page not found. ');
        }

        return $this->_pages[$hash];
    }

    /**
     *
     * return the key of the current page
     *
     * @return string  hash code of current page
     */
    public function key()
    {
        $this->sort();

        return key($this->_index);
    }

    /**
     * move index pointer to next page in the container
     *
     * @return void
     */
    public function next()
    {
        $this->sort();
        next($this->_index);
    }

    /**
     * set index pointer to first page in the container
     *
     * @return void
     */
    public function rewind()
    {
        $this->sort();
        reset($this->_index);
    }

    /**
     *
     * check if container index is valid
     *
     * @return bool
     */
    public function valid()
    {
        $this->sort();

        return current($this->_index) !== false;
    }

    /**
     *
     * proxy to hasPages()
     *
     * @return bool  whether container has any pages
     */
    public function hasChildren()
    {
//        return $this->valid() && $this->current()->hasPages();
        return $this->hasPages();
    }

    /**
     *
     * return the child container
     *
     * @return Page\AbstractPage|null
     */
    public function getChildren()
    {
        $hash = key($this->_index);

        if (isset($this->_pages[$hash])) {
            return $this->_pages[$hash];
        }

        return null;
    }

    /**
     *
     * return number of pages in container
     *
     * @return int  number of pages in the container
     */
    public function count()
    {
        return count($this->_index);
    }

}

