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

namespace Ppb\Model\Elements\AdminSettings;

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
            /**
             * --------------
             * SITE FEES MANAGEMENT
             * --------------
             */
            array(
                'form_id'      => 'fees_category',
                'id'           => 'category_id',
                'element'      => 'select',
                'label'        => $this->_('Select Category'),
                ## -- START :: CHANGE -- [ MOD:- ADVANCED CLASSIFIEDS @version 1.0 ]
                'multiOptions' => $this->getCategories()->getMultiOptions($selectFeesCategories, null,
                    ## -- END :: CHANGE -- [ MOD:- ADVANCED CLASSIFIEDS @version 1.0 ]
                    $translate->_('Default')),
                'attributes'   => array(
                    'class' => 'form-control input-medium',
                    'id'    => 'category-selector',
                ),
                'bodyCode'     => "
                    <script type=\"text/javascript\">
                        $(document).ready(function() { 
                            $('#category-selector').change(function() { 
                                var categoryId = $(this).val();
                                var action = $(this).closest('form').attr('action');
                                var url = action.replace(/(\/category_id)(\/[0-9]+)/g, '');
                                
                                $(location).attr('href', url + '/category_id/' + categoryId);
                            });
                        });
                    </script>",
            ),
            ## -- START :: ADD -- [ MOD:- ADVANCED CLASSIFIEDS @version 1.0 ]
        );
    }
}

