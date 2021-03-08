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
 * address selector form element
 */

namespace Ppb\Form\Element;

use Cube\Form\Element\Radio;

class SelectAddress extends Radio
{
    /**
     *
     * render the form element
     *
     * @return string
     */
    public function render()
    {
        $output = null;
        $value = $this->getValue();

        $translate = $this->getTranslate();

        foreach ((array)$this->_multiOptions as $key => $option) {
            $checked = ($value == $key) ? ' checked="checked" ' : '';

            if (is_array($option)) {
                $title = $this->_getData($option, 'title');
                $description = $this->_getData($option, 'description');

                $multiOptionAttributes = array();

                $locationId = $this->_getData($option, 'locationId');
                if ($locationId !== null) {
                    $multiOptionAttributes['data-location-id'] = $locationId;
                }

                $postCode = $this->_getData($option, 'postCode');
                if ($postCode !== null) {
                    $multiOptionAttributes['data-post-code'] = $postCode;
                }

                if (count($multiOptionAttributes) > 0) {
                    $this->addMultiOptionAttributes($key, $multiOptionAttributes);
                }
            }
            else {
                $title = $option;
                $description = null;
            }

            $this->addAttribute('class', 'form-check-input');

            $attributes = array(
                'type="' . $this->_element . '"',
                'name="' . $this->_name . '"',
                'value="' . $key . '"',
                $this->renderAttributes(),
                $this->renderOptionAttributes($key),
                $checked
            );

            $output .= '<div class="form-check">'
                . '<input ' . implode(' ', array_filter($attributes)) . '>'
                . '<label class="form-check-label">' . $translate->_($title) . '</label>'
                . (($description !== null) ? '<small class="form-text text-dark mb-1">' . $translate->_($description) . '</small>' : '')
                . '</div>'
                . "\n";
        }

        return $output;
    }

    protected function _getData($array, $key)
    {
        return (isset($array[$key])) ? $array[$key] : null;
    }
} 