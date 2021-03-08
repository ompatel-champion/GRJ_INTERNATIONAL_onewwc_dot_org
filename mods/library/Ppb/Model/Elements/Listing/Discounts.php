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
 * MOD:- DISCOUNT RULES
 *
 * @version 3.2
 */

/**
 * class that will generate extra elements for the admin elements model
 * we can have a multiple number of such classes, they just need to have a different name
 * any elements in this class will override original elements
 */

namespace Ppb\Model\Elements\Listing;

use Ppb\Model\Elements\AbstractElements;

class Discounts extends AbstractElements
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
        $settings = $this->getSettings();

        return array(
            array(
                'form_id'      => array('product', 'prefilled', 'product_edit', 'bulk'),
                'subtitle'     => $this->_('Discount Settings'),
                'before'       => array('id', 'hpfeat'),
                'subform'      => 'settings',
                'id'           => 'enable_discount_rule',
                'element'      => ($this->getData('listing_type') == 'product') ? 'checkbox' : 'hidden',
                'label'        => $this->_('Enable Discount Rule'),
                'description'  => $this->_('Set up a discount rule for your listing.'),
                'value'        => 0,
                'multiOptions' => array(
                    1 => null,
                ),
                'attributes'   => array(
                    'class' => 'field-changeable',
                ),
                'bulk'         => array(
                    'notes' => $translate->_('Allowed types: 0 => No, 1 => Yes'),
                    'type'  => $translate->_('integer'),
                ),
                'bodyCode'     => "
                    <script type=\"text/javascript\">
                        function checkDiscountRulesFields()
                        {
                            if ($('input:checkbox[name=\"enable_discount_rule\"]').is(':checked')) {
                                $('.field-discount-rule').closest('.form-group').show();
                            }
                            else {
                                $('.field-discount-rule').closest('.form-group').hide();
                            } 
                        }   

                        $(document).ready(function() {
                            checkDiscountRulesFields();
                        });
                        
                        $(document).on('change', '.field-changeable', function() {
                            checkDiscountRulesFields();
                        });
                    </script>"
            ),
            array(
                'form_id'    => array('product', 'prefilled', 'product_edit', 'bulk'),
                'after'      => array('id', 'enable_discount_rule'),
                'subform'    => 'settings',
                'id'         => 'discount_reduction_amount',
                'element'    => '\\Ppb\\Form\\Element\\LocalizedNumeric',
                'label'      => $this->_('Reduction'),
                'required'   => ($this->getData('enable_discount_rule')) ? true : false,
                'filters'    => array(
                    '\\Ppb\\Filter\\LocalizedNumeric',
                ),
                'validators' => array(
                    'Numeric',
                ),
                'attributes' => array(
                    'class' => 'form-control input-small field-discount-rule',
                ),
                'bulk'       => array(
                    'sample' => '10',
                ),

            ),
            array(
                'form_id'      => array('product', 'prefilled', 'product_edit', 'bulk'),
                'after'        => array('id', 'discount_reduction_amount'),
                'subform'      => 'settings',
                'id'           => 'discount_reduction_type',
                'element'      => 'select',
                'description'  => $this->_('Enter the reduction this discount rule will apply.'),
                'multiOptions' => array(
                    'percent' => '%',
                    'flat'    => $settings['currency'],
                ),
                'attributes'   => array(
                    'class' => 'form-control input-small field-discount-rule',
                ),
            ),

            array(
                'form_id'     => array('product', 'prefilled', 'product_edit', 'bulk'),
                'after'       => array('id', 'discount_reduction_type'),
                'subform'     => 'settings',
                'id'          => 'discount_start_date',
                'element'     => '\\Ppb\\Form\\Element\\DateTime',
                'label'       => $this->_('Start Date'),
                'description' => $this->_('(Optional) Enter a start date for the discount rule, or leave the field empty if wishing for the discount rule to start right away.'),
                'validators'  => array(
                    array('GreaterThan', array(date('Y-m-d H:i:s', time()), false)),
                ),
                'attributes'  => array(
                    'class' => 'form-control input-medium field-discount-rule',
                ),
                'customData'  => array(
                    'formData' => array(
                        'minDate'   => 'new Date()',
                        'stepping'  => '5',
                        'showClear' => 'true',
                    ),
                ),
                'bulk'        => array(
                    'type'  => $translate->_('datetime'),
                    'notes' => $translate->_('Accepted format: yyyy-mm-dd hh:mm:ss'),
                ),
            ),
            array(
                'form_id'     => array('product', 'prefilled', 'product_edit', 'bulk'),
                'after'       => array('id', 'discount_start_date'),
                'subform'     => 'settings',
                'id'          => 'discount_expiration_date',
                'element'     => '\\Ppb\\Form\\Element\\DateTime',
                'label'       => $this->_('Expiration Date'),
                'description' => $this->_('(Optional) Enter an expiration date for the discount rule.'),
                'required'    => false,
                'validators'  => array(
                    array('GreaterThan', array($this->getData('discount_start_date'), false)),
                ),
                'attributes'  => array(
                    'class' => 'form-control input-medium field-discount-rule',
                ),
                'customData'  => array(
                    'formData' => array(
                        'minDate'   => 'new Date()',
                        'stepping'  => '5',
                        'showClear' => 'true',
                    ),
                ),
                'bulk'        => array(
                    'type'  => $translate->_('datetime'),
                    'notes' => $translate->_('Accepted format: yyyy-mm-dd hh:mm:ss'),
                ),
            ),
            array(
                'form_id'      => array('product', 'prefilled', 'product_edit', 'bulk'),
                'after'        => array('id', 'discount_expiration_date'),
                'subform'      => 'settings',
                'id'           => 'discount_stop_further_rules',
                'element'      => 'checkbox',
                'label'        => $this->_('Stop Global Rules'),
                'description'  => $this->_('This rule will take precedence over global rules. Check the checkbox above if you wish to stop global rules from applying.'),
                'multiOptions' => array(
                    1 => null,
                ),
                'attributes'   => array(
                    'class' => 'field-discount-rule',
                )
            ),
        );
    }
}

