<?php

/**
 *
 * PHP Pro Bid
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2019 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     8.1 [rev.8.1.02]
 */

/**
 * voucher creation form
 */

namespace Members\Form;

use Ppb\Form\AbstractBaseForm;

class Voucher extends AbstractBaseForm
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
     * @param int    $userId if user id != null, we have a listing voucher
     */
    public function __construct($action = null, $userId = null)
    {
        parent::__construct($action);

        $settings = $this->getSettings();

        $this->setMethod(self::METHOD_POST);

        $id = $this->createElement('hidden', 'id');
        $this->addElement($id);

        $name = $this->createElement('text', 'name');
        $name->setLabel('Name')
            ->setDescription('Enter voucher name.')
            ->setAttributes(array(
                'class' => 'form-control input-large',
            ))
            ->setRequired()
            ->setValidators(array(
                'NoHtml',
                array('StringLength', array(null, 255)),
            ));
        $this->addElement($name);

        $code = $this->createElement('text', 'code');
        $code->setLabel('Voucher Code')
            ->setDescription('Enter voucher code.')
            ->setAttributes(array(
                'class' => 'form-control input-large',
            ))
            ->setRequired()
            ->setValidators(array(
                'NoHtml',
                array('StringLength', array(null, 255)),
            ));
        $this->addElement($code);

        $reductionAmount = $this->createElement('\\Ppb\\Form\\Element\\LocalizedNumeric', 'reduction_amount');
        $reductionAmount->setLabel('Reduction')
            ->setRequired()
            ->setValidators(array(
                'Numeric'
            ))
            ->setAttributes(array(
                'class' => 'form-control input-small'
            ))
            ->setFilters(array(
                '\\Ppb\\Filter\\LocalizedNumeric',
            ));
        $this->addElement($reductionAmount);

        $reductionType = $this->createElement('select', 'reduction_type');
        $reductionType->setDescription('Enter voucher reduction.')
            ->setMultiOptions(array(
                'percent' => '%',
                'flat'    => $settings['currency'],
            ))
            ->setAttributes(array(
                'class' => 'form-control input-small'
            ));
        $this->addElement($reductionType);


        $expirationDate = $this->createElement('\\Ppb\\Form\\Element\\DateTime', 'expiration_date');
        $expirationDate->setLabel('Expiration Date')
            ->setDescription('(Optional) Set an expiration date.')
            ->setAttributes(array(
                'class' => 'form-control input-medium'
            ));
        $this->addElement($expirationDate);

        $nbUses = $this->createElement('text', 'uses_remaining');
        $nbUses->setLabel('Number of Uses')
            ->setDescription('(Optional) Enter the maximum number of uses.')
            ->setValidators(array(
                'Digits'
            ))
            ->setAttributes(array(
                'class' => 'form-control input-small'
            ));
        $this->addElement($nbUses);

        if ($userId) {
            $assignedListings = $this->createElement('text', 'assigned_listings');
            $assignedListings->setLabel('Assign to Listings')
                ->setDescription('(Optional) Enter the ids of the listings, separated by comma you wish to assign the voucher to or leave empty if you wish '
                    . 'for it to apply to all your listings.')
                ->setAttributes(array(
                    'class' => 'form-control input-xlarge'
                ));
            $this->addElement($assignedListings);
        }

        $this->addSubmitElement($this->_buttons[self::BTN_SUBMIT], self::BTN_SUBMIT);

        $this->setPartial('forms/generic-horizontal.phtml');
    }

    /**
     *
     * will generate the edit form
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
                sprintf($translate->_('Edit Voucher - ID: #%s'), $id));
        }

        return $this;
    }
}