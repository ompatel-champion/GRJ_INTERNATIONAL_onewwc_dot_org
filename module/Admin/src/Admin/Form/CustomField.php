<?php

/**
 *
 * PHP Pro Bid
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2017 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.9 [rev.7.9.01]
 */
/**
 * custom field creation form
 */

namespace Admin\Form;

use Ppb\Model\Elements,
    Ppb\Form\AbstractBaseForm;

class CustomField extends AbstractBaseForm
{

    public function __construct($formId = null, $action = null)
    {
        parent::__construct($action);

        $this->setTitle('Create Custom Field')
            ->setMethod(self::METHOD_POST);

        $includedForms = $this->getIncludedForms();

        $this->setIncludedForms(
            array_merge($includedForms, (array)$formId));

        $this->setModel(
            new Elements\CustomField($formId));

        $this->addElements(
            $this->getModel()->getElements());

        if (count($this->getElements()) > 0) {
            $this->addSubmitElement();
            $this->setPartial('forms/generic-horizontal.phtml');
        }
    }

    /**
     *
     * will generate the edit custom field form
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
                sprintf($translate->_('Edit Custom Field - ID: #%s'), $id));
        }

        return $this;
    }

}

