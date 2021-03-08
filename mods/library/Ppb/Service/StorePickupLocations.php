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
 * pickup locations table service class
 */
/**
 * MOD:- PICKUP LOCATIONS
 */

namespace Ppb\Service;

use Ppb\Db\Table\StorePickupLocations as StorePickupLocationsTable,
    Cube\Controller\Front,
    Ppb\Model\Elements,
    Ppb\Db\Table\Row\User as UserModel;

class StorePickupLocations extends AbstractService
{

    /**
     *
     * columns to be saved in the serializable address field
     *
     * @var array
     */
    protected $_storeLocationFields = array();

    /**
     *
     * class constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->setTable(
            new StorePickupLocationsTable());
    }

    /**
     *
     * set store location fields (by default from user elements model)
     *
     * @param array $storeLocationFields
     *
     * @return array
     */
    public function setStoreLocationFields($storeLocationFields = null)
    {
        if ($storeLocationFields === null) {
            $userModel = new Elements\User();
            $elements = $userModel->getElements();

            foreach ($elements as $element) {
                if (array_intersect((array)$element['form_id'], array('store-location')) && $element['element'] != 'hidden') {
                    $storeLocationFields[] = $element['id'];
                }
            }
        }
        $this->_storeLocationFields = (array)$storeLocationFields;

        return $this;
    }

    /**
     *
     * get store location fields
     *
     * @return array
     */
    public function getStoreLocationFields()
    {
        if (!$this->_storeLocationFields) {
            $this->setStoreLocationFields();
        }

        return $this->_storeLocationFields;
    }


    /**
     *
     * save a store location
     *
     * @param array  $data   address data (serializable)
     * @param string $prefix the prefix of the input fields (optional)
     *
     * @return int  the id of the saved column
     * @throws \InvalidArgumentException
     */
    public function save($data, $prefix = null)
    {
        $row = array();

        if ($prefix !== null) {
            foreach ($data as $key => $value) {
                if (strstr($key, $prefix) !== false) {
                    unset($data[$key]);
                    $key = str_replace($prefix, '', $key);
                    $data[$key] = $value;
                }
            }
        }

        if (isset($data['id'])) {
            $select = $this->_table->select()
                ->where("id = ?", $data['id']);

            $row = $this->_table->fetchRow($select);
        }

        $storeLocation = array();

        $storeLocationFields = $this->getStoreLocationFields();

        foreach ($storeLocationFields as $field) {
            $storeLocation[$field] = (isset($data[$field])) ? $data[$field] : null;
        }


        if (count($row) > 0) {
            $this->_table->update(array(
                'address'  => serialize($storeLocation),
                'currency' => $data['currency'],
                'price'    => $data['price'],
            ), "id='{$row['id']}'");

            $id = $row['id'];
        }
        else {
            $this->_table->insert(array(
                'address'  => serialize($storeLocation),
                'currency' => $data['currency'],
                'price'    => $data['price'],
            ));

            $id = $this->_table->lastInsertId();
        }

        // save geolocation for the address
        $geoLocation = $this->_saveGeoLocation($storeLocation, $id);

        return $id;
    }

    /**
     *
     * get the available store locations of a user in a key => value format
     * to be used for the pickup locations address selector
     *
     * @param UserModel $user
     * @param string    $separator
     * @param bool      $enhanced if true, it will return the data as an array, usable by the SelectAddress form element
     *
     * @return array
     */
    public function getMultiOptions(UserModel $user, $separator = ', ', $enhanced = false)
    {
        $data = array();

        $view = Front::getInstance()->getBootstrap()->getResource('view');
        /** @var \Members\View\Helper\UserDetails $userDetails */
        $userDetails = $view->getHelper('userDetails');

        $object = clone $user;

        $select = $this->getTable()->select()
            ->order('id DESC');
        $storeLocations = $user->findDependentRowset('\Ppb\Db\Table\StorePickupLocations', null, $select);

        foreach ($storeLocations as $storeLocation) {
            $title = $userDetails->userDetails($object)->setAddress($storeLocation)->displayAddress($separator);
            if ($enhanced) {
                $data[(string)$storeLocation['id']] = array(
                    'title'      => $title,
                    'locationId' => $storeLocation['country'],
                    'postCode'   => $storeLocation['zip_code'],
                );
            }
            else {
                $data[(string)$storeLocation['id']] = $title;
            }
        }

        return $data;
    }

    protected function _saveGeoLocation($data, $id)
    {
        $geoLocationService = new GeoLocation();
        $geoLocationService->setAddress($data);

        $coordinates = $geoLocationService->getCoordinates();

        $storePickupLocation = $this->findBy('id', $id);
        $storePickupLocation->save(array(
            'latitude'  => $coordinates['latitude'],
            'longitude' => $coordinates['longitude'],
        ));

        return $this;

    }

}

