<?php

/**
 *
 * Cube Framework
 *
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2019 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 *
 * @version     2.1 [rev.2.1.01]
 */

namespace Cube\Translate\Adapter;

use Cube\Locale;

/**
 * abstract translate adapter
 *
 * Class AbstractAdapter
 *
 * @package Cube\Translate\Adapter
 */
abstract class AbstractAdapter
{

    /**
     *
     * translation sentences
     *
     * @var array
     */
    protected $_translate = array();

    /**
     *
     * current locale
     *
     * @var string
     */
    protected $_locale;

    /**
     *
     * class constructor
     *
     * @param array $options
     */
    public function __construct($options = array())
    {
        if ($options) {
            $this->addTranslation($options);
        }
    }

    /**
     *
     * get translation sentences array
     *
     * @return array
     */
    public function getTranslate()
    {
        return $this->_translate;
    }

    /**
     *
     * get locale
     *
     * @return string
     */
    public function getLocale()
    {
        if (!isset($this->_locale)) {
            $this->setLocale(Locale::DEFAULT_LOCALE);
        }

        return $this->_locale;
    }

    /**
     *
     * set active locale
     *
     * @param string|\Cube\Locale $locale
     *
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function setLocale($locale)
    {
        if (is_string($locale)) {
            if (Locale::isLocale($locale) === false) {
                throw new \InvalidArgumentException(
                    sprintf("Invalid locale '%s' set in the translation adapter.", $locale));
            }

            $this->_locale = $locale;
        }
        else if ($locale instanceof Locale) {
            $this->_locale = $locale->getLocale();
        }

        return $this;
    }

    /**
     *
     * translate a sentence in the object active locale or the set locale
     *
     * @param string $message
     * @param string $locale
     *
     * @throws \InvalidArgumentException
     * @return string
     */
    public function translate($message, $locale = null)
    {
        if (empty($message)) {
            return '';
        }

        if (is_numeric($message)) {
            return $message;
        }

        if ($locale === null) {
            $locale = $this->getLocale();
        }
        else if (is_string($locale)) {
            if (Locale::isLocale($locale) === false) {
                throw new \InvalidArgumentException(
                    sprintf("Invalid locale '%s' set in the translation adapter.", $locale));
            }
        }
        else if ($locale instanceof Locale) {
            $locale = $locale->getLocale();
        }
        else {
            throw new \InvalidArgumentException("The locale variable must be null, 
                a string or an object of type \Cube\Locale");
        }

        if (isset($this->_translate[$locale])) {
            $message = trim($this->_filterString($message));

            if (array_key_exists($message, $this->_translate[$locale])) {
                return str_replace(array("'"), array('&#039;'), $this->_translate[$locale][$message]);
            }
        }

        return $message;
    }

    /**
     *
     * proxy to translate method
     *
     * @param string              $message
     * @param string|\Cube\Locale $locale
     *
     * @return string
     */
    public function _($message, $locale = null)
    {
        return $this->translate($message, $locale);
    }

    /**
     *
     * filter a string of ascii tags
     *
     * @param string $string
     *
     * @return string
     */
    protected function _filterString($string)
    {
        return str_ireplace(
            array('&amp;', '&#039;', '&quot;', '&lt;', '&gt;', '&nbsp;'),
            array('&', "'", '"', '<', '>', ' '), $string);
    }

    /**
     *
     * add new translation in the adapter
     * needs a file that includes an array
     *
     * @param array $options
     *
     * @return $this
     * @throws \InvalidArgumentException
     */
    abstract public function addTranslation($options = array());

}

