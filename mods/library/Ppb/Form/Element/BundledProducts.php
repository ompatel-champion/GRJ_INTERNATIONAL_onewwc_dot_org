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
 * MOD:- PRODUCT BUNDLES
 */

/**
 * bundled products form element
 */

namespace Ppb\Form\Element;

use Cube\Form\Element,
    Cube\Controller\Front;

class BundledProducts extends Element
{

    /**
     *
     * type of element - override the variable from the parent class
     *
     * @var string
     */
    protected $_element = 'bundledProducts';

    protected $_bundledProducts = array();

    /**
     *
     * class constructor
     *
     * @param string $name
     */
    public function __construct($name)
    {
        parent::__construct('select', $name);

        $this->_request = Front::getInstance()->getRequest();
    }

    /**
     *
     * get bundled products
     *
     * @return array
     */
    public function getBundledProducts()
    {
        return $this->_bundledProducts;
    }

    /**
     *
     * set bundled products
     *
     * @param array $bundledProducts
     *
     * @return $this
     */
    public function setBundledProducts($bundledProducts)
    {
        $this->_bundledProducts = $bundledProducts;

        return $this;
    }

    /**
     *
     * render composite element
     *
     * @return string
     */
    public function render()
    {
        $output = null;

        $elements = array();

        $bundledProducts = $this->getBundledProducts();

        $view = Front::getInstance()->getBootstrap()->getResource('view');

        if (count($bundledProducts) > 0) {
            foreach ($bundledProducts as $key => $data) {
                if (!empty($data['products'])) {
                    $nbProducts = count($data['products']);

                    if ($nbProducts > 1) {
                        $element = new Element\Select($this->getName());
                        $element->addMultiOption('', '-- none --');
                    }
                    else {
                        $element = new Element\Checkbox($this->getName());
                    }

                    $element->setMultiple(true);

                    /** @var \Ppb\Db\Table\Row\Listing $product */
                    foreach ($data['products'] as $product) {
                        if ($element instanceof Element\Checkbox) {
                            $productDescription = $view->partial('partials/listing-list.phtml',
                                array('listing' => $product));
                        }
                        else {
                            $productDescription = '[#' . $product['id'] . '] ' . $product['name'] . ' (+' . $view->amount($product['buyout_price'], $product['currency']) . ')';
                        }

                        $element->addMultiOption($product['id'], $productDescription);
                    }

                    if (!empty($data['title'])) {
                        $element->setSubtitle($data['title']);
                    }

                    $elements[] = $element;
                }
            }
        }

        /** @var \Cube\Form\Element\Select|\Cube\Form\Element\Checkbox $element */
        foreach ($elements as $element) {
            $subtitle = $element->getSubtitle();

            if (!empty($subtitle)) {
                $output .= '<h5 class="subtitle">' . $subtitle . '</h5>';
            }

            $output .= '<div class="mb-3 bundled-products">' . $element->render() . '</div>';
        }

        return $output;
    }

}

