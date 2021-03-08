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
 * advert form
 */

namespace Admin\Form;

use Ppb\Form\AbstractBaseForm,
    Ppb\Service\Advertising as AdvertisingService,
    Ppb\Service\Table\Relational\Categories as CategoriesService;

class Advert extends AbstractBaseForm
{

    const BTN_SUBMIT = 'btn_submit';

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
     * @param string $advertType (image or code - default: image)
     * @param string $action     the form's action
     */
    public function __construct($advertType = null, $action = null)
    {
        parent::__construct($action);

        $this->setMethod(self::METHOD_POST);

        $translate = $this->getTranslate();

        $id = $this->createElement('hidden', 'id');
        $id->setBodyCode("<script type=\"text/javascript\">
                $(document).on('change', '#advert-type', function() {
                    $(this).closest('form').submit();
                });
            </script>");
        $this->addElement($id);

        $title = $this->createElement('text', 'name');
        $title->setLabel('Advert Name')
            ->setDescription('Enter advert name (for internal use only).')
            ->setRequired()
            ->setAttributes(array(
                'class' => 'form-control input-medium',
            ))
            ->setValidators(array(
                'NoHtml',
                array('StringLength', array(null, 255)),
            ));
        $this->addElement($title);

        $advertisingService = new AdvertisingService();

        $section = $this->createElement('select', 'section');
        $section->setLabel('Section')
            ->setDescription('Select advert section.')
            ->setMultiOptions(
                $advertisingService->getSections()
            )
            ->setAttributes(array(
                'class' => 'form-control input-medium',
            ));
        $this->addElement($section);

        /** @var \Ppb\Form\Element\Selectize $categoriesIds */
        $categoriesIds = $this->createElement('\\Ppb\\Form\\Element\\Selectize', 'category_ids');
        $categoriesIds->setLabel('Select Categories')
            ->setDescription('(Optional) Select the categories for which this advert will be displayed.')
            ->setAttributes(array(
                'class'       => 'form-control input-xlarge',
                'placeholder' => 'Choose Categories...',
            ))
            ->setDataUrl(
                $this->getView()->url(array('module' => 'app', 'controller' => 'async', 'action' => 'selectize-categories')))
            ->setMultiple(true);
        $this->addElement($categoriesIds);

        $languagesMultiOptions = array_merge(array('' => $translate->_('All Languages')), \Ppb\Utility::getLanguages());
        $language = $this->createElement('select', 'language');
        $language->setLabel('Language')
            ->setDescription('Select for which language this advert will appear.')
            ->setMultiOptions($languagesMultiOptions)
            ->setAttributes(array(
                'class' => 'form-control input-medium',
            ));
        $this->addElement($language);

        $section = $this->createElement('select', 'type');
        $section->setLabel('Type')
            ->setDescription('Select advert type.')
            ->setMultiOptions(array(
                'image' => $translate->_('Image'),
                'code'  => $translate->_('Code'),
                'html'  => $translate->_('HTML'),
            ))
            ->setAttributes(array(
                'id'    => 'advert-type',
                'class' => 'form-control input-small',
            ));
        $this->addElement($section);


        if ($advertType == 'code') {
            $content = $this->createElement('textarea', 'content');
            $content->setLabel('Content')
                ->setDescription('Enter advert code.')
                ->setAttributes(
                    array(
                        'rows'  => 16,
                        'class' => 'form-control textarea-code code-field')
                )
                ->setRequired();
            $this->addElement($content);
        }
        else if ($advertType == 'html') {
            $content = $this->createElement('\\Ppb\\Form\\Element\\Wysiwyg', 'content');
            $content->setLabel('Content')
                ->setDescription('Enter advert html.')
                ->setAttributes(
                    array(
                        'rows'  => 16,
                        'class' => 'form-control')
                )
                ->setRequired();
            $this->addElement($content);
        }
        else {
            $advertImage = $this->createElement('\Ppb\Form\Element\MultiUpload', 'content');
            $advertImage->setLabel('Image')
                ->setDescription('Upload advert image.')
                ->setRequired()
                ->setCustomData(array(
                    'buttonText'      => $translate->_('Select Image'),
                    'acceptFileTypes' => '/(\.|\/)(gif|jpe?g|png)$/i',
                    'formData'        => array(
                        'fileSizeLimit' => 10000000, // approx 10MB
                        'uploadLimit'   => 1,
                    ),
                ));
            $this->addElement($advertImage);

            $url = $this->createElement('text', 'url');
            $url->setLabel('Advert Url')
                ->setDescription('The advert redirects to the above url.')
                ->setRequired()
                ->setAttributes(array(
                    'class' => 'form-control input-medium',
                ))
                ->setValidators(array(
                    'Url',
                ));
            $this->addElement($url);

            $title = $this->createElement('text', 'image_title');
            $title->setLabel('Image Title')
                ->setDescription('(Optional) Enter an advert image title.')
                ->setAttributes(array(
                    'class' => 'form-control input-medium',
                ))
                ->setValidators(array(
                    'NoHtml',
                    array('StringLength', array(null, 255)),
                ));
            $this->addElement($title);

            $newTab = $this->createElement('checkbox', 'new_tab');
            $newTab->setLabel('Open In New Tab')
                ->setDescription('Check above for the advert, when clicked, to open the url in a new tab. Leave unchecked to open in the same tab.')
                ->setMultiOptions(array(
                    1 => null,
                ));
            $this->addElement($newTab);

            $directLink = $this->createElement('checkbox', 'direct_link');
            $directLink->setLabel('Direct Link')
                ->setDescription('Check above to use the direct link for the advert. Advert clicks will not be recorded in this case.')
                ->setMultiOptions(array(
                    1 => null,
                ));
            $this->addElement($directLink);
        }

        $this->addSubmitElement($this->_buttons[self::BTN_SUBMIT], self::BTN_SUBMIT);

        $this->setPartial('forms/generic-horizontal.phtml');
    }

    /**
     *
     * set data
     *
     * @param array|null $data
     *
     * @return $this
     */
    public function setData(array $data = null)
    {
        $data = parent::setData($data);

        $categoryIds = $this->getData('category_ids');

        if (!empty($categoryIds)) {
            $categoriesService = new CategoriesService();
            $categoriesSelect = $categoriesService->getTable()->select()
                ->where('id IN (?)', (array)$categoryIds);
            $categoriesMultiOptions = $categoriesService->getMultiOptions($categoriesSelect, null, false, true);

            $this->getElement('category_ids')
                ->setMultiOptions($categoriesMultiOptions);
        }

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
}