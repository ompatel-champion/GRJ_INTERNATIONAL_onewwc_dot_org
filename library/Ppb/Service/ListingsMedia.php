<?php

/**
 *
 * PHP Pro Bid
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2020 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     8.2 [rev.8.2.01]
 */

/**
 * listings media table service class
 */

namespace Ppb\Service;

use Ppb\Db\Table\ListingsMedia as ListingsMediaTable,
    Cube\Db\Expr;

class ListingsMedia extends AbstractService
{

    /**
     * media types
     */
    const TYPE_IMAGE = 'image';
    const TYPE_VIDEO = 'video';
    const TYPE_DOWNLOAD = 'download';
    const TYPE_CSV = 'csv';

    /**
     *
     * available media types array
     *
     * @var array
     */
    protected static $_types = array(
        self::TYPE_IMAGE    => 'Image',
        self::TYPE_VIDEO    => 'Video',
        self::TYPE_DOWNLOAD => 'Download',
        self::TYPE_CSV      => 'CSV',
    );

    /**
     *
     * class constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->setTable(
            new ListingsMediaTable());
    }

    /**
     *
     * get listings media types
     *
     * @return array
     */
    public static function getTypes()
    {
        return self::$_types;
    }

    /**
     *
     * the input array will save the data from any of the array keys matching the types array
     *
     * @param int   $listingId
     * @param array $post
     *
     * @return $this
     */
    public function save($listingId, $post = array())
    {
        $ids = array(0);

        foreach (self::getTypes() as $type => $desc) {
            if (!empty($post[$type])) {
                $data = (is_array($post[$type])) ? $post[$type] : (array)\Ppb\Utility::unserialize($post[$type]);
                $ids = array_merge($ids, $this->_saveByType($listingId, $data, $type));
            }
        }

        $this->fetchAll(
            $this->getTable()->select()
                ->where('listing_id = ?', $listingId)
                ->where('id NOT IN (?)', $ids))->delete();

        return $this;
    }

    /**
     *
     * save data in listings_media table
     *
     * @param int    $listingId the id of the listing
     * @param array  $data      array data
     * @param string $type      image, video, download
     *
     * @return array    ids resulted from the insert/update queries
     */
    protected function _saveByType($listingId, array $data, $type = 'image')
    {
        $orderId = 0;
        $ids = array();

        $table = $this->getTable();

        // save media
        foreach ((array)$data as $value) {
            if (!empty($value)) {
                $row = $table->fetchRow(
                    $table->select()
                        ->where("listing_id = ?", $listingId)
                        ->where("value = ?", $value)
                        ->where('type = ?', $type));

                if ($row !== null) {
                    $table->update(array('order_id' => $orderId++),
                        $table->getAdapter()->quoteInto('id = ?', $row['id'])
                    );
                    $ids[] = $row['id'];
                }
                else {
                    $table->insert(array(
                        'value'      => $value,
                        'listing_id' => $listingId,
                        'type'       => $type,
                        'order_id'   => $orderId++,
                        'created_at' => new Expr('now()'),
                    ));
                    $ids[] = $table->lastInsertId();
                }
            }
        }

        return $ids;
    }

}

