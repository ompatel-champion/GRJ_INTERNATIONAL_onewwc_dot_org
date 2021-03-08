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
 * content menu widget view helper class
 */

namespace App\View\Helper;

use Ppb\View\Helper\AbstractHelper,
    Ppb\Service\ContentMenus as ContentMenusService;

class ContentMenu extends AbstractHelper
{

    /**
     *
     * the view partial to be used
     *
     * @var string
     */
    protected $_partial = 'app/cms/menu-default.phtml';

    /**
     *
     * menu object
     *
     * @var \Ppb\Db\Table\Row\ContentMenu
     */
    protected $_menu = null;

    /**
     *
     * get menu object
     *
     * @return \Ppb\Db\Table\Row\ContentMenu
     */
    public function getMenu()
    {
        return $this->_menu;
    }

    /**
     *
     * set menu object
     *
     * @param \Ppb\Db\Table\Row\ContentMenu $menu
     *
     * @return $this
     */
    public function setMenu($menu)
    {
        $this->_menu = $menu;

        return $this;
    }

    /**
     *
     * content menu helper initialization class
     *
     * @param string $handle
     * @param string $partial
     *
     * @return $this
     */
    public function contentMenu($handle = null, $partial = null)
    {
        if ($handle !== null) {
            $contentMenusService = new ContentMenusService();
            /** @var \Ppb\Db\Table\Row\ContentMenu $menu */
            $menu = $contentMenusService->findBy('handle', $handle);
            $this->setMenu($menu);
        }

        if ($partial !== null) {
            $this->setPartial($partial);
        }

        return $this;
    }

    /**
     *
     * render partial
     *
     * @return string
     */
    public function render()
    {
        $menu = $this->getMenu();

        if ($menu !== null) {
            $view = $this->getView();

            $view->setVariables(array(
                'menu' => $menu,
            ));

            return $view->process(
                $this->getPartial(), true);
        }

        return '';
    }

}

