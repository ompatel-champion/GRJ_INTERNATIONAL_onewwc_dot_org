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
 * MOD:- PICKUP LOCATIONS
 *
 * @version 1.6
 */

/**
 * class that will generate extra elements for the admin elements model
 * we can have a multiple number of such classes, they just need to have a different name
 * any elements in this class will override original elements
 */

namespace Ppb\Model\Elements\User;

use Ppb\Model\Elements\AbstractElements;

## -- ONE LINE :: ADD -- [ MOD:- PICKUP LOCATIONS ]
use Ppb\Service\Table\Currencies as CurrenciesService;

class PickupLocations extends AbstractElements
{
    /**
     *
     * related class
     *
     * @var bool
     */
    protected $_relatedClass = true;

    /**
     *
     * get model elements
     *
     * @return array
     */
    public function getElements()
    {
        $translate = $this->getTranslate();
        $settings = $this->getSettings();

        $countries = $this->getLocations()->getMultiOptions();

        ## -- START :: ADD -- [ MOD:- PICKUP LOCATIONS ]
        $currenciesService = new CurrenciesService();
        $currencies = $currenciesService->getMultiOptions('iso_code');

        $currency = ($this->getData('currency') === null) ? $settings['currency'] : $this->getData('currency');
        ## -- END :: ADD -- [ MOD:- PICKUP LOCATIONS ]

        ## -- ONE LINE :: ADD -- [ MOD:- PICKUP LOCATIONS ]
        $country = ($this->getData('country') === null) ? $this->getFirstElement($countries) : $this->getData('country');

        ## -- ONE LINE :: CHANGE -- [ MOD:- PICKUP LOCATIONS ]
        $states = array();
        if ($this->getData('country') !== null) {
            $states = $this->getLocations()->getMultiOptions(
                $this->getData('country'));
        }

        ## -- START :: ADD -- [ MOD:- PICKUP LOCATIONS ]
        $cities = array();
        if ($this->getData('state') !== null) {
            $cities = $this->getLocations()->getMultiOptions(
                $this->getData('state'));
        }
//        $state = ($this->getData('state') === null) ? $this->getFirstElement($states) : $this->getData('state');
//        $cities = $this->getLocations()->getMultiOptions(
//            $state);

        ## -- END :: ADD -- [ MOD:- PICKUP LOCATIONS ]

        return array(
            ## -- START :: CHANGE -- [ MOD:- PICKUP LOCATIONS ]
            array(
                'form_id'     => 'store-location',
                'id'          => 'pickup_store_name',
                'element'     => 'text',
                'label'       => $this->_('Location Name'),
                'required'    => true,
                'description' => $this->_('Enter name of the pickup location.'),
                'attributes'  => array(
                    'class' => 'form-control input-medium',
                ),
            ),
            array(
                'form_id'     => array('address', 'store-location'),
                'id'          => 'address',
                'element'     => 'text',
                'label'       => $this->_('Address'),
                'required'    => true,
                'description' => (in_array('store-location', $this->_formId)) ?
                    $this->_('Enter the address of the pickup location.') :
                    $this->_('Enter your address.'),
                'attributes'  => array(
                    'class' => 'form-control input-medium',
                ),
            ),
            array(
                'form_id'      => array('address', 'store-location'),
                'id'           => 'country',
                'element'      => 'select',
                'label'        => $this->_('Country'),
                'multiOptions' => $countries,
                'required'     => true,
                'description'  => (in_array('store-location', $this->_formId)) ?
                    $this->_('Enter the country of the pickup location.') :
                    $this->_('Enter the country you live in.'),
                'attributes'   => array(
                    'class' => 'form-control input-medium',
                ),
                'bodyCode'     => "
                    <script type=\"text/javascript\">
                        function ChangeState() {
                            var countryId = $('[name=\"country\"]').val();
                            $.post(
                                '" . $this->getView()->url(array('module' => 'app', 'controller' => 'async', 'action' => 'select-location')) . "',
                                {
                                    id: $('[name=\"country\"]').val(),
                                    name: 'state'
                                },
                                function (data) {
                                    var div = $('[name=\"state\"]').closest('div');
                                    $('[name=\"state\"]').remove();
                                    div.prepend(data);

                                    ChangeCity();
                                }
                            );
                        }

                        $(document).on('change', '[name=\"country\"]', function() {
                            ChangeState();
                        });
                    </script>"
            ),
            array(
                'form_id'      => array('address', 'store-location'),
                'id'           => 'state',
                'element'      => (count($states) > 0) ? 'select' : 'text',
                'label'        => $this->_('State/County'),
                'multiOptions' => $states,
                'required'     => true,
                'description'  => (in_array('store-location', $this->_formId)) ?
                    $this->_('Enter the state/county of the pickup location.') :
                    $this->_('Enter the state/county you live in.'),
                'attributes'   => array(
                    'class' => 'form-control input-medium',
                ),
                'bodyCode'     => "
                    <script type=\"text/javascript\">
                        function ChangeCity() {
                            var stateId = $('[name=\"state\"]').val();
                            $.post(
                                '" . $this->getView()->url(array('module' => 'app', 'controller' => 'async', 'action' => 'select-location')) . "',
                                {
                                    id: stateId,
                                    name: 'city'
                                },
                                function (data) {
                                    var div = $('[name=\"city\"]').closest('div');
                                    $('[name=\"city\"]').remove();
                                    div.prepend(data);
                                }
                            );
                        }

                        $(document).on('change', '[name=\"state\"]', function() {
                            ChangeCity();
                        });
                    </script>"
            ),
            array(
                'form_id'      => array('address', 'store-location'),
                'id'           => 'city',
                'after'        => array('id', 'state'),
                'element'      => (count($cities) > 0) ? 'select' : 'text',
                'label'        => $this->_('City'),
                'multiOptions' => $cities,
                'required'     => true,
                'description'  => (in_array('store-location', $this->_formId)) ?
                    $this->_('Enter the city of the pickup location.') :
                    $this->_('Enter the city you live in.'),
                'attributes'   => array(
                    'class' => 'form-control input-medium',
                ),
            ),
            array(
                'form_id'     => array('address', 'store-location'),
                'id'          => 'zip_code',
                'after'       => array('id', 'city'),
                'element'     => 'text',
                'label'       => $this->_('Zip/Post Code'),
                'required'    => true,
                'description' => (in_array('store-location', $this->_formId)) ?
                    $this->_('Enter the zip/post code of the pickup location.') :
                    $this->_('Enter your zip/post code.'),
                'attributes'  => array(
                    'class' => 'form-control input-medium',
                ),
                'filters'     => array(
                    '\\Ppb\\Filter\\NoSpaces',
                ),
            ),
            array(
                'form_id'     => 'address',
                'id'          => 'phone',
                'after'       => array('id', 'zip_code'),
                'element'     => 'text',
                'label'       => $this->_('Phone'),
                'description' => $this->_('Enter your phone number.'),
                'attributes'  => array(
                    'class' => 'form-control input-medium',
                ),
                'required'    => true,
                'validators'  => array(
                    'Phone',
                ),
            ),
            array(
                'form_id'     => 'store-location',
                'id'          => 'store_phone_number',
                'element'     => 'text',
                'label'       => $this->_('Phone'),
                'description' => $this->_('(Optional) Enter the phone number of the pickup location.'),
                'attributes'  => array(
                    'class' => 'form-control input-medium',
                ),
                'validators'  => array(
                    'Phone',
                ),
            ),
            array(
                'form_id'     => 'store-location',
                'id'          => 'store_email_address',
                'element'     => 'text',
                'label'       => $this->_('Email'),
                'description' => $this->_('(Optional) Enter the email address of the pickup location.'),
                'attributes'  => array(
                    'class' => 'form-control input-medium',
                ),
                'validators'  => array(
                    'Email',
                ),
            ),
            array(
                'subtitle'     => $this->_('Collection Service'),
                'form_id'      => 'store-location',
                'id'           => 'currency',
                'element'      => (count($currencies) > 1) ? 'select' : 'hidden',
                'label'        => $this->_('Currency'),
                'multiOptions' => $currencies,
                'value'        => $currency,
                'required'     => true,
                'attributes'   => array(
                    'class' => 'form-control input-small field-changeable',
                ),
            ),
            array(
                'form_id'     => 'store-location',
                'id'          => 'price',
                'element'     => 'text',
                'label'       => $this->_('Price'),
                'description' => $this->_('Enter a price for the collection service.'),
                'prefix'      => '<span class="listing-currency">' . $currency . '</span>',
                'attributes'  => array(
                    'class' => 'form-control input-mini',
                ),
                'validators'  => array(
                    'Numeric',
                    array('GreaterThan',
                        array(0, true)),
                ),
            ),
            array(
                'form_id'     => 'store-location',
                'id'          => 'collection_days',
                'element'     => '\\Ppb\\Form\\Element\\Range',
                'label'       => $this->_('Estimated Collection'),
                'description' => $this->_('(Optional) Enter an estimated duration after which the user can collect the item(s).'),
                'suffix'      => $this->_('days'),
                'attributes'  => array(
                    'class' => 'form-control input-mini',
                ),
            ),

            array(
                'form_id'     => 'store-location',
                'id'          => 'opening_hours',
                'element'     => '\\Ppb\\Form\\Element\\MultiKeyValue',
                'label'       => $this->_('Opening Hours'),
                'description' => $this->_('(Optional) Enter the opening hours of the pickup location, using the following format: '
                    . 'Key: day(s)- eg. Monday, Mon. - Fri. ; Value: hours - eg. 10-18, closed'),
                'attributes'  => array(
                    'class' => 'form-control input-medium',
                ),
            ),
        );
    }
}

