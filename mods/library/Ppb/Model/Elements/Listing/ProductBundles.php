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

namespace Ppb\Model\Elements\Listing;

use Ppb\Model\Elements\AbstractElements,
    Cube\Controller\Front,
    Ppb\Db\Table\Row\User as UserModel,
    Ppb\Service,
    Ppb\Form\Element\Selectize;

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
     * listing owner
     *
     * @var \Ppb\Db\Table\Row\User
     */
    protected $_user;

    /**
     *
     * get current user
     *
     * @return \Ppb\Db\Table\Row\User
     */
    public function getUser()
    {
        if (!$this->_user instanceof UserModel) {
            $this->setUser(
                Front::getInstance()->getBootstrap()->getResource('user'));
        }

        return $this->_user;
    }

    /**
     *
     * set current user
     *
     * @param \Ppb\Db\Table\Row\User $user
     *
     * @return $this
     */
    public function setUser(UserModel $user)
    {
        $this->_user = $user;

        return $this;
    }

    /**
     *
     * get products that can be bundled
     *
     * @return array
     */
    public function getBundledProducts()
    {
        $listingsService = new Service\Listings();

        $select = $listingsService->getTable()->select()
            ->where('listing_type IN (?)', array('product'))
            ->where('stock_levels is null or stock_levels = ""');

        if ($id = $this->getData('id')) {
            $select->where('id != ?', $id);
        }

        $rowset = $this->getUser()->findDependentRowset('\Ppb\Db\Table\Listings', null, $select);

        $listings = array();

        /** @var \Ppb\Db\Table\Row\Listing $row */
        foreach ($rowset as $row) {
            $id = $row->getData('id');
            $listings[$id] = '[#' . $id . '] ' . $row->getData('name');
        }

        return $listings;
    }

    /**
     *
     * get model elements
     *
     * @return array
     */
    public function getElements()
    {
        $translate = $this->getTranslate();

        return array(
            array(
                'form_id'      => array('product', 'product_edit'),
                'subform'      => 'details',
                'id'           => 'hidden_product',
                'after'        => array('id', 'listing_type'),
                'element'      => 'checkbox',
                'label'        => $this->_('Hidden Product'),
                'description'  => $this->_('Check the above checkbox if you for the product to only be usable in product bundles.'),
                'multiOptions' => array(
                    '1' => null,
                ),
                'bodyCode'     => "
                    <script type=\"text/javascript\">
                        function checkProductBundlesFields()
                        {
                            var listingType = $('[name=\"listing_type\"]');                           

                            if (listingType.val() === 'auction') {
                               $('[name=\"hidden_product\"]').prop('checked', false).closest('.form-group').hide();
                            }
                            else if (listingType.val() === 'product') {                                
                                $('[name=\"hidden_product\"]').closest('.form-group').show();
                            }                
                        }   

                        $(document).ready(function() {
                            checkProductBundlesFields();
                        });
                        
                        $(document).on('change', '.field-changeable', function() {
                            checkProductBundlesFields();
                        });
                    </script>"
            ),
            array(
                'form_id'     => array('product', 'product_edit'),
                'subtitle'    => $this->_('Product Bundles'),
                'subform'     => 'details',
                'id'          => 'product_bundles',
                'before'      => array('id', 'currency'),
                'element'     => '\\Ppb\\Form\\Element\\Composite\\Selectize',
                'label'       => $this->_('Bundled Products'),
                'description' => $this->_('Add products that can be bundled with this listing.'),
                'elements'    => array(
                    array(
                        'id'         => 'title',
                        'element'    => 'text',
                        'attributes' => array(
                            'class'       => 'form-control input-default mr-1',
                            'placeholder' => $translate->_('Title'),
                        ),
                    ),
                    array(
                        'id'         => 'order',
                        'element'    => 'text',
                        'attributes' => array(
                            'class'       => 'form-control input-mini mr-1',
                            'placeholder' => $translate->_('Order ID'),
                        ),
                    ),
                    array(
                        'id'           => 'products',
                        'element'      => '\\Ppb\\Form\\Element\\Selectize',
                        'attributes'   => array(
                            'class'       => 'form-control input-medium',
                            'placeholder' => $translate->_('Choose Products...'),
                        ),
                        'multiOptions' => $this->getBundledProducts(),
                        'dataUrl'      => Selectize::NO_REMOTE,
                        'multiple'     => true,
                    ),
                ),
                'arrange'     => true,
            ),
        );
    }
}

