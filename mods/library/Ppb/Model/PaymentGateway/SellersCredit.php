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
 * sellers credit gateway model class
 */
/**
 * MOD:- SELLERS CREDIT
 */

namespace Ppb\Model\PaymentGateway;

use Cube\Controller\Request\AbstractRequest,
    Cube\Controller\Front,
    Ppb\Service,
    Ppb\Db\Table\Row\Transaction as TransactionModel,
    Ppb\Db\Table\Row\User as UserModel;

class SellersCredit extends AbstractPaymentGateway
{
    /**
     * payment gateway name
     */

    const NAME = 'SellersCredit';

    /**
     * payment simulator description
     */
    protected $_description = 'Click on the button on the right to pay the seller directly from your account balance on the website.';

    public function __construct($userId = null)
    {
        parent::__construct(self::NAME, $userId);
    }

    /**
     *
     * method is set to false as it will never display if other gateways are called
     *
     * @return bool
     */
    public function enabled()
    {
        return false;
    }

    /**
     *
     * check if the transaction can be paid for using sellers credit
     *
     * @param TransactionModel $transaction
     *
     * @return bool
     */
    public function canPay(TransactionModel $transaction)
    {
        /** @var \Ppb\Db\Table\Row\Sale $sale */
        $sale = $transaction->findParentRow('\Ppb\Db\Table\Sales');

        if ($sale->canPaySellersCredit()) {
            return true;
        }

        return false;
    }

    public function formElements()
    {
        return array(
            array(
                'id'      => 'transaction_id',
                'value'   => $this->getTransactionId(),
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
        return parent::getIpnUrl();
    }

    /**
     *
     * process ipn:
     * get transaction id and if unpaid and logged in user = sale buyer and can pay then process transaction
     *
     * @param \Cube\Controller\Request\AbstractRequest $request
     *
     * @return bool      return true if ipn is valid (for the simulator it will always be true
     */
    public function processIpn(AbstractRequest $request)
    {
        if ($request->isPost()) {
            $this->setTransactionId($_POST['transaction_id'])
                ->setGatewayPaymentStatus('Completed')
                ->setGatewayTransactionCode('SellersCreditTXN');

            $user = Front::getInstance()->getBootstrap()->getResource('user');

            if ($user) {
                $transactionId = (int)$this->getTransactionId();

                $transactionsService = new Service\Transactions();
                $transaction = $transactionsService->findBy('id', $transactionId);

                if ($transaction) {
                    if ($transaction['user_id'] == $user['id']) {
                        /** @var \Ppb\Db\Table\Row\Sale $sale */
                        $sale = $transaction->findParentRow('\Ppb\Db\Table\Sales');

                        if ($sale) {
                            return $sale->canPaySellersCredit(true);
                        }
                    }
                }
            }
        }

        return false;
    }

    /**
     *
     * for the sellers credit method, this will always be true
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
}

