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
 * app module acl
 */
/**
 * MOD:- BANK TRANSFER
 * MOD:- MOLLIE GATEWAY INTEGRATION
 */
 
namespace App\Model;

use Cube\Permissions;

class Acl extends Permissions\Acl
{

    public function __construct()
    {
        $guest = new Permissions\Role('Guest');
        $incomplete = new Permissions\Role('Incomplete');
        $suspended = new Permissions\Role('Suspended');
        $user = new Permissions\Role('User');
        $buyer = new Permissions\Role('Buyer');
        $seller = new Permissions\Role('Seller');
        $buyerSeller = new Permissions\Role('BuyerSeller');

        $this->addRole($guest);
        $this->addRole($incomplete, $guest);
        $this->addRole($suspended, $guest);
        $this->addRole($user, $guest);
        $this->addRole($buyer, $user);
        $this->addRole($seller, $user);
        $this->addRole($buyerSeller, array($buyer, $seller));

        $index = new Permissions\Resource('Index');
        $sections = new Permissions\Resource('Sections');
        $uploader = new Permissions\Resource('Uploader');
        $async = new Permissions\Resource('Async');
        $payment = new Permissions\Resource('Payment');
        $cron = new Permissions\Resource('Cron');
        $typeahead = new Permissions\Resource('Typeahead');
        $rss = new Permissions\Resource('Rss');
        $cms = new Permissions\Resource('Cms');

        $this->addResource($index);
        $this->addResource($sections);
        $this->addResource($uploader);
        $this->addResource($async);
        $this->addResource($payment);
        $this->addResource($cron);
        $this->addResource($typeahead);
        $this->addResource($rss);
        $this->addResource($cms);

        $this->allow('Guest', 'Index');
        $this->allow('Guest', 'Sections');

        // the flash component doesnt store the session
        $this->allow('Guest', 'Uploader');

        // async controller - allowed for everyone
        $this->allow('Guest', 'Async');

        $this->allow('Guest', 'Typeahead');
        $this->allow('Guest', 'Rss');

        // payment controller - signup fee, ipn, completed and failed actions are allowed for everyone,
        // all other fees only allowed if a user is logged in
        $this->allow('Guest', 'Payment', 'UserSignup');
        $this->allow('Guest', 'Payment', 'Ipn');
        $this->allow('Guest', 'Payment', 'Completed');
        $this->allow('Guest', 'Payment', 'Failed');

        ## -- START :: ADD -- [ MOD:- BANK TRANSFER ]
        // guest access needed for when the admin would pay users using pagseguro or bank transfer
        $this->allow('Guest', 'Payment', 'BankTransfer');
        ## -- END :: ADD -- [ MOD:- BANK TRANSFER ]

        ## -- ADD 1L -- [ MOD:- MOLLIE GATEWAY INTEGRATION ]
        $this->allow('Guest', 'Payment', 'Mollie');
        $this->allow('Suspended', 'Payment', 'CreditBalance');
        $this->allow('Incomplete', 'Payment', 'DirectPayment');
        $this->allow('User', 'Payment');

        // cron jobs controller - allowed for everyone
        $this->allow('Guest', 'Cron');

        // cms controller - allowed for everyone
        $this->allow('Guest', 'Cms');

        /* listings module */
        $listingResource = new Permissions\Resource('Listing');
        $this->addResource($listingResource);
        $this->allow('Seller', 'Listing');
    }

}

