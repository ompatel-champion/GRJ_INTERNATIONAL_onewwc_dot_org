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
 * sales table rowset class
 */

namespace Ppb\Db\Table\Rowset;

use Ppb\Service\Table\Currencies as CurrenciesService;

class Sales extends AbstractStatus
{

    /**
     *
     * row object class
     *
     * @var string
     */
    protected $_rowClass = '\Ppb\Db\Table\Row\Sale';

    /**
     *
     * activate sale invoices from the selected rowset
     *
     * @return $this
     */
    public function activate()
    {
        $this->resetCounter();

        /** @var \Ppb\Db\Table\Row\Sale $sale */
        foreach ($this as $sale) {
            $sale->updateActive(1);
            $this->incrementCounter();
        }

        return $this;
    }

    /**
     *
     * remove marked deleted status from marked deleted items
     *
     * @return $this
     */
    public function undelete()
    {
        $this->save(array(
            'seller_deleted' => 0,
            'buyer_deleted'  => 0,
        ));

        $this->setCounter(
            $this->count());

        return $this;
    }

    public function delete()
    {
        /** @var \Ppb\Db\Table\Row\Sale $sale */
        foreach ($this as $sale) {
            $result = $sale->delete($this->getAdmin());

            if ($result) {
                $this->incrementCounter();
            }
            else {
                $translate = $this->getTranslate();
                $message = sprintf($translate->_("Invoice #%s cannot be deleted."), $sale->getData('id'));
                $this->addMessage($message);
            }
        }

        return $this;
    }

    /**
     *
     * check if all rows in the rowset can be edited / combined
     *
     * @return bool
     */
    public function canEdit()
    {
        $hashes = array();

        /** @var \Ppb\Db\Table\Row\Sale $sale */
        foreach ($this as $sale) {
            if ($sale->canEdit($this->_admin)) {
                $hashes[] = $sale->combineHash();
            }
        }

        if (count($hashes) == count($this) && count(array_unique($hashes)) === 1) {
            return true;
        }

        return false;
    }

    /**
     *
     * count number of items in rowset
     *
     * @return int
     */
    public function countItems()
    {
        $result = 0;

        /** @var \Ppb\Db\Table\Row\Sale $sale */
        foreach ($this as $sale) {
            $result += $sale->countItems();
        }

        return $result;
    }

    /**
     *
     * calculate total value of all rows in rowset, converted to the requested currency
     *
     * @param string $currency
     * @param bool   $simple
     * @param bool   $applyVoucher
     *
     * @return float
     */
    public function calculateTotal($currency, $simple = false, $applyVoucher = true)
    {
        $result = 0;

        $currenciesService = new CurrenciesService();

        /** @var \Ppb\Db\Table\Row\Sale $sale */
        foreach ($this as $sale) {
            $saleTotal = $sale->calculateTotal($simple, $applyVoucher);

            $result += $currenciesService->convertAmount($saleTotal, $sale['currency'], $currency);
        }

        return $result;

    }
}

