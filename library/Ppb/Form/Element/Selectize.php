<?php

/**
 *
 * PHP Pro Bid
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2018 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     8.0 [rev.8.0.04]
 */

/**
 * select form element with selectize jquery plugin (w/ optional remote loading)
 *
 * always call setDataUrl method after calling the setAttributes method, to initialize the plugin
 */

namespace Ppb\Form\Element;

use Cube\Form\Element\Select,
    Cube\Controller\Front;

class Selectize extends Select
{

    const ELEMENT_CLASS_NO_REMOTE = 'element-selectize';
    const ELEMENT_CLASS_REMOTE = 'element-selectize-remote';

    /**
     * if not loading data remotely
     */
    const NO_REMOTE = 'no-remote';

    /**
     *
     * type of element - override the variable from the parent class
     *
     * @var string
     */
    protected $_element = 'Selectize';

    /**
     *
     * data url
     *
     * @var string
     */
    protected $_dataUrl = self::NO_REMOTE;

    /**
     *
     * product attribute flag
     *
     * @var bool
     */
    protected $_productAttribute = false;

    /**
     *
     * class constructor
     *
     * @param string $name
     * @param bool   $initialize
     */
    public function __construct($name, $initialize = true)
    {
        parent::__construct($name);

        $baseUrl = Front::getInstance()->getRequest()->getBaseUrl();

        $this->setHeaderCode('<link href="' . $baseUrl . '/js/selectize/css/selectize.bootstrap4.css" rel="stylesheet" type="text/css">')
            ->setHeaderCode('<script type="text/javascript" src="' . $baseUrl . '/js/selectize/js/standalone/selectize.js"></script>');

        $this->addAttribute('class', self::ELEMENT_CLASS_NO_REMOTE);
    }

    /**
     *
     * get data url
     *
     * @return string
     */
    public function getDataUrl()
    {
        return $this->_dataUrl;
    }

    /**
     *
     * set data url
     *
     * @param string $dataUrl
     *
     * @return $this
     */
    public function setDataUrl($dataUrl)
    {
        $this->_dataUrl = $dataUrl;

        if ($dataUrl !== self::NO_REMOTE) {
            $id = $this->getAttribute('id');

            if (empty($id)) {
                $class = $this->getAttribute('class');
                $class = trim(str_replace(self::ELEMENT_CLASS_NO_REMOTE, '', $class));
                parent::addAttribute('class', $class, false);
                parent::addAttribute('class', self::ELEMENT_CLASS_REMOTE);
            }
        }

        return $this;
    }

    /**
     *
     * check product attribute flag
     *
     * @return bool
     */
    public function isProductAttribute()
    {
        return $this->_productAttribute;
    }

    /**
     *
     * set product attribute flag
     *
     * @param bool $productAttribute
     *
     * @return $this
     */
    public function setProductAttribute($productAttribute)
    {
        $this->_productAttribute = $productAttribute;

        if ($productAttribute) {
            $this->addAttribute('class', 'product-attribute');
        }

        return $this;
    }

    /**
     *
     * if we have an id attribute, remove the default selectize class
     *
     * @param string $key
     * @param string $value
     * @param bool   $append
     *
     * @return $this
     */
    public function addAttribute($key, $value, $append = true)
    {
        if ($key == 'id') {
            $class = parent::getAttribute('class');
            $class = trim(str_replace(array(self::ELEMENT_CLASS_NO_REMOTE, self::ELEMENT_CLASS_REMOTE), '', $class));
            parent::addAttribute('class', $class, false);
        }

        parent::addAttribute($key, $value, $append);

        return $this;
    }

    /**
     *
     * override method and add body code that is required for the element to work properly
     * it is considered that this method is called last, before the render() method
     *
     * @return array
     */
    public function getBodyCode()
    {
        $bodyCode = parent::getBodyCode();

        $id = $this->getAttribute('id');

        if (($dataUrl = $this->getDataUrl()) !== self::NO_REMOTE) {
            $functionName = $this->_generateFunctionName($id ? $id : self::ELEMENT_CLASS_REMOTE);

            array_push($bodyCode,
                "<script type=\"text/javascript\">
                    function " . $functionName . "(element) {
                        element.selectize({
                            valueField: 'value',
                            labelField: 'label',
                            searchField: 'label',
                            options: [],
                            plugins: ['remove_button'],            
                            preload: 'focus',
                            loadingClass: 'loading',
                            load: function (query, callback) {
                                $.ajax({
                                    url: '" . $this->getDataUrl() . "',
                                    type: 'GET',
                                    data: {           
                                        term: query        
                                    },
                                    error: function () {
                                        callback();
                                    },
                                    success: function (res) {
                                        callback(res);
                                    }
                                });
                            }
                        });
                    }
                    
                    " . $functionName . "($('" . ($id ? '#' . $id : '.' . self::ELEMENT_CLASS_REMOTE) . "'));
                </script>");
        }
        else {
            $functionName = $this->_generateFunctionName($id ? $id : self::ELEMENT_CLASS_NO_REMOTE);

            array_push($bodyCode,
                "<script type=\"text/javascript\">
                    function " . $functionName . "(element) {
                        element.selectize({
                            valueField: 'value',
                            labelField: 'label',
                            searchField: 'label',
                            options: [],
                            plugins: ['remove_button']
                        });
                    }
                    
                    " . $functionName . "($('" . ($id ? '#' . $id : '.' . self::ELEMENT_CLASS_NO_REMOTE) . "'));
                </script>");
        }

        parent::addAttribute('data-function-name', $functionName, false);

        return $bodyCode;
    }

    /**
     *
     * generate function name - camel case
     *
     * @param string $name
     *
     * @return string
     */
    protected function _generateFunctionName($name)
    {
        return 'selectize' . str_replace(' ', '', ucwords(str_replace('-', ' ', $name)));
    }

}

