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
 * payment simulator gateway model class
 */
/**
 * MOD:- BANK TRANSFER
 * MOD:- ESCROW & BANK TRANSFERS
 */

namespace Ppb\Model\PaymentGateway;

use Cube\Controller\Request\AbstractRequest,
    Ppb\Service;

class BankTransfer extends AbstractPaymentGateway
{
    /**
     * payment gateway name
     */

    const NAME = 'BankTransfer';

    const ACTION = 'bank-transfer';

    /**
     *
     * seller id in case of a direct payment
     *
     * @var int
     */
    protected $_sellerId;

    /**
     *
     * post url
     *
     * @var string
     */
    protected $_postUrl;

    /**
     * bank transfer description
     */
    protected $_description = 'Bank Transfer description.';

    public function __construct($userId = null)
    {
        parent::__construct(self::NAME, $userId);

        $this->setSellerId($userId);
    }

    /**
     *
     * set seller id
     *
     * @param int $sellerId
     */
    public function setSellerId($sellerId)
    {
        $this->_sellerId = $sellerId;
    }

    /**
     *
     * get seller id
     *
     * @return int
     */
    public function getSellerId()
    {
        return $this->_sellerId;
    }


    public function enabled()
    {
        return true;
    }

    public function formElements()
    {
        return array(
            array(
                'id'      => 'transaction_id',
                'value'   => $this->getTransactionId(),
                'element' => 'hidden',
            ),
            array(
                'id'      => 'seller_id',
                'value'   => $this->getSellerId(),
                'element' => 'hidden',
            ),
        );
    }

    /**
     *
     * get payment box post url
     *
     * @return string
     */
    public function getPostUrl()
    {
        if (empty($this->_postUrl)) {
        $params = array(
            'module'     => parent::MODULE,
            'controller' => parent::CONTROLLER,
            'action'     => self::ACTION,
        );

            $this->setPostUrl(
                $this->getView()->url($params));
        }

        return $this->_postUrl;
    }

    /**
     *
     * set post url
     *
     * @param string $postUrl
     *
     * @return $this
     */
    public function setPostUrl($postUrl)
    {
        $this->_postUrl = $postUrl;

        return $this;
    }

    /**
     *
     * return true at all times when we are to accept a bank transfer, since the ipn is triggered manually by the seller or the admin
     *
     * @param float  $amount
     * @param string $currency
     *
     * @return bool
     */
    public function checkIpnAmount($amount, $currency)
    {
        return true;
    }

    /**
     *
     * process ipn
     *
     * @param \Cube\Controller\Request\AbstractRequest $request
     *
     * @return bool      if the bank transfer can be accepted by the currently logged in user, return true. otherwise return false
     */
    public function processIpn(AbstractRequest $request)
    {
        $transactionId = $request->getParam('id');

        $bankTransfersService = new Service\BankTransfers();
        /** @var \Ppb\Db\Table\Row\BankTransfer $bankTransfer */
        $bankTransfer = $bankTransfersService->findBy('id', $transactionId);

        if ($bankTransfer !== null) {
            if ($bankTransfer->canAccept()) {
                $bankTransfer->save(array(
                    'transfer_status' => Service\BankTransfers::STATUS_PAID,
                ));

                return true;
            }
        }

        return false;
    }

}

