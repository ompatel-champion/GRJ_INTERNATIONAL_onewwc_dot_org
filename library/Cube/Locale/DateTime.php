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

namespace Cube\Locale;

class DateTime
{

    /**
     *
     * localized format
     *
     * @var int
     */
    protected $_format;

    /**
     *
     * holds an instance of the object
     *
     * @var $this
     */
    private static $_instance;

    /**
     *
     * returns an instance of the object and creates it if it wasnt instantiated yet
     *
     * @return $this
     */
    public static function getInstance()
    {
        if (!self::$_instance instanceof self) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     *
     * get format
     *
     * @return int
     */
    public function getFormat()
    {
        return $this->_format;
    }

    /**
     *
     * set format
     *
     * @param int $format
     *
     * @return $this
     */
    public function setFormat($format)
    {
        $this->_format = $format;

        return $this;
    }

    /**
     *
     * take a localized date and return a mysql datetime format
     *
     * @param string $value
     * @param string $format
     *
     * @return string
     */
    public function localizedToDateTime($value, $format = 'Y-m-d H:i:s')
    {
        return (!empty($value)) ? date($format, strtotime($value)) : '';
    }

    /**
     *
     * convert datetime to localized format
     *
     * @param string $value
     *
     * @return string
     */
    public function dateTimeToLocalized($value)
    {
        return strftime($this->getFormat(), $value);
    }

}

