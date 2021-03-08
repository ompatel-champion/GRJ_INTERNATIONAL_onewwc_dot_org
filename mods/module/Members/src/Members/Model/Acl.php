<?php

/**
 *
 * PHP Pro Bid
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2019 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     8.1 [rev.8.1.01]
 */
/**
 * members module acl
 */
/**
 * MOD:- DISCOUNT RULES
 * MOD:- EBAY IMPORTER
 *
 * @version 3.1
 */
 
namespace Members\Model;

use Cube\Permissions,
    Cube\Controller\Front;

class Acl extends Permissions\Acl
{

    public function __construct()
    {
        $settings = Front::getInstance()->getBootstrap()->getResource('settings');

        /* create roles */
        $guest = new Permissions\Role('Guest');
        $incomplete = new Permissions\Role('Incomplete');
        $suspended = new Permissions\Role('Suspended');
        $user = new Permissions\Role('User');
        $buyer = new Permissions\Role('Buyer');
        $seller = new Permissions\Role('Seller');
        $buyerSeller = new Permissions\Role('BuyerSeller');

        $this->addRole($guest);
        $this->addRole($incomplete);
        $this->addRole($suspended);
        $this->addRole($user);
        $this->addRole($buyer, $user);
        $this->addRole($seller, $user);
        $this->addRole($buyerSeller, array($buyer, $seller));


        /* create resources */
        $userResource = new Permissions\Resource('User');
        $summaryResource = new Permissions\Resource('Summary');
        $messagingResource = new Permissions\Resource('Messaging');
        $invoicesResource = new Permissions\Resource('Invoices');
        $offersResource = new Permissions\Resource('Offers');
        $buyingResource = new Permissions\Resource('Buying');
        $sellingResource = new Permissions\Resource('Selling');
        $storeSetupResource = new Permissions\Resource('Store');
        $reputationResource = new Permissions\Resource('Reputation');
        $profileResource = new Permissions\Resource('Profile');
        $accountResource = new Permissions\Resource('Account');
        $toolsResource = new Permissions\Resource('Tools');
        $storesResource = new Permissions\Resource('Stores');

        $this->addResource($userResource);
        $this->addResource($summaryResource);

        $this->addResource($invoicesResource);
        $this->addResource($offersResource);
        $this->addResource($buyingResource);
        $this->addResource($sellingResource);
        $this->addResource($storeSetupResource);
        $this->addResource($profileResource);
        $this->addResource($accountResource);
        $this->addResource($toolsResource);

        if ($settings['enable_messaging']) {
            $this->addResource($messagingResource);
            $this->allow(array('Suspended', 'User'), 'Messaging');
        }

        // stores controller - allowed for everyone if stores are enabled
        if ($settings['enable_stores']) {
            $this->addResource($storesResource);
            $this->allow(array('Guest', 'Incomplete', 'Suspended', 'User'), 'Stores');
        }

        if ($settings['enable_reputation']) {
            $this->addResource($reputationResource);
            $this->allow('User', 'Reputation');
            $this->allow(array('Guest', 'Incomplete', 'Suspended'), 'Reputation', 'Details');
        }

        /* create rules */
        $this->allow('Guest', 'User');
        $this->deny('Guest', 'User', 'Activate');
        $this->deny('Guest', 'User', 'Verification');
        $this->deny('Guest', 'User', 'Logout');

        $this->allow('Incomplete', 'User', 'Activate');
        $this->allow('Incomplete', 'User', 'ConfirmRegistration');
        $this->allow('Incomplete', 'User', 'Logout');

        $this->allow('Suspended', 'User', 'Logout');
        $this->allow('Suspended', 'Summary');
        $this->allow('Suspended', 'Account');

        $this->allow('User', 'User', 'Edit');
        $this->allow('User', 'User', 'EditPaymentGateway');
        $this->allow('User', 'User', 'Verification');
        $this->allow('User', 'User', 'Logout');
        $this->allow('User', 'Summary');

        $this->allow(array('Incomplete', 'Suspended', 'User'), 'User', 'NewsletterUnsubscribe');
        $this->allow(array('Guest', 'Incomplete', 'Suspended', 'User'), 'User', 'NewsletterSubscriptionConfirmation');

        $this->allow('User', 'Profile');
        $this->allow('User', 'Account');
        $this->allow('User', 'Tools');

        $this->deny('Buyer', 'Account', 'AccountSettings');

        $this->allow(array('Guest', 'Incomplete', 'Suspended'), 'Tools', 'WatchedItems');

        if (!$settings['enable_shipping']) {
            $this->deny('User', 'Tools', 'PostageSetup');
        }

        if (!$settings['enable_postmen']) {
            $this->deny('User', 'Tools', 'Postmen');
        }

        if (!$settings['enable_bulk_lister']) {
            $this->deny('User', 'Tools', 'BulkLister');
        }

        $this->allow('Buyer', 'Invoices');
        $this->allow('Guest', 'Invoices', 'InvoiceTotals');

        $this->allow('Buyer', 'Offers');
        $this->allow('Buyer', 'Buying');

        $this->deny('Buyer', 'Tools', 'GlobalSettings');
        $this->deny('Buyer', 'Tools', 'FeesCalculator');
        $this->deny('Buyer', 'Tools', 'PostageSetup');
        $this->deny('Buyer', 'Tools', 'PrefilledFields');
        $this->deny('Buyer', 'Tools', 'BlockUsers');
        $this->deny('Buyer', 'Tools', 'SellerVouchers');
        $this->deny('Buyer', 'Tools', 'Vouchers');
        $this->deny('Buyer', 'Tools', 'RefundRequests');
        $this->deny('Buyer', 'Tools', 'BulkLister');
        $this->deny('Buyer', 'Tools', 'SocialMedia');

        if (!$settings['enable_social_media_user']) {
            $this->deny('User', 'Tools', 'SocialMedia');
        }
        
        ## -- START :: ADD -- [ MOD:- DISCOUNT RULES @version 1.0 ]
        $this->deny('Buyer', 'Tools', 'DiscountRules');
        ## -- END :: ADD -- [ MOD:- DISCOUNT RULES @version 1.0 ]
        
        ## -- ADD -- [ MOD:- EBAY IMPORTER ]
        if (!$settings['enable_ebay_importer']) {
            $this->deny('User', 'Tools', 'EbayImport');
        }
        ## -- ./ADD -- [ MOD:- EBAY IMPORTER ]

        $this->deny('Seller', 'Tools', 'WatchedItems');
        $this->deny('Seller', 'Tools', 'FavoriteStores');
        $this->deny('Seller', 'Tools', 'KeywordsWatch');

        $this->allow('Seller', 'Invoices');
        $this->allow('Seller', 'Offers');
        $this->allow('Seller', 'Selling');

        $this->allow('Seller', 'Store');

        if (!$settings['custom_stores_categories']) {
            $this->deny('Seller', 'Store', 'Categories');
        }


        /* listings module */
        $listingResource = new Permissions\Resource('Listing');
        $this->addResource($listingResource);
        $this->allow('Seller', 'Listing');
    }

}

