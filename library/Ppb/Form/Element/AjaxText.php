<?php

/**
 *
 * PHP Pro Bid
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2018 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     8.0 [rev.8.0.01]
 */
/**
 * ajax text custom form element
 *
 * creates an element which initially is a simple text, which if clicked will be transformed in an
 * editable text box with attached save/cancel buttons
 */

namespace Ppb\Form\Element;

use Cube\Form\Element;

class AjaxText extends Element
{

    const ELEMENT_CLASS = 'ajax-text';
    const INPUT_NAME = 'textContent';
    /**
     *
     * type of element - override the variable from the parent class
     *
     * @var string
     */
    protected $_element = 'ajaxText';

    /**
     *
     * base url of the application
     *
     * @var string
     */
    protected $_baseUrl;

    /**
     *
     * ajax post url for the success button
     *
     * @var string
     */
    protected $_postUrl;

    /**
     *
     * class constructor
     *
     * @param string $name
     */
    public function __construct($name)
    {
        parent::__construct($this->_element, $name);

        $this->setBodyCode(
            "<script type=\"text/javascript\">" . "\n"
            . " $(document).ready(function() { " . "\n"
            . "     $('." . self::ELEMENT_CLASS . " .ajax-text-span').on('click', function() { " . "\n"
            . "         var el = $(this).closest('label'); " . "\n"
            . "         var v = el.find('.ajax-text-span').html(); " . "\n"
            . "         el.find('[name=\"" . self::INPUT_NAME . "\"]').val(v); " . "\n"
            . "         el.find('.ajax-text-span').hide(); " . "\n"
            . "         el.find('div').removeClass('d-none').addClass('d-flex'); " . "\n"
            . "         feather.replace(); " . "\n"
            . "     }); " . "\n"
            . "      $('." . self::ELEMENT_CLASS . " .btn-success').on('click', function() {
                        var el = $(this).closest('label');
                        var v = el.find('[name=\"" . self::INPUT_NAME . "\"]').val();
                        var postUrl = el.data('post-url');
                        $.post(
                            postUrl,
                            {
                                comments: v
                            },
                            function (data) {
                                el.find('.ajax-text-span').html(v).show();
                                el.find('div').removeClass('d-flex').addClass('d-none');
                            },
                            'json'
                        );
                    }); " . "\n"
            . "     $('." . self::ELEMENT_CLASS . " .btn-danger').on('click', function() { " . "\n"
            . "         var el = $(this).closest('label'); " . "\n"
            . "         el.find('.ajax-text-span').show(); " . "\n"
            . "         el.find('div').removeClass('d-flex').addClass('d-none'); " . "\n"
            . "     }); " . "\n"
            . " }); " . "\n"
            . "</script>");

        $this->setHeaderCode(
            "<style type=\"text/css\">" . "\n"
            . "." . self::ELEMENT_CLASS . " { " . "\n"
            . "     display: block; " . "\n"
            . "} " . "\n"
            . "." . self::ELEMENT_CLASS . " span.ajax-text-span { " . "\n"
            . "     cursor: text; " . "\n"
            . "     padding-bottom: 2px; " . "\n"
            . "     border-bottom: 1px dotted #999; " . "\n"
            . "} " . "\n"
            . "</style>");
    }

    /**
     *
     * set ajax post url
     *
     * @param string $postUrl
     * @return $this
     */
    public function setPostUrl($postUrl)
    {
        $this->_postUrl = $postUrl;

        return $this;
    }

    /**
     *
     * get ajax post url
     *
     * @throws \RuntimeException
     * @return string
     */
    public function getPostUrl()
    {
        if (!$this->_postUrl) {
            throw new \RuntimeException("The post url for the AjaxText form element must be set.");
        }

        return $this->_postUrl;
    }

    /**
     *
     * render element
     *
     * @return string
     */
    public function render()
    {
        $translate = $this->getTranslate();

        $value = $this->getValue();

        return '<label class="' . self::ELEMENT_CLASS . '" data-post-url="' . $this->getPostUrl() . '">'
               . '<span class="ajax-text-span" title="' . $translate->_('Edit Text') . '">'
               . $value
               . '</span>'
               . '<div class="align-items-center d-none">'
               . '  <input type="text" name="' . self::INPUT_NAME . '" '
               . $this->renderAttributes()
               . ' value="' . $value . '" '
               . $this->_endTag . ' '
               . '      <button class="btn btn-success ml-1" type="button"><span data-feather="check"></span></button> '
               . '      <button class="btn btn-danger ml-1" type="button"><span data-feather="x"></span></button> '
               . '</div>'
               . '</label>';
    }

}

