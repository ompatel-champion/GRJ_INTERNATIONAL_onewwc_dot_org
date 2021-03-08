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
 * flat rates location groups composite element
 *
 * creates an element that will contain an unlimited number of rows that include the following columns:
 * - a "first" field - for the postage cost of the first item in an invoice
 * - an "additional" field - for each additional item in an invoice
 * - a name text field
 * - a value select field of type Selectize
 */

namespace Ppb\Form\Element;

use Ppb\Form\Element\Composite\Selectize as CompositeSelectizeElement,
    Ppb\Service;

class FlatRatesLocationGroups extends CompositeSelectizeElement
{

    const FIELD_NAME = 'name';
    const FIELD_LOCATIONS = 'locations';
    const FIELD_FIRST = 'first';
    const FIELD_ADDL = 'addl';

    /**
     *
     * type of element - override the variable from the parent class
     *
     * @var string
     */
    protected $_element = 'flatRatesLocationGroups';

    /**
     *
     * class constructor
     *
     * @param string $name
     */
    public function __construct($name)
    {
        parent::__construct($name);

        $translate = $this->getTranslate();

        $locationsService = new Service\Table\Relational\Locations();

        $this->setElements(array(
            array(
                'id'         => self::FIELD_NAME,
                'element'    => 'text',
                'attributes' => array(
                    'class'       => 'location-groups form-control input-default mr-1',
                    'placeholder' => $translate->_('Group Name'),
                ),
            ),
            array(
                'id'         => self::FIELD_FIRST,
                'element'    => '\\Ppb\\Form\\Element\\LocalizedNumeric',
                'attributes' => array(
                    'class'       => 'form-control input-mini input-flat-rates mr-1',
                    'placeholder' => $translate->_('First'),
                ),
            ),
            array(
                'id'         => self::FIELD_ADDL,
                'element'    => '\\Ppb\\Form\\Element\\LocalizedNumeric',
                'attributes' => array(
                    'class'       => 'form-control input-mini input-flat-rates mr-1',
                    'placeholder' => $translate->_('Addl.'),
                ),
            ),
            array(
                'id'           => self::FIELD_LOCATIONS,
                'element'      => '\\Ppb\\Form\\Element\\Selectize',
                'attributes'   => array(
                    'class'       => 'form-control input-default',
                    'placeholder' => $translate->_('Choose Location(s)'),
                ),
                'multiOptions' => $locationsService->getMultiOptions(),
                'multiple'     => true,
            )
        ));
    }

}

