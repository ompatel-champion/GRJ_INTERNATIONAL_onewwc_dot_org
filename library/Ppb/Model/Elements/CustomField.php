<?php

/**
 *
 * PHP Pro Bid
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2019 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     8.1 [rev.8.1.01]
 */

namespace Ppb\Model\Elements;

use Cube\Validate,
    Ppb\Service;

class CustomField extends AbstractElements
{

    /**
     * element of type selectize
     */
    const ELEMENT_SELECTIZE = '\Ppb\Form\Element\Selectize';

    /**
     *
     * element types allowed
     *
     * @var array
     */
    protected $_elements = array(
        'text'                  => 'text',
        'select'                => 'select',
        'radio'                 => 'radio',
        'checkbox'              => 'checkbox',
        'password'              => 'password',
        'textarea'              => 'textarea',
        self::ELEMENT_SELECTIZE => 'selectize',
    );

    /**
     *
     * class constructor
     *
     * @param mixed $formId
     */
    public function __construct($formId = null)
    {
        parent::__construct();

        $this->setFormId($formId);
    }

    /**
     *
     * generate custom field creation form elements
     *
     * @return array
     */
    public function getElements()
    {
        $view = $this->getView();

        $translate = $this->getTranslate();

        $categoriesMultiOptions = array();

        $categoriesService = $this->getCategories();

        if ($this->getData('category_ids')) {
            $categoriesSelect = $categoriesService->getTable()->select()
                ->where('id IN (?)', (array)$this->getData('category_ids'));
            $categoriesMultiOptions = $categoriesService->getMultiOptions($categoriesSelect, null, false, true);
        }

        $listingElementsModel = new Listing('bulk');

        $customFieldsService = new Service\CustomFields();

        $listingElements = array_unique(array_filter(array_merge(
            array_column($listingElementsModel->getElements(), 'id'),
            array_keys($customFieldsService->getAliases())
        )));

        $customField = $customFieldsService->findBy('id', $this->getData('id'));

        $alias = ($customField !== null) ? $customField['alias'] : null;

        if (!empty($alias)) {
            if (($key = array_search($alias, $listingElements)) !== false) {
                unset($listingElements[$key]);
            }
        }

        $aliasNotInArray = new Validate\NotInArray($listingElements);
        $aliasNotInArray->setMessage($translate->_("The alias entered is not available."));

        $elements = array(
            array(
                'form_id'      => 'global',
                'id'           => 'element',
                'element'      => 'select',
                'label'        => $this->_('Html Element'),
                'multiOptions' => $this->_elements,
                'description'  => $this->_('Select the type of element.'),
                'attributes'   => array(
                    'class' => 'form-control input-medium field-changeable',
                ),
                'bodyCode'     => "
                    <script type=\"text/javascript\">
                        function updateCustomFieldProperties() {
                            var el = $('select[name=\"element\"]').val();
                            var productAttribute = $('input:checkbox[name=\"product_attribute\"]');

                            if (el === 'select' || el === 'radio' || el === 'checkbox' || el.search(/Selectize/) !== -1) {
                                $('#compositeSelectizeMultiOptions').closest('.form-group').show();
                            }
                            else {
                                $('#compositeSelectizeMultiOptions').closest('.form-group').hide();
                            }           

                            if (el === 'checkbox' || el.search(/Selectize/) !== -1) {
                                productAttribute.closest('.form-group').show();
                            }
                            else {
                                productAttribute.prop('checked', false).closest('.form-group').hide();
                            }

                            if (productAttribute.is(':checked')) {
                                $('input:checkbox[name=\"required\"]').prop('checked', false).closest('.form-group').hide();
                            }
                            else {
                                $('input:checkbox[name=\"required\"]').closest('.form-group').show();
                            }
                        }

                        $(document).ready(function() {             
                            updateCustomFieldProperties();
                        });

                        $(document).on('change', '.field-changeable', function() {
                            updateCustomFieldProperties();
                        });
                    </script>",
            ),
            array(
                'form_id'      => 'global',
                'id'           => 'product_attribute',
                'element'      => 'checkbox',
                'label'        => $this->_('Product Attribute'),
                'description'  => $this->_('Check above for the element to become a product attribute.'),
                'multiOptions' => array(
                    1 => null,
                ),
                'attributes'   => array(
                    'class' => 'field-changeable',
                ),
            ),
            array(
                'form_id'     => 'global',
                'id'          => 'label',
                'element'     => 'text',
                'label'       => $this->_('Label'),
                'description' => $this->_('Enter a label for the element.'),
                'attributes'  => array(
                    'class' => 'form-control input-medium',
                ),
                'required'    => true,
            ),
            array(
                'form_id'     => 'global',
                'id'          => 'alias',
                'element'     => 'text',
                'label'       => $this->_('Alias'),
                'description' => $this->_('(Optional) Enter an alias for the element, or leave empty to have it automatically generated.'),
                'attributes'  => array(
                    'class' => 'form-control input-medium',
                ),
                'validators'  => array(
                    $aliasNotInArray
                ),
            ),
            array(
                'form_id'     => 'global',
                'id'          => 'description',
                'element'     => 'text',
                'label'       => $this->_('Description'),
                'description' => $this->_('(Optional) Enter a description for the element.'),
                'attributes'  => array(
                    'class' => 'form-control input-xlarge',
                ),
            ),
            array(
                'form_id'     => 'global',
                'id'          => 'subtitle',
                'element'     => 'text',
                'label'       => $this->_('Subtitle'),
                'description' => $this->_('(Optional) Enter a subtitle for the element. Use it for separating custom fields into sections.'),
                'attributes'  => array(
                    'class' => 'form-control input-xlarge',
                ),
            ),
            array(
                'form_id'     => 'global',
                'id'          => 'prefix',
                'element'     => 'text',
                'label'       => $this->_('Prefix'),
                'description' => $this->_('(Optional) Enter a prefix for the element.'),
                'attributes'  => array(
                    'class' => 'form-control input-mini',
                ),
            ),
            array(
                'form_id'     => 'global',
                'id'          => 'suffix',
                'element'     => 'text',
                'label'       => $this->_('Suffix'),
                'description' => $this->_('(Optional) Enter a suffix for the element.'),
                'attributes'  => array(
                    'class' => 'form-control input-mini',
                ),
            ),
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
                'dataUrl'      => $view->url(array('module' => 'app', 'controller' => 'async', 'action' => 'selectize-categories')),
                'multiple'     => true,
                'multiOptions' => $categoriesMultiOptions,
            ),
            array(
                'form_id'     => 'global',
                'id'          => 'attributes',
                'element'     => '\\Ppb\\Form\\Element\\Composite',
                'label'       => $this->_('Attributes'),
                'description' => $this->_('(Optional) Add attributes for the element (id, class, etc.).<br>'
                    . 'Recommended attribute: class => form-control'),
                'elements'    => array(
                    array(
                        'id'         => 'key',
                        'element'    => 'text',
                        'attributes' => array(
                            'class'       => 'form-control input-small mr-1',
                            'placeholder' => $translate->_('Key'),
                        ),
                    ),
                    array(
                        'id'         => 'value',
                        'element'    => 'text',
                        'attributes' => array(
                            'class'       => 'form-control input-medium',
                            'placeholder' => $translate->_('Value'),
                        ),
                    ),
                ),
                'arrange'     => true,
            ),
            array(
                'form_id'     => 'global',
                'id'          => 'multiOptions',
                'element'     => '\\Ppb\\Form\\Element\\Composite\\Selectize',
                'label'       => $this->_('Options'),
                'description' => $this->_('Add options for the element.'),
                'elements'    => array(
                    array(
                        'id'         => 'key',
                        'element'    => 'text',
                        'attributes' => array(
                            'class'       => 'form-control input-small mr-1',
                            'placeholder' => $translate->_('Key'),
                        ),
                    ),
                    array(
                        'id'         => 'value',
                        'element'    => 'text',
                        'attributes' => array(
                            'class'       => 'form-control input-default mr-1',
                            'placeholder' => $translate->_('Value'),
                        ),
                    ),
                    array(
                        'id'         => 'categories',
                        'element'    => '\\Ppb\\Form\\Element\\Selectize',
                        'attributes' => array(
                            'class'       => 'form-control input-medium',
                            'placeholder' => $translate->_('Choose Categories...'),
                        ),
                        'dataUrl'    => $view->url(array('module' => 'app', 'controller' => 'async', 'action' => 'selectize-categories')),
                        'multiple'   => true,
                    ),
                ),
                'arrange'     => true,
            ),
            array(
                'form_id'      => 'global',
                'id'           => 'required',
                'element'      => 'checkbox',
                'label'        => $this->_('Required'),
                'description'  => $this->_('Check above to make the element required.'),
                'multiOptions' => array(
                    1 => null,
                ),
            ),
            array(
                'form_id'      => 'item',
                'id'           => 'searchable',
                'element'      => 'checkbox',
                'label'        => $this->_('Searchable'),
                'description'  => $this->_('Check above to make the element searchable.'),
                'multiOptions' => array(
                    1 => null,
                ),
            ),
        );

        return $this->_arrayMergeOrdering($elements, parent::getRelatedElements());
    }

}

