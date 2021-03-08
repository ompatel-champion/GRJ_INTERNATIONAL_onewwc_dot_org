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
 * menu form
 */

namespace Admin\Form;

use Ppb\Form\AbstractBaseForm,
    Cube\Validate,
    Ppb\Db\Table,
    Ppb\Service\Table\Relational\ContentSections as ContentSectionsService,
    Ppb\Form\Element\Selectize;

class ContentMenu extends AbstractBaseForm
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
     * @param string $action the form's action
     * @param int    $id     used for when editing
     */
    public function __construct($action = null, $id = null)
    {
        parent::__construct($action);

        $translate = $this->getTranslate();

        $this->setMethod(self::METHOD_POST);

        ## MENU ID
        $sectionId = $this->createElement('hidden', 'id');
        $this->addElement($sectionId);
        ## /MENU ID


        ## NAME
        $name = $this->createElement('text', 'name');
        $name->setLabel('Name')
            ->setDescription('Enter the name of the menu.')
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
            'table' => new Table\ContentMenus(),
            'field' => 'handle',
        ));
        $handleDuplicateRecord->setMessage("%s: '%value%' already exists.");

        if ($id !== null) {
            $handleDuplicateRecord->setExclude(array('field' => 'id', 'value' => $id));
        }

        $handle = $this->createElement('text', 'handle');
        $handle->setLabel('Handle')
            ->setDescription('Enter the handle of the menu.')
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


        ## SECTIONS SELECTOR
        $sectionsService = new ContentSectionsService();

        $select = $sectionsService->getTable()
            ->select()
            ->order(array('parent_id ASC', '-order_id DESC', 'name ASC'));

        $sectionsMultiOptions = array('' => '') + $sectionsService->getMultiOptions($select);

        /** @var \Ppb\Form\Element\Composite\Selectize $content */
        $content = $this->createElement('\\Ppb\\Form\\Element\\Composite\\Selectize', 'content');
        $content->setLabel('Sections')
            ->setDescription('Select which sections will be included in the menu.')
            ->setArrange(true)
            ->setElements(array(
                array(
                    'id'           => 'sections',
                    'element'      => '\\Ppb\\Form\\Element\\Selectize',
                    'attributes'   => array(
                        'class'       => 'form-control input-medium',
                        'placeholder' => $translate->_('Select Section...'),
                    ),
                    'multiOptions' => $sectionsMultiOptions,
                    'dataUrl'      => Selectize::NO_REMOTE,
                    'multiple'     => false,
                ),
            ));
        $this->addElement($content);
        ## /SECTIONS SELECTOR


        ## SUBMIT BUTTON
        $this->addSubmitElement($this->_buttons[self::BTN_SUBMIT], self::BTN_SUBMIT);
        ## /SUBMIT BUTTON


        $this->setPartial('forms/generic-horizontal.phtml');
    }

}