<?php

/**
 *
 * PHP Pro Bid
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2018 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     8.0 [rev.8.0.03]
 */

/**
 * bulk lister service class
 */
/**
 * MOD:- EBAY IMPORTER
 */

namespace Ppb\Service\Listings;

use Ppb\Service\Listings as ListingsService,
    Ppb\Service\CustomFields as CustomFieldsService,
    Ppb\Model\Elements,
    Ppb\Http\GenerateCSV,
    External\ParseCSV,
    External\ForceUTF8;
## -- START :: ADD -- [ MOD:- EBAY IMPORTER ]
use Ppb\Service;
## -- END :: ADD -- [ MOD:- EBAY IMPORTER ]

class BulkLister extends ListingsService
{

    /**
     * bulk lister file max size
     */
    const MAX_SIZE = 20; // 20 MB
    const ARRAY_SEPARATOR = '||';

    protected $_jsonColumns = array('stock_levels', 'postage');

    protected $_forceUtf8Columns = array('name', 'short_description', 'description', 'item_selected_carriers');

    protected $_prefilledFields = array();

    /**
     *
     * get prefilled fields
     *
     * @return array
     */
    public function getPrefilledFields()
    {
        return $this->_prefilledFields;
    }

    /**
     *
     * set prefilled fields
     *
     * @param array $prefilledFields
     *
     * @return $this
     */
    public function setPrefilledFields($prefilledFields)
    {
        $this->_prefilledFields = (is_array($prefilledFields)) ? $prefilledFields : array();

        return $this;
    }

    /**
     * we will have a generate sample file method
     */
    /**
     * we will have a save all method that will call the parent save() method for each row and do post save actions for each row
     * if in account mode, it will output a combined message for all listed items together with the total balance debit added
     * if in live payment, it will output a message with the number of items activated, and the number of items that will need the
     * setup fee to be paid
     */
    /**
     * we will have a method that will validate each uploaded row based on the Elements\Listing model and return true if all are valid
     * or return an array of error messages (error/row #/row item title)
     */

    /**
     *
     * get listing model elements that apply to the bulk file structure
     *
     * @return array
     */
    public function getBulkElements()
    {
        $model = new Elements\Listing('bulk');

        $listingElements = $model->getElements();

        $elements = array();

        foreach ($listingElements as $element) {
            $formId = (isset($element['form_id'])) ? $element['form_id'] : array();
            if (in_array('bulk', (array)$formId)) {
                if ($element['element'] !== false) {
                    if (isset($element['bulk']['multiOptions'])) {
                        $element['multiOptions'] = $element['bulk']['multiOptions'];
                    }

                    if (isset($element['bulk']['label'])) {
                        $element['label'] = $element['bulk']['label'];
                    }

                    if (isset($element['bulk']['sample'])) {
                        $element['sample'] = $element['bulk']['sample'];
                    }
                    else if (isset($element['value'])) {
                        $element['sample'] = $element['value'];
                    }
                    else if (isset($element['multiOptions'])) {
                        $multiOptions = array_keys($element['multiOptions']);
                        $element['sample'] = reset($multiOptions);
                    }
                    else {
                        $element['sample'] = null;
                    }

                    array_push($elements, $element);
                }
            }
        }

        return $elements;
    }

    /**
     *
     * generate and download sample csv file
     *
     * @return void
     */
    public function downloadSampleFile()
    {
        $elements = $this->getBulkElements();

        $heading = array_map(function ($element) {
            return $element['id'];
        }, $elements);

        $data = array_map(function ($element) {
            return $element['sample'];
        }, $elements);

        $download = new GenerateCSV('bulk-lister-sample.csv');
        $download->setHeading($heading)
            ->setData(array(
                $data))
            ->send();
    }

    /**
     *
     * parse csv file and return an array containing data formatted in order to be parsed by the listing form.
     *
     * using external ParseCSV class, will properly import descriptions with new lines in them etc
     *
     * @param string $fileName
     *
     * @return array
     */
    public function parseCSV($fileName)
    {
        $data = array();

        $filePath = \Ppb\Utility::getPath('uploads') . '/' . $fileName;

        $csv = new ParseCSV($filePath);

        $customFieldsService = new CustomFieldsService();
        $aliases = $customFieldsService->getAliases();

        if (count($csv->data) > 0) {
            $data = $csv->data;

            foreach ($data as $id => $row) {
                foreach ($row as $key => $value) {
                    if (array_key_exists($key, $aliases)) {
                        $key = $aliases[$key];
                        $data[$id][$key] = $value;
                    }

                    if (in_array($key, $this->_forceUtf8Columns)) {
                        $data[$id][$key] = $value = ForceUTF8\Encoding::toUTF8($value);
                    }

                    if (!is_array($value)) {
                        // we use json for columns that accept arrays
                        if (in_array($key, $this->_jsonColumns)) {
                            $data[$id][$key] = $this->_jsonDecode($value);
                        }
                        else if (stristr($value, self::ARRAY_SEPARATOR)) {
                            $data[$id][$key] = explode(self::ARRAY_SEPARATOR, $value);
                        }
                    }
                }
            }
        }


        return $data;
    }

    ## -- START :: ADD -- [ MOD:- EBAY IMPORTER ]
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
     * @param \Cube\Db\Table\Row $ebayUser
     *
     * @return array
     */
    public function ebayImport($ebayUser)
    {
        $data = array();

        $entriesPerPage = 60;
        $pageNumber = 1;

        $ebayAPIService = new Service\EbayAPI();
        $totalListings = $ebayAPIService->getTotalListings($ebayUser['ebay_username'], $ebayUser['ebay_marketplace']);

        do {
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

                    foreach ($items as $itm) {
                        if (!empty($itm['itemId'])) {
                            $ebayItemId = $itm['itemId'];

                            $localItem = $this->findBy('ebay_item_id', $ebayItemId);

                            if (!$localItem) {
                                $result = $ebayAPIService->getSingleItemResponse($ebayItemId);

                                if ($result->Ack == 'Success') {
                                    $ebayListingType = $result->Item->ListingType;

                                    if (in_array($ebayListingType, array('FixedPriceItem', 'Chinese'))) {
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
                                        $item = array(
                                            'ebay_item_id'      => $ebayItemId,
                                            'list_in'           => 'site',
                                            'listing_type'      => $listingType,
                                            'category_id'       => $categoryId,
                                            'name'              => (string)$result->Item->Title,
                                            'description'       => (string)$result->Item->Description,
                                            'start_price'       => (float)$result->Item->CurrentPrice,
                                            'buyout_price'      => $buyoutPrice,
                                            'currency'          => $currency,
                                            'item_condition'    => $itemCondition,
//                                        'start_time'   => date('Y-m-d H:i:s', strtotime((string)$result->Item->StartTime)),
                                            'start_time_type'   => 0,
                                            'end_time_type'     => 'custom',
                                            'end_time'          => date('Y-m-d H:i:s', strtotime((string)$result->Item->EndTime)),
                                            'quantity'          => $quantity,
                                            'image'             => (array)$result->Item->PictureURL,
                                            'sku'               => (string)$result->Item->SKU,
                                            'enable_make_offer' => $enableMakeOffer,
                                        );

                                        // get item weight using trading api
                                        if (($itemWeight = $ebayAPIService->getItemWeight($ebayItemId, $ebayUser)) !== false) {
                                            $item['item_weight'] = $itemWeight;
                                        }

                                        $item = array_merge($prefilledFields, $item);

                                        $data[] = $item;
                                    }
                                }
                            }
                            else {
                                $data[] = null;
                            }
                        }
                    }
                }
            }

            $listingsParsed = $entriesPerPage * $pageNumber;
            $pageNumber++;
        } while ($listingsParsed < $totalListings);

        return $data;
    }
    ## -- END :: ADD -- [ MOD:- EBAY IMPORTER ]

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

