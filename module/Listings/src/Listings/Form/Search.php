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
 * listing search form
 */

namespace Listings\Form;

use Ppb\Model\Elements,
    Ppb\Form\AbstractBaseForm;

class Search extends AbstractBaseForm
{

    const SUBMIT_SEARCH = 'submit_search';

    /**
     *
     * submit buttons - overridden by child methods
     *
     * @var array
     */
    protected $_buttons = array(
        self::SUBMIT_SEARCH => 'Search',
    );

    /**
     *
     * don't add submit button automatically
     *
     * @var bool
     */
    protected $_addSubmitButton = false;

    /**
     *
     * class constructor
     *
     * @param string|array                $formId the id of the form, used by the form elements model
     * @param string                      $action the form's action
     * @param \Ppb\Db\Table\Row\User|null $store  in case we are searching in a store
     */
    public function __construct($formId = null, $action = null, $store = null)
    {
        parent::__construct($action);

        $this->setTitle('Listings Search')
            ->setMethod(self::METHOD_GET);

        $includedForms = $this->getIncludedForms();

        $this->setIncludedForms(
            array_merge($includedForms, (array)$formId));

        $model = new Elements\Search($formId);
        $model->setStore($store);

        $this->addElements(
            $model->getElements());

        $this->setModel($model);

        $this->addSubmitElement('Search', self::SUBMIT_SEARCH);

        $this->setPartial('forms/search.phtml');
    }

    /**
     *
     * basic form, use different view partial
     *
     * @return $this
     */
    public function generateBasicForm()
    {
        $this->setPartial('forms/basic-search.phtml');

        return $this;
    }

    /**
     *
     * method to create a form element from an array
     *
     * @param array $elements
     * @param bool  $allElements
     * @param bool  $clearElements
     *
     * @return $this
     */
    public function addElements(array $elements, $allElements = false, $clearElements = true)
    {
        parent::addElements($elements, $allElements, $clearElements);

        if ($this->hasElement('csrf')) {
            $this->removeElement('csrf');
        }

        return $this;
    }
    /**
     *
     * set the data of the submitted form
     * plus add the data in the search model
     *
     * @param array $data form data
     *
     * @return \Listings\Form\Search
     */

    /**
     *
     * @param array $data
     *
     * @return $this
     */
    public function setData(array $data = null)
    {
        parent::setData($data);

        $this->addSubmitElement('Search', self::SUBMIT_SEARCH);

        return $this;
    }

}