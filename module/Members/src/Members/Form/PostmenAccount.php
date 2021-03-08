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

/**
 * postmen account setup form
 */

namespace Members\Form;

use Ppb\Form,
    Ppb\Service\PostmenShippingAPI;

class PostmenAccount extends Form\AbstractBaseForm
{

    const BTN_SUBMIT = 'postmen_account';

    /**
     *
     * submit buttons values
     *
     * @var array
     */
    protected $_buttons = array(
        self::BTN_SUBMIT => 'Save Settings',
    );

    /**
     *
     * class constructor
     *
     * @param string $action the form's action
     */
    public function __construct($action = null)
    {
        parent::__construct($action);

        $this->setMethod(self::METHOD_POST);


        $apiKey = $this->createElement('text', PostmenShippingAPI::API_KEY);
        $apiKey->setLabel('API Key')
            ->setDescription('Enter your Postmen account API key')
            ->setRequired()
            ->setAttributes(array(
                'class' => 'form-control input-large',
            ));
        $this->addElement($apiKey);

        $apiMode = $this->createElement('radio', PostmenShippingAPI::API_MODE);
        $apiMode->setLabel('API Mode')
            ->setDescription('Select whether to run the API in testing or production mode.')
            ->setValue(PostmenShippingAPI::API_MODE_TESTING)
            ->setMultiOptions(array(
                PostmenShippingAPI::API_MODE_TESTING    => 'Testing',
                PostmenShippingAPI::API_MODE_PRODUCTION => 'Production',
            ));
        $this->addElement($apiMode);


        $this->addSubmitElement($this->_buttons[self::BTN_SUBMIT], self::BTN_SUBMIT);

        $this->setPartial('forms/generic-horizontal.phtml');
    }

}