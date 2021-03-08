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
 * date view helper class
 *
 * Theory of operation:
 * - dates are saved based on the timezone set in the admin area
 */

namespace Ppb\View\Helper;

class Date extends AbstractHelper
{

    /**
     *
     * the format the date will be output in (strftime format required)
     *
     * @var string
     */
    protected $_format;

    /**
     *
     * class constructor
     */
    public function __construct()
    {
        $settings = $this->getSettings();

        $this->setFormat(
            $settings['date_format']);
    }

    /**
     *
     * get format
     *
     * @return string
     */
    public function getFormat()
    {
        return $this->_format;
    }

    /**
     *
     * set format
     *
     * @param string $format
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
     * display a formatted date
     *
     * @param string $date
     * @param bool   $dateOnly
     * @param string $format
     *
     * @return string
     */
    public function date($date, $dateOnly = false, $format = null)
    {
        if ($date === null) {
            return $this->getTranslate()->_('n/a');
        }

        if (!is_numeric($date)) {
            $date = strtotime($date);
        }

        if ($format === null) {
            $format = $this->getFormat();
        }

        if ($dateOnly) {
            $format = trim(str_ireplace('%H:%M:%S', '', $format));
        }

        return strftime($format, $date);
    }

}

