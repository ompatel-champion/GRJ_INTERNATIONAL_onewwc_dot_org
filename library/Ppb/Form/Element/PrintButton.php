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
 * print button form element
 */

namespace Ppb\Form\Element;

use Cube\Form\Element\Button,
    Cube\Controller\Front;

class PrintButton extends Button
{

    const BTN_CLASS = 'print-button';
    /**
     *
     * type of element - override the variable from the parent class
     *
     * @var string
     */
    protected $_element = 'submit';

    /**
     *
     * base url of the application
     *
     * @var string
     */
    protected $_baseUrl;

    /**
     *
     * class constructor
     *
     * @param string $name
     */
    public function __construct($name)
    {
        parent::__construct($name);

        $this->_baseUrl = Front::getInstance()->getRequest()->getBaseUrl();

        $this->setHeaderCode('<link href="' . $this->_baseUrl . '/js/printarea/print.css" media="print" rel="stylesheet" type="text/css">')
            ->setBodyCode('<script type="text/javascript" src="' . $this->_baseUrl . '/js/printarea/jquery.printarea.js"></script>')
            ->setBodyCode("<script type=\"text/javascript\">
                $(document).ready(function() {
                    $('." . self::BTN_CLASS . "').click(function () {
                        var container = $(this).attr('rel');
                        $('#' + container).printArea();
                        return false;
                    });
                });
            </script>");

        $this->addAttribute('class', self::BTN_CLASS);
    }

}

