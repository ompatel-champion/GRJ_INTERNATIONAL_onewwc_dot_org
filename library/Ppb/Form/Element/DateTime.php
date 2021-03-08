<?php

/**
 *
 * PHP Pro Bid
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2020 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     8.2 [rev.8.2.01]
 */

/**
 * date custom form element
 *
 * creates an element of type date using the bootstrap datetimepicker plugin
 */

namespace Ppb\Form\Element;

use Cube\Form\Element,
    Cube\Controller\Front,
    Ppb\Filter;

class DateTime extends Element
{

    /**
     *
     * default format
     */
    const DEFAULT_FORMAT = 'YYYY-MM-DD HH:mm';

    /**
     *
     * custom formats
     *
     * @var array
     */
    protected $_customFormats = array(
        '%d.%m.%Y %H:%M:%S' => "DD.MM.YYYY HH:mm",
        '%d.%m.%Y'          => "DD.MM.YYYY",
        '%m/%d/%Y %H:%M:%S' => "MM/DD/YYYY HH:mm",
        '%m/%d/%Y'          => "MM/DD/YYYY",
    );

    /**
     *
     * type of element - override the variable from the parent class
     *
     * @var string
     */
    protected $_element = 'text';

    /**
     *
     * class constructor
     *
     * @param string $name
     */
    public function __construct($name)
    {
        parent::__construct($this->_element, $name);

        $this->addAttribute('id', $name)
            ->addFilter(new Filter\LocalizedDateTime());
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

        $formData = array(
            'locale: "' . $this->getTranslate()->getLocale() . '"',
            'ignoreReadonly: true',
            'keepInvalid: true',
        );

        $settings = Front::getInstance()->getBootstrap()->getResource('settings');

        $dateFormat = $this->_datetimepickerFormat($settings['date_format']);
        if (isset($this->_customData['formData'])) {
            foreach ((array)$this->_customData['formData'] as $key => $value) {
                if ($key == 'format') {
                    $dateFormat = $this->_datetimepickerFormat($value, $value);
                    $value = "'" . $dateFormat . "'";
                }

                $formData[] = "{$key}: {$value}";
            }

            if (!array_key_exists('format', (array)$this->_customData['formData']) && !empty($settings['date_format'])) {
                $formData[] = "format: '" . $dateFormat . "'";
            }

        }

        $formData = implode(", \n", $formData);

        $this
            ->setBodyCode("<script type=\"text/javascript\">" . "\n"
                . " // Tempus Dominus date picker load remove class " . "\n"
                . " $('*[data-toggle=\"datetimepicker\"]').removeClass('datetimepicker-input'); " . "\n"
                . " // Add class back to Tempus Dominus date picker " . "\n"
                . " $(document).on('click toggle change hide keydown keyup', '*[data-toggle=\"datetimepicker\"]', function() { " . "\n"
                . "     $(this).addClass('datetimepicker-input'); " . "\n"
                . " }); " . "\n"
                . "</script>")
            ->setBodyCode("<script type=\"text/javascript\">" . "\n"
                . " function updateDatetimepicker(element, format, value) { " . "\n"
                . "    if (value) { " . "\n"
                . "        var minDate = element.datetimepicker('minDate'); " . "\n"
                . "        if (minDate > value) { " . "\n"
                . "            element.datetimepicker('minDate', moment(value)); " . "\n"
                . "        } " . "\n"
                . "        var maxDate = element.datetimepicker('minDate'); " . "\n"
                . "        if (maxDate < value) { " . "\n"
                . "            element.datetimepicker('maxDate', moment(value)); " . "\n"
                . "        } " . "\n"
                . "        element.datetimepicker('date', moment(value, format).toDate()); " . "\n"
                . "    } " . "\n"
                . " } " . "\n"
                . "</script>")
            ->setBodyCode(
                "<script type=\"text/javascript\">" . "\n"
                . " $(document).ready(function() { " . "\n"
                . "     var date" . $this->getName() . " = $('#" . $this->getName() . "').val(); " . "\n"
                . "     $('#" . $this->getName() . "').val(''); " . "\n"
                . "     $('#" . $this->getName() . "').datetimepicker({ " . "\n"
                . "         {$formData} " . "\n"
                . "     }); " . "\n"
                . "     updateDatetimepicker($('#" . $this->getName() . "'), '" . $dateFormat . "', date" . $this->getName() . "); " . "\n"
                . " }); " . "\n"
                . "</script>");

        return $this;
    }

    /**
     *
     * renders the date time form element
     *
     * @return string   the html code of the element
     */
    public function render()
    {
        $value = $this->getValue();

        if (!is_string($value)) {
            $value = '';
        }
        else {
            $value = str_replace('"', '&quot;', $value);
        }

        if (!empty($value)) {
            $value = Front::getInstance()->getBootstrap()->getResource('view')->date($value);
        }

        $multiple = ($this->getMultiple() === true) ? $this->_brackets : '';

        $this->addAttribute('class', 'has-icon-right');
        $this->addAttribute('class', 'datetimepicker-input');

        $attributes = array(
            'type="' . $this->_type . '"',
            'name="' . $this->_name . $multiple . '"',
            'value="' . $value . '"',
            $this->renderAttributes()
        );

        $dateTimePickerAttributes = array(
            'data-toggle="datetimepicker"',
            'data-target="#' . $this->getAttribute('id') . '"',
        );

        return $this->getPrefix()
            . '<div class="has-icons mr-1">'
            . '<input ' . implode(' ', array_filter(array_merge($attributes, $dateTimePickerAttributes)))
            . $this->_endTag . ' '
            . '<span class="glyphicon glyphicon-calendar icon-right" ' . implode(' ', array_filter($dateTimePickerAttributes)) . '></span>'
            . '</div>'
            . ' '
            . $this->getSuffix();
    }

    /**
     *
     * convert strfdate format to datetimepicker compatible date format
     *
     * @param string $format
     * @param string $defaultFormat
     *
     * @return string
     */
    protected function _datetimepickerFormat($format, $defaultFormat = self::DEFAULT_FORMAT)
    {
        return (array_key_exists($format, $this->_customFormats)) ? $this->_customFormats[$format] : $defaultFormat;
    }
}

