<?php

/**
 *
 * PHP Pro Bid
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2020 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     8.2 [rev.8.2.02]
 */

/**
 * authorize.net payment gateway model class
 *
 * Accept Hosted Solution
 * https://developer.authorize.net/api/reference/features/accept_hosted.html
 * https://developer.authorize.net/api/reference/index.html#accept-suite-get-an-accept-payment-page
 *
 * TODO: not getting a transId variable for the ipn to work in sandbox.
 */

namespace Ppb\Model\PaymentGateway;

use Cube\Controller\Request\AbstractRequest,
    Ppb\Service;
use net\authorize\api\contract\v1 as AnetAPI;
use net\authorize\api\controller as AnetController;

class AuthorizeNet extends AbstractPaymentGateway
{
    /**
     * payment gateway name
     */

    const NAME = 'AuthorizeNet';

    /**
     * required settings
     */
    const MERCHANT_ID = 'x_login';
    const TRANSACTION_KEY = 'authnet_transaction_key';

    const POST_URL = 'https://accept.authorize.net/payment/payment';
    const SANDBOX_POST_URL = 'https://test.authorize.net/payment/payment';

    /**
     *
     * description
     *
     * @var string
     */
    protected $_description = 'Click to pay through Authorize.net.';

    protected $_ipnCodes = array(
        1 => 'Approved',
        2 => 'Declined',
        3 => 'Error',
        4 => 'Held for Review',
    );

    /**
     * accepted currency - all payments will be converted before processing payment
     */
    const ACCEPTED_CURRENCY = 'USD';

    /**
     *
     * stripe payment form custom partial
     *
     * @var string
     */
    protected $_paymentFormPartial = 'forms/authorize-net-payment.phtml';

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
        if (!empty($this->_data[self::MERCHANT_ID]) && !empty($this->_data[self::TRANSACTION_KEY])) {
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
        return array(
            array(
                'form_id'     => 'AuthorizeNet',
                'id'          => self::MERCHANT_ID,
                'element'     => 'text',
                'label'       => $this->_('Authorize.net Merchant ID'),
                'description' => $this->_('Enter your merchant ID'),
                'attributes'  => array(
                    'class' => 'form-control input-medium',
                ),
            ),
            array(
                'form_id'     => 'AuthorizeNet',
                'id'          => self::TRANSACTION_KEY,
                'element'     => 'text',
                'label'       => $this->_('Authorize.net Transaction Key'),
                'description' => $this->_('Enter your merchant transaction key'),
                'attributes'  => array(
                    'class' => 'form-control input-medium',
                ),
            ),
        );
    }

    public function formElements()
    {
        require APPLICATION_PATH . '/vendor/AuthorizeNet/autoload.php';

        $translate = $this->getTranslate();

        $currency = $this->getCurrency();

        $transactionsService = new Service\Transactions();
        $transaction = $transactionsService->findBy('id', $this->getTransactionId());

        if ($currency != self::ACCEPTED_CURRENCY) {
            $amount = $this->getAmount();

            $currenciesService = new Service\Table\Currencies();
            $convertedAmount = $currenciesService->convertAmount($amount, $currency, self::ACCEPTED_CURRENCY);

            $this->setCurrency(self::ACCEPTED_CURRENCY)
                ->setAmount($convertedAmount);

            $transaction->save(array(
                'amount'   => $convertedAmount,
                'currency' => self::ACCEPTED_CURRENCY,
            ));


            $this->_description .= '<br>'
                . sprintf($translate->_('Converted Amount: <strong>%s</strong>'), $this->getView()->amount($convertedAmount, self::ACCEPTED_CURRENCY));
        }

        $view = $this->getView();

        /** @var \Cube\View\Helper\Script $scriptHelper */
        $scriptHelper = $view->getHelper('script');
        $scriptHelper->addHeaderCode('<link href="' . $this->_view->baseUrl . '/mods/css/stripe.css" rel="stylesheet" type="text/css">')
            ->addHeaderCode('<script type="text/javascript">
                    $(function () {
                        $("#btnOpenAuthorizeNetIFrame").click(function () {
                            bootbox.dialog({
                                message: \'<div id="iframe_holder" style="width:100%;"><iframe id="add_payment" class="embed-responsive-item panel" name="add_payment" width="100%" height="600" frameborder="0" scrolling="no" style="display: none;"></iframe></div>\',
                                size: "large"
                            });
                            
                            $("#add_payment").show();
                            $("#send_token").prop({ "action": "' . $this->getPostUrl() . '", "target": "add_payment" }).submit();
                        });
                    });
                </script>')
            ->addBodyCode('<script type="text/javascript">
                    (function () {
                        if (!window.CommunicationHandler) window.CommunicationHandler = {};
                        
                        function parseQueryString(str) {
                            var vars = [];
                            var arr = str.split(\'&\');
                            var pair;
                            for (var i = 0; i < arr.length; i++) {
                                pair = arr[i].split(\'=\');
                                vars[pair[0]] = unescape(pair[1]);
                            }
                            return vars;
                        }
                                               
                        CommunicationHandler.onReceiveCommunication = function (argument) {
                            params = parseQueryString(argument.qstr)
                            
                            switch (params["action"]) {
                                case "successfulSave":
                                    break;
                                case "cancel":
                                    break;
                                case "resizeWindow":
                                    var w = parseInt(params["width"]);
                                    var h = parseInt(params["height"]);
                                    var ifrm = document.getElementById("add_payment");
                                    ifrm.style.width = w.toString() + "px";
                                    ifrm.style.height = h.toString() + "px";
                                    break;
                                case "transactResponse":
                                    var transResponse = JSON.parse(params[\'response\']);
                                    
                                    if (transResponse.responseCode === "1") {
                                        window.location.replace("' . $this->getIpnUrl() . '&transId=" + transResponse.transId + "&refId=" + transResponse.refId);
                                    }
                                    else {
                                        window.location.replace("' . $this->getFailureUrl() . '");                                        
                                    }
                                    break;
                            }
                        };
                    }());
                </script>');

        /**
         * Create a merchantAuthenticationType object with authentication details
         * retrieved from the constants file
         */
        $merchantAuthentication = new AnetAPI\MerchantAuthenticationType();
        $merchantAuthentication->setName($this->_data[self::MERCHANT_ID]);
        $merchantAuthentication->setTransactionKey($this->_data[self::TRANSACTION_KEY]);

        // Set the transaction's refId
        $refId = $this->getTransactionId();

        //create a transaction
        $transactionRequestType = new AnetAPI\TransactionRequestType();
        $transactionRequestType->setTransactionType("authCaptureTransaction");
        $transactionRequestType->setAmount($this->getAmount())
            ->setCurrencyCode($this->getCurrency());

        // Set Hosted Form options
        $setting1 = new AnetAPI\SettingType();
        $setting1->setSettingName("hostedPaymentButtonOptions");
        $setting1->setSettingValue("{\"text\": \"Pay\"}");

        $setting2 = new AnetAPI\SettingType();
        $setting2->setSettingName("hostedPaymentOrderOptions");
        $setting2->setSettingValue("{\"show\": false}");

        $setting3 = new AnetAPI\SettingType();
        $setting3->setSettingName("hostedPaymentReturnOptions");
        $setting3->setSettingValue(
            "{\"url\": \"" . $this->getIpnUrl() . "\", \"cancelUrl\": \"" . $this->getFailureUrl() . "\", \"showReceipt\": false}"
        );

        $setting4 = new AnetAPI\SettingType();
        $setting4->setSettingName("hostedPaymentIFrameCommunicatorUrl");
        $setting4->setSettingValue("{\"url\": \"" . $this->getView()->url('vendor/AuthorizeNet/IFrameCommunicator.html') . "\"}");

        // Build transaction request
        $request = new AnetAPI\GetHostedPaymentPageRequest();
        $request->setMerchantAuthentication($merchantAuthentication);
        $request->setRefId($refId);
        $request->setTransactionRequest($transactionRequestType);

        $request->addToHostedPaymentSettings($setting1);
        $request->addToHostedPaymentSettings($setting2);
        $request->addToHostedPaymentSettings($setting3);
        $request->addToHostedPaymentSettings($setting4);

        //execute request
        $controller = new AnetController\GetHostedPaymentPageController($request);
        $response = $controller->executeWithApiResponse(\net\authorize\api\constants\ANetEnvironment::SANDBOX);

        if (($response != null) && ($response->getMessages()->getResultCode() == "Ok")) {
            // This is an encrypted string that the merchant must include when posting to the Authorize.Net web page.
            // If not used within 15 minutes of the original API call, this token expires.
            return array(

                array(
                    'id'      => 'token',
                    'element' => 'hidden',
                    'value'   => $response->getToken(),
                ),
            );
        }
        else {
            $errorMessages = $response->getMessages()->getMessage();
            $errorMessage = "ERROR :  Failed to get hosted payment page token. <br>"
                . "RESPONSE : " . $errorMessages[0]->getCode() . "  " . $errorMessages[0]->getText();

            return array(
                array(
                    'id'      => 'authorize_net_payment_form',
                    'element' => 'description',
                    'value'   => '<div class="alert alert-danger">' . $translate->_($errorMessage) . '</div>',
                ),
            );

        }
    }

    /**
     *
     * get the form post url (live or sandbox)
     *
     * @return string
     */
    public function getPostUrl()
    {
        return $this->_isSandbox() ? self::SANDBOX_POST_URL : self::POST_URL;
    }

    /**
     *
     * get ipn url
     *
     * @return string
     */
    public function getIpnUrl()
    {
        $params = self::$ipnUrl;
        $params['gateway'] = strtolower($this->getGatewayName());
        if ($transactionId = $this->getTransactionId()) {
            $params['transaction_id'] = $transactionId;
        }

        return $this->getView()->url($params);
    }

    /**
     *
     * process ipn
     *
     * @param \Cube\Controller\Request\AbstractRequest $request
     *
     * @return bool
     */
    public function processIpn(AbstractRequest $request)
    {
        $response = false;

        require APPLICATION_PATH . '/vendor/AuthorizeNet/autoload.php';

        /* Create a merchantAuthenticationType object with authentication details
       retrieved from the constants file */
        $merchantAuthentication = new AnetAPI\MerchantAuthenticationType();
        $merchantAuthentication->setName($this->_data[self::MERCHANT_ID]);
        $merchantAuthentication->setTransactionKey($this->_data[self::TRANSACTION_KEY]);

        // Set the transaction's refId
        // The refId is a Merchant-assigned reference ID for the request.
        // If included in the request, this value is included in the response.
        // This feature might be especially useful for multi-threaded applications.

        $anetRequest = new AnetAPI\GetTransactionDetailsRequest();
        $anetRequest->setMerchantAuthentication($merchantAuthentication);
        $anetRequest->setRefId($request->getParam('refId'))
            ->setTransId(strval($request->getParam('transId')));

        $controller = new AnetController\GetTransactionDetailsController($anetRequest);

        $endPoint = $this->_isSandbox() ?
            \net\authorize\api\constants\ANetEnvironment::SANDBOX : \net\authorize\api\constants\ANetEnvironment::PRODUCTION;

        $anetResponse = $controller->executeWithApiResponse($endPoint);

        $this->setTransactionId($request->getParam('refId'));

        if (($anetResponse != null) && ($anetResponse->getMessages()->getResultCode() == "Ok")) {
            $this->setAmount($anetResponse->getTransaction()->getAuthAmount())
                ->setCurrency('USD')
                ->setGatewayPaymentStatus($anetResponse->getTransaction()->getTransactionStatus());

            $response = true;
        }
        else {

            $errorMessage = "ERROR: Invalid response" . '<br>';
            $errorMessages = $anetResponse->getMessages()->getMessage();
            $errorMessage .= "Response: " . $errorMessages[0]->getCode() . "  " . $errorMessages[0]->getText();
            $this->setGatewayPaymentStatus($errorMessage);
        }

        return $response;
    }

    /**
     *
     * check if sandbox mode is enabled
     *
     * @return bool
     */
    protected function _isSandbox()
    {
        return false;
    }
}

