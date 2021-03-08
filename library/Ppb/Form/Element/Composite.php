<?php

/**
 *
 * PHP Pro Bid
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2018 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     8.0 [rev.8.0.05]
 */

/**
 * composite form element
 *
 * creates an element containing an unlimited (jquery powered) list of rows, each having the same column structure
 */

namespace Ppb\Form\Element;

use Ppb\Form\Element,
    Cube\Form\Element as ElementBase;

class Composite extends Element
{

    /**
     * container classes
     */
    const CONTAINER = 'composite-container';
    const ROW = 'composite-row';

    /**
     * button classes
     */
    const BTN_ADD = 'btn-composite-add';
    const BTN_DELETE = 'btn-composite-delete';
    const BTN_MOVE_UP = 'btn-composite-move-up';
    const BTN_MOVE_DOWN = 'btn-composite-move-down';

    /**
     *
     * type of element - override the variable from the parent class
     *
     * @var string
     */
    protected $_element = 'composite';

    /**
     *
     * array of elements included in a row
     *
     * @var array
     */
    protected $_elements = array();

    /**
     *
     * true if rows can be arranged (up / down)
     *
     * @var bool
     */
    protected $_arrange = false;

    /**
     *
     * used for countable elements
     *
     * @var int
     */
    protected $_counter = 0;

    /**
     *
     * class constructor
     *
     * @param string $name
     */
    public function __construct($name)
    {
        parent::__construct($this->_element, $name);
    }

    /**
     *
     * get elements
     *
     * @return array
     */
    public function getElements()
    {
        return $this->_elements;
    }

    /**
     *
     * add elements
     *
     * @param array $elements
     *
     * @return $this
     */
    public function setElements(array $elements)
    {
        foreach ($elements as $element) {
            $this->addElement($element);
        }

        return $this;
    }

    /**
     *
     * add single element
     * only an array is accepted as we will generate the name automatically
     *
     * also add any header / body code associated with the element to the composite elements' header / body code
     *
     * @param mixed $element
     *
     * @return $this
     */
    public function addElement($element)
    {
        if ($element instanceof ElementBase) {
            $formElement = $element;
            $elementId = $element->getName();
        }
        else {
            $formElement = $this->_createElement($element['element'], $element['id']);

            foreach ($element as $method => $params) {
                $methodName = 'set' . ucfirst($method);
                if (method_exists($formElement, $methodName) && !empty($element[$method])) {
                    $formElement->$methodName(
                        $this->_prepareData($params));
                }

                $elementHeaderCode = $formElement->getHeaderCode();
                foreach ($elementHeaderCode as $headerCode) {
                    $this->setHeaderCode($headerCode);
                }

                $elementBodyCode = $formElement->getBodyCode();
                foreach ($elementBodyCode as $bodyCode) {
                    $this->setBodyCode($bodyCode);
                }
            }

            $elementId = (string)$element['id'];
        }

        $this->_elements[$elementId] = $formElement;

        return $this;
    }

    /**
     *
     * check if an element exists in the elements array
     *
     * @param string $elementId
     *
     * @return bool
     */
    public function hasElement($elementId)
    {
        return (array_key_exists($elementId, $this->_elements)) ? true : false;
    }

    /**
     *
     * remove element from elements array
     *
     * @param string $elementId
     *
     * @return $this
     */
    public function removeElement($elementId)
    {
        if ($this->hasElement($elementId)) {
            unset($this->_elements[$elementId]);
        }

        return $this;
    }

    /**
     *
     * get arrange flag
     *
     * @return bool
     */
    public function isArrange()
    {
        return $this->_arrange;
    }

    /**
     *
     * set arrange flag
     *
     * @param bool $arrange
     *
     * @return $this
     */
    public function setArrange($arrange)
    {
        $this->_arrange = $arrange;

        return $this;
    }

    /**
     *
     * override method and add body code that is required for the element to work properly
     * it is considered that this method is called last, before the render() method
     *
     * @return array
     */
    public function getBodyCode()
    {
        $bodyCode = parent::getBodyCode();

        $translate = $this->getTranslate();

        array_push($bodyCode,
            "<script type=\"text/javascript\">
                    $('#" . $this->_generateContainerId() . "').elementComposite({
                        htmlAdd: '" . $translate->_('Add') . "',
                        htmlDelete: '" . $translate->_('Delete') . "',
                        arrange: " . (($this->isArrange()) ? 'true' : 'false') . "
                    });                   
                </script>");

        return $bodyCode;
    }

    /**
     *
     * render the form element
     *
     * @return string
     */
    public function render()
    {
        $values = $this->getValue();

        $output = '<div class="' . self::CONTAINER . '" id="' . $this->_generateContainerId() . '">';

        if (is_array($values)) {
            reset($values);
            $key = key($values);

            foreach ((array)$values[$key] as $id => $value) {
                if (!empty($value)) {
                    $output .= $this->_renderRow($id);
                }
            }
        }

        $output .= $this->_renderRow()
            . '</div>';

        return $output;
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


    /**
     *
     * prepare serialized data and return it as an array which can be used by the class methods
     *
     * @param mixed $data
     * @param bool  $raw if true, do not combine multi key value fields as key => value
     *
     * @return array
     */
    protected function _prepareData($data, $raw = false)
    {
        if (!is_array($data)) {
            $array = \Ppb\Utility::unserialize($data);

            if ($array === $data) {
                return $data;
            }

            if ($raw === true) {
                return $array;
            }

            $keys = (isset($array['key'])) ? array_values($array['key']) : array();
            $values = (isset($array['value'])) ? array_values($array['value']) : array();

            return array_filter(
                array_combine($keys, $values));
        }

        return $data;
    }

    /**
     *
     * generate container id
     * each composite element will have its own separate jquery initialization
     *
     * @return string
     */
    public function _generateContainerId()
    {
        return $this->_element . ucfirst($this->getName());
    }
}

