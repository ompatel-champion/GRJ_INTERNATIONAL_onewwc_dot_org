<?php

/**
 *
 * PHP Pro Bid
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2018 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     8.0 [rev.8.0.02]
 */

/**
 * live date & time view helper class
 */

namespace Ppb\View\Helper;

class LiveTime extends AbstractHelper
{

    const ELEMENT_ID = 'live-time-id';
    const DATE_FORMAT_JAVASCRIPT = 'F d, Y H:i:s';
    const DEFAULT_DATE_FORMAT = '%m/%d/%Y %H:%M:%S';

    /**
     *
     * the format the date will be output in (strftime format required)
     *
     * @var string
     */
    protected $_format;

    /**
     *
     * LiveTime constructor.
     */
    public function __construct()
    {
        $settings = $this->getSettings();

        $dateFormat = (!empty($settings['date_format'])) ? $settings['date_format'] : self::DEFAULT_DATE_FORMAT;

        $this->setFormat($dateFormat);
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
     * display a formatted date (w/ live clock component)
     *
     * @param string $date
     * @param null   $format
     *
     * @return string
     */
    public function liveTime($date, $format = null)
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

        $this->_generateJavascript(
            date(self::DATE_FORMAT_JAVASCRIPT, $date));

        $format = str_replace(
            array(':', '%H', '%I', '%l', '%M', '%p', '%P', '%r', '%R', '%S', '%T', '%X', '%z', '%Z'), '', $format);

        return '<span id="' . self::ELEMENT_ID . '" class="d-none">' . strftime($format, $date) . ' ' . '<span></span></span>';
    }

    protected function _generateJavascript($dateTime)
    {
        /** @var \Cube\View\Helper\Script $scriptHelper */
        $scriptHelper = $this->getView()->getHelper('script');
        $scriptHelper->addBodyCode("<script type=\"text/javascript\">
                var serverDate = new Date('{$dateTime}'); 
                function padLength(value){ 
                    return (value.toString().length===1)? '0' + value : value;
                }
                
                function displayTime() { 
                    serverDate.setSeconds(serverDate.getSeconds() + 1);
                    var element = $('#" . self::ELEMENT_ID . "');
                    element.find('span').html(padLength(serverDate.getHours()) + ':' + padLength(serverDate.getMinutes()) + ':' + padLength(serverDate.getSeconds()));
                    element.removeClass('d-none');
                } 
 
                setInterval('displayTime()', 1000); 
            </script>");
    }

}

