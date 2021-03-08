<?php

/**
 *
 * PHP Pro Bid
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2016 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.7
 */
/**
 * bulk lister service class
 */
/**
 * MOD:- EBAY IMPORTER
 *
 * @version 3.0
 */

namespace Ppb\Service\Listings\BulkLister;

use Ppb\Service\Listings\BulkLister as BulkListerListingsService,
    Cube\Db\Expr,
    Ppb\Service;

class Ebay extends BulkListerListingsService
{
    const DEFAULT_AUCTION_DURATION = 7;

    /**
     * import types
     */
    const IMPORT_TYPE_ALL_INC_DUPLICATES = 'all_inc_duplicates';
    const IMPORT_TYPE_ALL_WO_DUPLICATES = 'all_wo_duplicates';
    const IMPORT_TYPE_NEW_WO_DUPLICATES = 'new_wo_duplicates';

    /**
     *
     * list in options
     *
     * @var array
     */
    protected $_listIn = array();


    /**
     *
     * get list in options
     *
     * @return array
     */
    public function getListIn()
    {
        if (empty($this->_listIn)) {
            $this->setListIn();
        }

        return $this->_listIn;
    }

    /**
     *
     * set list in field options
     * if store only mode is enabled, items will all be listed as in "Both"
     *
     * @param array $listIn
     *
     * @return $this
     */
    public function setListIn(array $listIn = null)
    {
        if ($listIn === null) {
            $settings = $this->getSettings();
            $user = $this->getUser();

            if (!$settings['store_only_mode']) {
                $listIn['site'] = 'Site';
            }

            $storeEnabled = false;
            if ($user['id']) {
                $storeEnabled = $user->storeStatus();
            }

            if ($storeEnabled) {
                if ($settings['stores_force_list_in_both']) {
                    $listIn = array();
                }
                else if (!$settings['store_only_mode']) {
                    $listIn['store'] = 'Store';
                }
                $listIn['both'] = 'Both';
            }
        }

        $this->_listIn = (array)$listIn;

        return $this;
    }

    public function deleteEbaySoldItems($username, $marketplace = null)
    {
        $entriesPerPage = 60;
        $pageNumber = 1;

        $ebayAPIService = new Service\EbayAPI();

        $totalListings = $ebayAPIService->getTotalListings($username, $marketplace, 'findCompletedItems');

        $ebayItemIds = array();

        do {
            $response = $ebayAPIService->getFindingServiceResponse($username, $marketplace, 'findCompletedItems', $entriesPerPage, $pageNumber);

            if (isset($response['ack'])) {
                if ($response['ack'] == 'Success') {
                    if ($totalListings > 1) {
                        $items = $response['searchResult']['item'];
                    }
                    else {
                        $items[] = $response['searchResult']['item'];
                    }

                    foreach ($items as $itm) {
                        if (!empty($itm['itemId'])) {
                            $ebayItemIds[] = $itm['itemId'];
                        }
                    }
                }
            }

            $listingsParsed = $entriesPerPage * $pageNumber;
            $pageNumber++;
        } while ($listingsParsed < $totalListings);

        if (count($ebayItemIds) > 0) {
            $this->getTable()->update(
                array('deleted' => 1),
                array($this->getTable()->getAdapter()->quoteInto('ebay_item_id in (?)', $ebayItemIds))
            );
        }

        return $this;
    }

    /**
     *
     * import ebay listings based on the seller's ebay username, using ebay's finding api, ebay's trading api and ebay's shopping api
     * we have some fields that we need to manually set up (temp solution)
     * requires an object of type Row that is a row from the ebay_users table
     *
     * @param \Cube\Db\Table\Row $ebayUser ebay user object (ebay users table)
     *
     * @return array
     */
    public function importAll($ebayUser)
    {
        $data = array();

        $entriesPerPage = 25;
        $pageNumber = 1;

        $ebayAPIService = new Service\EbayAPI();
        $totalListings = $ebayAPIService->getTotalListings($ebayUser['ebay_username'], $ebayUser['ebay_marketplace']);

        do {
            array_merge($data, $this->import($ebayUser, $pageNumber, $entriesPerPage, $totalListings));

            $listingsParsed = $entriesPerPage * $pageNumber;
            $pageNumber++;
        } while ($listingsParsed < $totalListings);

        return $data;
    }

    /**
     *
     * import ebay items (paginated)
     *
     * @param \Cube\Db\Table\Row $ebayUser ebay user object (ebay users table)
     * @param int                $pageNumber
     * @param int                $entriesPerPage
     * @param int                $totalListings
     * @param string             $importType
     *
     * @return array
     */
    public function import($ebayUser, $pageNumber = 1, $entriesPerPage = 25, $totalListings = null, $importType = self::IMPORT_TYPE_ALL_INC_DUPLICATES)
    {
        $data = array();

        $ebayAPIService = new Service\EbayAPI();

        if ($totalListings === null) {
            $totalListings = $ebayAPIService->getTotalListings($ebayUser['ebay_username'], $ebayUser['ebay_marketplace']);
        }

        $response = $ebayAPIService->getFindingServiceResponse($ebayUser['ebay_username'], $ebayUser['ebay_marketplace'], 'findItemsAdvanced', $entriesPerPage, $pageNumber);

        if (isset($response['ack'])) {
            if ($response['ack'] == 'Success') {
                $prefilledFields = $this->getPrefilledFields();
                if ($totalListings > 1) {
                    $items = $response['searchResult']['item'];
                }
                else {
                    $items[] = $response['searchResult']['item'];
                }

                $ebayCategoriesService = new Service\Table\EbayCategories();

                $listInKeys = array_keys($this->getListIn());
                $listIn = array_shift($listInKeys);

                foreach ($items as $itm) {
                    if (!empty($itm['itemId'])) {
                        $ebayItemId = $itm['itemId'];

                        if ($importType == self::IMPORT_TYPE_ALL_INC_DUPLICATES) {
                            $localItem = 0;
                        }
                        else {
                            $select = $this->getTable()
                                ->select(array('nb_rows' => new Expr('count(*)')))
                                ->where('user_id = ?', $ebayUser['user_id'])
                                ->where('ebay_item_id = ?', $ebayItemId);

                            $stmt = $select->query();

                            $localItem = (integer)$stmt->fetchColumn('nb_rows');
                        }

                        if (!$localItem) {
                            $result = $ebayAPIService->getSingleItemResponse($ebayItemId);

                            if ($result->Ack == 'Success') {
                                $ebayListingType = $result->Item->ListingType;

                                $listingType = ($ebayListingType == 'FixedPriceItem') ? 'product' : 'auction';
                                $currency = (string)$result->Item->CurrentPrice->attributes();
                                $quantity = ($listingType == 'auction') ? 1 : (int)$result->Item->Quantity;

                                $buyoutPrice = ($listingType == 'product') ? (float)$result->Item->CurrentPrice : (float)$result->Item->BuyItNowPrice;

                                $itemCondition = 'see-description';

                                if ($result->Item->ConditionID == 1000) {
                                    $itemCondition = 'new';
                                }
                                else if ($result->Item->ConditionID > 1000) {
                                    $itemCondition = 'used';
                                }

                                $categoryId = $ebayCategoriesService->getLocalCategoryId($result->Item->PrimaryCategoryID);

                                $enableMakeOffer = ($result->Item->BestOfferEnabled == 'true') ? 1 : 0;

                                $images = array();
                                foreach ($result->Item->PictureURL as $image) {
                                    $images[] = (string)$image;
                                }

                                $endTime = date('Y-m-d H:i:s', strtotime((string)$result->Item->EndTime));
                                $item = array(
                                    'ebay_item_id'      => $ebayItemId,
                                    'list_in'           => $listIn,
                                    'listing_type'      => $listingType,
                                    'category_id'       => $categoryId,
                                    'name'              => (string)$result->Item->Title,
                                    'description'       => (string)$result->Item->Description,
                                    'start_price'       => (float)$result->Item->CurrentPrice,
                                    'buyout_price'      => $buyoutPrice,
                                    'currency'          => $currency,
                                    'item_condition'    => $itemCondition,
//                                        'start_time'   => date('Y-m-d H:i:s', strtotime((string)$result->Item->StartTime)),
                                    'start_time_type'   => 0, // now
                                    'end_time_type'     => 1, // custom
                                    'end_time'          => $endTime,
                                    'quantity'          => $quantity,
                                    'image'             => $images,
                                    'sku'               => (string)$result->Item->SKU,
                                    'enable_make_offer' => $enableMakeOffer,
                                    'ebay_raw_data'     => json_encode($result),
                                    'duplicate'         => $localItem,
                                );

                                // get item weight using trading api
                                if (($itemWeight = $ebayAPIService->getItemWeight($ebayItemId, $ebayUser)) !== false) {
                                    $item['item_weight'] = $itemWeight;
                                }

                                $item = array_filter(array_merge($prefilledFields, $item));

                                if ($item['listing_type'] == 'auction' && empty($item['end_time'])) {
                                    $item['end_time_type'] = 0; // duration

                                    $settings = $this->getSettings();
                                    $item['duration'] = (!empty($settings['ebay_default_auction_duration'])) ?
                                        $settings['ebay_default_auction_duration'] : self::DEFAULT_AUCTION_DURATION;
                                }

                                $data[] = $item;
                            }
                        }
                        else {
                            $data[] = null;
                        }
                    }
                }
            }
        }

        return $data;
    }

    /**
     *
     * decode json or return the original string if it cannot be decoded
     *
     * @param $string
     *
     * @return mixed
     */
    protected function _jsonDecode($string)
    {
        $array = json_decode($string, true);

        return ($array !== null) ? $array : $string;
    }
}

