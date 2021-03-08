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
 * stripe gateway model class
 */
/**
 * MOD:- STRIPE GATEWAY INTEGRATION
 * @version 1.3
 */

namespace Ppb\Model\PaymentGateway;

use Cube\Controller\Request\AbstractRequest,
    Cube\Controller\Front,
    Ppb\Service;

class Stripe extends AbstractPaymentGateway
{
    /**
     * payment gateway name
     */

    const NAME = 'Stripe';

    /**
     * required settings
     */
    const SECRET_KEY = 'secret_key';
    const PUBLISHABLE_KEY = 'publishable_key';

    /**
     *
     * do not redirect if selected in cart
     *
     * @var bool
     */
    public static $automaticRedirect = false;

    /**
     * skrill description
     */
    protected $_description = '';

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

        /** @var \Cube\View\Helper\Script $scriptHelper */
        $scriptHelper = $this->_view->getHelper('script');
        $scriptHelper->addHeaderCode('<script type="text/javascript" src="https://js.stripe.com/v2/"></script>');
    }

    /**
     *
     * check if the gateway is enabled
     *
     * @return bool
     */
    public function enabled()
    {
        if (!empty($this->_data[self::SECRET_KEY]) && !empty($this->_data[self::PUBLISHABLE_KEY])) {
            return true;
        }

        return false;
    }

    /**
     *
     * get setup form elements
     *
     * @return array
     */
    public function getElements()
    {
        $translate = $this->getTranslate();

        return array(
            array(
                'form_id'     => 'Stripe',
                'id'          => self::SECRET_KEY,
                'element'     => 'text',
                'label'       => $this->_('Stripe Secret Key'),
                'description' => $this->_('Enter your Stripe secret key.'),
                'attributes'  => array(
                    'class' => 'form-control input-medium',
                ),
            ),
            array(
                'form_id'     => 'Stripe',
                'id'          => self::PUBLISHABLE_KEY,
                'element'     => 'text',
                'label'       => $this->_('Stripe Publishable Key'),
                'description' => $translate->_('Enter your Stripe publishable key.'),
                'attributes'  => array(
                    'class' => 'form-control input-medium',
                ),
            ),
        );
    }

    /**
     * @return array
     */
    public function formElements()
    {
        /** @var \Cube\View\Helper\Script $scriptHelper */
        $scriptHelper = $this->_view->getHelper('script');
        $scriptHelper->addBodyCode('<script type="text/javascript">Stripe.setPublishableKey("' . $this->_data[self::PUBLISHABLE_KEY] . '");</script>')
            ->addBodyCode('<script type="text/javascript" src="' . $this->_view->baseUrl . '/js/stripe.js"></script>');

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
                'id'      => 'currency',
                'value'   => $this->getCurrency(),
                'element' => 'hidden',
            ),
            array(
                'id'      => 'description',
                'value'   => $this->getName(),
                'element' => 'hidden',
            ),
            array(
                'id'          => 'card_number',
                'element'     => '\\Ppb\\Form\\Element\\CardNumber',
                'label'       => $this->_('Card Number'),
                'description' => $this->_('Enter the number without spaces or hyphens.'),
                'attributes'  => array(
                    'class' => 'form-control input-medium card-number',
                ),
            ),
            array(
                'id'         => 'card_exp_date',
                'element'    => '\\Ppb\\Form\\Element\\CardDate',
                'label'      => $this->_('Expiration (mm/yyyy)'),
                'attributes' => array(
                    'class' => 'form-control input-mini card-date',
                ),
            ),
            array(
                'id'         => 'card_cvc',
                'element'    => '\\Ppb\\Form\\Element\\CardNumber',
                'label'      => $this->_('CVC'),
                'attributes' => array(
                    'class' => 'form-control input-mini card-cvc',
                ),
            ),
        );


    }

    public function getPostUrl()
    {
        return $this->getIpnUrl();
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

        if ($request->isPost()) {
            $this->setTransactionId($request->getParam('transaction_id'));

            require_once(__DIR__ . '/../../../External/Stripe/lib/Stripe.php');

            // set your secret key: remember to change this to your live secret key in production
            // see your keys here https://manage.stripe.com/account
            \Stripe::setApiKey($this->_data[self::SECRET_KEY]);

            $amount = $request->getParam('amount') * 100;

            $success = false;
            $charge = null;


            try {
                // Charge the order:
                $charge = \Stripe_Charge::create(array(
                        "amount"      => $amount, // amount in cents, again
                        "currency"    => $request->getParam('currency'),
                        "card"        => $request->getParam('stripeToken'),
                        "description" => $request->getParam('description'),
                    )
                );

                $success = true;

            } catch (\Stripe_CardError $e) {
                $this->setGatewayPaymentStatus('Failed');
            }

            if ($success == true) {
                // Check that it was paid:
                if ($charge->paid == true) {
                    $response = true;

                    $this->setGatewayPaymentStatus('Paid')
                        ->setAmount($charge->amount / 100)
                        ->setCurrency(
                            strtoupper($charge->currency))
                        ->setGatewayTransactionCode($charge->id);
                }
                else {
                    $this->setGatewayPaymentStatus('Failed');
                }
            }

        }

        return $response;
    }
}

