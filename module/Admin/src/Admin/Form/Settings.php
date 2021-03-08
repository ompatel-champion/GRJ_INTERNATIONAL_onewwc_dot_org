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

namespace Admin\Form;

use Ppb\Model\Elements,
    Ppb\Form\AbstractBaseForm;

class Settings extends AbstractBaseForm
{

    public function __construct($formId, $action = null)
    {
        parent::__construct($action);

        $includedForms = $this->getIncludedForms();

        $this->setIncludedForms(
            array_merge($includedForms, (array)$formId));

        $this->setMethod(self::METHOD_POST);

        $this->setModel(
            new Elements\AdminSettings($formId));

        $this->addElements(
            $this->getModel()->getElements());

        if (count($this->getElements()) > 0) {
            $this->addSubmitElement();
            $this->setPartial('forms/generic-horizontal.phtml');
        }
    }

}

