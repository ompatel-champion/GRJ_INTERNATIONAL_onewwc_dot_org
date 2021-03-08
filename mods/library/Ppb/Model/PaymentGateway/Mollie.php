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
 * mollie gateway model class
 */
/**
 * MOD:- MOLLIE GATEWAY INTEGRATION
 *
 * @version 1.1
 */

namespace Ppb\Model\PaymentGateway;

use Cube\Controller\Request\AbstractRequest,
    Cube\Controller\Front,
    Ppb\Service\Table\Currencies as CurrenciesService,
    Ppb\Service;

class Mollie extends AbstractPaymentGateway
{
    /**
     * payment gateway name
     */

    const NAME = 'Mollie';

    /**
     * required settings
     */
    const API_KEY = 'api_key';

    /**
     * only accepts euro amounts
     */
    const ACCEPTED_CURRENCY = 'EUR';

    /**
     * mollie description
     */
    protected $_description = 'Click to pay through Mollie. The amount to be paid is %s';

    /**
     *
     * view object
     *
     * @var \Cube\View
     */
    protected $_view;

    public function __construct($userId = null)
    {
        parent::__construct(self::NAME, $userId);

        $this->_view = Front::getInstance()->getBootstrap()->getResource('view');
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
     * set transaction amount
     * convert all amounts to EUR before going to the payment page
     *
     * @param string $amount
     *
     * @throws \RuntimeException
     * @return $this
     */
    public function setAmount($amount)
    {
        $currency = $this->getCurrency();

        if (empty($currency)) {
            $translate = $this->getTranslate();

            throw new \RuntimeException($translate->_("Please set the currency before setting the amount."));
        }

        if ($currency != self::ACCEPTED_CURRENCY) {
            $currenciesService = new CurrenciesService();
            $amount = $currenciesService->convertAmount($amount, $currency, self::ACCEPTED_CURRENCY);
            $this->setCurrency(self::ACCEPTED_CURRENCY);
        }

        parent::setAmount($amount);

        return $this;
    }

    /**
     *
     * get setup form elements
     *
     * @return array
     */
    public function getElements()
    {
        return array(
            array(
                'form_id'     => 'Mollie',
                'id'          => self::API_KEY,
                'element'     => 'text',
                'label'       => $this->_('Mollie API Key'),
                'description' => $this->_('Enter your Mollie account API key.'),
                'attributes'  => array(
                    'class' => 'form-control input-medium',
                ),
            ),
        );
    }

    /**
     *
     * get description - translate first
     *
     * @return string
     */
    public function getDescription()
    {
        return sprintf($this->_description, $this->_view->amount($this->getAmount(), $this->getCurrency()));
    }

    /**
     * @return array
     */
    public function formElements()
    {
        require_once __DIR__ . "/../../../External/Mollie/API/Autoloader.php";

        $mollie = new \Mollie_API_Client;
        $mollie->setApiKey($this->_data[self::API_KEY]);

        $issuers = $mollie->issuers->all();

        $multiOptions = array();

        foreach ($issuers as $issuer) {
//            if ($issuer->method == \Mollie_API_Object_Method::IDEAL) {
            $key = htmlspecialchars($issuer->id);
            $value = htmlspecialchars($issuer->name);

            $multiOptions[$key] = $value;
//            }
        }

        return array(
            array(
                'id'      => 'transaction_id',
                'value'   => $this->getTransactionId(),
                'element' => 'hidden',
            ),
            array(
                'id'      => 'amount',
                'value'   => $this->getAmount(),
                'element' => 'hidden',
            ),
            array(
                'id'      => 'description',
                'value'   => $this->getName(),
                'element' => 'hidden',
            ),
            array(
                'id'      => self::API_KEY,
                'value'   => $this->_data[self::API_KEY],
                'element' => 'hidden',
            ),
            array(
                'id'      => 'webhook_url',
                'value'   => $this->getIpnUrl(),
                'element' => 'hidden',
            ),
            array(
                'id'      => 'redirect_url',
                'value'   => $this->getSuccessUrl(),
                'element' => 'hidden',
            ),
//            array(
//                'id'           => 'issuer',
//                'element'      => 'select',
//                'label'        => $this->_('Provider'),
////                'description'  => $this->_('Select iDEAL provider.'),
//                'attributes'   => array(
//                    'class' => 'form-control input-medium',
//                ),
//                'multiOptions' => $multiOptions,
//            ),
        );


    }

    public function getPostUrl()
    {
        return $this->getView()->url(array(
            'module'     => parent::MODULE,
            'controller' => parent::CONTROLLER,
            'action'     => strtolower($this->getGatewayName()),
        ));
    }

    /**
     *
     * always return true
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
     * @return bool      return true if ipn returns a valid transaction
     */
    public function processIpn(AbstractRequest $request)
    {
        $errno = null;
        $errstr = null;

        $response = false;

        $molliePaymentId = $request->getParam('id');

        require_once __DIR__ . "/../../../External/Mollie/API/Autoloader.php";

        $mollie = new \Mollie_API_Client;
        $mollie->setApiKey($this->_data[self::API_KEY]);

        $payment = $mollie->payments->get($molliePaymentId);

        $this->setTransactionId(
            $payment->metadata->order_id);

        if ($payment->isPaid()) {
            $response = true;

            $this->setGatewayPaymentStatus('Paid')
                ->setCurrency(self::ACCEPTED_CURRENCY)
                ->setAmount($payment->amount)
                ->setGatewayTransactionCode($payment->id);
        }
        else {
            $this->setGatewayPaymentStatus('Failed');
        }

        return $response;
    }
}

