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
 * tax types details view helper class
 */

namespace Ppb\View\Helper;

use Ppb\Db\Table\Row\TaxType as TaxTypeModel,
    Ppb\Service,
    Cube\Locale\Format as LocaleFormat;

class TaxType extends AbstractHelper
{

    /**
     *
     * tax type model
     *
     * @var \Ppb\Db\Table\Row\TaxType
     */
    protected $_taxType;

    /**
     *
     * locations table service
     *
     * @var \Ppb\Service\Table\Relational\Locations
     */
    protected $_locations;

    /**
     *
     * main method, only returns object instance
     *
     * @param int|string|\Ppb\Db\Table\Row\TaxType $taxType
     *
     * @return $this
     */
    public function taxType($taxType = null)
    {
        if ($taxType !== null) {
            $this->setTaxType($taxType);
        }

        return $this;
    }

    /**
     *
     * get tax type model
     *
     * @return \Ppb\Db\Table\Row\TaxType
     * @throws \InvalidArgumentException
     */
    public function getTaxType()
    {
        if (!$this->_taxType instanceof TaxTypeModel) {
            throw new \InvalidArgumentException("The tax type model has not been instantiated");
        }

        return $this->_taxType;
    }

    /**
     *
     * set tax type model
     *
     * @param \Ppb\Db\Table\Row\TaxType $taxType
     *
     * @return $this
     */
    public function setTaxType(TaxTypeModel $taxType)
    {
        $this->_taxType = $taxType;

        return $this;
    }


    /**
     *
     * get locations table service
     *
     * @return \Ppb\Service\Table\Relational\Locations
     */
    public function getLocations()
    {
        if (!$this->_locations instanceof Service\Table\Relational\Locations) {
            $this->setLocations(
                new Service\Table\Relational\Locations());
        }

        return $this->_locations;
    }

    /**
     *
     * set locations table service
     *
     * @param \Ppb\Service\Table\Relational\Locations $locations
     *
     * @return $this
     */
    public function setLocations(Service\Table\Relational\Locations $locations)
    {
        $this->_locations = $locations;

        return $this;
    }

    /**
     *
     * display tax name field
     *
     * @return string
     */
    public function name()
    {
        return $this->getTranslate()->_($this->getTaxType()->getData('name'));
    }

    /**
     *
     * display tax description field
     *
     * @return string
     */
    public function description()
    {
        return $this->getTranslate()->_($this->getTaxType()->getData('description'));
    }

    /**
     *
     * display locations where the tax type applies
     *
     * @return string
     */
    public function locations()
    {
        $output = array();

        $taxType = $this->getTaxType();

        $translate = $this->getTranslate();

        $locationsIds = array_filter((array)\Ppb\Utility::unserialize($taxType->getData('locations_ids')));

        if (count($locationsIds) > 0) {
            $locations = $this->getLocations()->fetchAll($this->getLocations()->getTable()->select()->where('id IN (?)', $locationsIds));

            foreach ($locations as $location) {
                $output[] = $translate->_($location['name']);
            }
        }

        return implode(', ', $output);
    }


    /**
     *
     * display tax rate
     *
     * @return string
     */
    public function rate()
    {
        return round($this->getTaxType()->getData('amount')) . '%';
    }

    /**
     *
     * display details on the tax that will apply
     *
     * @return string
     */
    public function display()
    {
        $taxType = $this->getTaxType();

        $translate = $this->getTranslate();

        $taxAmount = $taxType['amount'];

        $taxAmountDisplay = ($taxAmount == intval($taxAmount)) ?
            round($taxAmount) : LocaleFormat::getInstance()->numericToLocalized($taxType['amount']);

        return sprintf($translate->_('+%s %s'),
            $taxAmountDisplay . '%', $taxType['description']);
    }
}

