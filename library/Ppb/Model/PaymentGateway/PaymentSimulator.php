<?php

/**
 *
 * PHP Pro Bid
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2019 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     8.2 [rev.8.2.01]
 */
/**
 * payment simulator gateway model class
 */

namespace Ppb\Model\PaymentGateway;

use Cube\Controller\Request\AbstractRequest;

class PaymentSimulator extends AbstractPaymentGateway
{
    /**
     * payment gateway name
     */

    const NAME = 'PaymentSimulator';

    /**
     *
     * do not redirect if selected in cart
     *
     * @var bool
     */
    public static $automaticRedirect = false;

    /**
     * payment simulator description
     */
    protected $_description = 'Payment Simulator description.';

    public function __construct($userId = null)
    {
        parent::__construct(self::NAME, $userId);
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
     * process ipn
     *
     * @param \Cube\Controller\Request\AbstractRequest $request
     * @return bool      return true if ipn is valid (for the simulator it will always be true
     */
    public function processIpn(AbstractRequest $request)
    {
        if ($request->isPost()) {
            $this->setTransactionId($_POST['transaction_id'])
                    ->setGatewayPaymentStatus('Completed')
                    ->setGatewayTransactionCode('SimulatorTXN');

            return true;
        }

        return false;
    }

    /**
     *
     * for the payment simulator, this will always be true
     *
     * @param float  $amount
     * @param string $currency
     * @return bool
     */
    public function checkIpnAmount($amount, $currency)
    {
        return true;
    }
}

