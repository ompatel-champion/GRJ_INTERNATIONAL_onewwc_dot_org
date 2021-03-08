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
 * class that will generate extra elements for the admin elements model
 * we can have a multiple number of such classes, they just need to have a different name
 * any elements in this class will override original elements
 */
/**
 * MOD:- ADVANCED CLASSIFIEDS
 */

namespace Ppb\Model\Elements\Listing;

use Ppb\Model\Elements\AbstractElements,
    Ppb\Db\Table\Row\User as UserModel,
    Cube\Controller\Front,
    Ppb\Service;

class AdvancedClassifieds extends AbstractElements
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
     * fees service
     *
     * @var \Ppb\Service\Fees
     */
    protected $_fees;

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
     * get fees service
     *
     * @return \Ppb\Service\Fees
     */
    public function getFees()
    {
        if (!$this->_fees instanceof Service\Fees) {
            $this->setFees(
                new Service\Fees());
        }

        return $this->_fees;
    }

    /**
     *
     * set fees service
     *
     * @param \Ppb\Service\Fees $fees
     *
     * @return $this
     */
    public function setFees(Service\Fees $fees)
    {
        $this->_fees = $fees;

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
        $translate = $this->getTranslate();
        $settings = $this->getSettings();

        $listingType = $this->getData('listing_type');

        $this->getFees()->setCategoryId($this->getData('category_id'))
            ->setUser($this->getUser())
            ## -- ADD 1L -- [ MOD:- ADVANCED CLASSIFIEDS ]
            ->setType(($listingType == 'classified') ?
                'classified' : 'default');


        return array(
            array(
                'append'  => true,
                'form_id' => array('global', 'prefilled', 'fees_calculator', 'classified_fees_calculator'),
                'id'      => 'user_id',
            ),
            array(
                'append'     => true,
                'form_id'    => array('global', 'fees_calculator', 'bulk', 'classified_fees_calculator'),
                'id'         => 'category_id',
                'attributes' => array(
                    'data-categories-display' => (($listingType == 'classified') ? 'enable_classifieds' : '')
                ),
            ),
            array(
                'form_id'     => array('global', 'bulk'),
                'subform'     => 'details',
                'id'          => 'addl_category_id',
                'after' => array('id', 'category_id'),
                'element'     => ($settings['addl_category_listing']) ? '\\Ppb\\Form\\Element\\Category' : false,
                'label'       => $this->_('Additional Category'),
                'suffix'      => $this->getView()->amount($this->getFees()->getFeeAmount(Service\Fees::ADDL_CATEGORY),
                    null,
                    '<span class="badge badge-text badge-slim">+%s</span>'),
                'description' => $this->_('Select an additional category where to list your item (optional).'),
                'validators'  => $this->getData('addl_category_id') ? array(
                    '\\Ppb\\Validate\\Db\\Category',
                    array('Different', array($translate->_('Main Category'), $this->getData('category_id')))
                ) : null,
                'bulk'        => array(
                    'type' => $translate->_('integer'),
                ),
                'attributes'  => array(
                    'data-categories-display' => (($listingType == 'classified') ? 'enable_classifieds' : '')
                ),
            ),
//            array(
//                'form_id'      => array('fees_calculator', 'classified_fees_calculator'),
//                'id'           => 'addl_category_id',
//                'element'      => ($settings['addl_category_listing']) ? 'checkbox' : false,
//                'label'        => $this->_('Additional Category'),
//                'description'  => $this->_('Check the above checkbox if you wish to list the item in an additional category.'),
//                'multiOptions' => array(
//                    1 => $this->getView()->amount($this->getFees()->getFeeAmount(Service\Fees::ADDL_CATEGORY),
//                        null,
//                        '<span class="badge badge-text badge-slim">+%s</span>'),
//                ),
//            ),
//            array(
//                'append'  => true,
//                'form_id' => array('fees_calculator', 'classified_fees_calculator'),
//                'id'      => 'image',
//            ),
//            array(
//                'append'  => true,
//                'form_id' => array('fees_calculator', 'classified_fees_calculator'),
//                'id'      => 'video',
//            ),
            array(
                'append'  => true,
                'form_id' => array('auction', 'product', 'classified', 'fees_calculator', 'bulk', 'classified_fees_calculator'),
                'id'      => 'hpfeat',
            ),
            array(
                'append'  => true,
                'form_id' => array('auction', 'product', 'classified', 'fees_calculator', 'bulk', 'classified_fees_calculator'),
                'id'      => 'catfeat',
            ),
            array(
                'append'  => true,
                'form_id' => array('auction', 'product', 'classified', 'fees_calculator', 'bulk', 'classified_fees_calculator'),
                'id'      => 'highlighted',
            ),
        );
    }
}

