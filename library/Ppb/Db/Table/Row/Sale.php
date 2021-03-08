<?php

/**
 *
 * PHP Pro Bid
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2020 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     8.2 [rev.8.2.03]
 */

/**
 * sales table row object model
 */

namespace Ppb\Db\Table\Row;

use Ppb\Db\Table\SalesListings,
    Ppb\Db\Table\Vouchers as VouchersTable,
    Cube\Db\Expr,
    Ppb\Service,
    Ppb\Model\Shipping as ShippingModel;

class Sale extends AbstractRow
{

    /**
     * sale payment statuses
     */
    const PAYMENT_UNPAID = 0;
    const PAYMENT_PAID = 1;
    const PAYMENT_PAID_DIRECT_PAYMENT = 2;
    const PAYMENT_PAY_ARRIVAL = 3;


    /**
     * sale shipping statuses
     */
    const SHIPPING_PROCESSING = 0;
    const SHIPPING_SENT = 1;
    const SHIPPING_PROBLEM = 2;
    const SHIPPING_NA = -1;

    /**
     *
     * payment statuses array
     *
     * @var array
     */
    public static $paymentStatuses = array(
        self::PAYMENT_UNPAID              => 'Unpaid',
        self::PAYMENT_PAID                => 'Paid',
        self::PAYMENT_PAID_DIRECT_PAYMENT => 'Paid (Direct Payment)',
        self::PAYMENT_PAY_ARRIVAL         => 'Payment on Arrival',
    );

    /**
     *
     * shipping statuses array
     *
     * @var array
     */
    public static $shippingStatuses = array(
        self::SHIPPING_PROCESSING => 'Processing',
        self::SHIPPING_SENT       => 'Posted/Sent',
        self::SHIPPING_PROBLEM    => 'Problem',
        self::SHIPPING_NA         => 'N/A',
    );


    /**
     *
     * sale data array keys
     *
     * @var array
     */
    protected static $saleDataKeys = array(
        'currency',
        'country',
        'state',
        'address',
        'pickup_options'
    );

    /**
     *
     * sales listings rowset
     * (used to override the sales listings table in order to preview a sale total)
     *
     * @var array
     */
    protected $_salesListings = array();

    /**
     *
     * sales service
     *
     * @var \Ppb\Service\Sales
     */
    protected $_sales;

    /**
     *
     * serializable fields
     *
     * @var array
     */
    protected $_serializable = array('sale_data');

    /**
     *
     * the tax amount that corresponds to the sale
     * calculated by the calculateTotal() method
     *
     * @var bool|float
     */
    protected $_taxAmount = false;

    /**
     *
     * voucher object to be applied
     *
     * @var \Ppb\Db\Table\Row\Voucher|null
     */
    protected $_voucher = false;

    /**
     *
     * get sales listings array / rowset
     *
     * @return array|\Ppb\Db\Table\Rowset\SalesListings
     */
    public function getSalesListings()
    {
        if (empty($this->_salesListings)) {
            $this->setSalesListings(
                $this->findDependentRowset('\Ppb\Db\Table\SalesListings'));
        }

        return $this->_salesListings;
    }

    /**
     *
     * set sales listings array / rowset
     *
     * @param array|\Ppb\Db\Table\Rowset\SalesListings $salesListings
     *
     * @return $this
     */
    public function setSalesListings($salesListings)
    {
        $this->clearSalesListings();

        foreach ($salesListings as $saleListing) {
            $this->addSaleListing($saleListing);
        }

        return $this;
    }

    /**
     *
     * clear sales listings array
     *
     * @return $this
     */
    public function clearSalesListings()
    {
        $this->_salesListings = array();

        return $this;
    }

    /**
     *
     * add sale listing row
     *
     * @param array|\Ppb\Db\Table\Row\SaleListing $saleListing
     *
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function addSaleListing($saleListing)
    {
        if ($saleListing instanceof SaleListing) {
            $this->_salesListings[] = $saleListing;
        }
        else {
            if (!array_key_exists('price', $saleListing) ||
                !array_key_exists('quantity', $saleListing)
            ) {
                throw new \InvalidArgumentException("The sale listing array must include the 'price' and 'quantity' keys");
            }

            $this->_salesListings[] = new SaleListing(array(
                'data'  => $saleListing,
                'table' => new SalesListings(),
            ));
        }

        return $this;
    }

    /**
     *
     * set tax amount
     *
     * @param bool|float $taxAmount
     *
     * @return $this
     */
    public function setTaxAmount($taxAmount)
    {
        $this->_taxAmount = floatval($taxAmount);

        return $this;
    }

    /**
     *
     * get tax amount - calculate it if unset
     *
     * @param bool $force force tax calculation
     *
     * @return bool|float
     */
    public function getTaxAmount($force = false)
    {
        if ($this->_taxAmount === false || $force === true) {
            $this->calculateTotal();
        }

        return $this->_taxAmount;
    }

    /**
     * @param null|\Ppb\Db\Table\Row\Voucher $voucher
     */
    public function setVoucher($voucher)
    {
        $this->_voucher = $voucher;
    }

    /**
     * @return null|\Ppb\Db\Table\Row\Voucher
     */
    public function getVoucher()
    {
        if ($this->_voucher === false) {
            if (($data = \Ppb\Utility::unserialize($this->getData('voucher_details'))) !== false) {
                $voucher = new Voucher(array(
                    'table' => new VouchersTable(),
                    'data'  => $data,
                ));
            }
            else {
                $voucher = null;
            }

            $this->setVoucher($voucher);
        }

        return $this->_voucher;
    }

    /**
     *
     * get ids of listings included in the sale object
     *
     * @return array
     */
    public function getListingsIds()
    {
        $listingsIds = array();

        $salesListings = $this->getSalesListings();


        /** @var \Ppb\Db\Table\Row\SaleListing $saleListing */
        foreach ($salesListings as $saleListing) {
            $listingsIds[] = $saleListing->getData('listing_id');
        }

        return $listingsIds;
    }

    /**
     *
     * the method will calculate the total value of the sale invoice, including postage and insurance amounts
     * "postage_amount" and "insurance_amount" will always be calculated when the sale row is created
     * the total is calculated based on the "tax_rate" field and tax applies to the total amount
     *
     * @param bool $simple       if true, add postage, insurance and tax
     * @param bool $applyVoucher we can opt to not apply the voucher when calculating the total price (for example on the initial cart page)
     *
     * @return float
     */
    public function calculateTotal($simple = false, $applyVoucher = true)
    {
        $result = 0;
        $taxAmount = null;

        $salesListings = $this->getSalesListings();

        $applyVoucher = ($this->getData('pending') && $applyVoucher) ? true : false;

        /** @var \Ppb\Db\Table\Row\SaleListing $saleListing */
        foreach ($salesListings as $saleListing) {
            $result += $saleListing->calculateTotal($applyVoucher);
        }

        if ($simple === false) {
            if ($this->hasPostage()) {
                $result += $this->getData('postage_amount');

                if ($this->getInsuranceAmount()) {
                    $result += $this->getData('insurance_amount');
                }
            }

            if ($this->getData('tax_rate') > 0) {
                $taxAmount = $result * $this->getData('tax_rate') / 100;
                $result += $taxAmount;
            }
        }

        $this->setTaxAmount($taxAmount);

        return $result;
    }

    /**
     *
     * count items in sale invoice (by quantity)
     *
     * @return int
     */
    public function countItems()
    {
        $result = 0;

        $salesListings = $this->getSalesListings();

        /** @var \Ppb\Db\Table\Row\SaleListing $saleListing */
        foreach ($salesListings as $saleListing) {
            $result += $saleListing->getData('quantity');
        }

        return $result;
    }

    /**
     *
     * get the active status of a sale invoice
     * 7.8: by default if a sale is under force payment effect, we treat it as inactive except for the possibility for the
     * buyer to make payment or for the seller to update payment status
     *
     * @param bool $expiresAt
     *
     * @return bool
     */
    public function isActive($expiresAt = true)
    {
        $active = ($this->getData('active') && !$this->getData('pending')) ? true : false;

        return ($expiresAt && $this->getData('expires_at')) ? false : $active;
    }

    /**
     *
     * checks if the sale is marked as paid
     *
     * @return bool
     */
    public function isPaid()
    {
        return ($this->getData('flag_payment') == self::PAYMENT_UNPAID) ? false : true;
    }

    /**
     *
     * whether an invoice can be edited/combined
     * if the logged in user is the buyer, we add an additional flag to check if the sale is locked for editing
     * (as in only the seller / admin can edit it)
     *
     * @8.2 disable editing when a listing from the invoice has been deleted (listing_id = null)
     *
     * @param bool $admin
     *
     * @return bool
     */
    public function canEdit($admin = false)
    {
        if (!count($this)) {
            return false;
        }

        $canEdit =
            ($this->isActive() || $admin) &&
            !$this->isPaid() &&
            $this->getData('buyer_id') &&
            $this->getData('seller_id');

        $countSalesListings = $this->countDependentRowset('\Ppb\Db\Table\SalesListings', null,
            $this->getTable()->select()->where('listing_id is null'));
        if ($countSalesListings > 0) {
            return false;
        }

        $user = $this->getUser();

        if ($user['id'] === $this->getData('buyer_id') && $this->getData('edit_locked')) {
            $canEdit = false;
        }

        return $canEdit;
    }

    /**
     *
     * check if the invoice is marked as deleted
     *
     * @param string $markedBy
     *
     * @return bool
     */
    public function isMarkedDeleted($markedBy = null)
    {
        $sellerDeleted = $this->getData('seller_deleted');
        $buyerDeleted = $this->getData('buyer_deleted');

        if ($markedBy == 'seller') {
            return ($sellerDeleted) ? true : false;
        }

        if ($markedBy == 'buyer') {
            return ($buyerDeleted) ? true : false;
        }

        return ($sellerDeleted || $buyerDeleted) ? true : false;
    }

    /**
     *
     * check if the invoice can be deleted
     *
     * @return bool
     */
    public function canDelete()
    {
        if (!count($this)) {
            return false;
        }

        $canDelete = (
        !$this->getData('expires_at')
        ) ? true : false;

        return $canDelete;
    }

    /**
     *
     * check if the active user can combine this invoice
     * invoices cannot be combined if they are not editable or if a voucher has been applied on one of them
     *
     * @param bool $admin
     *
     * @return bool
     */
    public function canCombinePurchases($admin = false)
    {
        if (!$this->canEdit($admin)) {
            return false;
        }

        if (!$this->getData('voucher_details')) {
            $settings = $this->getSettings();

            if ($this->isSeller() || $admin || $settings['buyer_create_invoices']) {
                return true;
            }
        }

        return false;
    }

    /**
     *
     * objects having the same hash can be combined
     *
     * @return string
     */
    public function combineHash()
    {
        $array = array(
            $this->getData('buyer_id'),
            $this->getData('seller_id'),
            $this->getData('currency'),
            $this->getData('country'),
            $this->getData('state'),
            $this->getData('address'),
            $this->getData('apply_tax', 0),
            $this->getData('pickup_options'),
        );

        return hash('sha256', implode('', $array));
    }

    /**
     *
     * check if the logged in user is the seller in the sale transaction
     *
     * @param bool $admin check if the logged in user is an admin as well
     *
     * @return bool
     */
    public function isSeller($admin = false)
    {
        $user = $this->getUser();

        $result = false;
        if ($this->getData('seller_id') == $user['id']) {
            $result = true;
        }
        else if ($admin == true && $user['role'] == 'Admin') {
            $result = true;
        }

        return $result;
    }

    /**
     *
     * check if the logged in user is the buyer in the sale transaction
     *
     * @return bool
     */
    public function isBuyer()
    {
        $user = $this->getUser();

        if ($this->getData('buyer_id') == $user['id']) {
            return true;
        }

        return false;
    }

    /**
     *
     * get the pickup option that applies to this sale
     *
     * @return string|null
     */
    public function getPickupOptions()
    {
        /** @var \Ppb\Db\Table\Rowset\SalesListings $salesListings */
        $salesListings = $this->findDependentRowset('\Ppb\Db\Table\SalesListings');

        /** @var \Ppb\Db\Table\Row\SaleListing $saleListing */
        foreach ($salesListings as $saleListing) {
            /** @var \Ppb\Db\Table\Row\Listing $listing */
            $listing = $saleListing->findParentRow('\Ppb\Db\Table\Listings');

            return $listing[ShippingModel::FLD_PICKUP_OPTIONS];
        }

        return null;
    }

    /**
     *
     * check if the invoice is pick-up only
     *
     * @return bool
     */
    public function isPickupOnly()
    {
        return $this->getPickupOptions() == ShippingModel::MUST_PICKUP;
    }

    /**
     *
     * return true if pick-up was selected for this invoice
     *
     * @return bool
     */
    public function isPickup()
    {
        return $this->getPostageMethod() == ShippingModel::VALUE_PICK_UP;
    }

    /**
     *
     * get the direct payment methods that apply to this sale
     *
     * @param string $type      type of payment methods to retrieve ('direct', 'offline' or null for all)
     * @param bool   $intersect either intersect or merge
     *
     * @return array|null
     */
    public function getPaymentMethods($type = null, $intersect = true)
    {
        $paymentMethods = null;

        /** @var \Ppb\Db\Table\Rowset\SalesListings $salesListings */
        $salesListings = $this->findDependentRowset('\Ppb\Db\Table\SalesListings');

        /** @var \Ppb\Db\Table\Row\SaleListing $saleListing */
        foreach ($salesListings as $saleListing) {
            /** @var \Ppb\Db\Table\Row\Listing $listing */
            $listing = $saleListing->findParentRow('\Ppb\Db\Table\Listings');
            if ($listing instanceof Listing) {
                $listingPaymentMethods = $listing->getPaymentMethods($type);

                if ($paymentMethods === null) {
                    $paymentMethods = $listingPaymentMethods;
                }
                else {
                    if ($intersect) {
                        $paymentMethods = array_uintersect($paymentMethods, $listingPaymentMethods,
                            function ($a, $b) {
                                return strcmp($a['name'] . $a['type'], $b['name'] . $b['type']);
                            });
                    }
                    else {
                        $paymentMethods = array_merge($paymentMethods, $listingPaymentMethods);
                    }
                }
            }
        }

        return $paymentMethods;
    }

    /**
     *
     * return true if direct payment can be made for the sale by the logged in user (the buyer)
     *
     * @return array|false  an array of direct payment methods or false if payment is not possible
     */
    public function canPayDirectPayment()
    {
        $user = $this->getUser();

        if ($this->isActive(false) &&
            $user['id'] == $this->getData('buyer_id') &&
            !$this->isPaid()
        ) {
            $paymentMethods = $this->getPaymentMethods('direct');

            if (is_array($paymentMethods)) {
                if (count($paymentMethods) > 0) {
                    return $paymentMethods;
                }
            }
        }

        return false;
    }

    /**
     *
     * set expires at flag
     * used by the force payment function
     *
     * @7.8: if not all sale listings are products, disable this function
     * @8.0: force payment limit can be a seller specific setting or a global setting
     *
     * @param bool $reset
     *
     * @return $this
     */
    public function setExpiresFlag($reset = false)
    {
        $settings = $this->getSettings();

        $salesListings = $this->findDependentRowset('\Ppb\Db\Table\SalesListings');

        /** @var \Ppb\Db\Table\Row\SaleListing $saleListing */
        foreach ($salesListings as $saleListing) {
            /** @var \Ppb\Db\Table\Row\Listing $listing */
            $listing = $saleListing->findParentRow('\Ppb\Db\Table\Listings');

            if (!$listing->isProduct()) {
                $reset = true;
                break;
            }
        }

        if ($reset) {
            $flag = new Expr('null');
            $expiresAt = null;
        }
        else {
            /** @var \Ppb\Db\Table\Row\User $seller */
            $seller = $this->findParentRow('\Ppb\Db\Table\Users', 'Seller');
            $forcePaymentLimit = $seller->getGlobalSettings('force_payment_limit');
            if (!$forcePaymentLimit) {
                $forcePaymentLimit = $settings['force_payment_limit'];
            }

            $flag = new Expr('now() + interval ' . $forcePaymentLimit . ' minute');
            $expiresAt = date('Y-m-d H:i:s', time() + $forcePaymentLimit * 60);
        }

        $this->save(array(
            'expires_at' => $flag,
        ));

        $this->addData('expires_at', $expiresAt);

        return $this;
    }

    /**
     *
     * get the insurance amount that will apply to the sale
     *
     * @return float|null
     */
    public function getInsuranceAmount()
    {
        if ($this->getData('apply_insurance')) {
            return $this->getData('insurance_amount');
        }

        return null;
    }

    /**
     *
     * check if postage is available for the selected sale
     * this flag is independent on the enable shipping global setting, to preserve the postage amount
     * in case the global setting was changed
     *
     * @return bool
     */
    public function hasPostage()
    {
        if ($this->getData('enable_shipping')) {
            return true;
        }

        if ($this->getData('pending')) {
            $salesListings = $this->getSalesListings();
            /** @var \Ppb\Db\Table\Row\SaleListing $saleListing */
            foreach ($salesListings as $saleListing) {
                /** @var \Ppb\Db\Table\Row\Listing $listing */
                $listing = $saleListing->findParentRow('\Ppb\Db\Table\Listings');

                if (!$listing->isShipping()) {
                    return false;
                }
            }

            return true;
        }

        return false;
    }

    /**
     *
     * get the postage method for the sale
     *
     * @return string
     */
    public function getPostageMethod()
    {
        if (isset($this->_data['postage']['method'])) {
            return $this->_data['postage']['method'];
        }

        return 'N/A';
    }

    /**
     *
     * get payment status description
     *
     * @return string
     */
    public function getPaymentStatusDescription()
    {
        if (array_key_exists($this->_data['flag_payment'], self::$paymentStatuses)) {
            return self::$paymentStatuses[$this->_data['flag_payment']];
        }

        return 'N/A';
    }

    /**
     *
     * get the sale transaction row object or null if no transaction exists
     *
     * @return \Cube\Db\Table\Row|null
     */
    public function getSaleTransaction()
    {
        $select = $this->getTable()->select()
            ->where('paid = ?', 1);
        /** @var \Cube\Db\Table\Row|null $transaction */
        $transaction = $this->findDependentRowset('\Ppb\Db\Table\Transactions', null, $select)->getRow(0);

        return $transaction;
    }

    /**
     *
     * function that checks if the sale invoice can be viewed
     * only the seller or the buyer can view a sale invoice
     *
     * @return bool
     */
    public function canView()
    {
        if (!count($this) || !$this->isActive()) {
            return false;
        }

        $user = $this->getUser();

        if (in_array($user['id'], array($this->getData('seller_id'), $this->getData('buyer_id')))) {
            return true;
        }

        return false;
    }

    /**
     *
     * checks if the messaging feature is enabled for the sale
     *
     * @return bool
     */
    public function messagingEnabled()
    {
        $settings = $this->getSettings();

        if ($settings['enable_messaging']) {
            return true;
        }

        return false;
    }

    /**
     *
     * create a link to the messaging controller
     * if we have a topic, redirect to the topic, otherwise create a new topic
     *
     * @param bool $admin
     *
     * @return array
     */
    public function messagingLink($admin = false)
    {
        if ($admin) {
            $action = ($this->getData('messaging_topic_id')) ? 'messaging-topic' : 'messaging-create';

            $array = array(
                'module'     => 'admin',
                'controller' => 'tools',
                'action'     => $action,
            );
        }
        else {
            $action = ($this->getData('messaging_topic_id')) ? 'topic' : 'create';

            $array = array(
                'module'     => 'members',
                'controller' => 'messaging',
                'action'     => $action,
            );
        }

        if (stristr($action, 'topic') !== false) {
            $array['id'] = $this->getData('messaging_topic_id');
        }
        else {
            $array['sale_id'] = $this->getData('id');
            $array['topic_type'] = Service\Messaging::SALE_TRANSACTION;
        }

        return $array;
    }

    /**
     *
     * create link to sale invoice
     *
     * @param bool  $admin
     * @param array $params
     *
     * @return array
     */
    public function invoiceLink($admin = false, $params = array())
    {
        if ($admin) {
            $array = array(
                'module'     => 'admin',
                'controller' => 'listings',
                'action'     => 'view-invoice',
            );
        }
        else {
            $array = array(
                'module'     => 'members',
                'controller' => 'invoices',
                'action'     => 'view',
            );
        }

        foreach ($params as $key => $value) {
            $array[$key] = $value;
        }

        $array['id'] = $this->getData('id');

        return array_filter($array);
    }

    /**
     *
     * create a link to the post reputation form
     * will include all listings in the sale transaction that have pending feedback
     *
     * @param int $posterId
     *
     * @return array
     */
    public function reputationLink($posterId)
    {
        $array = array();

        $reputationService = new Service\Reputation();

        $salesListings = $this->findDependentRowset('\Ppb\Db\Table\SalesListings');

        $salesListingsIds = array(0);

        foreach ($salesListings as $saleListing) {
            $salesListingsIds[] = $saleListing['id'];
        }

        $select = $reputationService->getTable()->select()
            ->where('poster_id = ?', $posterId)
            ->where('posted = ?', 0)
            ->where('sale_listing_id IN (?)', $salesListingsIds);

        $reputations = $reputationService->fetchAll($select);

        $ids = array();

        foreach ($reputations as $reputation) {
            $ids[] = $reputation['id'];
        }

        if (count($ids) > 0) {
            $array = array(
                'module'     => 'members',
                'controller' => 'reputation',
                'action'     => 'post',
                'id'         => $ids,
            );
        }

        return $array;
    }

    /**
     *
     * edit invoice link
     *
     * @param bool  $admin
     * @param array $params
     *
     * @return array
     */
    public function editLink($admin = false, $params = array())
    {
        if ($admin) {
            $array = array(
                'module'     => 'admin',
                'controller' => 'listings',
                'action'     => 'edit-invoice',
            );
        }
        else {
            $array = array(
                'module'     => 'members',
                'controller' => 'invoices',
                'action'     => 'edit',
            );
        }

        foreach ($params as $key => $value) {
            $array[$key] = $value;
        }

        $array['sale_id'] = $this->getData('id');

        return array_filter($array);
    }

    /**
     *
     * update status link
     *
     * @param bool $admin
     *
     * @return array
     */
    public function updateStatusLink($admin = false)
    {
        if ($admin) {
            $array = array(
                'module'     => 'admin',
                'controller' => 'listings',
                'action'     => 'update-invoice-status',
            );
        }
        else {
            $array = array(
                'module'     => 'members',
                'controller' => 'invoices',
                'action'     => 'update-status',
            );
        }

        $array['sale_id'] = $this->getData('id');

        return $array;
    }

    /**
     *
     * delete invoice link
     *
     * @param bool $admin
     *
     * @return array
     */
    public function deleteLink($admin = false)
    {
        if ($admin) {
            $array = array(
                'module'     => 'admin',
                'controller' => 'listings',
                'action'     => 'delete-invoice',
            );
        }
        else {
            $array = array(
                'module'     => 'members',
                'controller' => 'invoices',
                'action'     => 'delete',
            );
        }

        $array['sale_id'] = $this->getData('id');

        return $array;
    }

    /**
     *
     * check if the buyer can checkout the cart
     * - logged in user needs to be different than the seller
     * - check for available inventory
     * - if shipping is enabled, check if the item shipping to the buyer's selected address
     * - we need to have a shopping cart
     *
     * @return bool|string  true if ok, a message otherwise
     */
    public function canCheckout()
    {
        $translate = $this->getTranslate();
        $user = $this->getUser();

        $userId = (!empty($user['id'])) ? $user['id'] : null;

        if (!$this->getData('pending')) {
            return sprintf($translate->_('Invalid shopping cart selected.'));
        }
        else if ($userId == $this->getData('seller_id')) {
            return sprintf($translate->_('You cannot purchase your own products.'));
        }

        return true;
    }

    /**
     *
     * checks whether the logged in user can pay sale fee or not
     * params are needed for when calling the method for the sale / purchase emails
     *
     * @param bool $isBuyer
     * @param bool $isSeller
     *
     * @return bool
     */
    public function canPayFee($isBuyer = false, $isSeller = false)
    {
        if ($this->isActive(false)) {
            return false;
        }

        $settings = $this->getSettings();
        $user = $this->getUser();

        switch ($settings['sale_fee_payer']) {
            case 'buyer':
                if ($user['id'] == $this->getData('buyer_id') || $isBuyer) {
                    return true;
                }
                break;
            case 'seller':
                if ($user['id'] == $this->getData('seller_id') || $isSeller) {
                    return true;
                }
                break;
        }

        return false;
    }

    /**
     *
     * determine and set the 'active' flag for the sale
     *
     * @param int $active
     *
     * @return $this
     */
    public function updateActive($active = 1)
    {
        $this->save(array(
            'active' => (int)$active,
        ));

        return $this;
    }

    /**
     *
     * update payment and shipping statuses for the sale object
     * also reset expires at flag if payment status is marked as paid - one way action
     *
     * @param array $post
     *
     * @return $this
     */
    public function updateStatus($post)
    {
        $saleData = (array)$this->getData('sale_data');
        $saleData['tracking_link'] = $post['tracking_link'];

        $this->saveSaleData(array(
            'tracking_link' => $post['tracking_link'],
        ));

        $data = array(
            'flag_shipping' => $post['flag_shipping'],
            'flag_payment'  => $post['flag_payment'],
        );

        if ($post['flag_payment'] != self::PAYMENT_UNPAID) {
            $this->setExpiresFlag(true);
        }

        parent::save($data);

        return $this;
    }

    /**
     *
     * save sale data serialized field
     *
     * @param array $post
     *
     * @return $this
     */
    public function saveSaleData($post)
    {
        $saleData = array_merge((array)\Ppb\Utility::unserialize($this->getData('sale_data')), $post);

        foreach (self::$saleDataKeys as $key) {
            if (!array_key_exists($key, $saleData)) {
                $saleData[$key] = null;
            }
        }

        $settings = $this->getSettings();
        $saleData['enable_shipping'] = ($settings['enable_shipping']) ? true : false;

        $data = array(
            'sale_data' => serialize($saleData),
        );

        parent::save($data);

        return $this;
    }

    /**
     *
     * save serialized voucher details
     *
     * @param string|\Ppb\Db\Table\Row\Voucher $voucher voucher code or voucher object
     *
     * @return $this
     */
    public function saveVoucherDetails($voucher)
    {
        if (!$voucher instanceof Voucher) {
            $vouchersService = new Service\Vouchers();
            $voucher = $vouchersService->findBy($voucher, $this->getData('seller_id'));
        }

        if ($voucher instanceof Voucher) {
            $voucher = ($voucher->isValid()) ? serialize($voucher->getData()) : null;
        }

        $this->save(array(
            'voucher_details' => $voucher
        ));

        return $this;
    }

    /**
     *
     * update sales listings quantities
     * doesnt check for available quantity - this needs to be checked in the form
     * it is also checked in the canCheckout method
     *
     * @param array $quantities
     *
     * @return $this
     */
    public function updateQuantities(array $quantities)
    {
        $salesListings = $this->getSalesListings();

        /** @var \Ppb\Db\Table\Row\SaleListing $saleListing */
        foreach ($salesListings as $saleListing) {
            $key = $saleListing['id'];
            if (array_key_exists($key, $quantities)) {
                $quantity = ($quantities[$key] > 1) ? $quantities[$key] : 1;
                $saleListing->save(array(
                    'quantity' => $quantity,
                ));
            }
        }

        return $this;
    }

    /**
     *
     * revert the sale then delete it
     * the following actions will be taken:
     * - the quantities of the listings will be reset
     * - the sale transaction fee will be refunded (if payer in account mode)
     * - delete related reputation table rows
     * - TODO: if listings were closed ahead of time - reopen them
     *
     * @return $this
     */
    public function revert()
    {
        // revert quantities for each listing in the sale
        $salesListings = $this->getSalesListings();

        /** @var \Ppb\Db\Table\Row\SaleListing $saleListing */
        foreach ($salesListings as $saleListing) {
            /** @var \Ppb\Db\Table\Row\Listing $listing */
            $listing = $saleListing->findParentRow('\Ppb\Db\Table\Listings');

            if ($listing instanceof Listing) {
                $listing->updateQuantity($saleListing->getData('quantity'), $saleListing['product_attributes'], Listing::ADD);
            }

            // delete the reputation rows for each sale listing in the sale
            $saleListing->findDependentRowset('\Ppb\Db\Table\Reputation')->delete();
        }

        // refund sale transaction fee(s)
        $accountingRowset = $this->findDependentRowset('\Ppb\Db\Table\Accounting');

        /** @var \Ppb\Db\Table\Row\Accounting $accounting */
        foreach ($accountingRowset as $accounting) {
            $accounting->acceptRefundRequest(true);
        }

        $this->delete(true);


        return $this;
    }

    /**
     *
     * mark deleted if user is buyer or seller, or remove from database if admin
     *
     * @param bool $admin
     *
     * @return int|bool
     */
    public function delete($admin = false)
    {
        if ($admin === true) {
            return parent::delete();
        }

        $user = $this->getUser();

        if ($user['id'] == $this->getData('seller_id')) {
            $this->save(array(
                'seller_deleted' => 1
            ));

            return true;
        }
        else if ($user['id'] == $this->getData('buyer_id')) {
            $this->save(array(
                'buyer_deleted' => 1
            ));

            return true;
        }

        return false;
    }
}
