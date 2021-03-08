<?php

/**
 *
 * PHP Pro Bid
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.0
 */
/**
 * pickup locations table row object model
 */
/**
 * MOD:- PICKUP LOCATIONS
 *
 * @version 1.6
 */

namespace Ppb\Db\Table\Row;

use Ppb\Service;

class StorePickupLocation extends AbstractRow
{

    const GOOGLE_MAPS_LINK = false;

    /**
     *
     * serializable fields
     *
     * @var array
     */
    protected $_serializable = array('address');

    /**
     *
     * check if an store location can be edited
     * or return an error message string otherwise
     *
     * @return bool|string
     */
    public function canEdit()
    {
        return true;
    }

    /**
     *
     * check if a store location can be deleted (if not part of an invoice)
     * or return an error message string otherwise
     *
     * @return bool|string
     */
    public function canDelete()
    {
        $translate = $this->getTranslate();

        if ($this->_usedInSales()) {
            return $translate->_('This store location cannot be removed because it is used in an invoice.');
        }

        return true;
    }


    /**
     *
     * display the pickup location
     *
     * @param bool $simple
     *
     * @return string
     */
    public function display($simple = false)
    {
        $translate = $this->getTranslate();
        $storeLocation = $this;

        if (!$storeLocation instanceof StorePickupLocation) {
            return '<em>' . $translate->_('N/A') . '</em>';
        }

        $locationsService = new Service\Table\Relational\Locations();
        $storeLocationsService = new Service\StorePickupLocations();

        $locationFields = $storeLocationsService->getStoreLocationFields();

        $storeLocationData = $storeLocation->getData();

        foreach ($storeLocationData as $key => $value) {
            if (!in_array($key, $locationFields)) {
                unset($storeLocationData[$key]);
            }
        }

        $storeLocation = (array)$storeLocationData;

        if (is_numeric($storeLocation['country'])) {
            $row = $locationsService->findBy('id', (int)$storeLocation['country']);
            $storeLocation['country'] = ($row != null) ? $translate->_($row->getData('name')) : $translate->_('n/a');
        }

        if (is_numeric($storeLocation['state'])) {
            $row = $locationsService->findBy('id', (int)$storeLocation['state']);
            $storeLocation['state'] = ($row != null) ? $translate->_($row->getData('name')) : $translate->_('n/a');
        }

        if (!empty($storeLocation['opening_hours'])) {
            $keys = (isset($storeLocation['opening_hours']['key'])) ? array_values($storeLocation['opening_hours']['key']) : array();
            $values = (isset($storeLocation['opening_hours']['value'])) ? array_values($storeLocation['opening_hours']['value']) : array();

            $storeLocation['opening_hours'] = array_filter(
                array_combine($keys, $values));

            $openingHours = '<em>Opening Hours</em><br>';
            foreach ($storeLocation['opening_hours'] as $key => $value) {
                $openingHours .= '<small>' . $key . '</small> ' . $value . '<br>';
            }

            $storeLocation['opening_hours'] = $openingHours;
        }


        if (!empty($storeLocation['collection_days'])) {
            $collectionDays = null;
            $collectionDaysSimple = null;
            if (!empty($storeLocation['collection_days'][0])) {
                $collectionDays = $storeLocation['collection_days'][0];
                $collectionDaysSimple = date('d M y', strtotime('+ ' . $storeLocation['collection_days'][0] . ' day'));
            }
            if (!empty($storeLocation['collection_days'][1])) {
                if (!empty($collectionDays)) {
                    $collectionDays .= ' - ';
                    $collectionDaysSimple .= ' to ';
                }
                $collectionDays .= $storeLocation['collection_days'][1];
                $collectionDaysSimple .= date('d M y', strtotime('+ ' . $storeLocation['collection_days'][1] . ' day'));
            }

            $collectionDays .= ' days';

            $storeLocation['collection_days'] = '<em>Estimated Collection</em>: ' . $collectionDays;
            $storeLocation['collection_days_simple'] = '<em>Estimated Collection</em>: ' . $collectionDaysSimple;
        }
        else {
            $storeLocation['collection_days'] = null;
            $storeLocation['collection_days_simple'] = null;
        }

        if ($simple === true) {
            $storeAddress = $storeLocation['address']
                . ', '
                . $storeLocation['city']
                . ', '
                . $storeLocation['state']
                . ', '
                . $storeLocation['zip_code']
                . ', '
                . $storeLocation['country'];


            if (self::GOOGLE_MAPS_LINK) {
                $storeAddress = '<a href="http://maps.google.com/?q=' . $storeAddress . '" target="_blank" title="View on Google Maps">'
                    . $storeAddress . '</a>';
            }

            return $storeLocation['pickup_store_name']
                . ' [ '
                . $storeAddress
                . ' ]'
                . (($storeLocation['collection_days']) ? '<div>' . $storeLocation['collection_days'] . '</div>' : '');
        }

        return '<address>'
            . '<strong>' . $storeLocation['pickup_store_name'] . '</strong>'
            . '<br>'
            . $storeLocation['address']
            . '<br>'
            . $storeLocation['city']
            . '<br>'
            . $storeLocation['zip_code']
            . '<br>'
            . $storeLocation['state']
            . ', '
            . $storeLocation['country']
            . ((!empty($storeLocation['store_phone_number'])) ?
                '<br><abbr title="' . $translate->_('Phone Number') . '"><i class="fa fa-phone"></i></abbr> ' . $storeLocation['store_phone_number'] : '')
            . ((!empty($storeLocation['store_email_address'])) ?
                '<br><abbr title="' . $translate->_('Email Address') . '"><i class="fa fa-envelope"></i></abbr> ' . $storeLocation['store_email_address'] : '')
            . ((!empty($storeLocation['collection_days'])) ?
                '<br><br>' . $storeLocation['collection_days'] : '')
            . ((!empty($storeLocation['opening_hours'])) ?
                '<br><br>' . $storeLocation['opening_hours'] : '')
            . '</address>';
    }

    /**
     *
     * check if the store location has been used in a sale transaction
     *
     * @return bool
     */
    protected function _usedInSales()
    {
        $rowset = $this->findDependentRowset('\Ppb\Db\Table\Sales');
        if (count($rowset) > 0) {
            return true;
        }

        return false;
    }
}

