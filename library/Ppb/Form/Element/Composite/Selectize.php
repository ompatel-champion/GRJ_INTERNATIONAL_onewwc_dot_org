<?php

/**
 *
 * PHP Pro Bid
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2018 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     8.0 [rev.8.0.01]
 */

/**
 * composite form element
 *
 * creates an element containing an unlimited (jquery powered) list of rows, each having the same column structure
 */

namespace Ppb\Form\Element\Composite;

use Ppb\Form\Element\Composite,
    Ppb\Form\Element\Selectize as SelectizeElement,
    Ppb\Service;

class Selectize extends Composite
{

    /**
     *
     * type of element - override the variable from the parent class
     *
     * @var string
     */
    protected $_element = 'compositeSelectize';

    /**
     *
     * categories service object
     *
     * @var \Ppb\Service\Table\Relational\Categories
     */
    protected $_categories;

    /**
     *
     * get categories table service
     *
     * @return \Ppb\Service\Table\Relational\Categories
     */
    public function getCategories()
    {
        if (!$this->_categories instanceof Service\Table\Relational\Categories) {
            $this->setCategories(
                new Service\Table\Relational\Categories());
        }

        return $this->_categories;
    }

    /**
     *
     * set categories table service
     *
     * @param \Ppb\Service\Table\Relational\Categories $categories
     *
     * @return $this
     */
    public function setCategories(Service\Table\Relational\Categories $categories)
    {
        $this->_categories = $categories;

        return $this;
    }

    /**
     *
     * render a single row of the composite element
     *
     * @param mixed $id
     *
     * @return string
     */
    protected function _renderRow($id = null)
    {
        $translate = $this->getTranslate();

        $output = '<div class="' . self::ROW . ' align-items-start d-flex mb-1">';

        $elements = $this->getElements();

        $values = $this->getValue();

        $categoriesService = $this->getCategories();

        /** @var \Cube\Form\Element $element */
        foreach ($elements as $name => $element) {
            $elementName = $this->getName() . '[' . $name . ']' . '[' . $this->_counter . ']';
            $element->setName($elementName);

            if (!empty($values[$name][$id])) {
                $element->setValue($values[$name][$id]);
            }
            else {
                $element->setValue('');
            }

            if ($element instanceof SelectizeElement) {
                if ($element->getDataUrl() !== SelectizeElement::NO_REMOTE) {
                    $elementValue = (array)$element->getValue();

                    if (count($elementValue) > 0) {
                        $categoriesSelect = $categoriesService->getTable()->select()
                            ->where('id IN (?)', $elementValue);
                        $categoriesMultiOptions = $categoriesService->getMultiOptions($categoriesSelect, null, false, true);
                        $element->setMultiOptions($categoriesMultiOptions);
                    }
                }
            }

            $output .= $element->render();
        }

        if ($id === null) {
            $output .= '<button class="' . self::BTN_ADD . ' btn btn-secondary ml-1">' . $translate->_('Add') . '</button>';
        }
        else {
            if ($this->isArrange()) {
                $output .= '<a class="' . self::BTN_MOVE_UP . ' btn btn-light ml-1" href="#"><span data-feather="chevron-up"></span></a>'
                    . '<a class="' . self::BTN_MOVE_DOWN . ' btn btn-light ml-1" href="#"><span data-feather="chevron-down"></span></a>';
            }
            $output .= '<button class="' . self::BTN_DELETE . ' btn btn-danger ml-1">' . $translate->_('Delete') . '</button>';
        }

        $output .= '</div>';

        $this->_counter++;

        return $output;
    }

}
