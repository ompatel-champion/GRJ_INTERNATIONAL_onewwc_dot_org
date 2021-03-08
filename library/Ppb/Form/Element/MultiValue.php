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
 * multi value form element
 *
 * creates an element containing an unlimited (jquery powered) list of value rows
 *
 * DEPRECATED [@version 8.0]
 */

namespace Ppb\Form\Element;

use Cube\Form\Element;

class MultiValue extends Element
{

    /**
     *
     * type of element - override the variable from the parent class
     *
     * @var string
     */
    protected $_element = 'multiValue';

    /**
     *
     * class constructor
     *
     * @param string $name
     */
    public function __construct($name)
    {
        parent::__construct($this->_element, $name);

        $translate = $this->getTranslate();

        $this->setBodyCode(
            "<script type=\"text/javascript\">" . "\n"
            . " function moveUp(item) { " . "\n"
            . "     var before = item.prev(); " . "\n"
            . "     item.insertBefore(before); " . "\n"
            . " } " . "\n"
            . " function moveDown(item) { " . "\n"
            . "     var after = item.next(); " . "\n"
            . "     item.insertAfter(after); " . "\n"
            . " } " . "\n"
            . " $(document).on('click', '.delete-field-row', function(e) { " . "\n"
            . "     e.preventDefault(); " . "\n"
            . "     $(this).closest('.field-row').remove(); " . "\n"
            . " }); " . "\n"
            . " $(document).on('click', '.add-field-row', function(e) { " . "\n"
            . "     e.preventDefault(); " . "\n"
            . "     var parent = $(this).closest('.form-group').find('.multi-value-rows'); " . "\n"
            . "     var row = $(this).closest('.field-row'); " . "\n"
            . "     var cloned = row.clone(true, true); " . "\n"
            . "     row.find('input[type=text]').val(''); " . "\n"
            . "     cloned.find('.add-field-row').remove(); " . "\n"
            . "     $('<a>').attr('href', '#').attr('class', 'btn btn-light btn-fld-move-up mr-1').html(' <span data-feather=\"chevron-up\"></span>').appendTo(cloned); " . "\n"
            . "     $('<a>').attr('href', '#').attr('class', 'btn btn-light btn-fld-move-down mr-1').html('<span data-feather=\"chevron-down\"></span>').appendTo(cloned); " . "\n"
            . "     $('<button>').attr('class', 'delete-field-row btn btn-danger').html('" . $translate->_('Delete') . "').appendTo(cloned); " . "\n"
            . "     parent.append(cloned); " . "\n"
            . "     feather.replace(); " . "\n"
            . " }); " . "\n"
            . " $(document).on('click', '.btn-fld-move-up', function(e) { " . "\n"
            . "     e.preventDefault(); " . "\n"
            . "     var row = $(this).closest('.field-row'); " . "\n"
            . "     moveUp(row); " . "\n"
            . " }); " . "\n"
            . " $(document).on('click', '.btn-fld-move-down', function(e) { " . "\n"
            . "     e.preventDefault(); " . "\n"
            . "     var row = $(this).closest('.field-row'); " . "\n"
            . "     moveDown(row); " . "\n"
            . " }); " . "\n"
            . "</script>");
    }

    /**
     *
     * render the form element
     *
     * @return string
     */
    public function render()
    {
        $output = null;

        $values = $this->getValue();

        $output .= '<div class="multi-value-rows">';
        foreach ((array)$values as $value) {
            if (!empty($value)) {
                $output .= $this->_renderRow(false, $value);
            }
        }
        $output .= '</div>';

        $output .= $this->_renderRow();

        return $output;
    }

    /**
     *
     * render a single row of the element
     *
     * @param bool   $new
     * @param string $value
     *
     * @return string
     */
    protected function _renderRow($new = true, $value = null)
    {
        $translate = $this->getTranslate();

        $this->addAttribute('class', 'mr-1');

        $output = '<div class="field-row align-items-center d-flex mb-1">'
            . '<input type="text" name="' . $this->_name . '[]" '
            . $this->renderAttributes()
            . ' value="' . $value . '" '
            . $this->_endTag
            . (($new === true) ?
                ' <button class="add-field-row btn btn-secondary">' . $translate->_('Add') . '</button>' :
                ' <a class="btn btn-light btn-fld-move-up mr-1" href="#"><span data-feather="chevron-up"></span></a><a class="btn btn-light btn-fld-move-down mr-1" href="#"><span data-feather="chevron-down"></span></a>
                  <button class="delete-field-row btn btn-danger">' . $translate->_('Delete') . '</button>')
            . '</div>';

        return $output;
    }

}

