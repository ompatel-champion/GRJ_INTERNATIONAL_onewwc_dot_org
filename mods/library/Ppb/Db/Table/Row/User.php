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
 * users table row object model
 */
/**
 * MOD:- EBAY IMPORTER
 * MOD:- SELLERS CREDIT
 * MOD:- BANK TRANSFER
 */

namespace Ppb\Db\Table\Row;

use Cube\Db\Expr,
    Cube\Db\Select,
    Cube\Controller\Front,
    Cube\View\Helper\Url as UrlViewHelper,
    Ppb\Model\Shipping as ShippingModel,
    Members\Model\Mail\Register as RegisterMail,
    Ppb\Service,
    Ppb\Service\PostmenShippingAPI;

class User extends AbstractRow
{

    /**
     * name of user token cookie
     */
    const USER_TOKEN = 'UserToken';

    /**
     * remember me cookie
     */
    const REMEMBER_ME = 'RememberMe';

    /**
     *
     * reputation service
     *
     * @var \Ppb\Service\Reputation
     */
    protected $_reputation;

    /**
     *
     * shipping model
     *
     * @var \Ppb\Model\Shipping
     */
    protected $_shipping;

    /**
     *
     * generate unique user token
     *
     * @return string
     */
    public static function generateToken()
    {
        return uniqid(time(), true);
    }

    /**
     *
     * get reputation service
     *
     * @return \Ppb\Service\Reputation
     */
    public function getReputation()
    {
        if (!$this->_reputation instanceof Service\Reputation) {
            $this->setReputation(
                new Service\Reputation());
        }

        return $this->_reputation;
    }

    /**
     *
     * set reputation service
     *
     * @param \Ppb\Service\Reputation $reputation
     *
     * @return \Ppb\Db\Table\Row\User
     */
    public function setReputation(Service\Reputation $reputation)
    {
        $this->_reputation = $reputation;

        return $this;
    }

    /**
     *
     * get shipping model
     *
     * @return \Ppb\Model\Shipping
     */
    public function getShipping()
    {
        if (!$this->_shipping instanceof ShippingModel) {
            $this->setShipping(
                new ShippingModel($this));
        }

        return $this->_shipping;
    }

    /**
     *
     * set shipping model
     *
     * @param \Ppb\Model\Shipping $shipping
     *
     * @return \Ppb\Db\Table\Row\User
     */
    public function setShipping(ShippingModel $shipping)
    {
        $this->_shipping = $shipping;

        return $this;
    }

    /**
     *
     * checks if the user accepts public questions
     *
     * @return bool
     */
    public function acceptPublicQuestions()
    {
        $settings = $this->getSettings();
        if ($settings['enable_public_questions'] && $this->getGlobalSettings('enable_public_questions')) {
            return true;
        }

        return false;
    }

    /**
     *
     * check if the user account is approved
     *
     * @return bool
     */
    public function isApproved()
    {
        if ($this->getData('approved') == 1) {
            return true;
        }

        return false;
    }

    /**
     *
     * check if the user account has the email activated flag set
     *
     * @return bool
     */
    public function isMailActivated()
    {
        if ($this->getData('mail_activated') == 1) {
            return true;
        }

        return false;
    }

    /**
     *
     * account suspended (role)
     *
     * @return bool
     */
    public function accountSuspended()
    {
        return (!in_array($this->getRole(), array('Buyer', 'BuyerSeller'))) ? true : false;
    }

    /**
     *
     * check if user account is active
     *
     * @return bool
     */
    public function isActive()
    {
        if (
            $this->getData('active') &&
            $this->isApproved() &&
            $this->isMailActivated() &&
            $this->getData('payment_status') == 'confirmed'
        ) {
            return true;
        }

        return false;
    }

    /**
     *
     * check if the user has buying privileges
     *
     * @return bool
     */
    public function isBuyer()
    {
        return true;
    }

    /**
     *
     * check if the user has selling privileges
     *
     * @return bool
     */
    public function isSeller()
    {
        $settings = $this->getSettings();

        $privateSite = (isset($settings['private_site'])) ? (bool)$settings['private_site'] : false;
        if (!$privateSite || $this->getData('is_seller')) {
            return true;
        }

        return false;
    }

    /**
     *
     * check if user can request selling privileges
     *
     * @param bool $simple if true, it will show even if the user has already requested selling privileges
     *
     * @return bool
     */
    public function canRequestSellingPrivileges($simple = false)
    {
        if ($this->isSeller()) {
            return false;
        }

        $settings = $this->getSettings();

        $privateSiteRequestSellingPrivileges = (isset($settings['private_site_request_seller_privileges'])) ? (bool)$settings['private_site_request_seller_privileges'] : false;

        if ($privateSiteRequestSellingPrivileges) {
            if ($simple) {
                return true;
            }
            else if (!$this->getData('request_selling_privileges')) {
                return true;
            }
        }

        return false;
    }

    /**
     *
     * check if the user can list items
     * (is a seller and if seller verification is required, is verified)
     *
     * @7.9 seller can only list items if the account is active
     *
     * @return bool
     */
    public function canList()
    {
        $settings = $this->getSettings();

        $isSeller = $this->isSeller();

        if ($isSeller &&
            $this->isActive() &&
            $this->isForceStore() &&
            ($this->isVerified() || !$settings['seller_verification_mandatory'])
        ) {
            return true;
        }

        return false;
    }

    /**
     *
     * get user role
     * we have Guest, Incomplete, Suspended, Buyer, Seller, BuyerSeller
     *
     * @return string the role of the user
     */
    public function getRole()
    {
        if (!count($this)) {
            return 'Guest';
        }

        if ($this->getData('approved') && $this->getData('mail_activated') && $this->getData('payment_status') == 'confirmed') {
            if ($this->getData('active')) {
                if ($this->isSeller()) {
                    return 'BuyerSeller';
                }
                else {
                    return 'Buyer';
                }
            }
            else {
                return 'Suspended';
            }
        }
        else {
            return 'Incomplete';
        }
    }

    /**
     *
     * get store status (true if enabled and items can be listed in it, false otherwise)
     *
     * @param bool $simple if this flag is set, it will not check if items can be listed in the store
     *
     * @return bool      true if user can list in store, false otherwise
     */
    public function storeStatus($simple = false)
    {
        $settings = $this->getSettings();

        if ($settings['enable_stores'] && $this->getData('store_active')) {
            if ($simple === true) {
                return true;
            }

            if ($this->getData('store_subscription_id')) {
                $subscription = $this->findParentRow('\Ppb\Db\Table\StoresSubscriptions');

                if ($this->countStoreListings() < $subscription['listings']) {
                    return true;
                }

                return false;
            }
            else {
                // default store - unlimited items
                return true;
            }
        }

        return false;
    }

    /**
     *
     * get store logo
     *
     * @return string|null
     */
    public function storeLogo()
    {
        $storeSettings = $this->getStoreSettings();
        $logo = null;

        if (!empty($storeSettings['store_logo_path'])) {
            $logo = (is_array($storeSettings['store_logo_path'])) ?
                current(array_filter($storeSettings['store_logo_path'])) : $storeSettings['store_logo_path'];
        }

        return $logo;
    }

    /**
     *
     * returns the user's total number of store listings (open/closed, active/suspended)
     *
     * @return int
     */
    public function countStoreListings()
    {
        return $this->countListings('in-store');
    }

    /**
     *
     * count a user's total number of active and open listings
     *
     * @param string $filter
     *
     * @return int
     */
    public function countListings($filter = null)
    {
        $listingsService = new Service\Listings();

        $filters = array('active', 'open');

        if ($filter !== null) {
            array_push($filters, $filter);
        }

        $select = $listingsService->select(Service\Listings::SELECT_SIMPLE, array(
            'user_id' => $this->getData('id'),
            'filter'  => $filters
        ));

        $select->reset(Select::COLUMNS)
            ->reset(Select::ORDER);

        $select->columns(array('nb_rows' => new Expr('count(*)')));

        $stmt = $select->query();

        return (integer)$stmt->fetchColumn('nb_rows');
    }

    /**
     *
     * count the number of unpaid invoices
     *
     * @return int
     */
    public function countUnpaidInvoices()
    {
        $salesService = new Service\Sales();

        $select = $salesService->getTable()->select()
            ->reset(Select::COLUMNS)
            ->columns(array('nb_rows' => new Expr('count(*)')))
            ->where('buyer_id = ?', $this->getData('id'))
            ->where('flag_payment = ?', Sale::PAYMENT_UNPAID)
            ->where('pending = ?', 0)
            ->where('seller_deleted = ?', 0);

        $stmt = $select->query();

        return (integer)$stmt->fetchColumn('nb_rows');
    }

    /**
     *
     * get user payment mode
     *
     * @return string will return the account type ('live', 'account')
     */
    public function userPaymentMode()
    {
        $settings = $this->getSettings();

        if ($settings['user_account_type'] == 'global') {
            return $settings['payment_mode'];
        }

        return $this->getData('account_mode');
    }

    /**
     *
     * update user balance (if in account mode)
     * on the front end only listings from active users are shown
     *
     * @param float $amount positive = debit | negative = credit
     *
     * @return \Ppb\Db\Table\Row\User
     */
    public function updateBalance($amount)
    {
        $paymentMode = $this->userPaymentMode();

        if ($paymentMode == 'account') {
            $settings = $this->getSettings();

            $balance = $this->getData('balance') + $amount;

            $this->save(array(
                'balance' => $balance,
            ));

            if ($amount > 0 && $balance > $this->getData('max_debit')) {
                $sendEmail = false;
                if ($settings['suspend_over_limit_accounts']) {
                    $this->updateActive(0);
                    $sendEmail = true;
                }
                else if (!$this->getData('debit_exceeded_date')) { // only set this flag if the column is null
                    $sendEmail = true;
                    $this->save(array(
                        'debit_exceeded_date' => new Expr('now()')
                    ));
                }

                if ($sendEmail) {
                    $mail = new \Members\Model\Mail\User();
                    $mail->accountBalanceExceeded($this)->send();
                }

            }
            else if ($amount < 0 && $balance < $this->getData('max_debit')) {
                $this->save(array(
                    'debit_exceeded_date' => new Expr('null') // reset cron suspension flag
                ));
                $this->updateActive(1);
            }
        }

        return $this;
    }

    /**
     *
     * when suspending the user, also suspend his active listings
     * inactive listings will not have their active flag altered
     *
     * @param int $active
     *
     * @return $this
     */
    public function updateActive($active = 1)
    {
        $listingsFlag = ($active) ? -1 : 1;

        $listingsService = new Service\Listings();
        $listingsService->getTable()->update(
            array('active' => (-1) * $listingsFlag),
            "user_id = '" . $this->getData('id') . "' AND active = '{$listingsFlag}'");

        $this->save(array(
            'active' => $active
        ));

        return $this;

    }

    /**
     *
     * update the store settings for the user
     *
     * @8.0: if changing the store subscription, reset the next payment field
     *
     * @param array $data
     *
     * @return \Ppb\Db\Table\Row\User
     */
    public function updateStoreSettings(array $data)
    {
        $array = array();

        // if store account id is changed, store_active = 0
        if (!empty($data['store_subscription_id'])) {
            if ($data['store_subscription_id'] != $this->getData('store_subscription_id')) {
                $array['store_active'] = 0;
                $storeNextPayment = $this->getData('store_next_payment');
                if (strtotime($storeNextPayment) > time()) {
                    $array['store_next_payment'] = new Expr('now()');
                }
            }
            $array['store_subscription_id'] = $data['store_subscription_id'];
        }

        if (isset($data['store_name'])) {
            $array['store_name'] = $data['store_name'];
            $array['store_slug'] = $this->_sluggizeStoreName($array['store_name']);

            if (!isset($data['store_logo_path'])) {
                $data['store_logo_path'] = '';
            }
        }

        if (isset($data['store_category_id'])) {
            $array['store_category_id'] = $data['store_category_id'];
        }

        $data = array_merge($this->getStoreSettings(), $data);

        $array['store_settings'] = serialize($data);

        $this->save($array);

        return $this;
    }

    /**
     *
     * update the postage settings for the user
     *
     * @param array $data
     *
     * @return \Ppb\Db\Table\Row\User
     */
    public function updatePostageSettings(array $data)
    {
        $this->save(array(
            'postage_settings' => serialize($data)
        ));

        return $this;
    }

    /**
     *
     * update the selling prefilled fields for the user
     *
     * @param array $data
     *
     * @return \Ppb\Db\Table\Row\User
     */
    public function updatePrefilledFields(array $data)
    {
        $this->save(array(
            'prefilled_fields' => serialize($data)
        ));

        return $this;
    }

    /**
     *
     * update the global settings field for the user
     *
     * @param array $data
     *
     * @return \Ppb\Db\Table\Row\User
     */
    public function updateGlobalSettings(array $data)
    {
        $data = array_merge($this->getGlobalSettings(), $data);

        $this->save(array(
            'global_settings' => serialize($data)
        ));

        return $this;
    }

    /**
     *
     * update preferred seller status
     *
     * @param int $flag
     *
     * @return $this
     */
    public function updatePreferredSeller($flag)
    {
        $flag = ($flag == 1) ? 1 : 0;

        if ($flag) {
            $settings = $this->getSettings();

            $expirationDate = ($settings['preferred_sellers_expiration'] > 0) ?
                new Expr('(now() + interval ' . intval($settings['preferred_sellers_expiration']) . ' day)') : new Expr('null');
        }
        else {
            $expirationDate = new Expr('now()');
        }
        $this->save(array(
            'preferred_seller'            => $flag,
            'preferred_seller_expiration' => $expirationDate,
        ));

        return $this;
    }

    /**
     *
     * updates the user verification related fields (flag and last/next payment dates)
     *
     * @param int  $flag
     * @param bool $updateLastPayment if set to true and the flag is enabled, will update the last payment field
     * @param null $recurringDays     custom recurring days field. if null, will use the value from the settings table
     * @param null $refundUser
     *
     * @return $this
     */
    public function updateUserVerification($flag, $updateLastPayment = true, $recurringDays = null, $refundUser = null)
    {
        $flag = ($flag == 1) ? 1 : 0;

        $params = array(
            'user_verified' => $flag,
        );

        if ($flag) {
            $settings = $this->getSettings();

            $recurringDays = ($recurringDays !== null) ? $recurringDays : $settings['user_verification_recurring'];

            $userVerifiedNextPayment = date('Y-m-d H:i:s', strtotime($this->getData('user_verified_next_payment')));
            $nextPayment = ($recurringDays > 0) ?
                new Expr('(greatest(now(), "' . $userVerifiedNextPayment . '") + interval ' . intval($recurringDays) . ' day)') : new Expr('null');

            $refundUser = ($refundUser !== null) ? $refundUser : $settings['user_verification_refund'];

            if ($updateLastPayment) {
                $params['user_verified_last_payment'] = new Expr('now()');
            }

            if ($refundUser && $this->userPaymentMode() == 'account') {
                $params['balance'] = $this->getData('balance') - $settings['user_verification_fee'];
            }
        }
        else {
            $nextPayment = new Expr('now()');
        }

        $params['user_verified_next_payment'] = $nextPayment;

        $this->save($params);

        return $this;
    }

    /**
     *
     * updates the user's store account and subscription
     *
     * @param int           $flag
     * @param bool|null|int $storeSubscriptionId if false, don't update the subscription id, if null set default account, otherwise set subscription id
     * @param bool          $updateLastPayment
     *
     * @return $this
     */
    public function updateStoreSubscription($flag, $storeSubscriptionId = false, $updateLastPayment = true)
    {
        $flag = ($flag == 1) ? 1 : 0;

        $params = array();
        $params['store_active'] = $flag;

        if ($storeSubscriptionId !== false) {
            $params['store_subscription_id'] = ($storeSubscriptionId === null) ?
                new Expr('null') : (int)$storeSubscriptionId;
        }

        $this->save($params);

        $params = array();

        if ($flag) {
            if ($this->getData('store_subscription_id') > 0) {
                $storeSubscription = $this->findParentRow('\Ppb\Db\Table\StoresSubscriptions');

                $storeNextPayment = date('Y-m-d H:i:s', strtotime($this->getData('store_next_payment')));
                $params['store_next_payment'] = ($storeSubscription['recurring_days'] > 0) ?
                    new Expr('(greatest(now(), "' . $storeNextPayment . '") + interval ' . intval($storeSubscription['recurring_days']) . ' day)') : new Expr('null');
            }
            else {
                $params['store_next_payment'] = new Expr('null');
            }

            if ($updateLastPayment) {
                $params['store_last_payment'] = new Expr('now()');
            }

            if (count($params) > 0) {
                $this->save($params);
            }
        }

        return $this;
    }

    /**
     *
     * check if we have a user sign up fee set
     *
     * @return bool
     */
    public function isSignUpFee()
    {
        $feesService = new Service\Fees\UserSignup($this);
        $signupFee = $feesService->getFeeAmount('signup');

        return ($signupFee > 0) ? true : false;
    }

    /**
     *
     * process registration
     *
     * @param bool $forceActivation
     * @param bool $edit
     *
     * @return array    output messages in flash messenger format
     */
    public function processRegistration($forceActivation = false, $edit = false)
    {
        $messages = array();

        $settings = $this->getSettings();
        $translate = $this->getTranslate();

        $params = array(
            'approved'         => 0,
            'mail_activated'   => 0,
            'registration_key' => $this->generateRegistrationKey()
        );

        $mail = new RegisterMail(
            array_merge($this->getData(), $params));

        if (!$edit) {
        $messages[] = array(
            'msg'   => $translate->_('Thank you for registering.'),
            'class' => 'alert-success',
        );
        }

        if ($forceActivation === true) {
            $params['payment_status'] = $params['active'] = $params['approved'] = $params['mail_activated'] = 1;

            $mail->registerDefault()->send();
        }
        else {
            if (!$this->isSignUpFee()) {
                $params['payment_status'] = 'confirmed';
                $params['active'] = 1;
            }

            switch ($settings['signup_settings']) {
                case 0:
                    $messages[] = array(
                        'msg'   => $translate->_('Registration completed.'),
                        'class' => 'alert-info',
                    );
                    $params['approved'] = $params['mail_activated'] = 1;

                    $mail->registerDefault()->send();

                    break;
                case 1:
                    $messages[] = array(
                        'msg'   => $translate->_('An email has been sent to the address you have submitted with details on how to activate your account.'),
                        'class' => 'alert-info',
                    );

                    $params['approved'] = 1;

                    $mail->registerConfirm()->send();

                    break;
                case 2:
                    $messages[] = array(
                        'msg'   => $translate->_('Your account will be approved after it will be reviewed by an administrator.'),
                        'class' => 'alert-info',
                    );

                    $mail->registerApprovalUser()->send();
                    $mail->registerApprovalAdmin()->send();

                    break;
            }
        }

        $this->save($params);

        return $messages;
    }

    /**
     *
     * generate a unique registration key used for verifying the user's email address
     *
     * @return string
     */
    public function generateRegistrationKey()
    {
        $id = $this->getData('id');
        $username = $this->getData('username');
        $hash = md5(uniqid(time()));

        return substr(
            hash('sha256', $id . $username . $hash), 0, 10);
    }

    /**
     *
     * save the user's settings (admin area) and return any output messages
     *
     * @param array $params
     *
     * @return array
     */
    public function updateSettings(array $params)
    {
        $translate = $this->getTranslate();

        $messages = array();
        $data = array(
            'account_mode' => $params['account_mode'],
        );

        if ($params['user_verified'] != $this->getData('user_verified')) {
            $this->updateUserVerification($params['user_verified'], false, null, false);

            $status = ($params['user_verified'] == 1) ? 'verified' : 'unverified';
            $messages[] = sprintf($translate->_("The account has been %s."), $status);
        }

        if ($params['account_mode'] == 'account') {
            $data = array_merge($data, array(
                'balance'   => $params['balance'],
                'max_debit' => $params['max_debit'],
            ));
            if ($params['balance'] != $this->getData('balance')) {
                $view = Front::getInstance()->getBootstrap()->getResource('view');

                $messages[] = sprintf($translate->_("The user's balance has been changed to: %s %s"),
                    $view->amount(abs($params['balance'])), (($params['balance'] > 0) ? 'debit' : 'credit'));

                $settings = $this->getSettings();

                $amount = $params['balance'] - $this->getData('balance');

                $name = array(
                    'string' => 'Admin Balance Adjustment - User ID: #%s',
                    'args'   => array($this->getData('id')),
                );

                if ($params['balance_adjustment_reason']) {
                    $name = array(
                        'string' => 'Admin Balance Adjustment - User ID: #%s - Comment: %s',
                        'args'   => array($this->getData('id'), $params['balance_adjustment_reason']),
                    );
                }

                // save balance adjustment process in the accounting table
                $accountingService = new Service\Accounting();
                $accountingService->save(array(
                    'name'     => $name,
                    'amount'   => $amount,
                    'user_id'  => $this->getData('id'),
                    'currency' => $settings['currency'],
                ));
            }
        }

        if (array_key_exists('is_seller', $params)) {
            if ($params['is_seller'] != $this->getData('is_seller')) {
                $data['is_seller'] = $params['is_seller'];

                $status = ($params['is_seller'] == 1) ? $translate->_('enabled') : $translate->_('disabled');
                $messages[] = sprintf($translate->_("The user's listing capabilities have been %s."), $status);
            }
        }

        if (array_key_exists('preferred_seller', $params)) {
            if ($params['preferred_seller'] != $this->getData('preferred_seller')) {
                $this->updatePreferredSeller($params['preferred_seller']);

                $status = ($params['preferred_seller'] == 1) ? $translate->_('enabled') : $translate->_('disabled');
                $messages[] = sprintf($translate->_("The preferred seller status has been %s."), $status);
            }
        }

        if (array_key_exists('store_active', $params)) {
            if ($params['store_active'] != $this->getData('store_active')) {
                $data['store_active'] = $params['store_active'];
                $status = ($params['store_active'] == 1) ? $translate->_('enabled') : $translate->_('disabled');
                $messages[] = sprintf($translate->_("The store has been %s."), $status);

                if ($params['store_active'] == 0) {
                    $data['store_next_payment'] = new Expr('now()');
                }
            }
        }

        if (array_key_exists('assign_default_store_account', $params)) {
            if ($params['assign_default_store_account']) {
                $data = array_merge($data,
                    array('store_active'          => 1,
                          'store_subscription_id' => new Expr('null'),
                          'store_next_payment'    => new Expr('null'),
                    ));

                $messages[] = $translate->_("The default store account has been set.");
            }
        }

        if (count($data) > 0) {
            $this->save($data);
        }

        return $messages;
    }

    /**
     *
     * get the reputation score of the user
     * proxy for the \Ppb\Service\Reputation::getScore() method
     *
     * @return integer
     */
    public function getReputationScore()
    {
        $reputationData = \Ppb\Utility::unserialize($this->getData('reputation_data'));

        if (isset($reputationData['score'])) {
            return $reputationData['score'];
        }

        return $this->getReputation()->getScore($this->getData('id'));
    }

    /**
     *
     * get the positive reputation percentage of the user
     * proxy for the \Ppb\Service\Reputation::getPercentage() method
     *
     * @return string
     */
    public function getReputationPercentage()
    {
        $reputationData = \Ppb\Utility::unserialize($this->getData('reputation_data'));

        if (isset($reputationData['percentage'])) {
            return $reputationData['percentage'];
        }

        return $this->getReputation()->getPercentage($this->getData('id'));
    }

    /**
     *
     * get user postage settings
     *
     * @param string|null $key
     *
     * @return array
     */
    public function getPostageSettings($key = null)
    {
        $postageSettings = \Ppb\Utility::unserialize($this->getData('postage_settings'), array());

        if ($key !== null) {
            return isset($postageSettings[$key]) ? $postageSettings[$key] : null;
        }

        return $postageSettings;
    }

    /**
     *
     * get user store settings
     *
     * @param string|null $key
     *
     * @return array|string|null
     */
    public function getStoreSettings($key = null)
    {
        $storeSettings = \Ppb\Utility::unserialize($this->getData('store_settings'), array());

        $storeSettings['store_subscription_id'] = $this->getData('store_subscription_id');

        if ($key !== null) {
            return isset($storeSettings[$key]) ? $storeSettings[$key] : null;
        }

        return $storeSettings;
    }

    /**
     *
     * get listing setup prefilled fields
     * include the user's default address in the prefilled fields array
     * if the array is empty, return null
     * 7.8: remove any null variables from the array
     *
     * @return array|null
     */
    public function getPrefilledFields()
    {
        $prefilledFields = \Ppb\Utility::unserialize($this->getData('prefilled_fields'), null);

        $userAddress = array(
            'country' => $this->getData('country'),
            'state'   => $this->getData('state'),
            'address' => $this->getData('zip_code'),
        );

        $prefilledFields = array_filter(array_merge($userAddress, (array)$prefilledFields));

        return (count($prefilledFields) > 0) ? $prefilledFields : null;
    }


    /**
     *
     * get user global settings
     *
     * @param string|null $key
     *
     * @return array|string|null
     */
    public function getGlobalSettings($key = null)
    {
        $globalSettings = \Ppb\Utility::unserialize($this->getData('global_settings'), array());

        if ($key !== null) {
            return isset($globalSettings[$key]) ? $globalSettings[$key] : null;
        }

        return $globalSettings;
    }

    /**
     *
     * calculate the reputation score of a user based on different input variables
     * proxy for the \Ppb\Service\Reputation::calculateScore() method
     *
     * @param float  $score          score threshold
     * @param string $operand        calculation operand
     * @param string $reputationType reputation type to be calculated (sale, purchase)
     * @param string $interval       calculation interval
     *
     * @return int                      resulted score
     */
    public function calculateReputationScore($score = null, $operand = '=', $reputationType = null, $interval = null)
    {
        return intval($this->getReputation()
            ->calculateScore($this->getData('id'), $score, $operand, $reputationType, $interval));
    }

    /**
     *
     * check if the user can pay the signup fee
     *
     * @return bool
     */
    public function canPaySignupFee()
    {
        if ($this->getData('payment_status') != 'confirmed') {
            return true;
        }

        return false;
    }

    /**
     *
     * check if the user can apply tax to his listings
     * return the tax types that are enabled if tax can be applied
     * seller will choose which taxes to apply from the global settings page
     *
     * @return \Ppb\Db\Table\Rowset\TaxTypes|false
     */
    public function canApplyTax()
    {
        $settings = $this->getSettings();

        if ($settings['enable_tax_listings']) {
            if ($this->getGlobalSettings('enable_tax')) {
                $taxTypesIds = array_filter((array)\Ppb\Utility::unserialize($this->getGlobalSettings('tax_type')));

                if (count($taxTypesIds) > 0) {
                    $taxTypesService = new Service\Table\TaxTypes();
                    /** @var \Ppb\Db\Table\Rowset\TaxTypes $taxTypes */
                    $taxTypes = $taxTypesService->fetchAll(
                        $taxTypesService->getTable()->getAdapter()->quoteInto('id IN (?)', $taxTypesIds)
                    );

                    return (count($taxTypes) > 0) ? $taxTypes : false;
                }
            }
        }

        return false;
    }

    /**
     *
     * check if the user is a verified user
     * - used on the purchase controller for buyer verification
     * - used on the listing controller (add/edit actions) for the seller verification
     *
     * @param bool $default
     *
     * @return bool
     * @throws \RuntimeException
     */
    public function isVerified($default = true)
    {
        $settings = $this->getSettings();

        if ($settings['user_verification']) {
            if ($this->getData('user_verified')) {
                return true;
            }

            return false;
        }

        return (bool)$default;
    }

    /**
     *
     * check if the user has enabled force payment for his products
     *
     * @return bool
     */
    public function isForcePayment()
    {
        $settings = $this->getSettings();
        if ($settings['enable_products'] && $settings['enable_force_payment']) {
            if ($this->getGlobalSettings('enable_force_payment')) {
                return true;
            }
        }

        return false;
    }

    /**
     *
     * if the force stores setting is enabled and if the user doesnt have an active store
     * and has a number of live items that is higher than the force stores setting, it will return false
     *
     * @return bool
     */
    public function isForceStore()
    {
        $settings = $this->getSettings();

        if ($settings['enable_stores'] && $settings['force_stores']) {
            if (!$this->storeStatus()) {
                if ($this->countListings() >= $settings['force_stores']) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     *
     * check if the social media widget can display for the user
     *
     * @return bool
     */
    public function displaySocialMediaLinks()
    {
        $settings = $this->getSettings();

        $result = false;

        if ($settings['enable_social_media_user']) {
            switch ($settings['social_media_user_type']) {
                case 'all':
                    $result = true;
                    break;
                case 'verified':
                    $result = $this->isVerified();
                    break;
                case 'store':
                    $result = $this->storeStatus();
                    break;
            }
        }

        return $result;
    }

    /**
     *
     * check if the user is an administrator
     *
     * @return bool
     */
    public function isAdmin()
    {
        $roles = array_keys(Service\Users::getAdminRoles());
        if (in_array($this->getData('role'), $roles)) {
            return true;
        }

        return false;
    }

    /**
     *
     * returns an array used by the url view helper to generate the store url
     * if the store is disabled, return the main page url
     *
     * @return array
     */
    public function storeLink()
    {
        if ($this->storeStatus(true)) {
            $slug = $this->getData('store_slug');

            if ($slug) {
                return array(
                    'module'     => 'listings',
                    'controller' => 'browse',
                    'action'     => 'store',
                    'store_slug' => $slug,
                );
            }
            else {
                return array(
                    'module'     => 'listings',
                    'controller' => 'browse',
                    'action'     => 'store',
                    'name'       => $this->storeName(),
                    'store_id'   => $this->getData('id'),
                );
            }
        }
        else {
            return array(
                'module'     => 'app',
                'controller' => 'index',
                'action'     => 'index',
            );
        }
    }

    /**
     *
     * user's store name
     *
     * @return string
     */
    public function storeName()
    {
        return ($this->getData('store_name')) ? $this->getData('store_name') : $this->getData('username');
    }

    /**
     *
     * returns an array used by the url view helper to generate the user's other items url
     *
     * @return array
     */
    public function otherItemsLink()
    {
        return array(
            'module'     => 'listings',
            'controller' => 'browse',
            'action'     => 'index',
            'filter'     => 'other-items',
            'username'   => $this->getData('username'),
            'user_id'    => $this->getData('id'),
        );
    }

    /**
     * returns an array used by the url view helper to generate the user's feedback details url
     *
     * @return array
     */
    public function reputationLink()
    {
        $username = $this->getData('username');

        return array(
            'module'     => 'members',
            'controller' => 'reputation',
            'action'     => 'details',
            'username'   => (stristr($username, ' ')) ? $this->getData('id') : $username,
        );
    }

    /**
     *
     * set the active address for the user
     *
     * @param int|null|\Ppb\Db\Table\Row\UserAddressBook $address
     *
     * @return $this
     */
    public function setAddress($address = null)
    {
        if (!$address instanceof UserAddressBook) {
            $address = $this->getAddress(intval($address));
        }

        if ($address instanceof UserAddressBook) {
            foreach ($address as $key => $value) {
                if ($key == 'id') {
                    $key = 'address_id';
                }
                $this->addData($key, $value);
            }
        }

        return $this;
    }

    /**
     *
     * get a user's address from the address book table
     *
     * @param int|null $id the id of the address or null if we are looking for the primary address
     *
     * @return \Ppb\Db\Table\Row\UserAddressBook|null   will return an address book row object or null if the user has no address saved
     */
    public function getAddress($id = null)
    {
        $select = $this->getTable()->select();
        if (!$id) {
            $select->where('is_primary = ?', 1);
        }
        else {
            $select->where('id = ?', $id);
        }

        $rowset = $this->findDependentRowset('\Ppb\Db\Table\UsersAddressBook', null, $select);

        /** @var \Ppb\Db\Table\Row\UserAddressBook $result */
        $result = $rowset->getRow(0);

        return $result;
    }

    /**
     *
     * check whether to display make offer ranges
     *
     * @return bool
     */
    public function displayMakeOfferRanges()
    {
        $settings = $this->getSettings();
        if ($settings['show_make_offer_ranges'] && $this->getGlobalSettings('show_make_offer_ranges')) {
            return true;
        }

        return false;
    }

    /**
     *
     * check if a user has added this store owner as favorite
     *
     * @param int $userId
     *
     * @return bool
     */
    public function isFavoriteStore($userId)
    {
        return (count($this->_getFavoriteStores($userId))) ? true : false;
    }

    /**
     *
     * add/remove this user store to favorites for a certain user
     *
     * @param int $userId
     *
     * @return $this
     */
    public function processFavoriteStore($userId)
    {
        if ($this->isFavoriteStore($userId)) {
            $this->_getFavoriteStores($userId)->delete();
        }
        else {
            $favoriteStoresService = new Service\FavoriteStores();
            $favoriteStoresService->save(array(
                'user_id'  => $userId,
                'store_id' => $this->getData('id'),
            ));
        }

        return $this;
    }

    /**
     *
     * checks if the user is in vacation
     *
     * @return bool
     */
    public function isVacation()
    {
        if ($this->getGlobalSettings('vacation_mode')) {
            $returnDate = $this->getGlobalSettings('vacation_mode_return_date');
            if (empty($returnDate) || (strtotime($returnDate) > time())) {
                return true;
            }
        }

        return false;
    }

    /**
     *
     * check if postmen shipping api is enabled
     *
     * @return bool
     */
    public function isPostmenShippingApi()
    {
        $postmenShippingApi = new PostmenShippingAPI(
            $this->getGlobalSettings(PostmenShippingAPI::API_KEY),
            $this->getGlobalSettings(PostmenShippingAPI::API_MODE)
        );

        return $postmenShippingApi->isEnabled();
    }

    /**
     *
     * check if the user account can be deleted
     *
     * @return bool
     */
    public function canDelete()
    {
        $user = $this->getUser();

        if ($user instanceof User) {
            $adminRoles = array_keys(Service\Users::getAdminRoles());
            if (
                !in_array($this->getData('role'), $adminRoles) ||
                $user['role'] == Service\Users::ADMIN_ROLE_PRIMARY
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     *
     * check if the user has selected to receive seller specific email notifications
     *
     * @return bool
     */
    public function emailSellerNotifications()
    {
        return (
            $this->getGlobalSettings('disable_emails') ||
            $this->getGlobalSettings('disable_seller_notifications')
        ) ? false : true;
    }

    /**
     *
     * check if the user has selected to receive offers module specific email notifications
     *
     * @return bool
     */
    public function emailOffersNotifications()
    {
        return (
            $this->getGlobalSettings('disable_emails') ||
            $this->getGlobalSettings('disable_offers_notifications')
        ) ? false : true;
    }

    /**
     *
     * get country iso code
     *
     * @return string|null
     */
    public function getCountryIsoCode()
    {
        if ($countryId = $this->getData('country')) {
            $locationsService = new Service\Table\Relational\Locations();
            $country = $locationsService->findBy('id', (int)$countryId);

            if ($country !== null) {
                if (!empty($country['iso_code'])) {
                    return strtoupper($country['iso_code']);
                }
            }
        }

        return null;
    }

    /**
     *
     * get state iso code
     *
     * @return string|null
     */
    public function getStateIsoCode()
    {
        if ($stateName = $this->getData('state')) {
            if (is_numeric($stateName)) {
                $locationsService = new Service\Table\Relational\Locations();
                $state = $locationsService->findBy('id', (int)$stateName);

                if ($state !== null) {
                    if (!empty($state['iso_code'])) {
                        return strtoupper($state['iso_code']);
                    }
                }
            }

            return $stateName;
        }

        return null;
    }
    
    /**
     *
     * check if the user has selected to receive messaging module specific email notifications
     *
     * @return bool
     */
    public function emailMessagingNotifications()
    {
        return (
            $this->getGlobalSettings('disable_emails') ||
            $this->getGlobalSettings('disable_messaging_notifications')
        ) ? false : true;
    }


    ## -- START :: ADD -- [ MOD:- EBAY IMPORTER ]
    /**
     *
     * remove all ebay imported listings that havent been sold through the website
     *
     * @return $this
     */
    public function removeEbayImportedListings()
    {
        $select = $this->getTable()->select()
            ->where('ebay_item_id IS NOT NULL');
        /** @var \Ppb\Db\Table\Rowset\Listings $ebayListings */
        $ebayListings = $this->findDependentRowset('\Ppb\Db\Table\Listings', null, $select);

        $ebayListings->setAdmin(true)
            ->delete();

        return $this;
    }
    ## -- END :: ADD -- [ MOD:- EBAY IMPORTER ]

    ## -- START :: ADD -- [ MOD:- SELLERS CREDIT ]
    /**
     *
     * check if the user accepts the sellers credit payment method
     *
     * @return bool
     */
    public function acceptSellersCredit()
    {
        if ($this->userPaymentMode() == 'account') {
            return true;
        }

        return false;
    }

    /**
     *
     * check if the user can pay for a transaction using the sellers credit payment method
     * TODO: also add check for pending bids/offers to be calculated here - if automatic payments are enabled that is.
     *
     * @param float                     $amount
     * @param string                    $currency
     *
     * @return boolean|float
     */
    public function canPaySellersCredit($amount, $currency)
    {
        if ($this->userPaymentMode() == 'account') {
            $currenciesService = new Service\Table\Currencies();

            $amount = $currenciesService->convertAmount($amount, $currency);

            $balance = (-1) * $this->getData('balance');

            if ($balance >= $amount) {
                return true;
            }
            else {
                return ($amount - $balance);
            }

        }

        return false;
    }
    ## -- END :: ADD -- [ MOD:- SELLERS CREDIT ]
    
    /**
     *
     * get favorite stores rowset for a certain user
     *
     * @param int $userId
     *
     * @return \Cube\Db\Table\Rowset
     */
    protected function _getFavoriteStores($userId = null)
    {
        $select = null;

        if ($userId !== null) {
            $select = $this->getTable()->select()
                ->where('user_id = ?', $userId);
        }

        return $this->findDependentRowset('\Ppb\Db\Table\FavoriteStores', 'Store', $select);
    }

    /**
     *
     * return sluggized store name value
     * uses the cleanString method from the Url view helper
     *
     * @param string $storeName
     *
     * @return string
     */
    protected function _sluggizeStoreName($storeName)
    {
        $usersService = new Service\Users();

        $duplicate = true;
        do {
            $storeSlug = UrlViewHelper::cleanString($storeName);

            $select = $usersService->getTable()->select()
                ->reset(Select::COLUMNS)
                ->columns(array('nb_rows' => new Expr('count(*)')))
                    ->where('store_slug = ?', $storeSlug)
                ->where('id != ?', $this->getData('id'));

            $stmt = $select->query();

            $nbRows = (integer)$stmt->fetchColumn('nb_rows');

            if ($nbRows > 0) {
                $storeName .= '1';
            }
            else {
                $duplicate = false;
            }
        } while ($duplicate === true);

        return $storeSlug;
    }

    ## -- START :: ADD -- [ MOD:- BANK TRANSFER ]
    /**
     *
     * get a user's bank account from the bank accounts table
     *
     * @param int|null $id the id of the bank account or null
     *
     * @return \Ppb\Db\Table\Row\BankAccount|null   will return an bank account row object or null if the user has no bank account saved
     */
    public function getBankAccount($id = null)
    {
        $select = $this->getTable()->select();
        if ($id) {
            $select->where('id = ?', $id);
        }

        $rowset = $this->findDependentRowset('\Ppb\Db\Table\BankAccounts', null, $select);

        $result = $rowset->getRow(0);


        return $result;
    }
    ## -- END :: ADD -- [ MOD:- BANK TRANSFER ]
}

