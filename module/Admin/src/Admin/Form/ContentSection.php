<?php

/**
 *
 * PHP Pro Bid
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2018 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     8.0 [rev.8.0.02]
 */

/**
 * section form
 */

namespace Admin\Form;

use Ppb\Form\AbstractBaseForm,
    Ppb\Db\Table\Row\ContentSection as ContentSectionModel,
    Ppb\Service\Table\Relational\ContentSections as ContentSectionsService,
    Cube\Validate,
    Ppb\Db\Table;

class ContentSection extends AbstractBaseForm
{

    const BTN_SUBMIT = 'submit';

    /**
     *
     * submit buttons values
     *
     * @var array
     */
    protected $_buttons = array(
        self::BTN_SUBMIT => 'Save',
    );

    /**
     *
     * class constructor
     *
     * @param string $action   the form's action
     * @param int    $id       used for when editing
     * @param int    $parentId used when creating a subsection
     */
    public function __construct($action = null, $id = null, $parentId = null)
    {
        parent::__construct($action);


        $this->setMethod(self::METHOD_POST);

        ## SECTION ID
        $sectionId = $this->createElement('hidden', 'id');
        $this->addElement($sectionId);
        ## /SECTION ID


        ## NAME
        $name = $this->createElement('text', 'name');
        $name->setLabel('Name')
            ->setDescription('Enter the name of the section.')
            ->setAttributes(array(
                'class' => 'form-control input-large',
            ))
            ->setRequired()
            ->setValidators(array(
                'NoHtml',
                array('StringLength', array(null, 255)),
            ));
        $this->addElement($name);
        ## /NAME


        ## HANDLE
        $handleDuplicateRecord = new Validate\Db\NoRecordExists(array(
            'table' => new Table\ContentSections(),
            'field' => 'handle',
        ));
        $handleDuplicateRecord->setMessage("%s: '%value%' already exists.");

        if ($id !== null) {
            $handleDuplicateRecord->setExclude(array('field' => 'id', 'value' => $id));
        }

        $handle = $this->createElement('text', 'handle');
        $handle->setLabel('Handle')
            ->setDescription('Enter the handle of the section.')
            ->setAttributes(array(
                'class' => 'form-control input-large',
            ))
            ->setRequired()
            ->setValidators(array(
                'Alphanumeric',
                $handleDuplicateRecord,
            ));
        $this->addElement($handle);
        ## /HANDLE


        ## PARENT ID
        $contentSectionsService = new ContentSectionsService();
        $contentSectionsSelect = $contentSectionsService->getTable()->select()
            ->where('type = ?', ContentSectionModel::TYPE_TREE);

        if ($id != null) {
            $contentSectionsSelect->where('id != ?', $id);
        }

        $parent = $this->createElement('select', 'parent_id');
        $parent->setLabel('Parent')
            ->setDescription('(Optional) Select the parent of this section.')
            ->setValue($parentId)
            ->setMultiOptions(
                array('' => 'None') + $contentSectionsService->getMultiOptions($contentSectionsSelect, null, false, true)
            )
            ->setBodyCode('
                <script type="text/javascript">
                    function checkListingFormFields()
                    {
                        var parentId = $(\'[name="parent_id"]\');
                        var type = $(\'[name="type"]\');
                        
                        if (parentId.val() !== "") {
                            type.closest(".form-group").hide();    
                        }
                        else {
                            type.closest(".form-group").show();
                        }
                    }
                    
                    $(document).ready(function() {
                        checkListingFormFields();
                    });
                    
                    $(document).on("change", ".field-changeable", function() {
                        checkListingFormFields();
                    });
                </script>')
            ->setAttributes(array(
                'class' => 'form-control input-large field-changeable',
            ));
        $this->addElement($parent);
        ## /PARENT ID


        ## TYPE
        $type = $this->createElement('select', 'type');
        $type->setLabel('Type')
            ->setDescription('Available types: <br>'
                . '<em>Single</em><br>'
                . 'Accepts a single entry (about us, contact us, fees, terms etc)<br>'
                . '<em>Multiple</em><br>'
                . 'Accepts multiple similar entries (news, blog etc.)<br>'
                . '<em>Tree</em><br>'
                . 'Accepts sub-sections in a tree structure.<br>'
            )
            ->setMultiOptions(
                ContentSectionModel::$sectionTypes)
            ->setAttributes(array(
                'class' => 'form-control input-large',
            ));
        $this->addElement($type);
        ## /TYPE


        ## LOCALE
//        $locale = $this->createElement('select', 'locale');
//        $locale->setLabel('Locale')
//            ->setDescription('Select the locale of the section.')
//            ->setMultiOptions(\Ppb\Utility::getLanguages())
//            ->setAttributes(array(
//                'class' => 'form-control input-large',
//            ));
//        $this->addElement($locale);
        ## /LOCALE


        ## SLUG
        $slug = $this->createElement('text', 'slug');
        $slug->setLabel('Slug')
            ->setDescription('The slug of the section. <br>'
                . '<em>Single</em><br>'
                . 'The uri of the page will be the slug of the section.<br>'
                . '<em>Multiple</em><br>'
                . 'The uri of an entry will be created by merging the slug of the section with the slug of of the entry.<br>'
                . 'Eg.: blog/my-first-blog-post<br>'
                . '<em>Tree</em><br>'
                . 'The uri of a leaf will be created by merging the slugs of each branch with the slug of the leaf.<br>'
                . 'Eg.: section/first-subsection/an-article'
            )
            ->setAttributes(array(
                'class' => 'form-control input-large',
            ))
            ->setRequired()
            ->setValidators(array(
                'Alphanumeric',
            ));
        $this->addElement($slug);
        ## /SLUG


        ## ENTRY VIEW FILE
        $entryViewFile = $this->createElement('text', 'entry_view_file');
        $entryViewFile->setLabel('Entry View File')
            ->setDescription('(Optional) If using a custom view file, enter the name of the file.<br>'
                .'<strong>Important</strong>: The file needs to be uploaded in the <em>app/cms</em> views folder.')
            ->setAttributes(array(
                'class' => 'form-control input-large',
            ))
            ->setValidators(array(
                'NoHtml',
            ));
        $this->addElement($entryViewFile);
        ## /ENTRY VIEW FILE


        ## ACTIVE
        $active = $this->createElement('checkbox', 'active');
        $active->setLabel('Active')
            ->setDescription('Check the above checkbox to activate the section.')
            ->setMultiOptions(array(
                1 => null,
            ));
        $this->addElement($active);
        ## /ACTIVE


        ## ORDER ID
        $orderId = $this->createElement('text', 'order_id');
        $orderId->setLabel('Order ID')
            ->setDescription('(Optional) Enter an order id for the section. By default, sections are ordered alphabetically.'
            )
            ->setAttributes(array(
                'class' => 'form-control input-small',
            ))
            ->setValidators(array(
                'Digits',
            ));
        $this->addElement($orderId);
        ## /ORDER ID


        ## SUBMIT BUTTON
        $this->addSubmitElement($this->_buttons[self::BTN_SUBMIT], self::BTN_SUBMIT);
        ## /SUBMIT BUTTON


        $this->setPartial('forms/generic-horizontal.phtml');
    }

}