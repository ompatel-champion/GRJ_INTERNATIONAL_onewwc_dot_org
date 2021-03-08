<?php

/**
 *
 * PHP Pro Bid
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2020 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     9.0 [rev.9.0.01]
 */
/**
 * wysiwyg custom element
 *
 * uses trumbowyg
 *
 * Documentation:
 * https://alex-d.github.io/Trumbowyg/documentation
 */

namespace Ppb\Form\Element;

use Cube\Form\Element\Textarea,
    Cube\Controller\Front;

class Wysiwyg extends Textarea
{

    const ELEMENT_CLASS = 'wysiwyg';
    /**
     *
     * type of element - override the variable from the parent class
     *
     * @var string
     */
    protected $_element = 'wysiwyg';

    /**
     *
     * class constructor
     *
     * @param string $name
     */
    public function __construct($name)
    {
        parent::__construct($name);

        $baseUrl = Front::getInstance()->getRequest()->getBaseUrl();

        $this->setHeaderCode('<link href="' . $baseUrl . '/js/trumbowyg/trumbowyg.css" media="screen" rel="stylesheet" type="text/css">')
            ->setBodyCode('<script src="' . $baseUrl . '/js/trumbowyg/trumbowyg.min.js"></script>');

        $this->setBodyCode(
            "<script type=\"text/javascript\">" . "\n"
            . " $('." . self::ELEMENT_CLASS . "').trumbowyg({ semantic: false, removeformatPasted: true }); " . "\n"
            . "</script>");

        $this->addAttribute('class', self::ELEMENT_CLASS);
    }

    /**
     *
     * set the custom data for the element, and add the javascript code
     *
     * @param array $customData
     *
     * @return $this
     */
    public function setCustomData($customData)
    {
        $this->_customData = $customData;

        $formData = array();
        if (isset($this->_customData['formData'])) {
            foreach ((array)$this->_customData['formData'] as $key => $value) {
                $formData[] = "'{$key}' : {$value}";
            }
        }
        $formData = implode(", \n", $formData);

        if (!empty($formData)) {
            $class = $this->getAttribute('class');
            $class = str_replace(self::ELEMENT_CLASS, '', $class);
            $this->addAttribute('class', $class, false);

            $this->setBodyCode(
                "<script type=\"text/javascript\">" . "\n"
                . " $('[name=\"" . $this->getName() . "\"]').trumbowyg({ " . "\n"
                . $formData . "\n"
                . " }); " . "\n"
                . "</script>");
        }

        return $this;
    }
}

