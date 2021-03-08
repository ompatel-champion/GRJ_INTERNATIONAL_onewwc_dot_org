<?php

/**
 *
 * Cube Framework
 *
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2018 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 *
 * @version     2.0 [rev.2.0.08]
 */

/**
 * navigation view helper
 */

namespace Cube\View\Helper;

use Cube\Navigation\AbstractContainer,
    Cube\Navigation\Page\AbstractPage,
    Cube\Permissions;

class Navigation extends AbstractHelper
{

    /**
     *
     * initial navigation object (should not modified)
     *
     * @var \Cube\Navigation\Page\AbstractPage
     */
    protected $_initialContainer;

    /**
     *
     * navigation object
     *
     * @var \Cube\Navigation\Page\AbstractPage
     */
    protected $_container;

    /**
     * Root container
     *
     * @var \Cube\Navigation\AbstractContainer
     */
    protected $_root;

    /**
     *
     * path where to search for navigation view partials
     *
     * @var string
     */
    protected $_path;

    /**
     *
     * the minimum depth from which the rendering will start
     * default = 0 - from first page
     *
     * @var integer
     */
    protected $_minDepth = 0;

    /**
     *
     * the maximum depth where the rendering will stop
     * default = 0 - until last page
     *
     * @var integer
     */
    protected $_maxDepth = 0;

    /**
     *
     * ACL object to use
     *
     * @var \Cube\Permissions\Acl
     */
    protected $_acl;

    /**
     *
     * ACL role to use
     *
     * @var string|\Cube\Permissions\RoleInterface
     */
    protected $_role;

    /**
     *
     * get the initial navigation object
     *
     * @return \Cube\Navigation\Page\AbstractPage
     * @throws \InvalidArgumentException
     */
    public function getInitialContainer()
    {
        if (!$this->_initialContainer instanceof AbstractContainer) {
            throw new \InvalidArgumentException(
                sprintf("'%s' must be of type \Cube\Navigation\AbstractContainer.", $this->_container));
        }

        return $this->_initialContainer;
    }

    /**
     *
     * set the initial navigation container
     *
     * @param \Cube\Navigation\Page\AbstractPage $container
     *
     * @throws \InvalidArgumentException
     * @return $this
     */
    public function setInitialContainer($container)
    {
        if ($container instanceof AbstractContainer) {
            $this->_initialContainer = $container;
            $this->setContainer($this->_initialContainer);
        }
        else {
            throw new \InvalidArgumentException(
                sprintf("'%s' must be of type \Cube\Navigation\AbstractContainer.", $this->_container));
        }

        return $this;
    }

    /**
     *
     * get the navigation object
     *
     * @return \Cube\Navigation\Page\AbstractPage
     * @throws \InvalidArgumentException
     */
    public function getContainer()
    {
        if (!$this->_container instanceof AbstractContainer) {
            throw new \InvalidArgumentException(
                sprintf("'%s' must be of type \Cube\Navigation\AbstractContainer.", $this->_container));
        }

        return $this->_container;
    }

    /**
     *
     * set the navigation container
     *
     * @param \Cube\Navigation\Page\AbstractPage $container
     *
     * @throws \InvalidArgumentException
     * @return $this
     */
    public function setContainer($container)
    {
        if ($container instanceof AbstractContainer) {
            $this->_container = $container;
        }
        else if ($container !== null) {
            throw new \InvalidArgumentException(
                sprintf("'%s' must be of type \Cube\Navigation\AbstractContainer.", $this->_container));
        }

        return $this;
    }

    /**
     *
     * reset container
     *
     * @param bool $initialContainer
     *
     * @return $this
     */
    public function resetContainer($initialContainer = true)
    {
        if ($initialContainer === true) {
            $this->_container = $this->getInitialContainer();
        }
        else {
            $this->_container = null;
        }

        return $this;
    }

    /**
     *
     * get navigation partials path
     *
     * @return string
     */
    public function getPath()
    {
        return $this->_path;
    }

    /**
     *
     * set navigation partials path
     *
     * @param string $path
     *
     * @return $this
     */
    public function setPath($path)
    {
        if (is_dir($path)) {
            $this->_path = $path;
        }

        return $this;
    }

    /**
     *
     * get the minimum depth of the container
     *
     * @return integer
     */
    public function getMinDepth()
    {
        return $this->_minDepth;
    }

    /**
     *
     * set the minimum depth of the container
     *
     * @param int $minDepth
     *
     * @return $this
     */
    public function setMinDepth($minDepth)
    {
        $this->_minDepth = (int)$minDepth;

        return $this;
    }

    /**
     *
     * get the maximum depth of the container
     *
     * @return int
     */
    public function getMaxDepth()
    {
        return $this->_maxDepth;
    }

    /**
     *
     * set the maximum depth of the container
     *
     * @param int $maxDepth
     *
     * @return $this
     */
    public function setMaxDepth($maxDepth)
    {
        $this->_maxDepth = (int)$maxDepth;

        return $this;
    }

    /**
     *
     * get ACL
     *
     * @return \Cube\Permissions\Acl
     * @throws \InvalidArgumentException
     */
    public function getAcl()
    {
        if (!$this->_acl instanceof Permissions\Acl) {
            throw new \InvalidArgumentException(
                sprintf("'%s' must be of type \Cube\Permissions\Acl.", $this->_acl));
        }

        return $this->_acl;
    }

    /**
     *
     * set ACL
     *
     * @param \Cube\Permissions\Acl $acl
     *
     * @throws \InvalidArgumentException
     * @return $this
     */
    public function setAcl($acl)
    {
        if ($acl instanceof Permissions\Acl) {
            $this->_acl = $acl;
        }
        else {
            throw new \InvalidArgumentException(
                sprintf("'%s' must be of type \Cube\Permissions\Acl.", $this->_acl));
        }

        return $this;
    }

    /**
     *
     * get ACL role
     *
     * @return string|\Cube\Permissions\RoleInterface
     */
    public function getRole()
    {
        return $this->_role;
    }

    /**
     *
     * set ACL role
     *
     * @param string|\Cube\Permissions\RoleInterface $role
     *
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function setRole($role)
    {
        if ($role === null || is_string($role) || $role instanceof Permissions\RoleInterface) {
            $this->_role = $role;
        }
        else {
            throw new \InvalidArgumentException(
                sprintf("'%s' must be null, a string, or an instance of type \Cube\Permissions\RoleInterface.",
                    $this->_role));
        }

        return $this;
    }

    /**
     *
     * function called by the reflection class when creating the helper
     * we will always check if the navigation object and view file are set correctly when calling the navigation proxy class
     *
     * @param \Cube\Navigation\Page\AbstractPage $container the navigation object
     * @param string                             $partial   the name of the view partial used for rendering the navigation object
     *
     * @return $this
     */
    public function navigation($container = null, $partial = null)
    {
        if ($container !== null) {
            $this->setContainer($container);
        }

        if ($partial !== null) {
            $this->setPartial($partial);
        }

        return $this;
    }

    /**
     *
     * create a navigation menu from a navigation container and a view partial
     *
     * @return string       the rendered menu
     */
    public function menu()
    {
        $view = $this->getView();
        $view->set('menu', $this->getContainer());

        return $view->process(
            $this->getPartial(), true);
    }

    /**
     *
     * render a partial
     *
     * @return string       the rendered partial
     */
    public function render()
    {
        $view = $this->getView();
        $view->set('container', $this->getContainer());

        return $view->process(
            $this->getPartial(), true);
    }

    /**
     *
     * find the active page in the container set in the helper
     *
     * @return \Cube\Navigation\Page\AbstractPage|null      return the page object if found or null otherwise
     */
    public function findActive()
    {
        $container = $this->getContainer();

        if ($container->isActive()) {
            return $container;
        }
        $iterator = new \RecursiveIteratorIterator($container,
            \RecursiveIteratorIterator::CHILD_FIRST);

        /** @var \Cube\Navigation\Page\AbstractPage $page */
        foreach ($iterator as $page) {
            if ($page->isActive()) {
                return $page;
            }
        }

        return null;
    }

    /**
     *
     * Returns the root container of the given page
     *
     * When rendering a container, the render method still store the given
     * container as the root container, and unset it when done rendering. This
     * makes sure finder methods will not traverse above the container given
     * to the render method.
     *
     * @param  \Cube\Navigation\Page\AbstractPage $page
     *
     * @return \Cube\Navigation\AbstractContainer
     */
    public function findRoot(AbstractPage $page)
    {
//        if ($this->_root) {
//            return $this->_root;
//        }

        $root = $page;

        while ($parent = $page->getParent()) {
            $root = $parent;
            if ($parent instanceof AbstractPage) {
                $page = $parent;
            }
            else {
                break;
            }
        }

        return $root;
    }

    /**
     *
     * checks if a page is accepted in the iteration
     * the method is to be called from the navigation view helper
     *
     * @param \Cube\Navigation\Page\AbstractPage $page
     * @param bool                               $recursive default true
     *
     * @return bool
     */
    public function accept(AbstractPage $page, $recursive = true)
    {
        $accept = true;

        if (!$this->_acceptAcl($page)) {
            $accept = false;
        }

        if ($accept && $recursive) {
            $parent = $page->getParent();
            if ($parent instanceof AbstractPage) {
                $accept = $this->accept($parent, true);
            }
        }

        return $accept;
    }

    /**
     *
     * check if a page is allowed by ACL
     *
     * rules:
     * - helper has no ACL, page is accepted
     * - page has a resource or privilege defined:
     *   => the ACL allows access to it using the helper's role,
     *   => [OBSOLETE] the ACL doesn't have the resource called in the page
     *   => if the resource isn't in the ACL - page isn't accepted
     *
     * - if page has no resource or privilege, page is accepted
     *
     * @param \Cube\Navigation\Page\AbstractPage $page
     *
     * @return bool
     */
    protected function _acceptAcl(AbstractPage $page)
    {
        if (!$acl = $this->getAcl()) {
            return true;
        }

        $role = $this->getRole();
        $resource = $page->getResource();
        $privilege = $page->getPrivilege();

        if ($resource || $privilege) {
            if ($acl->hasResource($resource)) {
                return $acl->isAllowed($role, $resource, $privilege);
            }

            return false;
        }

        return true;
    }

    /**
     *
     * get active page breadcrumbs array
     *
     * @return array
     */
    public function getBreadcrumbs()
    {
        $breadcrumbs = array();
        $depth = 0;

        $page = $this->findActive();

        if ($page instanceof AbstractPage) {

            array_push($breadcrumbs, $page);

            /** @var \Cube\Navigation\Page\AbstractPage $parent */
            while (($parent = $page->getParent()) instanceof AbstractPage) {
                if ($parent->get('filter') == 'true') {
                    break;
                }
                else {
                    array_push($breadcrumbs, $parent);
                    $page = $parent;
                }
            }

            $breadcrumbs = array_reverse($breadcrumbs);

            foreach ($breadcrumbs as $key => $page) {
                if ($depth < $this->_minDepth ||
                    ($depth > $this->_maxDepth && $this->_maxDepth > 0)
                ) {
                    unset($breadcrumbs[$key]);
                }

                $depth++;
            }
        }

        return $breadcrumbs;
    }

    /**
     *
     * create a breadcrumbs helper by getting the active page from the navigation container
     * and applying it to a breadcrumbs view partial
     * if no active page is found, return an empty display output
     *
     * @param array $breadcrumbsVariables accepts custom variables [home button: (home {params, label})]
     *
     * @return string|null
     */
    public function breadcrumbs(array $breadcrumbsVariables = null)
    {
        $breadcrumbs = $this->getBreadcrumbs();

        if (count($breadcrumbs) > 0) {
            $view = $this->getView();

            if (is_array($breadcrumbsVariables)) {
                $view->setVariables($breadcrumbsVariables);
            }

            $view = $this->getView();
            $view->set('breadcrumbs', $breadcrumbs);

            $output = $view->process(
                $this->getPartial(), true);

            if (is_array($breadcrumbsVariables)) {
                foreach ($breadcrumbsVariables as $key => $value) {
                    $view->clearVariable($key);
                }
            }

            return $output;
        }

        return null;
    }

    /**
     * Searches the root container for the forward 'next' relation of the given
     * $page
     *
     * @param  \Cube\Navigation\Page\AbstractPage $page
     *
     * @return \Cube\Navigation\Page\AbstractPage|null
     */
    public function relNext(AbstractPage $page)
    {
        $found = null;
        $break = false;
        $iterator = new \RecursiveIteratorIterator(
            $this->findRoot($page),
            \RecursiveIteratorIterator::SELF_FIRST
        );
        foreach ($iterator as $intermediate) {
            if ($intermediate === $page) {
                // current page; break at next accepted page
                $break = true;
                continue;
            }

            if ($break && $this->accept($intermediate)) {
                $found = $intermediate;
                break;
            }
        }

        return $found;
    }

    /**
     * Searches the root container for the forward 'prev' relation of the given
     * $page
     *
     * @param  \Cube\Navigation\Page\AbstractPage $page
     *
     * @return \Cube\Navigation\Page\AbstractPage|null
     */
    public function relPrev(AbstractPage $page)
    {
        $found = null;
        $prev = null;

        $root = $this->findRoot($page);

        $iterator = new \RecursiveIteratorIterator(
            $root,
            \RecursiveIteratorIterator::SELF_FIRST
        );


        foreach ($iterator as $intermediate) {
            if (!$this->accept($intermediate)) {
                continue;
            }
            if ($intermediate === $page) {
                $found = $prev;
                break;
            }

            $prev = $intermediate;
        }

        return $found;
    }

    /**
     *
     * create a pager helper from a navigation container and a view partial
     * will use the relNext and relPrev buttons
     *
     * @return string       the rendered pager
     */
    public function pager()
    {
        $container = $this->getContainer();

        $view = $this->getView();
        $view->set('container', $container)
            ->set('relNext', $this->relNext($container))
            ->set('relPrev', $this->relPrev($container));

        return $view->process(
            $this->getPartial(), true);
    }

    /**
     *
     * create a headline helper
     *
     * @param array $headlineVariables custom parameters to override default ones
     *
     * @return string
     */
    public function headline(array $headlineVariables = null)
    {
        $breadcrumbs = $this->getBreadcrumbs();

        $view = $this->getView();

        // get global variables
        $headline = $view->get('headline');
        $headlineButtons = $view->get('headlineButtons');

        // auto generate headline variable from breadcrumbs
        if (empty($headline)) {
            if (count($breadcrumbs) > 0) {
                $headline = end($breadcrumbs);
                $view->set('headline', $headline);
            }
        }

        // set local variables
        if (is_array($headlineVariables)) {
            $view->setVariables($headlineVariables);
        }

        $output = $view->process(
            $this->getPartial(), true);

        // clear local variables
        if (is_array($headlineVariables)) {
            foreach ($headlineVariables as $key => $value) {
                $view->clearVariable($key);
            }
        }

        // set global variables back
        $view->set('headline', $headline)
            ->set('headlineButtons', $headlineButtons);


        return $output;
    }
}

