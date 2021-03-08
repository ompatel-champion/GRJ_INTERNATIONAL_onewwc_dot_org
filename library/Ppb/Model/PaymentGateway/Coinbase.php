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
 * coinbase payment gateway model class
 */

namespace Ppb\Model\PaymentGateway;

include APPLICATION_PATH . '/vendor/GuzzleHttp/functions.php';
include APPLICATION_PATH . '/vendor/GuzzleHttp/Psr7/functions.php';
include APPLICATION_PATH . '/vendor/GuzzleHttp/Promise/functions.php';

use Cube\Controller\Request\AbstractRequest;
use CoinbaseCommerce\Resources\Charge;
use CoinbaseCommerce\ApiClient;
use CoinbaseCommerce\Resources\Event;
use Ppb\Db\Table\Row\Transaction as TransactionModel,
    Ppb\Service;

class Coinbase extends AbstractPaymentGateway
{
    /**
     * payment gateway name
     */
    const NAME = 'Coinbase';

    /**
     * required settings
     */
    const API_KEY = 'api_key';

    /**
     *
     * post url is set by the charge object
     *
     * @var string
     */
    protected $_postUrl;

    /**
     * paypal description
     */
    protected $_description = 'Click to pay using Coinbase Commerce.';

    /**
     *
     * stripe payment form custom partial
     *
     * @var string
     */
    protected $_paymentFormPartial = 'forms/coinbase-commerce-payment.phtml';

    /**
     *
     * no automatic redirect
     *
     * @var bool
     */
    public static $automaticRedirect = false;

    public function __construct($userId = null)
    {
        parent::__construct(self::NAME, $userId);
    }

    /**
     *
     * check if the gateway is enabled
     *
     * @return bool
     */
    public function enabled()
    {
        if (!empty($this->_data[self::API_KEY])) {
            return true;
        }

        return false;
    }

    /**
     *
     * get paypal setup form elements
     *
     * @return array
     */
    public function getElements()
    {
        $translate = $this->getTranslate();

        return array(
            array(
                'form_id'     => 'Coinbase',
                'id'          => self::API_KEY,
                'element'     => 'text',
                'label'       => $this->_('Coinbase Commerce API Key'),
                'description' => $translate->_('Enter your API key. An API key can be created from <a href="https://commerce.coinbase.com/dashboard/settings" target="_blank">https://commerce.coinbase.com/dashboard/settings</a>.<br>'
                        . 'To update the status of the payment, please create a webhook subscription using the below notification URL: <br>')
                    . '<em>' . $this->getIpnUrl() . '</em>',
                'attributes'  => array(
                    'class' => 'form-control input-medium',
                ),
            ),
        );
    }

    public function formElements()
    {
        ApiClient::init($this->_data[self::API_KEY]);

        $transactionsService = new Service\Transactions();
        /** @var \Ppb\Db\Table\Row\Transaction $transaction */
        $transaction = $transactionsService->findBy('id', $this->getTransactionId());

        $chargeObj = $this->_createCharge($transaction);

        if ($chargeObj->hosted_url) {
            $this->setPostUrl($chargeObj->hosted_url);
        }

        return array();
    }

    /**
     *
     * get gateway post url
     *
     * @return string
     */
    public function getPostUrl()
    {
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
     * process ipn
     *
     * @param \Cube\Controller\Request\AbstractRequest $request
     *
     * @return bool      return true if ipn returns a valid transaction
     */
    public function processIpn(AbstractRequest $request)
    {
        $errno = null;
        $errstr = null;

        $response = false;

        ApiClient::init($this->_data[self::API_KEY]);

        $transactionsService = new Service\Transactions();

        try {
            $allEvents = Event::getAll();
            foreach ($allEvents as $event) {
                if ($event->type == 'charge:confirmed') {
                    /** @var \Ppb\Db\Table\Row\Transaction $transaction */
                    $transaction = $transactionsService->findBy('code', $event->data->code);

                    if ($transaction !== null) {
                        $response = true;
                        $this->setTransactionId($transaction['id'])
                            ->setAmount($transaction['amount'])
                            ->setCurrency($transaction['currency'])
                            ->setGatewayPaymentStatus($event->type)
                            ->setGatewayTransactionCode($event->id);
                    }
                }

            }
        } catch (\Exception $exception) {
        }

        return $response;
    }

    /**
     *
     * create charge object or retrieve it if it already exists
     *
     * @param TransactionModel $transaction
     *
     * @return array|mixed|null
     */
    protected function _createCharge(TransactionModel $transaction)
    {
        ApiClient::init($this->_data[self::API_KEY]);

        $chargeId = $transaction->getData('coinbase_commerce_charge_id');

        $chargeObj = null;
        if ($chargeId) {
            try {
                $chargeObj = Charge::retrieve($chargeId);
            } catch (\Exception $e) {
            }
        }

        if ($chargeObj === null) {
            $chargeData = array(
                'name'         => $this->getName(),
                'description'  => $this->getName(),
                'local_price'  => array(
                    'amount'   => $this->getAmount(),
                    'currency' => $this->getCurrency()
                ),
                'metadata'     => array(
                    'transaction_id' => $this->getTransactionId(),
                ),
                'pricing_type' => 'fixed_price'
            );

            $chargeObj = Charge::create($chargeData);

            $transaction->save(array(
                'coinbase_commerce_charge_id' => $chargeObj->code
            ));
        }

        return $chargeObj;
    }
}

