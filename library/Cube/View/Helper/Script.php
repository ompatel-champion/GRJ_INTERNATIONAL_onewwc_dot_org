<?php

/**
 *
 * Cube Framework
 *
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2017 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 *
 * @version     2.0 [rev.2.0.01]
 */

/**
 * javascript, css etc view helper
 */

namespace Cube\View\Helper;

class Script extends AbstractHelper
{

    /**
     * operations
     */
    const APPEND = 'append';
    const PREPEND = 'prepend';

    /**
     *
     * code to be added between the <head> tags of the html code
     *
     * @var array
     */
    protected $_headerCode = array();

    /**
     *
     * code to be added between the <body> tags, preferably towards the
     * bottom, usable for javascript code etc
     *
     * @var array
     */
    protected $_bodyCode = array();

    /**
     *
     * prepend header code
     *
     * @param string $code
     *
     * @return $this
     */
    public function prependHeaderCode($code)
    {
        return $this->addHeaderCode($code, self::PREPEND);
    }

    /**
     *
     * append header code
     *
     * @param string $code
     *
     * @return $this
     */
    public function appendHeaderCode($code)
    {
        return $this->addHeaderCode($code, self::APPEND);
    }

    /**
     *
     * add code to the page header, duplicates will be skipped
     *
     * @param string $code
     * @param string $operation
     *
     * @return $this
     */
    public function addHeaderCode($code, $operation = self::APPEND)
    {
        $code = (string)$code;

        if (!in_array($code, $this->_headerCode)) {
            switch ($operation) {
                case self::PREPEND:
                    array_unshift($this->_headerCode, $code);
                    break;
                case self::APPEND:
                default:
                    array_push($this->_headerCode, $code);
                    break;
            }
        }

        return $this;
    }

    /**
     *
     * replace header code line
     *
     * @param string $search
     * @param string $replace
     *
     * @return $this
     */
    public function replaceHeaderCode($search, $replace)
    {
        foreach ($this->_headerCode as $key => $value) {
            if (strcmp($search, $value) === 0) {
                $this->_headerCode[$key] = $replace;
            }
        }

        return $this;
    }


    /**
     *
     * remove header code
     *
     * @param string $code
     *
     * @return $this
     */
    public function removeHeaderCode($code)
    {
        $code = $this->_addSpecialChars($code);

        foreach ($this->_headerCode as $key => $value) {
            $value = $this->_addSpecialChars($value);

            if ($value == $code) {
                unset($this->_headerCode[$key]);
            }
        }

        return $this;
    }

    /**
     *
     * clear header code variable
     *
     * @return $this
     */
    public function clearHeaderCode()
    {
        $this->_headerCode = array();

        return $this;
    }

    /**
     *
     * prepend body code
     *
     * @param string $code
     *
     * @return $this
     */
    public function prependBodyCode($code)
    {
        return $this->addBodyCode($code, self::PREPEND);
    }

    /**
     *
     * append body code
     *
     * @param string $code
     *
     * @return $this
     */
    public function appendBodyCode($code)
    {
        return $this->addBodyCode($code, self::APPEND);
    }

    /**
     *
     * add code to the page body, duplicates will be skipped
     *
     * @param string $code
     * @param string $operation
     *
     * @return $this
     */
    public function addBodyCode($code, $operation = self::APPEND)
    {
        if (is_array($code)) {
            foreach ($code as $c) {
                $this->addBodyCode($c, $operation);
            }
        }
        else {
            $code = (string)$code;

            if (!in_array($code, $this->_bodyCode)) {
                switch ($operation) {
                    case self::PREPEND:
                        array_unshift($this->_bodyCode, $code);
                        break;
                    case self::APPEND:
                    default:
                        array_push($this->_bodyCode, $code);
                        break;
                }
            }
        }

        return $this;
    }

    /**
     *
     * replace body code line
     *
     * @param string $search
     * @param string $replace
     *
     * @return $this
     */
    public function replaceBodyCode($search, $replace)
    {
        foreach ($this->_bodyCode as $key => $value) {
            if (strcmp($search, $value) === 0) {
                $this->_bodyCode[$key] = $replace;
            }
        }

        return $this;
    }

    /**
     *
     * remove body code
     *
     * @param string $code
     *
     * @return $this
     */
    public function removeBodyCode($code)
    {
        $code = $this->_addSpecialChars($code);

        foreach ($this->_bodyCode as $key => $value) {
            $value = $this->_addSpecialChars($value);

            if ($value == $code) {
                unset($this->_bodyCode[$key]);
            }
        }

        return $this;
    }

    /**
     *
     * clear body code variable
     *
     * @return $this
     */
    public function clearBodyCode()
    {
        $this->_bodyCode = array();

        return $this;
    }

    /**
     *
     * method that is called by the reflection class, returns an instance of the object
     *
     * @return \Cube\View\Helper\Script
     */
    public function script()
    {
        return $this;
    }

    /**
     *
     * display the header code
     *
     * @return string
     */
    public function displayHeaderCode()
    {
        return implode("\n", $this->_headerCode);
    }

    /**
     *
     * display the footer code
     *
     * @return string
     */
    public function displayBodyCode()
    {
        return implode("\n", $this->_bodyCode);
    }

    /**
     *
     * add special chars
     *
     * @param string $input
     *
     * @return mixed
     */
    private function _addSpecialChars($input)
    {
        return str_ireplace(
            array('&amp;', '&#039;', '&quot;', '&lt;', '&gt;', '&nbsp;'), array('&', "'", '"', '<', '>', ' '), $input);
    }
}

