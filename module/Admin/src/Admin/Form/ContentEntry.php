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

/**
 * entry form
 */

namespace Admin\Form;

use Ppb\Form\AbstractBaseForm,
    Cube\Db\Expr,
    Ppb\Db\Table\Row\ContentEntry as ContentEntryModel,
    Ppb\Service\Table\Relational\ContentSections as ContentSectionsService,
    Ppb\Service\ContentEntries as ContentEntriesService,
    Ppb\Service\Users as UsersService;

class ContentEntry extends AbstractBaseForm
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
     * @param string $action    the form's action
     * @param int    $id        used for when editing
     * @param int    $sectionId used when creating an entry with the section selected
     */
    public function __construct($action = null, $id = null, $sectionId = null)
    {
        parent::__construct($action);

        $this->setMethod(self::METHOD_POST);

        $translate = $this->getTranslate();

        ## ENTRY ID
        $id = $this->createElement('hidden', 'id');
        $this->addElement($id);
        ## /ENTRY ID


        ## SECTION ID
        $sectionsService = new ContentSectionsService();

        $select = $sectionsService->getTable()
            ->select()
            ->order(array('parent_id ASC', '-order_id DESC', 'name ASC'));

        $sections = $sectionsService->getTable()->fetchAll($select);

        $sectionMultiOptions = array('' => array(
            'None', array(
                'data-type' => 'multiple'
            )
        ));

        /** @var \Ppb\Db\Table\Row\ContentSection $section */
        foreach ($sections as $section) {
            $optionAttributes = array();

            $optionAttributes['data-type'] = $section['type'];
            $optionAttributes['data-has-entries'] = $section->countDependentRowset('\Ppb\Db\Table\ContentEntries') ? 'true' : 'false';

//            if ($section['id'] != $sectionId) {
//                if (!$section->canAddEntry()) {
//                    $optionAttributes['disabled'] = 'disabled';
//                }
//            }

            $sectionMultiOptions[$section['id']] = array(
                $sectionsService->getFullName($section['id']),
                $optionAttributes
            );
        }

        /** @var \Cube\Form\Element\Select $section */
        $section = $this->createElement('select', 'section_id');
        $section->setLabel('Section')
            ->setDescription('Select the section this entry belongs to.')
            ->setValue($sectionId)
            ->setMultiOptions(
                $sectionMultiOptions)
            ->setAttributes(array(
                'class' => 'form-control input-medium field-changeable',
            ))
            ->setBodyCode('
                <script type="text/javascript">
                    function checkListingFormFields()
                    {
                        var sectionId = $(\'[name="section_id"]\').find(":selected");
                        var type = $(\'[name="type"]\');
                        
                        if (sectionId.attr("data-type") === "multiple") {
                            type.closest(".form-group").show();                            
                            if (type.val() === "' . ContentEntryModel::TYPE_POST . '") {
                                $(".field-multiple-post").closest(".form-group").show();                              
                            }
                            else {
                                $(".field-multiple-post").closest(".form-group").hide();
                            }
                        }
                        else {
                            type.closest(".form-group").hide();
                            $(".field-multiple-post").closest(".form-group").hide();    
                        }
                    }
                    
                    $(document).ready(function() {
                        checkListingFormFields();
                    });
                    
                    $(document).on("change", ".field-changeable", function() {
                        checkListingFormFields();
                    });
                </script>');
        $this->addElement($section);
        ## /SECTION ID


        ## TYPE
        $type = $this->createElement('select', 'type');
        $type->setLabel('Type')
            ->setDescription('Available types: <br>'
                . '<em>Standard</em><br>'
                . 'Default entry, requires a title and content.<br>'
                . '<em>Multiple / Post</em><br>'
                . 'Used by sections of type multiple (eg. a blog post, a news article etc).'
            )
            ->setMultiOptions(
                ContentEntryModel::$entryTypes)
            ->setAttributes(array(
                'class' => 'form-control input-medium field-changeable',
            ));
        $this->addElement($type);
        ## /TYPE


        ## TITLE
        $title = $this->createElement('text', 'title');
        $title->setLabel('Title')
            ->setDescription('Enter a title for this entry.')
            ->setAttributes(array(
                'class' => 'form-control input-xlarge',
            ))
            ->setRequired()
            ->setValidators(array(
                'NoHtml',
                array('StringLength', array(null, 255)),
            ));
        $this->addElement($title);
        ## /TITLE


        ## LANGUAGE
        $localeMultiOptions = array('' => $translate->_('All Languages')) + \Ppb\Utility::getLanguages();

        $locale = $this->createElement('select', 'locale');
        $locale->setLabel('Language')
            ->setDescription('Select a locale this entry.')
            ->setAttributes(array(
                'class' => 'form-control input-medium',
            ))
            ->setMultiOptions($localeMultiOptions);
        $this->addElement($locale);
        ## /LANGUAGE


        ## SHORT DESCRIPTION (MULTIPLE > POST)
        $shortDescription = $this->createElement('\\Ppb\\Form\\Element\\Wysiwyg', 'short_description');
        $shortDescription->setLabel('Short Description')
            ->setDescription('(Optional) Enter a short description for the entry.')
            ->setAttributes(array(
                'class' => 'form-control',
                'rows'  => 5,
            ))
            ->setCustomData(array(
                'formData' => array(
                    'btns' => "[ ['viewHTML'], ['strong', 'em', 'del'], ['link'] ]",
                )
            ))
            ->setRequired();
        $this->addElement($shortDescription);
        ## /SHORT DESCRIPTION (MULTIPLE > POST)


        ## IMAGE (MULTIPLE > POST)
        $entryImage = $this->createElement('\\Ppb\\Form\\Element\\MultiUpload', 'image_path');
        $entryImage->setLabel('Image')
            ->setDescription('(Optional) Upload a custom image for this entry.')
            ->setAttributes(array(
                'class' => 'field-multiple-post',
            ))
            ->setCustomData(array(
                'buttonText'      => $translate->_('Select Image'),
                'acceptFileTypes' => '/(\.|\/)(gif|jpe?g|png)$/i',
                'formData'        => array(
                    'fileSizeLimit' => 10000000,
                    'uploadLimit'   => 1,
                )
            ));
        $this->addElement($entryImage);
        ## /IMAGE (MULTIPLE > POST)


        ## CONTENT
        $content = $this->createElement('\\Ppb\\Form\\Element\\Wysiwyg', 'content');
        $content->setLabel('Content')
            ->setDescription('Enter the content for this entry.<br>'
                . 'Allowed custom code: <br>'
                . '<%=action:{action}.{controller}.{module}%> <br>'
                . '<%=url:{param-key},{param-value};{param-key},{param-value};...%> <br>'
                . '<%=href:{uri}%>')
            ->setAttributes(
                array(
                    'rows'  => 12,
                    'class' => 'form-control'
                )
            )
            ->setRequired();
        $this->addElement($content);
        ## /CONTENT


        ## SLUG (MULTIPLE > POST)
        $slug = $this->createElement('text', 'slug');
        $slug->setLabel('Slug')
            ->setDescription('The slug of the entry.')
            ->setAttributes(array(
                'class' => 'form-control input-large field-multiple-post',
            ))
            ->setValidators(array(
                'Alphanumeric',
            ));
        $this->addElement($slug);
        ## /SLUG (MULTIPLE > POST)


        ## META TITLE
        $metaTitle = $this->createElement('text', 'meta_title');
        $metaTitle->setLabel('Meta Title')
            ->setDescription('(Optional) Add a custom meta title for this entry. If left empty, the meta title will be generated automatically.')
            ->setAttributes(array(
                'class' => 'form-control input-xlarge',
            ))
            ->setValidators(array(
                'NoHtml'
            ));
        $this->addElement($metaTitle);
        ## /META TITLE


        ## META DESCRIPTION
        $metaDescription = $this->createElement('textarea', 'meta_description');
        $metaDescription->setLabel('Meta Description')
            ->setDescription('(Optional) Add meta description for this entry. '
                . 'Your description should be no longer than 155 characters (including spaces)')
            ->setAttributes(
                array(
                    'rows'  => 4,
                    'class' => 'form-control'
                )
            );
        $this->addElement($metaDescription);
        ## /META DESCRIPTION


        ## AUTHOR
        $usersService = new UsersService();

        $select = $usersService->getTable()->select()
            ->where('role IN (?)', array_keys(UsersService::getAdminRoles()))
            ->order(array('username ASC'));

        $users = $usersService->getTable()->fetchAll($select);

        $authors = array('' => 'N/A');
        foreach ($users as $user) {
            $authors[(string)$user['id']] = $user['username'];
        }

        $author = $this->createElement('select', 'user_id');
        $author->setLabel('Author')
            ->setDescription('Select the author of the entry.')
            ->setAttributes(array(
                'class' => 'form-control input-medium',
            ))
            ->setMultiOptions($authors);
        $this->addElement($author);
        ## /AUTHOR


        ## POST DATE
        $postDate = $this->createElement('\\Ppb\\Form\\Element\\DateTime', 'post_date');
        $postDate->setLabel('Post Date')
            ->setDescription('(Optional) Set a post date for the entry.')
            ->setAttributes(array(
                'class' => 'form-control input-medium'
            ))
            ->setCustomData(array(
                'formData' => array(
                    'showClear' => 'true',
                ),
            ));
        $this->addElement($postDate);
        ## /POST DATE


        ## EXPIRY DATE
        $expiryDate = $this->createElement('\\Ppb\\Form\\Element\\DateTime', 'expiry_date');
        $expiryDate->setLabel('Expiry Date')
            ->setDescription('(Optional) Set an expiration date for the entry.')
            ->setAttributes(array(
                'class' => 'form-control input-medium'
            ))
            ->setCustomData(array(
                'formData' => array(
                    'showClear' => 'true',
                ),
            ));
        $this->addElement($expiryDate);
        ## /EXPIRY DATE


        ## DRAFT
        $draft = $this->createElement('checkbox', 'draft');
        $draft->setLabel('Draft')
            ->setDescription('Check the above checkbox if the entry is a draft.')
            ->setMultiOptions(array(
                1 => null,
            ));
        $this->addElement($draft);
        ## /DRAFT


        $this->addSubmitElement($this->_buttons[self::BTN_SUBMIT], self::BTN_SUBMIT);

        $this->setPartial('forms/generic-horizontal.phtml');
    }

    /**
     *
     * override method, make multiple > post specific fields optional
     *
     * @param array $data form data
     *
     * @return $this
     */
    public function setData(array $data = null)
    {
        if ($data['type'] != ContentEntryModel::TYPE_POST) {
            $this->getElement('short_description')
                ->setRequired(false);

            $this->getElement('slug')
                ->setRequired(false);
        }

        parent::setData($data);

        return $this;
    }

    /**
     *
     * will generate the edit content page form
     *
     * @param int $id
     *
     * @return $this
     */
    public function generateEditForm($id = null)
    {
        parent::generateEditForm($id);

        $id = ($id !== null) ? $id : $this->_editId;

        if ($id !== null) {
            $translate = $this->getTranslate();

            $this->setTitle(
                sprintf($translate->_('Edit Content Page - ID: #%s'), $id));
        }

        return $this;
    }

    /**
     *
     * form is valid
     * @8.2: for standard entries, check by locale as well
     *
     * @return bool
     */
    public function isValid()
    {
        $valid = parent::isValid();

        $id = $this->getElement('id')->getValue();
        $sectionId = $this->getElement('section_id')->getValue();
        $type = $this->getElement('type')->getValue();
        $locale = $this->getElement('locale')->getValue();

        // we can only have a single standard entry
        if ($type == ContentEntryModel::TYPE_STANDARD && $sectionId) {
            $contentEntriesService = new ContentEntriesService();

            $select = $contentEntriesService->getTable()
                ->select(array('nb_rows' => new Expr('count(*)')))
                ->where('section_id = ?', $sectionId)
                ->where('locale = ?', $locale)
                ->where('type = ?', ContentEntryModel::TYPE_STANDARD);

            if ($id) {
                $select->where('id != ?', $id);
            }

            $stmt = $select->query();

            $nbEntries = (integer)$stmt->fetchColumn('nb_rows');

            if ($nbEntries > 0) {
                $this->setMessage('An entry already exists for the selected section.');
                $valid = false;
            }
        }

        return $valid;
    }
}