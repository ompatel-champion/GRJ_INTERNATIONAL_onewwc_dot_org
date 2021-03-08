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
 * postmen shipper accounts table row object model
 */

namespace Ppb\Db\Table\Row;

class PostmenShipperAccount extends AbstractRow
{

    /**
     *
     * shipper account id
     *
     * @var string
     */
    protected $_id;

    /**
     *
     * account description
     *
     * @var string
     */
    protected $_description;

    /**
     *
     * account slug
     *
     * @var string
     */
    protected $_slug;

    /**
     *
     * address
     *
     * @var array
     */
    protected $_address;

    /**
     *
     * status (enabled, disabled, deleted)
     *
     * @var string
     */
    protected $_status;

    /**
     *
     * class constructor
     *
     * @param array $data
     */
    public function __construct($data = array())
    {
        parent::__construct($data);

        $details = (array)\Ppb\Utility::unserialize(
            $this->getData('details'));

        if (array_key_exists('id', $details)) {
            $this->setId($details['id']);
        }
        if (array_key_exists('slug', $details)) {
            $this->setSlug($details['slug']);
        }
        if (array_key_exists('status', $details)) {
            $this->setStatus($details['status']);
        }
        if (array_key_exists('description', $details)) {
            $this->setDescription($details['description']);
        }
        if (array_key_exists('address', $details)) {
            $this->setAddress($details['address']);
        }

    }

    /**
     *
     * get shipper account id
     *
     * @return string
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     *
     * set shipper account id
     *
     * @param string $id
     *
     * @return $this
     */
    public function setId($id)
    {
        $this->_id = $id;

        return $this;
    }

    /**
     *
     * get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->_description;
    }

    /**
     *
     * set description
     *
     * @param string $description
     *
     * @return $this
     */
    public function setDescription($description)
    {
        $this->_description = $description;

        return $this;
    }

    /**
     *
     * get slug
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->_slug;
    }

    /**
     *
     * set slug
     *
     * @param string $slug
     *
     * @return $this
     */
    public function setSlug($slug)
    {
        $this->_slug = $slug;

        return $this;
    }

    /**
     *
     * get status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->_status;
    }

    /**
     *
     * set status
     *
     * @param string $status
     *
     * @return $this
     */
    public function setStatus($status)
    {
        $this->_status = $status;

        return $this;
    }

    /**
     *
     * get address
     *
     * @return array
     */
    public function getAddress()
    {
        return $this->_address;
    }

    /**
     *
     * set address
     *
     * @param array $address
     *
     * @return $this
     */
    public function setAddress($address)
    {
        $this->_address = $address;

        return $this;
    }

    /**
     *
     * check if shipper account is enabled
     *
     * @return bool
     */
    public function isEnabled()
    {
        /** @var \Ppb\Db\Table\Row\User $user */
        $user = $this->findParentRow('\Ppb\Db\Table\Users');

        if ($user->isPostmenShippingApi()) {
            return ($this->getStatus() == 'enabled') ? true : false;
        }

        return false;
    }

}

