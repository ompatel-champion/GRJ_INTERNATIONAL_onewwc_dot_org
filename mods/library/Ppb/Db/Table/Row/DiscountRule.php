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
 * discount rules table row object model
 */
/**
 * MOD:- DISCOUNT RULES
 */
namespace Ppb\Db\Table\Row;

use Ppb\Service,
    Ppb\Db\Table;

class DiscountRule extends AbstractRow
{

    /**
     *
     * check if the conditions for this discount rule are met
     * IMPORTANT: this method will not check if the discount rule is active, expired or if it belongs to the correct seller!
     * IMPORTANT: the discount rule condition validator WILL NOT ALLOW the input of complete sql queries
     * IMPORTANT: if the generated query is not valid, the conditions will be considered invalid and the discount will not be applied
     *
     * @param int $listingId
     *
     * @return bool
     */
    public function validConditions($listingId)
    {
        $conditions = str_ireplace(
            array('&amp;', '&#039;', '&quot;', '&lt;', '&gt;', '&nbsp;'), array('&', "'", '"', '<', '>', ' '), $this->getData('conditions'));

        if (empty($conditions)) {
            return true;
        }

        $tablePrefix = $this->getTable()->getPrefix();

        $listingsTable = new Table\Listings();
        $salesTable = new Table\Sales();
        $salesListingsTable = new Table\SalesListings();

        $query = "SELECT count(*) AS `nb_rows`
            FROM `" . $tablePrefix . $listingsTable->getName() . "` AS `l`
            %searchByPurchasedListings%
            WHERE
                (`l`.`id` = '{$listingId}') AND (%conditions%)";


        $user = $this->getUser();
        $userId = (!empty($user['id'])) ? $user['id'] : 0;

        // add search by purchased listings snippet
        if (stristr($conditions, 'purchasedListingId')) {
            $query = str_replace('%searchByPurchasedListings%',
                ", `" . $tablePrefix . $salesListingsTable->getName() . "` AS `sl`
                LEFT JOIN `" . $tablePrefix . $salesTable->getName() . "` AS `s` ON `s`.`id` = `sl`.`sale_id`",
                $query);

            $query .= " AND (`s`.`buyer_id`='{$userId}' AND `s`.`flag_payment` > 0 AND `s`.`active` = '1')";
        }
        else {
            $query = str_replace('%searchByPurchasedListings%', '', $query);
        }

        // replace listingId, userId
        $conditions = str_replace(
            array('listingId', 'userId', 'purchasedListingId', 'price', 'name', 'description'),
            array("'{$listingId}'", "'{$userId}'", "`sl`.`listing_id`", "`l`.`buyout_price`", "`l`.`name`", "`l`.`description`"),
            $conditions);

        $query = str_replace('%conditions%', $conditions, $query);

        try {
            $statement = $this->getTable()->getAdapter()->query($query);
            $result = $statement->fetchColumn('nb_rows');
            if ($result > 0) {
                return true;
            }
        } catch (\Exception $e) {
        }

        return false;
    }

    /**
     *
     * apply the discount rule to a certain amount and return the updated amount
     *
     * @param float  $amount
     * @param string $currency
     *
     * @return float
     */
    public function apply($amount, $currency)
    {
        $reductionAmount = $this->getData('reduction_amount');
        switch ($this->getData('reduction_type')) {
            case 'flat':
                $settings = $this->getSettings();
                if ($currency !== null && $currency != $settings['currency']) {
                    $currenciesService = new Service\Table\Currencies();
                    $reductionAmount = $currenciesService->convertAmount($reductionAmount, $settings['currency'],
                        $currency);
                }

                $amount -= $reductionAmount;

                if ($amount < 0) {
                    $amount = 0;
                }
                break;
            case 'percent':
                $amount -= $amount * $reductionAmount / 100;
                break;
        }

        return $amount;
    }

}

