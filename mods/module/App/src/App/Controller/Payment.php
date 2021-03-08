<?php

/**
 *
 * PHP Pro Bid
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2020 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     8.2 [rev.8.2.04]
 */

/**
 * payment forms controller
 */
/**
 * MOD:- SELLERS CREDIT
 * MOD:- ESCROW PAYMENTS
 *
 * @version 1.1
 *
 * MOD:- ESCROW & BANK TRANSFERS
 * MOD:- ADVANCED CLASSIFIEDS
 * MOD:- MOLLIE GATEWAY INTEGRATION
 */

namespace App\Controller;

use Ppb\Controller\Action\AbstractAction,
    Cube\Controller\Front,
    Cube\Locale\Format as LocaleFormat,
    Ppb\Service,
    Ppb\Db\Table\Row\Listing as ListingModel,
    Ppb\Db\Table\Row\Transaction as TransactionModel,
    Ppb\Db\Table\Row\User as UserModel,
    Ppb\Db\Table\Row\Sale as SaleModel,
    App\Form;
## -- ADD 1L -- [ MOD:- ESCROW & BANK TRANSFERS ]
use Ppb\Model\PaymentGateway\BankTransfer as BankTransferModel;
## -- ADD 1L -- [ MOD:- MOLLIE GATEWAY INTEGRATION ]
use Ppb\Model\PaymentGateway\Mollie as MollieModel;

class Payment extends AbstractAction
{

    /**
     *
     * view object
     *
     * @var \Cube\View
     */
    protected $_view;

    /**
     *
     * transactions service
     *
     * @var \Ppb\Service\Transactions
     */
    protected $_transactions;

    /**
     *
     * payment gateways service
     *
     * @var \Ppb\Service\Table\PaymentGateways
     */
    protected $_paymentGateways;

    public function init()
    {
        $bootstrap = Front::getInstance()->getBootstrap();

        $this->_view = $bootstrap->getResource('view');
        $this->_view->setViewFileName('generic-page.phtml');

        $this->_transactions = new Service\Transactions();
        $this->_paymentGateways = new Service\Table\PaymentGateways();
    }

    public function Index()
    {
        return array();
    }

    /**
     *
     * user registration signup fee action
     *
     * @return array
     */
    public function UserSignup()
    {
        $paymentDescription = null;
        $gatewayForms = null;

        $translate = $this->getTranslate();

        $id = (!empty($this->_user['id'])) ?
            $this->_user['id'] : $this->getRequest()->getParam('id');
        $usersService = new Service\Users();

        $user = $usersService->findBy('id', (int)$id);

        if ($user instanceof UserModel) {
            if ($user->canPaySignupFee()) {
                $feesService = new Service\Fees\UserSignup();
                $feesService->setUser($user);

                $totalAmount = $feesService->getTotalAmount();

                if ($totalAmount > 0) {
                    $paymentDescription = sprintf(
                        $translate->_('Please use one of the payment gateways listed below to pay the user signup fee of %s'),
                        $this->_view->amount($totalAmount));

                    $transaction = array(
                        'name'                => array(
                            'string' => 'User Signup Fee - User ID: #%s',
                            'args'   => array($id),
                        ),
                        'amount'              => $totalAmount,
                        'tax_rate'            => $feesService->getTaxType()->getData('amount'),
                        'currency'            => $this->_settings['currency'],
                        'user_id'             => $id,
                        'transaction_details' => serialize(array(
                            'class' => '\\Ppb\\Service\\Fees\\UserSignup',
                            'data'  => array(
                                'user_id' => $id,
                            ),
                        )),
                    );

                    $transactionId = $this->_transactions->save($transaction);

                    $gatewayForms = $this->_prepareForms($transactionId, null, null, $user);
                }
                else {
                    $this->_flashMessenger->setMessage(array(
                        'msg'   => sprintf(
                            $translate->_("User #%s - '%s', has been activated. There were no fees to be paid."),
                            $user['id'], $user['username']),
                        'class' => 'alert-success',
                    ));

                    $user->save(array(
                        'active'         => 1,
                        'payment_status' => 'confirmed',
                    ));

                    $gatewayForms = array();
                }
            }
        }

        if ($gatewayForms === null) {
            $this->_flashMessenger->setMessage(array(
                'msg'   => $this->_('Error: Could not generate the payment form - the user does not exist or there is no signup fee to be paid.'),
                'class' => 'alert-danger',
            ));
        }

        return array(
            'headline'           => sprintf($translate->_('Payment - User Signup - ID: #%s'), $id),
            'messages'           => $this->_flashMessenger->getMessages(),
            'paymentDescription' => $paymentDescription,
            'gatewayForms'       => $gatewayForms,
        );
    }

    /**
     *
     * listing setup fee action
     *
     * @return array
     */
    public function ListingSetup()
    {
        $paymentDescription = null;
        $gatewayForms = null;

        $translate = $this->getTranslate();

        $id = (int)$this->getRequest()->getParam('id');
        $listingsService = new Service\Listings();

        $listing = $listingsService->findBy('id', (int)$id, false, true);

        if ($listing instanceof ListingModel) {
            ## -- START :: CHANGE -- [ MOD:- ADVANCED CLASSIFIEDS ]
            if ($listing->isOwner() && $listing['active'] == 0 && !$listing->isClassified()) {
            ## -- END :: CHANGE -- [ MOD:- ADVANCED CLASSIFIEDS ]
            $listingSetupService = new Service\Fees\ListingSetup(
                $listing, $this->_user);

            $savedListing = \Ppb\Utility::unserialize($listing['rollback_data']);
            if ($savedListing instanceof ListingModel) {
                $listingSetupService->setSavedListing($savedListing);
            }

            $listingSetupService->setVoucher(
                $listing->getData('voucher_code'));

            $listingFees = $listingSetupService->calculate();

            $totalAmount = $listingSetupService->getTotalAmount();
            $userPaymentMode = $this->_user->userPaymentMode();

            if ($userPaymentMode == 'live' && $totalAmount > 0) {
                $paymentDescription = sprintf(
                    $translate->_('Please use one of the payment gateways listed below to pay the listing fee of %s'),
                    $this->_view->amount($totalAmount));

                $transaction = array(
                    'name'                => array(
                        'string' => 'Listing Setup Fee - Listing ID: #%s',
                        'args'   => array($id),
                    ),
                    'amount'              => $totalAmount,
                    'tax_rate'            => $listingSetupService->getTaxType()->getData('amount'),
                    'currency'            => $this->_settings['currency'],
                    'user_id'             => $listing['user_id'],
                    'transaction_details' => serialize(array(
                        'class' => '\\Ppb\\Service\\Fees\\ListingSetup',
                        'data'  => array(
                            'listing_id' => $listing['id'],
                        ),
                    )),
                );

                $transactionId = $this->_transactions->save($transaction);

                $gatewayForms = $this->_prepareForms($transactionId);
            }
            else {
                $message = $listing->processPostSetupActions();

                if ($message) {
                    $this->_flashMessenger->setMessage(array(
                        'msg'   => $message,
                        'class' => 'alert-info',
                    ));
                }
                else {
                    $this->_flashMessenger->setMessage(array(
                        'msg'   => sprintf(
                            $translate->_("Listing #%s - '%s', has been activated. There were no fees to be paid."),
                            $listing['id'], $listing['name']),
                        'class' => 'alert-success',
                    ));
                }

                $gatewayForms = array();
            }
        }
        }
        else {
            $this->_flashMessenger->setMessage(array(
                'msg'   => $this->_('Error: Could not generate the payment form, the listing does not exist.'),
                'class' => 'alert-danger',
            ));
        }


        return array(
            'headline'           => sprintf($translate->_('Payment - Listing Setup - ID: #%s'), $id),
            'messages'           => $this->_flashMessenger->getMessages(),
            'paymentDescription' => $paymentDescription,
            'gatewayForms'       => $gatewayForms,
        );
    }

    ## -- START :: ADD -- [ MOD:- ADVANCED CLASSIFIEDS ]
    /**
     *
     * classified setup fee action
     *
     * @return array
     */
    public function ClassifiedSetup()
    {
        $paymentDescription = null;
        $gatewayForms = null;

        $translate = $this->getTranslate();

        $id = (int)$this->getRequest()->getParam('id');
        $listingsService = new Service\Listings();

        $listing = $listingsService->findBy('id', (int)$id, false, true);

        if ($listing->isOwner() && $listing['active'] == 0 && $listing->isClassified()) {
            $listingSetupService = new Service\Fees\ClassifiedSetup(
                $listing, $this->_user);

            $savedListing = \Ppb\Utility::unserialize($listing['rollback_data']);
            if ($savedListing instanceof ListingModel) {
                $listingSetupService->setSavedListing($savedListing);
            }

            $listingFees = $listingSetupService->calculate();

            $totalAmount = $listingSetupService->getTotalAmount();
            $userPaymentMode = $this->_user->userPaymentMode();

            if ($userPaymentMode == 'live' && $totalAmount > 0) {
                $paymentDescription = sprintf(
                    $translate->_('Please use one of the payment gateways listed below to pay the classified setup fee of %s'),
                    $this->_view->amount($totalAmount));

                $transaction = array(
                    'name'                => array(
                        'string' => 'Classified Setup Fee - Listing ID: #%s',
                        'args'   => array($id),
                    ),
                    'amount'              => $totalAmount,
                    'tax_rate'            => $listingSetupService->getTaxType()->getData('amount'),
                    'currency'            => $this->_settings['currency'],
                    'user_id'             => $listing['user_id'],
                    'transaction_details' => serialize(array(
                        'class' => '\\Ppb\\Service\\Fees\\ClassifiedSetup',
                        'data'  => array(
                            'listing_id' => $listing['id'],
                        ),
                    )),
                );

                $transactionId = $this->_transactions->save($transaction);

                $gatewayForms = $this->_prepareForms($transactionId);
            }
            else {
                $message = $listing->processPostSetupActions();

                if ($message) {
                    $this->_flashMessenger->setMessage(array(
                        'msg'   => $message,
                        'class' => 'alert-info',
                    ));
                }
                else {
                    $this->_flashMessenger->setMessage(array(
                        'msg'   => sprintf(
                            $translate->_("Classified #%s - '%s', has been activated. There were no fees to be paid."),
                            $listing['id'], $listing['name']),
                        'class' => 'alert-success',
                    ));
                }

                $gatewayForms = array();
            }
        }


        if ($gatewayForms === null) {
            $this->_flashMessenger->setMessage(array(
                'msg'   => $this->_('Error: Could not generate the payment form - the listing does not exist or there are no fees to be paid.'),
                'class' => 'alert-danger',
            ));
        }

        return array(
            'headline'           => sprintf($translate->_('Payment - Classified Setup - ID: #%s'), $id),
            'messages'           => $this->_flashMessenger->getMessages(),
            'paymentDescription' => $paymentDescription,
            'gatewayForms'       => $gatewayForms,
        );
    }
    ## -- END :: ADD -- [ MOD:- ADVANCED CLASSIFIEDS ]

    public function SaleTransaction()
    {
        $paymentDescription = null;
        $gatewayForms = null;

        $translate = $this->getTranslate();

        $id = (int)$this->getRequest()->getParam('id');
        $salesService = new Service\Sales();

        /** @var \Ppb\Db\Table\Row\Sale $sale */
        $sale = $salesService->findBy('id', (int)$id);

        if ($sale instanceof SaleModel) {
        if ($sale->canPayFee()) {
            $saleTransactionService = new Service\Fees\SaleTransaction(
                $sale, $this->_user);

            $saleFees = $saleTransactionService->calculate();

            $totalAmount = $saleTransactionService->getTotalAmount();
            $userPaymentMode = $this->_user->userPaymentMode();

            if ($userPaymentMode == 'live') {
                if ($totalAmount > 0) {
                    $paymentDescription = sprintf(
                        $translate->_('Please use one of the payment gateways listed below to pay the sale transaction fee of %s'),
                        $this->_view->amount($totalAmount));

                    $transaction = array(
                        'name'                => array(
                            'string' => 'Sale Transaction Fee - Sale ID: #%s',
                            'args'   => array($id),
                        ),
                        'amount'              => $totalAmount,
                        'tax_rate'            => $saleTransactionService->getTaxType()->getData('amount'),
                        'currency'            => $this->_settings['currency'],
                        'user_id'             => $this->_user['id'],
                        'transaction_details' => serialize(array(
                            'class' => '\\Ppb\\Service\\Fees\\SaleTransaction',
                            'data'  => array(
                                'sale_id' => $sale['id'],
                            ),
                        )),
                    );

                    $transactionId = $this->_transactions->save($transaction);

                    $gatewayForms = $this->_prepareForms($transactionId);
                }
                else {
                    $this->_flashMessenger->setMessage(array(
                        'msg'   => sprintf(
                            $translate->_("The sale transaction #%s has been activated. There were no fees to be paid."),
                            $sale['id']),
                        'class' => 'alert-success',
                    ));

                    $sale->updateActive();

                    $gatewayForms = array();
                }
            }
        }
        }

        if ($gatewayForms === null) {
            $this->_flashMessenger->setMessage(array(
                'msg'   => $this->_('Error: Could not generate the payment form - the sale does not exist or there are no fees to be paid.'),
                'class' => 'alert-danger',
            ));
        }

        return array(
            'headline'           => sprintf($translate->_('Payment - Sale Fee - Sale ID: #%s'), $id),
            'messages'           => $this->_flashMessenger->getMessages(),
            'paymentDescription' => $paymentDescription,
            'gatewayForms'       => $gatewayForms,
        );
    }

    /**
     *
     * sale direct payment action
     *
     * @return array
     */
    public function DirectPayment()
    {
        $paymentDescription = $gatewayForms = $gatewayContent = null;

        $translate = $this->getTranslate();

        $id = (int)$this->getRequest()->getParam('id');
        $salesService = new Service\Sales();

        /** @var \Ppb\Db\Table\Row\Sale $sale */
        $select = $salesService->getTable()->select()
            ->where('id = ?', $id);
            ## -- START :: REMOVE -- [ MOD:- ESCROW PAYMENTS ]
//            ->where('buyer_id = ?', $this->_user['id']);
            ## -- END :: REMOVE -- [ MOD:- ESCROW PAYMENTS ]

        ## -- START :: ADD -- [ MOD:- ESCROW PAYMENTS ]
        // MOD:- ESCROW PAYMENTS > NEEDED FOR WHEN THE ADMIN PAYS THE SELLER
        $inAdmin = $this->_loggedInAdmin();

        if (!$inAdmin) {
            $select->where('buyer_id = ?', $this->_user['id']);
        }
        ## -- END :: ADD -- [ MOD:- ESCROW PAYMENTS ]

        $sale = $salesService->getTable()->fetchRow($select);

        if (!$sale) {
            $this->_flashMessenger->setMessage(array(
                'msg'   => $this->_('Error: Could not generate the payment form - the sale does not exist, or you are not the buyer.'),
                'class' => 'alert-danger',
            ));
        }
        else {
            $gatewayContent = $this->_view->partial('partials/sale.phtml',
                array('sale'        => $sale,
                      'type'        => 'bought',
                      'caption'     => true,
                      'postageDesc' => true));

            if (($paymentMethods = $sale->canPayDirectPayment()) !== false) {
                $totalAmount = $sale->calculateTotal();
                ## -- START :: ADD -- [ MOD:- ESCROW PAYMENTS ]
                $enableEscrow = $sale->getData('enable_escrow');
                ## -- END :: ADD -- [ MOD:- ESCROW PAYMENTS ]

                $paymentDescription = sprintf(
                    $translate->_('Please use one of the payment gateways listed below to pay the seller the amount of %s'),
                    $this->_view->amount($totalAmount, $sale['currency']));

                ## -- START :: ADD -- [ MOD:- ESCROW PAYMENTS ]
                $transactionName = 'Direct Payment Purchase - Invoice ID: #%s';
                if ($enableEscrow) {
                    $transactionName = ($inAdmin) ?
                        'Escrow Payment - Admin to Seller - Invoice ID: #%s' : 'Escrow Payment - Buyer to Admin - Invoice ID: #%s';
                }
                ## -- END :: ADD -- [ MOD:- ESCROW PAYMENTS ]
                
                $transaction = array(
                    'name'                => array(
                        ## -- START :: CHANGE -- [ MOD:- ESCROW PAYMENTS ]
                        'string' => $transactionName,
                        ## -- END :: CHANGE -- [ MOD:- ESCROW PAYMENTS ]
                        'args'   => array($id),
                    ),
                    'amount'              => $totalAmount,
                    'currency'            => $sale['currency'],
                    ## -- START :: CHANGE -- [ MOD:- ESCROW PAYMENTS ]
                    'user_id'             => ($inAdmin) ? null : $this->_user['id'],
                    ## -- END :: CHANGE -- [ MOD:- ESCROW PAYMENTS ]
                    'sale_id'             => $sale['id'],
                    ## -- START :: ADD -- [ MOD:- ESCROW PAYMENTS ]
                    'escrow_buyer_admin'  => ($enableEscrow && !$inAdmin) ? true : false,
                    'escrow_admin_seller' => ($enableEscrow && $inAdmin) ? true : false,
                    ## -- END :: ADD -- [ MOD:- ESCROW PAYMENTS ]
                    'transaction_details' => serialize(array(
                        'class' => '\\Ppb\\Service\\Fees\\DirectPayment',
                        'data'  => array(
                            'sale_id' => $sale['id'],
                            ## -- START :: ADD -- [ MOD:- ESCROW PAYMENTS ]
                            'escrow_buyer_admin'  => ($enableEscrow && !$inAdmin) ? true : false,
                            'escrow_admin_seller' => ($enableEscrow && $inAdmin) ? true : false,
                            ## -- END :: ADD -- [ MOD:- ESCROW PAYMENTS ]
                        ),
                    )),
                );

                $transactionId = $this->_transactions->save($transaction);

                ## -- START :: CHANGE -- [ MOD:- ESCROW PAYMENTS ]
                if ($enableEscrow && !$inAdmin) {
                    $sellerId = null;
                    $gatewayIds = null;
                }
                else {
                    $sellerId = $sale['seller_id'];
                    $gatewayIds = array();
                    $paymentMethods = $sale->getPaymentMethods('direct');
                    foreach ($paymentMethods as $paymentMethod) {
                        $gatewayIds[] = $paymentMethod['id'];
                    }
                }
                ## -- END :: CHANGE -- [ MOD:- ESCROW PAYMENTS ]  

                $paymentMethodId = $sale['payment_method_id'];

                $redirect = $this->getRequest()->getParam('redirect');

                if ($redirect && in_array($paymentMethodId, $gatewayIds)) {
                    ## -- CHANGE 1L -- [ MOD:- SELLERS CREDIT ]
                    $gatewayIds[] = $paymentMethodId;
                }

                ## -- CHANGE 1L -- [ MOD:- ESCROW PAYMENTS ]
                $gatewayForms = $this->_prepareForms($transactionId, $sellerId, $gatewayIds);

                ## AUTOMATIC REDIRECT TO SELECTED DIRECT PAYMENT GATEWAY
                if ($redirect) {
                    $query = array();

                    /** @var \App\Form\Payment $form */
                    $form = reset($gatewayForms);
                    /** @var \Cube\Form\Element $element */
                    foreach ($form->getElements() as $element) {
                        $key = $element->getName();
                        $query[$key] = $element->getValue();
                    }

                    $this->_helper->redirector()->gotoUrl(
                        $form->getAction() . '?' . http_build_query($query));
                }
                ## /AUTOMATIC REDIRECT TO SELECTED DIRECT PAYMENT GATEWAY
            }
        }

        return array(
            'headline'           => $this->_('Purchase Details'),
            ## -- START :: ADD -- [ MOD:- ESCROW PAYMENTS ]
            'controller'         => ($inAdmin) ? 'Tools' : 'Payment',
            ## -- END :: ADD -- [ MOD:- ESCROW PAYMENTS ]
            'messages'           => $this->_flashMessenger->getMessages(),
            'gatewayContent'     => $gatewayContent,
            'paymentDescription' => $paymentDescription,
            'gatewayForms'       => $gatewayForms,
        );
    }

    public function CreditBalance()
    {
        $totalAmount = 0;
        $paymentDescription = null;
        $gatewayForms = null;

        $translate = $this->getTranslate();

        if (isset($this->_user['id'])) {
            if ($this->_user->userPaymentMode() == 'account') {

                $amount = LocaleFormat::getInstance()->localizedToNumeric(
                        $this->getRequest()->getParam('amount'), true);
                        
                ## -- ONE LINE :: CHANGE -- [ MOD:- SELLERS CREDIT ]
                if (!$amount) {
                    $amount = $this->_user['balance'];
                }

                $totalAmount = ($amount > 0) ?
                    max(array($amount, $this->_settings['min_invoice_value'])) :
                    max(array($this->_user['balance'], $this->_settings['min_invoice_value']));
            }
        }

        if ($totalAmount > 0) {
            $paymentDescription = sprintf(
                $translate->_('Please use one of the payment gateways listed below to credit your account with the amount of %s'),
                $this->_view->amount($totalAmount));


            $transaction = array(
                'name'                => array(
                    'string' => 'Credit Account - User ID: #%s',
                    'args'   => array($this->_user['id']),
                ),
                'amount'              => $totalAmount,
                'currency'            => $this->_settings['currency'],
                'user_id'             => $this->_user['id'],
                'transaction_details' => serialize(array(
                    'class' => '\\Ppb\\Service\\Fees\\AccountBalance',
                    'data'  => array(
                        'user_id' => $this->_user['id'],
                        'amount'  => $totalAmount,
                    ),
                )),
            );

            $transactionId = $this->_transactions->save($transaction);

            $gatewayForms = $this->_prepareForms($transactionId);
        }

        if ($gatewayForms === null) {
            $this->_flashMessenger->setMessage(array(
                'msg'   => $this->_('Error: Could not generate the payment form.'),
                'class' => 'alert-danger',
            ));
        }

        return array(
            'headline'           => $this->_('Payment - Credit Account'),
            'messages'           => $this->_flashMessenger->getMessages(),
            'paymentDescription' => $paymentDescription,
            'gatewayForms'       => $gatewayForms,
        );
    }

    public function StoreSubscription()
    {
        $paymentDescription = null;
        $gatewayForms = null;

        $translate = $this->getTranslate();

        $id = (!empty($this->_user['id'])) ?
            $this->_user['id'] : $this->getRequest()->getParam('id');

        $usersService = new Service\Users();

        $user = $usersService->findBy('id', (int)$id);

        $feesService = new Service\Fees\StoreSubscription($user);

        $totalAmount = $feesService->getTotalAmount();

        $storeNextPayment = $user->getData('store_next_payment');

        if (strtotime($storeNextPayment) > time() && $this->getRequest()->getParam('option') != 'renew') {
            $this->_flashMessenger->setMessage(array(
                'msg'   => $translate->_("Your store has been enabled."),
                'class' => 'alert-success',
            ));

            $user->save(array(
                'store_active' => 1,
            ));

            $this->_helper->redirector()->redirect('setup', 'store', 'members');
        }
        else if ($totalAmount > 0) {
            $storeSubscription = $feesService->getSubscription();

            $paymentDescription = sprintf(
                $translate->_('Please use one of the payment gateways listed below to pay the store subscription fee of %s'),
                $this->_view->amount($totalAmount));


            $transaction = array(
                'name'                => array(
                    'string' => 'Store Subscription Fee - %s Store - User ID: #%s',
                    'args'   => array($storeSubscription['name'], $id),
                ),
                'amount'              => $totalAmount,
                'tax_rate'            => $feesService->getTaxType()->getData('amount'),
                'currency'            => $this->_settings['currency'],
                'user_id'             => $id,
                'transaction_details' => serialize(array(
                    'class' => '\\Ppb\\Service\\Fees\\StoreSubscription',
                    'data'  => array(
                        'user_id'         => $id,
                        'subscription_id' => $storeSubscription['id'],
                    ),
                )),
            );

            $transactionId = $this->_transactions->save($transaction);

            $gatewayForms = $this->_prepareForms($transactionId);
        }
        else {
            $this->_flashMessenger->setMessage(array(
                'msg'   => sprintf(
                    $translate->_("The store for user ID #%s - '%s', has been activated. There were no fees to be paid."),
                    $user['id'], $user['username']),
                'class' => 'alert-success',
            ));

            $user->updateStoreSubscription(1, false, false);

            $gatewayForms = array();
        }

        if ($gatewayForms === null) {
            $this->_flashMessenger->setMessage(array(
                'msg'   => $this->_('Error: Could not generate the payment form - the user does not exist or there is no store subscription fee to be paid.'),
                'class' => 'alert-danger',
            ));
        }

        return array(
            'headline'           => $this->_('Payment - Store Subscription'),
            'messages'           => $this->_flashMessenger->getMessages(),
            'paymentDescription' => $paymentDescription,
            'gatewayForms'       => $gatewayForms,
        );
    }

    public function UserVerification()
    {
        $paymentDescription = null;
        $gatewayForms = null;

        $translate = $this->getTranslate();

        $id = (!empty($this->_user['id'])) ?
            $this->_user['id'] : $this->getRequest()->getParam('id');

        $usersService = new Service\Users();

        $user = $usersService->findBy('id', (int)$id);

        $feesService = new Service\Fees\UserVerification();

        $totalAmount = $feesService->getTotalAmount();

        if ($totalAmount > 0) {
            $paymentDescription = sprintf(
                $translate->_('Please use one of the payment gateways listed below to pay the user verification fee of %s'),
                $this->_view->amount($totalAmount));


            $transaction = array(
                'name'                => array(
                    'string' => 'User Verification Fee - User ID: #%s',
                    'args'   => array($this->_user['id']),
                ),
                'amount'              => $totalAmount,
                'tax_rate'            => $feesService->getTaxType()->getData('amount'),
                'currency'            => $this->_settings['currency'],
                'user_id'             => $id,
                'transaction_details' => serialize(array(
                    'class' => '\\Ppb\\Service\\Fees\\UserVerification',
                    'data'  => array(
                        'user_id'   => $id,
                        'recurring' => $this->_settings['user_verification_recurring'],
                        'refund'    => $this->_settings['user_verification_refund'],
                    ),
                )),
            );

            $transactionId = $this->_transactions->save($transaction);

            $gatewayForms = $this->_prepareForms($transactionId);
        }
        else {
            $this->_flashMessenger->setMessage(array(
                'msg'   => sprintf(
                    $translate->_("The account for user ID #%s - '%s', has been verified. There were no fees to be paid."),
                    $user['id'], $user['username']),
                'class' => 'alert-success',
            ));

            $user->updateUserVerification(1, false);

            $gatewayForms = array();
        }

        if ($gatewayForms === null) {
            $this->_flashMessenger->setMessage(array(
                'msg'   => $this->_('Error: Could not generate the payment form - the user does not exist or there is no user verification fee to be paid.'),
                'class' => 'alert-danger',
            ));
        }

        return array(
            'headline'           => $this->_('Payment - User Verification'),
            'messages'           => $this->_flashMessenger->getMessages(),
            'paymentDescription' => $paymentDescription,
            'gatewayForms'       => $gatewayForms,
        );
    }

    /**
     *
     * instant payment notification action - called by payment gateways
     *
     * @return void
     * @throws \RuntimeException
     */
    public function Ipn()
    {
        $ipnResult = false;
        $feesService = null;
        $transactionId = null;
        $redirectAction = null;

        $transactionId = $this->getRequest()->getParam('transaction_id');

        $gateway = $this->_paymentGateways->findBy('name', $this->getRequest()->getParam('gateway'));

        if ($gateway['id']) {
            $className = '\\Ppb\\Model\\PaymentGateway\\' . $gateway['name'];

            if (class_exists($className)) {
                $userId = null;

                if (!empty($transactionId)) {
                    /** @var \Ppb\Db\Table\Row\Transaction $transaction */
                    $transaction = $this->_transactions->findBy('id', $transactionId);

                    if ($transaction !== null) {
                        /** @var \Ppb\Db\Table\Row\Sale $sale */
                        $sale = $transaction->findParentRow('\Ppb\Db\Table\Sales');

                        if ($sale !== null) {
                            $userId = $sale->getData('seller_id');
                        }
                    }
                }

                ## -- ADD -- [ MOD:- MOLLIE GATEWAY INTEGRATION ]
                if ($gateway['name'] == 'Mollie') {
                    $molliePaymentId = $this->getRequest()->getParam('id');
                    $transaction = $this->_transactions->findBy('mollie_payment_id', $molliePaymentId);

                    $sale = $transaction->findParentRow('\Ppb\Db\Table\Sales');

                    if (count($sale) > 0) {
                        $userId = $sale->getData('seller_id');
                    }

                }
                ## -- ./ADD -- [ MOD:- MOLLIE GATEWAY INTEGRATION ]

                /** @var \Ppb\Model\PaymentGateway\AbstractPaymentGateway $gatewayModel */
                $gatewayModel = new $className($userId);
                $ipnResult = $gatewayModel->processIpn(
                    $this->getRequest());

                // if we wish to output a failure message from the payment gateway,
                // the transaction id needs to be set even if the payment is not successful
                $transactionId = $gatewayModel->getTransactionId();

                /** @var \Ppb\Db\Table\Row\Transaction $transaction */
                $transaction = $this->_transactions->findBy('id', $transactionId);

                if ($transaction->isPaid()) {
                    $redirectAction = 'Duplicate';
                }
                else if ($transactionId && $gatewayModel->checkIpnAmount($transaction['amount'], $transaction['currency'])) {
                    // update transaction row
                    $this->_transactions->save(array(
                        'id'                       => $transactionId,
                        'gateway_id'               => $gateway['id'],
                        'gateway_transaction_code' => $gatewayModel->getGatewayTransactionCode(),
                        'gateway_status'           => $gatewayModel->getGatewayPaymentStatus(),
                        'paid'                     => (int)$ipnResult,
                    ));

                    // run callback process
                    $transactionDetails = \Ppb\Utility::unserialize($transaction['transaction_details']);

                    $className = $transactionDetails['class'];
                    $feesService = new $className();

                    if ($feesService instanceof Service\Fees) {
                        $feesService->callback($ipnResult, $transactionDetails['data']);
                    }
                    else {
                        throw new \RuntimeException(
                            sprintf("IPN error - invalid fees service class called (%s).", $feesService));
                    }
                }
            }
        }

        $redirectAction = ($redirectAction === null) ? (($ipnResult === true) ? 'Completed' : 'Failed') : $redirectAction;

        $this->_postPaymentRedirect($transactionId, $redirectAction);

        $this->_helper->redirector()->redirect(
            $redirectAction);
    }

    /**
     *
     * payment completed action (generic)
     *
     * @return array
     */
    public function Completed()
    {
        $this->_postPaymentRedirect(
            $this->getRequest()->getParam('transaction_id'), 'Completed');

        $this->_view->setViewFileName(null);

        return array(
            'headline' => $this->_('Payment Completed'),
        );
    }

    /**
     *
     * payment failed action
     *
     * @return array
     */
    public function Failed()
    {
        $transactionId = $this->getRequest()->getParam('transaction_id');

        $this->_postPaymentRedirect($transactionId, 'Failed');

        $this->_view->setViewFileName(null);

        $gatewayMessage = null;

        $transaction = $this->_transactions->findBy('id', $transactionId);

        if ($transaction instanceof TransactionModel) {
            $gatewayMessage = $transaction->getData('gateway_status');
        }

        return array(
            'headline' => $this->_('Payment Failed'),
            'gatewayMessage' => $gatewayMessage,
        );
    }

    ## -- ADD -- [ MOD:- MOLLIE GATEWAY INTEGRATION ]
    public function Mollie()
    {
        require_once __DIR__ . "/../../../../../library/External/Mollie/API/Autoloader.php";

        $mollie = new \Mollie_API_Client;
        $mollie->setApiKey(
            $this->getRequest()->getParam(MollieModel::API_KEY));

        $transactionId = $this->getRequest()->getParam('transaction_id');

        $payment = $mollie->payments->create(array(
            "amount"      => $this->getRequest()->getParam('amount'),
//            "method"       => \Mollie_API_Object_Method::IDEAL,
            "description" => $this->getRequest()->getParam('description'),
            "webhookUrl"  => $this->getRequest()->getParam('webhook_url'),
            "redirectUrl" => $this->getRequest()->getParam('redirect_url'),
            "metadata"    => array(
                "order_id" => $transactionId,
            ),
//            "issuer"       => $this->getRequest()->getParam('issuer')
        ));

        $transactionsService = new Service\Transactions();
        $transaction = $transactionsService->findBy('id', $transactionId);
        $transaction->save(array(
            'mollie_payment_id' => $payment->id,
        ));

        $this->_helper->redirector()->gotoUrl(
            $payment->getPaymentUrl());
    }
    ## -- ./ADD -- [ MOD:- MOLLIE GATEWAY INTEGRATION ]

    /**
     *
     * generate payment gateway form objects
     *
     * @param string $transactionId transaction id (generated by each action)
     * @param int                    $userId        seller id (when fetching direct payment gateways)
     * @param int    $gatewayIds    gateway ids (to fetch data for specific gateways)
     * @param \Ppb\Db\Table\Row\User $user          payer user model
     *
     * @return array                array of \App\Form\Payment objects
     */
    protected function _prepareForms($transactionId, $userId = null, $gatewayIds = null, $user = null)
    {
        $formElements = array();

        ## -- ONE LINE :: ADD -- [ MOD:- SELLERS CREDIT ]
        if ((is_array($gatewayIds) && count($gatewayIds) > 0) || $gatewayIds === null) {
            $gateways = $this->_paymentGateways->getData($userId, $gatewayIds, true);

            foreach ($gateways as $gateway) {
                $className = '\\Ppb\\Model\\PaymentGateway\\' . $gateway['name'];

                if (class_exists($className)) {
                    /** @var \Ppb\Model\PaymentGateway\AbstractPaymentGateway $gatewayModel */
                    $gatewayModel = new $className($userId);

                    if ($gatewayModel->enabled()) {
                        $transaction = $this->_transactions->findBy('id', $transactionId);

                        $transactionName = \Ppb\Utility::unserialize($transaction['name']);
                    
                        if ($user !== null) {
                            $gatewayModel->setUser($user);
                        }
                    
                        $gatewayModel->setTransactionId($transaction['id'])
                            ->setName($transactionName)
                            ->setCurrency($transaction['currency'])
                            ->setAmount($transaction['amount']);

                        ## -- ADD -- [ MOD:- ESCROW & BANK TRANSFERS ]
                        if ($gatewayModel instanceof BankTransferModel && $this->_loggedInAdmin()) {
                            $gatewayModel->setPostUrl(
                                $this->_view->url(array('module' => 'admin', 'controller' => 'tools', 'action' => 'bank-transfer-pay-seller')));
                        }
                        ## -- ./ADD -- [ MOD:- ESCROW & BANK TRANSFERS ]
                    
                        $paymentForm = new Form\Payment($gatewayModel);

                        if (($paymentFormPartial = $gatewayModel->getPaymentFormPartial()) !== null) {
                            $paymentForm->setPartial($paymentFormPartial);
                        }
    
                        $formElements[] = $paymentForm;
                    }
                }
            }
        ## -- ONE LINE :: ADD -- [ MOD:- SELLERS CREDIT ]
        }

        ## -- START :: ADD -- [ MOD:- SELLERS CREDIT ]
        // add sellers credit gateway but only for direct payment etc.
        if ($userId) {
            /** @var \Ppb\Model\PaymentGateway\SellersCredit $gatewayModel */
            $gatewayModel = new \Ppb\Model\PaymentGateway\SellersCredit($userId);

            $transaction = $this->_transactions->findBy('id', $transactionId);

            $transactionName = \Ppb\Utility::unserialize($transaction['name']);

            $gatewayModel->setTransactionId($transaction['id'])
                ->setName($transactionName)
                ->setCurrency($transaction['currency'])
                ->setAmount($transaction['amount']);

            if ($gatewayModel->canPay($transaction)) {
                $formElements[] = new Form\Payment($gatewayModel);
            }
            
            
        }
        ## -- END :: ADD -- [ MOD:- SELLERS CREDIT ]

        return $formElements;
    }

    /**
     *
     * post payment redirect process
     *
     * @param int    $transactionId
     * @param string $action
     */
    protected function _postPaymentRedirect($transactionId, $action = 'Completed')
    {
        $transaction = $this->_transactions->findBy('id', $transactionId);

        if ($transaction instanceof TransactionModel) {
            // run callback process
            $transactionDetails = \Ppb\Utility::unserialize($transaction['transaction_details']);

            $className = $transactionDetails['class'];
            if (class_exists($className)) {
                $feesService = new $className();

                if ($feesService instanceof Service\Fees) {
                    $feesService->setTransactionDetails($transactionDetails);

                    if ($redirect = $feesService->getRedirect()) {
                        if ($action == 'Completed') {
                            $this->_flashMessenger->setMessage(array(
                                'msg'   => $this->_('Your payment has been confirmed. Thank you.'),
                                'class' => 'alert-success',
                            ));
                        }
                        else if ($action == 'Duplicate') {
                            $this->_flashMessenger->setMessage(array(
                                'msg'   => $this->_('This payment has already been processed.'),
                                'class' => 'alert-warning',
                            ));
                        }
                        else if ($action == 'Failed') {
                            $msg = $this->_('The payment has failed. Sorry for the inconvenience.');
                            $gatewayPaymentStatus = $transaction->getData('gateway_status');

                            if (!empty($gatewayPaymentStatus)) {
                                $msg .= '<br>' . sprintf($this->_('Message from payment gateway: %s'), $gatewayPaymentStatus);
                            }

                                $this->_flashMessenger->setMessage(array(
                                'msg'   => $msg,
                                    'class' => 'alert-danger',
                                ));
                            }

                        $redirectAction = (isset($redirect['action'])) ? $redirect['action'] : null;
                        $redirectController = (isset($redirect['controller'])) ? $redirect['controller'] : null;
                        $redirectModule = (isset($redirect['module'])) ? $redirect['module'] : null;
                        $redirectParams = (isset($redirect['params'])) ? $redirect['params'] : array();

                        $this->_helper->redirector()->redirect(
                            $redirectAction, $redirectController, $redirectModule, $redirectParams);
                    }
                }
            }
        }
    }

}

