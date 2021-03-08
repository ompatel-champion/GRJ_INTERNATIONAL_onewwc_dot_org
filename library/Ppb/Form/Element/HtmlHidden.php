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
 * html field with hidden element attached
 */

namespace Ppb\Form\Element;

use Cube\Form\Element\Hidden;

class HtmlHidden extends Hidden
{

    /**
     *
     * html code
     *
     * @var string
     */
    protected $_html;


    /**
     *
     * class constructor
     *
     * @param string $name
     */
    public function __construct($name)
    {
        parent::__construct($name);
        $this->setHidden(false);
    }

    /**
     *
     * get html code
     *
     * @return string
     */
    public function getHtml()
    {
        return $this->_html;
    }

    /**
     *
     * set html code
     *
     * @param string $html
     *
     * @return $this
     */
    public function setHtml($html)
    {
        $this->_html = $html;

        return $this;
    }

    /**
     *
     * render pseudo element
     *
     * @return string
     */
    public function render()
    {
        return $this->getPrefix() . ' '
            . $this->getHtml()
            . $this->getSuffix()
            . parent::render();
    }

}
