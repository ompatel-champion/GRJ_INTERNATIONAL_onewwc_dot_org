<?php

/**
 *
 * PHP Pro Bid
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.0
 */
/**
 * product bundles composite element
 *
 * creates an element that will contain an unlimited number of rows that include the following columns:
 * - a "title" field - will attach a title for the selection
 * - a "products" field - will allow one ore more products to be added in the same selection. If there are more products
 *   in a single selection, then only one of those products will be selectable (a radio button will be used)
 * - an "order" field - will order the fields on the display partial
 */
/**
 * MOD:- PRODUCT BUNDLES
 * TODO: potential issue with multi select on editing.
 */

namespace Ppb\Form\Element;

use Cube\Controller\Front,
    Cube\Form\Element;

class ProductBundles extends Element
{

    const FIELD_TITLE = 'title';
    const FIELD_PRODUCTS = 'products';
    const FIELD_ORDER = 'order';

    /**
     *
     * type of element - override the variable from the parent class
     *
     * @var string
     */
    protected $_element = 'productBundles';

    /**
     *
     * chzn select elements options
     *
     * @var array
     */
    protected $_chznMultiOptions = false;

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

        $this->_baseUrl = Front::getInstance()->getRequest()->getBaseUrl();

        $this->setHeaderCode('<link href="' . $this->_baseUrl . '/js/chosen/chosen.css" media="screen" rel="stylesheet" type="text/css">')
            ->setBodyCode('<script type="text/javascript" src="' . $this->_baseUrl . '/js/chosen/chosen.jquery.min.js"></script>')
            ->setBodyCode(
                "<script type=\"text/javascript\">" . "\n"
                . " $('." . ChznSelect::ELEMENT_CLASS . "').chosen(); " . "\n"
                . "</script>");

        $this->setBodyCode(
            "<script type=\"text/javascript\">
            $(document).on('click', '.delete-field-row', function(e) { " . "\n"
            . "     e.preventDefault(); " . "\n"
            . "     var cnt = 0; " . "\n"
            . "     $(this).closest('.field-row').remove(); " . "\n"
            . "     $('.{$name}-row').each(function() { " . "\n"
            . "         var selectName = $(this).find('select').attr('name').replace(/(\[\d+\])/g, '[' + cnt + ']');" . "\n"
            . "         $(this).find('select').attr('name', selectName); " . "\n"
            . "         cnt++; " . "\n"
            . "     }); " . "\n"
            . " }); " . "\n"
            . " $(document).on('click', '.add-field-row', function(e) { " . "\n"
            . "     e.preventDefault(); " . "\n"
            . "     var nbRows = $('.{$name}-row').length; " . "\n"
            . "     var row = $(this).closest('.field-row'); " . "\n"
            . "     var cloned = row.clone(true, true); " . "\n"
            . "     cloned.find('.add-field-row').remove(); " . "\n"
            . "     cloned.find('select').val(row.find('select').val()); " . "\n" // as per jquery clone bug (doesnt copy selected values)
            . "     cloned.find('.chzn-container').remove(); " . "\n"
            . "     cloned.find('select').css({display: 'inline-block'}).removeAttr('id').removeClass('chzn-done'); " . "\n"
            . "     $('<button>').attr('class', 'delete-field-row btn btn-default').html('" . $translate->_('Delete') . "').appendTo(cloned); " . "\n"
            . "     cloned.insertBefore(row); " . "\n"
            . "     row.find('.chzn-container').remove(); " . "\n"
            . "     row.find('input:text').val(''); " . "\n"
            . "     var selectObject = row.find('select'); " . "\n"
            . "     if (selectObject.attr('name')) { " . "\n"
            . "         var selectName = selectObject.attr('name').replace(/(\[\d+\])/g, '[' + nbRows + ']'); " . "\n"
            . "         selectObject.css({display: 'inline-block'}).removeAttr('id').removeClass('chzn-done').attr('name', selectName); " . "\n"
            . "         selectObject.val(''); " . "\n"
            . "     } " . "\n"
            . "     $('." . ChznSelect::ELEMENT_CLASS . "').chosen(); " . "\n"
            . " }); " . "\n"
            . "</script>");
    }

    /**
     *
     * get chzn elements multi options
     *
     * @return array
     */
    public function getChznMultiOptions()
    {
        return $this->_chznMultiOptions;
    }

    /**
     *
     * set chzn elements multi options
     *
     * @param array $chznMultiOptions
     *
     * @return \Ppb\Form\Element\ListingPostageLocations
     */
    public function setChznMultiOptions($chznMultiOptions)
    {
        $this->_chznMultiOptions = $chznMultiOptions;

        return $this;
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
        $counter = 0;

        $values = $this->getValue();

        foreach ((array)$values[self::FIELD_TITLE] as $id => $key) {
            if (!empty($key)) {
                $product = (isset($values[self::FIELD_PRODUCTS][$id])) ?
                    $values[self::FIELD_PRODUCTS][$id] : null;
                $output .= $this->_renderRow(false, $key, $values[self::FIELD_ORDER][$id], $product, $counter++);
            }
        }

        $output .= $this->_renderRow(true, null, null, null, $counter++);

        return $output;
    }

    /**
     *
     * render a single row of the element
     *
     * @param bool   $new
     * @param string $title
     * @param string $order
     * @param string $products
     * @param int    $counter
     *
     * @return string
     */
    protected function _renderRow($new = true, $title = null, $order = null, $products = null, $counter = null)
    {
        $translate = $this->getTranslate();

        $brackets = '';
        if ($counter !== null) {
            $brackets = '[' . $counter . ']';
        }

        $chznMultiOptions = $this->getChznMultiOptions();

        if ($chznMultiOptions !== false) {
            $chznSelect = new ChznSelect($this->_name . '[' . self::FIELD_PRODUCTS . ']' . $brackets, false);
            $chznSelect->setAttributes(array(
                'size'             => 1,
                'style'            => 'width: 200px;',
                'data-placeholder' => $translate->_('Choose Products'),
            ))
                ->setMultiOptions(
                    $this->getChznMultiOptions())
                ->setMultiple()
                ->setValue($products);
        }

        return '<div class="field-row ' . $this->_name . '-row">'
        . ' <input type="text" name="' . $this->_name . '[' . self::FIELD_TITLE . '][]" '
        . ' placeholder="' . $translate->_('Title') . '" class="form-control input-default"'
        . ' value="' . $title . '" '
        . $this->_endTag
        . ' <input type="text" name="' . $this->_name . '[' . self::FIELD_ORDER . '][]" '
        . ' placeholder="' . $translate->_('Order ID') . '" class="form-control input-mini"'
        . ' value="' . $order . '" '
        . $this->_endTag
        . ' '
        . (($chznMultiOptions !== false) ? $chznSelect->render() : '')

        . (($new === true) ?
            ' <button class="add-field-row btn btn-default">' . $translate->_('Add') . '</button>' :
            ' <button class="delete-field-row btn btn-default">' . $translate->_('Delete') . '</button>')
        . '</div>';
    }

}

