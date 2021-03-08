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

namespace Ppb\Model\Elements\CustomField;

use Ppb\Model\Elements\AbstractElements,
    Cube\Controller\Front;

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
     * get model elements
     *
     * @return array
     */
    public function getElements()
    {
        $translate = $this->getTranslate();

        $request = Front::getInstance()->getRequest();

        ## -- START :: ADD -- [ MOD:- ADVANCED CLASSIFIEDS ]
        $categoriesService = $this->getCategories();

        if (in_array('classified', $this->_formId)) {
            $categoriesSelect = $categoriesService->getTable()->select()
                ->where('enable_classifieds = ?', 1);
        }
        else {
            $categoriesSelect = $categoriesService->getTable()->select()
                ->where('enable_auctions = ?', 1);
        }

        $categoriesMultiOptions = $this->getCategories()->getMultiOptions($categoriesSelect, null, false, true);
        ## -- START :: ADD -- [ MOD:- ADVANCED CLASSIFIEDS ]

        ## -- START :: ADD -- [ MOD:- ADVANCED CLASSIFIEDS @version 1.0 ]
        $selectFeesCategories = "parent_id IS NULL AND custom_fees='1'";

        switch ($request->getParam('type')) {
            case 'classified':
                $selectFeesCategories .= " AND enable_classifieds='1'";
                break;
            default:
                $selectFeesCategories .= " AND enable_auctions='1'";
                break;
        }
        ## -- END :: ADD -- [ MOD:- ADVANCED CLASSIFIEDS @version 1.0 ]

        return array(
            array(
                'form_id'      => 'item',
                'id'           => 'category_ids',
                'element'      => '\\Ppb\\Form\\Element\\Selectize',
                'label'        => $this->_('Select Categories'),
                'description'  => $this->_('This field will apply for the categories selected above. Leave empty to apply to all categories.'),
                'attributes'   => array(
                    'id'          => 'selectizeCategoryIds',
                    'class'       => 'form-control input-xlarge',
                    'placeholder' => $translate->_('Choose Categories...'),
                ),
                'dataUrl'      => $this->getView()->url(array('module' => 'app', 'controller' => 'async', 'action' => 'selectize-categories')),
                'multiple'     => true,
                'multiOptions' => $categoriesMultiOptions,
            ),
            array(
                'form_id'      => array('item', 'classified'),
                'id'           => 'searchable',
                'element'      => 'checkbox',
                'label'        => $this->_('Searchable'),
                'description'  => $this->_('Check above to make the element searchable.'),
                'multiOptions' => array(
                    1 => null,
                ),
            ),
        );
    }
}

