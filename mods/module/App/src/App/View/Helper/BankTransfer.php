<?php

/**
 *
 * PHP Pro Bid
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.2
 */
/**
 * bank transfer view helper class
 */
/**
 * MOD:- BANK TRANSFER
 */

namespace App\View\Helper;

use Cube\View\Helper\AbstractHelper,
    Ppb\Db\Table\Row\BankTransfer as BankTransferModel,
    Ppb\Service;

class BankTransfer extends AbstractHelper
{

    /**
     *
     * bank transfer model
     *
     * @var \Ppb\Db\Table\Row\BankTransfer
     */
    protected $_bankTransfer;

    /**
     *
     * bank accounts service
     *
     * @var \Ppb\Service\BankTransfers
     */
    protected $_bankTransfers;

    /**
     *
     * main method, only returns object instance
     *
     * @param int|string|\Ppb\Db\Table\Row\BankTransfer $bankTransfer
     *
     * @return $this
     */
    public function bankTransfer($bankTransfer = null)
    {
        if ($bankTransfer !== null) {
            $this->setBankTransfer($bankTransfer);
        }

        return $this;
    }

    /**
     *
     * set bank transfers service
     *
     * @param \Ppb\Service\BankTransfers $bankTransfers
     *
     * @return $this
     */
    public function setBankTransfers(Service\BankTransfers $bankTransfers)
    {
        $this->_bankTransfers = $bankTransfers;

        return $this;
    }

    /**
     *
     * get bank accounts service
     *
     * @return \Ppb\Service\BankTransfers
     */
    public function getBankTransfers()
    {
        if (!$this->_bankTransfers instanceof Service\BankTransfers) {
            $this->setBankTransfers(
                new Service\BankTransfers());
        }

        return $this->_bankTransfers;
    }


    /**
     *
     * get bank transfer data
     *
     * @return \Ppb\Db\Table\Row\BankTransfer
     * @throws \InvalidArgumentException
     */
    public function getBankTransfer()
    {
        if (!$this->_bankTransfer instanceof BankTransferModel) {
            throw new \InvalidArgumentException("The bank transfer model has not been instantiated");
        }

        return $this->_bankTransfer;
    }

    /**
     *
     * set bank transfer data
     *
     * @param int|string|\Ppb\Db\Table\Row\BankTransfer $bankTransfer
     *
     * @throws \InvalidArgumentException
     * @return $this
     */
    public function setBankTransfer($bankTransfer)
    {
        if (is_int($bankTransfer) || is_string($bankTransfer)) {
            $bankTransfersService = $this->getBankTransfers();
            $bankTransfer = $bankTransfersService->findBy('id', $bankTransfer);
        }

        if (!$bankTransfer instanceof BankTransferModel) {
            throw new \InvalidArgumentException("The method requires a string, an integer or an object of type \Ppb\Db\Table\Row\BankTransfer.");
        }

        $this->_bankTransfer = $bankTransfer;

        return $this;
    }

    /**
     *
     * display the status of the bank transfer
     *
     * @return string
     */
    public function status()
    {
        $translate = $this->getTranslate();
        $output = null;

        $NA = '<em>' . $translate->_('N/A') . '</em>';
        try {
            $bankTransfer = $this->getBankTransfer();
        } catch (\Exception $e) {
            return $NA;
        }

        $bankTransfersStatuses = $this->getBankTransfers()->getTransferStatuses();

        $transferStatus = $bankTransfer->getData('transfer_status');
        $output = (isset($bankTransfersStatuses[$transferStatus])) ? $bankTransfersStatuses[$transferStatus] : $NA;


        switch ($transferStatus) {
            case Service\BankTransfers::STATUS_PENDING:
                $output = '<span class="badge badge-info">' . $output . '</span>';
                break;
            case Service\BankTransfers::STATUS_PAID:
                $output = '<span class="badge badge-success">' . $output . '</span>';
                break;
            case Service\BankTransfers::STATUS_DECLINED:
                $output = '<span class="badge badge-danger">' . $output . '</span>';
                break;
            case Service\BankTransfers::STATUS_CANCELLED:
                $output = '<span class="badge badge-warning">' . $output . '</span>';
                break;

        }

        return $output;
    }

    public function type()
    {
        $translate = $this->getTranslate();
        $output = null;

        $NA = '<em>' . $translate->_('N/A') . '</em>';
        try {
            $bankTransfer = $this->getBankTransfer();
        } catch (\Exception $e) {
            return $NA;
        }

        $transferTypes = $this->getBankTransfers()->getTransferTypes();

        $bankTransferType = $bankTransfer->getData('transfer_type');
        $output = (isset($transferTypes[$bankTransferType])) ? $transferTypes[$bankTransferType] : $NA;

        return $output;
    }

}

