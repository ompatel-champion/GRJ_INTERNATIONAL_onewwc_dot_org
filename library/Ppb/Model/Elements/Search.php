<?php

/**
 *
 * PHP Pro Bid
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2020 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     8.2 [rev.8.2.01]
 */

namespace Ppb\Model\Elements;

use Ppb\Db\Table\Row\User as UserModel,
    Ppb\Form\Element\CategoriesBrowse;

class Search extends AbstractElements
{

    /**
     *
     * form id
     *
     * @var array
     */
    protected $_formId = array();

    /**
     *
     * user object (used to generate store categories)
     *
     * @var \Ppb\Db\Table\Row\User
     */
    protected $_store;

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
     * get store user object
     *
     * @return \Ppb\Db\Table\Row\User
     */
    public function getStore()
    {
        return $this->_store;
    }

    /**
     *
     * set store user object
     *
     * @param \Ppb\Db\Table\Row\User $store
     *
     * @return $this
     */
    public function setStore(UserModel $store = null)
    {
        $this->_store = $store;

        return $this;
    }

    /**
     *
     * get form elements
     *
     * @return array
     */
    public function getElements()
    {
        $translate = $this->getTranslate();
        $settings = $this->getSettings();

        $categoriesSelect = $this->getCategories()->getTable()
            ->select()
            ->where('enable_auctions = ?', 1)
            ->order(array('order_id ASC', 'name ASC'));


        if ($this->_store instanceof UserModel) {
            $categoriesSelect->where("user_id is null OR user_id = '{$this->_store['id']}'");
        }
        else {
            $categoriesSelect->where('user_id is null');
        }

        $categoriesFilter = array(0);
        $childrenOfParentId = array();

        if ($parentId = $this->getData('parent_id')) {
            if (in_array('advanced', $this->_formId)) {
                $categoriesSelect->where('parent_id is null');
            }
            else {
                $categoriesSelect->where('parent_id = ?', $parentId);
            }

            $categoriesFilter = array_merge($categoriesFilter, array_keys(
                $this->getCategories()->getBreadcrumbs($parentId)));

            $childrenOfParentId = array_keys($this->getCategories()->getChildren($parentId, true));
        }
        else {
            $categoriesSelect->where('parent_id is null');

            if ($this->_store instanceof UserModel) {
                $storeCategories = $this->_store->getStoreSettings('store_categories');
                if ($storeCategories != null) {
                    $categoriesSelect->where('id IN (?)', $storeCategories);
                }
            }
        }

        $categoriesMultiOptions = $this->getCategories()->getMultiOptions($categoriesSelect, null, $translate->_('All Categories'));

        $customFields = $this->getCustomFields()->getFields(
            array(
                'type'         => 'item',
                'active'       => 1,
                'searchable'   => 1,
                'category_ids' => $categoriesFilter,
            ))->toArray();

        $showOnly = array(
            'accept_returns' => $translate->_('Returns Accepted'),
            'sold'           => $translate->_('Sold Items'),
        );

        if ($settings['enable_make_offer']) {
            $showOnly['make_offer'] = $translate->_('Offers Accepted');
        }

        $listingTypesMultiOptions = $this->getListingTypes();

        $currency = $this->getView()->amount(false)->getCurrency();
        $currencyCode = (!empty($currency['symbol'])) ? $currency['symbol'] : $currency['iso_code'];

        $countriesMultiOptions = $this->getLocations()->getMultiOptions(null, null, $translate->_('All Countries'));

        $elements = array(
            array(
                'form_id'    => 'global',
                'id'         => 'keywords',
                'element'    => 'text',
                'label'      => $this->_('Keywords'),
                'attributes' => array(
                    'class' => 'form-control'
                        . ((in_array('basic', $this->_formId) ||
                            in_array('stores', $this->_formId)) ? '' : ' input-xlarge'),
                ),
            ),
            array(
                'form_id'      => 'advanced',
                'id'           => 'parent_id',
                'element'      => 'select',
                'label'        => $this->_('Select Category'),
                'multiOptions' => $categoriesMultiOptions,
                'attributes'   => array(
                    'class' => 'form-control input-large',
                ),
                'bodyCode'     => "
                    <script type=\"text/javascript\">
                        $(document).on('change', '[name=\"parent_id\"]', function() {
                            $('body').addClass('loading');
                            $(this).closest('form').prop('action', '').submit();
                        });
                    </script>",
            ),
            array(
                'form_id'      => (array_key_exists('disable_store_categories', $settings) && $settings['disable_store_categories']) ? 'basic' : array('basic', 'stores'),
                'id'           => 'parent_id',
                'element'      => '\\Ppb\\Form\\Element\\CategoriesBrowse',
                'label'        => $this->_('Categories'),
                'multiOptions' => $categoriesMultiOptions,
                'attributes'   => array(
                    CategoriesBrowse::ACTIVE_CATEGORY   => ($parentId) ? $this->_categories->getBreadcrumbs($parentId) : null,
                    CategoriesBrowse::STORES_CATEGORIES => ((in_array('stores', $this->_formId)) ? true : false),
                ),
                'customData'   => array(
                    'rowset' => $this->getCategories()->fetchAll($categoriesSelect),
                ),
            ),
            array(
                'form_id'    => 'basic',
                'id'         => 'price',
                'element'    => '\\Ppb\\Form\\Element\\Range',
                'label'      => $this->_('Price'),
                'prefix'     => $currencyCode,
                'attributes' => array(
                    'class' => 'form-control input-tiny',
                )
            ),
            array(
                'form_id'      => array('basic', 'advanced'),
                'id'           => 'show_only',
                'element'      => 'checkbox',
                'label'        => $this->_('Show Only'),
                'multiOptions' => $showOnly
            ),
            array(
                'form_id'      => array('basic', 'advanced'),
                'id'           => 'listing_type',
                'element'      => 'checkbox',
                'label'        => $this->_('Format'),
                'multiOptions' => $listingTypesMultiOptions,
            ),
            array(
                'form_id'      => 'global',
                'id'           => 'country',
                'element'      => 'select',
                'label'        => $this->_('Location'),
                'multiOptions' => $countriesMultiOptions,
                'attributes'   => array(
                    'class' => 'form-control'
                        . ((in_array('basic', $this->_formId) ||
                            in_array('stores', $this->_formId)) ? '' : ' input-large'),
                ),
            ),
            array(
                'form_id' => 'global',
                'id'      => 'sort',
                'element' => 'hidden',
            ),
            array(
                'form_id' => 'global',
                'id'      => 'filter',
                'element' => 'hidden',
            ),
        );

        $customFieldsOptionsCategories = array_merge($categoriesFilter, $childrenOfParentId);

        foreach ($customFields as $key => $customField) {
            $customFields[$key]['form_id'] = array('basic', 'advanced');
            $customFields[$key]['id'] = (!empty($customField['alias'])) ?
                $customField['alias'] : 'custom_field_' . $customField['id'];

            // elements of type select and radio will be converted to checkboxes
            if (in_array($customField['element'], array('select', 'radio', 'checkbox', CustomField::ELEMENT_SELECTIZE))) {
                $customFields[$key]['element'] = 'checkbox';
                $customFields[$key]['selectedOptionsFirst'] = true;
            }

            if (in_array($customField['element'], array('text', 'textarea'))) {
                $attributes = unserialize($customField['attributes']);
                array_push($attributes['key'], 'class');
                if ($customField['element'] == 'text' && in_array('advanced', $this->_formId)) {
                    array_push($attributes['value'], 'form-control input-default');
                }
                else {
                    array_push($attributes['value'], 'form-control');
                }
                $customFields[$key]['attributes'] = serialize($attributes);
            }

            if (!empty($customField['multiOptions'])) {
                $multiOptions = \Ppb\Utility::unserialize($customField['multiOptions']);

                // display only the multi options that correspond to the categories selected
                if (!empty($multiOptions['categories'])) {
                    foreach ($multiOptions['categories'] as $k => $v) {
                        $categoriesArray = array_filter((array)$v);
                        if (count($categoriesArray) > 0) {
                            $intersect = array_intersect($categoriesArray, $customFieldsOptionsCategories);
                            if (count($intersect) == 0) {
                                unset($multiOptions['key'][$k]);
                                unset($multiOptions['value'][$k]);
                                unset($multiOptions['categories'][$k]);
                            }
                        }
                    }

                    unset($multiOptions['categories']);
                }

                $customFields[$key]['multiOptions'] = serialize($multiOptions);
            }
        }

        array_splice($elements, 3, 0, $customFields);

        return $this->_arrayMergeOrdering($elements, parent::getRelatedElements());
    }

}

