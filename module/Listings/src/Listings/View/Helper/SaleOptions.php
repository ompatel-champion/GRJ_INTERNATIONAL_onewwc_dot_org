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
 * sale status & options view helper class
 */

namespace Listings\View\Helper;

use Ppb\View\Helper\AbstractHelper,
    Ppb\View\Helper\Icon as IconHelper,
    Ppb\Db\Table\Row\Sale as SaleModel;

class SaleOptions extends AbstractHelper
{

    /**
     *
     * sale model
     *
     * @var \Ppb\Db\Table\Row\Sale
     */
    protected $_sale;

    /**
     *
     * absolute path flag
     *
     * @var bool
     */
    protected $_absolutePath = false;

    /**
     *
     * get sale model
     *
     * @return \Ppb\Db\Table\Row\Sale
     */
    public function getSale()
    {
        return $this->_sale;
    }

    /**
     *
     * set sale model
     *
     * @param \Ppb\Db\Table\Row\Sale $sale
     *
     * @return $this
     */
    public function setSale($sale)
    {
        $this->_sale = $sale;

        return $this;
    }

    /**
     *
     * get absolute path flag
     *
     * @return bool
     */
    public function isAbsolutePath()
    {
        return $this->_absolutePath;
    }

    /**
     *
     * set absolute path flag
     *
     * @param bool $absolutePath
     *
     * @return $this
     */
    public function setAbsolutePath($absolutePath)
    {
        $this->_absolutePath = $absolutePath;

        return $this;
    }

    /**
     *
     * sale status options helper
     *
     * @param \Ppb\Db\Table\Row\Sale $sale
     * @param bool|null              $absolutePath
     *
     * @return string
     */
    public function saleOptions(SaleModel $sale = null, $absolutePath = null)
    {
        if ($sale instanceof SaleModel) {
            $this->setSale($sale);
        }

        if ($absolutePath !== null) {
            $this->setAbsolutePath($absolutePath);
        }

        return $this;
    }

    public function paymentStatus($icon = true)
    {
        $iconTag = null;

        $sale = $this->getSale();

        $translate = $this->getTranslate();

        $flagPayment = $sale->getData('flag_payment');

        $title = (array_key_exists($flagPayment, SaleModel::$paymentStatuses)) ? SaleModel::$paymentStatuses[$flagPayment] : $translate->_('N/A');
        $title = $translate->_($title);

        if ($icon) {
            /** @var \Ppb\View\Helper\Icon $iconHelper */
            $iconHelper = $this->getView()->icon($this->isAbsolutePath(), IconHelper::TYPE_FEATHER_IMG);
            $iconTag = $iconHelper->render('dollar-sign', $title);
        }

        switch ($flagPayment) {
            case SaleModel::PAYMENT_PAID:
                if ($icon) {
                    $output = '<button type="button" class="btn btn-success" data-toggle="tooltip" title="' . $title . '">' . $iconTag . '</button>';
                }
                else {
                    $output = '<span class="text-success">' . $title . '</span>';
                }
                break;
            case SaleModel::PAYMENT_PAID_DIRECT_PAYMENT:
                if ($icon) {
                    $output = '<button type="button" class="btn btn-blue" data-toggle="tooltip" title="' . $title . '">' . $iconTag . '</button>';
                }
                else {
                    $output = '<span class="text-blue">' . $title . '</span>';
                }
                break;
            case SaleModel::PAYMENT_PAY_ARRIVAL:
                if ($icon) {
                    $output = '<button type="button" class="btn btn-gold" data-toggle="tooltip" title="' . $title . '">' . $iconTag . '</button>';
                }
                else {
                    $output = '<span class="text-gold">' . $title . '</span>';
                }
                break;
            case SaleModel::PAYMENT_UNPAID:
            default:
                if ($icon) {
                    $output = '<button type="button" class="btn btn-default" data-toggle="tooltip" title="' . $title . '">' . $iconTag . '</button>';
                }
                else {
                    $output = '<span class="text-primary">' . $title . '</span>';
                }
                break;
        }

        return $output;
    }

    /**
     *
     * shipping status icon / text display
     *
     * @param bool $icon
     *
     * @return null|string
     */
    public function shippingStatus($icon = true)
    {
        $output = $iconTag = null;

        $sale = $this->getSale();

        if ($sale->hasPostage()) {
            $translate = $this->getTranslate();

            $flagShipping = $sale->getData('flag_shipping');
            $title = (array_key_exists($flagShipping, SaleModel::$shippingStatuses)) ? SaleModel::$shippingStatuses[$flagShipping] : SaleModel::$shippingStatuses[SaleModel::SHIPPING_NA];
            $title = $translate->_($title);

            if ($icon) {
                /** @var \Ppb\View\Helper\Icon $iconHelper */
                $iconHelper = $this->getView()->icon($this->isAbsolutePath(), IconHelper::TYPE_FEATHER_IMG);
                $iconTag = $iconHelper->render('truck', $title);
            }

            switch ($flagShipping) {
                case SaleModel::SHIPPING_SENT:
                    if ($icon) {
                        $output = '<button type="button" class="btn btn-success" data-toggle="tooltip" title="' . $title . '">' . $iconTag . '</button>';
                    }
                    else {
                        $output = '<span class="text-success">' . $title . '</span>';
                    }
                    break;
                case SaleModel::SHIPPING_PROBLEM:
                    if ($icon) {
                        $output = '<button type="button" class="btn btn-danger" data-toggle="tooltip" title="' . $title . '">' . $iconTag . '</button>';
                    }
                    else {
                        $output = '<span class="text-danger">' . $title . '</span>';
                    }
                    break;
                case SaleModel::SHIPPING_NA:
                    if ($icon) {
                        $output = '<button type="button" class="btn btn-secondary" data-toggle="tooltip" title="' . $title . '">' . $iconTag . '</button>';
                    }
                    else {
                        $output = '<span class="text-secondary">' . $title . '</span>';

                    }
                    break;
                case SaleModel::SHIPPING_PROCESSING:
                default:
                    if ($icon) {
                        $output = '<button type="button" class="btn btn-default" data-toggle="tooltip" title="' . $title . '">' . $iconTag . '</button>';
                    }
                    else {
                        $output = '<span class="text-primary">' . $title . '</span>';

                    }
                    break;
            }
        }

        return $output;
    }

}

