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
 * MOD:- PRODUCT BUNDLES
 */

/**
 * class that will generate extra elements for the admin elements model
 * we can have a multiple number of such classes, they just need to have a different name
 * any elements in this class will override original elements
 */

namespace Ppb\Model\Elements\Purchase;

use Ppb\Model\Elements\AbstractElements;

class ProductBundles extends AbstractElements
{
    /**
     *
     * related class
     *
     * @var bool
     */
    protected $_relatedClass = true;

    /**
     *
     * listing
     *
     * @var \Ppb\Db\Table\Row\Listing
     */
    protected $_listing;

    /**
     *
     * get listing
     *
     * @return \Ppb\Db\Table\Row\Listing
     */
    public function getListing()
    {
        return $this->_listing;
    }

    /**
     *
     * set listing
     *
     * @param \Ppb\Db\Table\Row\Listing $listing
     *
     * @return $this
     */
    public function setListing($listing)
    {
        $this->_listing = $listing;

        return $this;
    }

    /**
     *
     * get model elements
     *
     * @return array
     */
    public function getElements()
    {
        return array(
            array(
                'form_id'         => array('cart'),
                'id'              => 'bundled_products',
                'before'          => array('id', 'quantity'),
                'element'         => '\\Ppb\\Form\\Element\\BundledProducts',
                'label'           => $this->_('Bundled Products'),
                'bundledProducts' => $this->getListing()->getBundledProducts(),
            ),
        );
    }
}

