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
 * user verification check controller plugin class
 * the plugin will be called when trying to list or to purchase an item.
 * - if mandatory seller verification is enabled and the listing create/edit page is accessed,
 * the user will be redirected to the verification page
 * - if mandatory buyer verification is enabled and the purchase confirm page is accessed (or the shopping cart checkout button is clicked),
 * the user will be redirected to the verification page
 *
 * @7.9: added clearRequestMethod method so that the verification page isnt accidentally submitted for example when clicking on "place bid"
 * and being automatically redirected
 * @7.10: added the cart checkout action as part of the mandatory buyer verification check
 * @8.0: only cart checkout and listing create are now checked, any purchase related actions (bid, buy, make offer, add to cart) are checked
 * from the Purchase/Confirm action
 *
 * TODO: maybe we will renounce in using this plugin and simply move all the stuff in the respective actions. Because for selling we can simply redirect to the dashboard, and for the checkout we will redirect to the cart page.
 */

namespace Listings\Controller\Plugin;

use Cube\Controller\Plugin\AbstractPlugin,
    Cube\Controller\Front,
    Ppb\Db\Table\Row\User as UserModel;

class UserVerificationCheck extends AbstractPlugin
{

    public function preDispatch()
    {
        $request = $this->getRequest();

        $controller = $request->getController();
        $action = $request->getAction();

        if (
            ($controller == 'Cart' && $action == 'Checkout') ||
            ($controller == 'Listing' && $action == 'Create')
        ) {
            $bootstrap = Front::getInstance()->getBootstrap();
            $user = $bootstrap->getResource('user');
            $settings = $bootstrap->getResource('settings');

            if ($user instanceof UserModel) {
                if (!$user->isVerified()) {
                    if (
                        ($settings['buyer_verification_mandatory'] && ($controller == 'Cart' && $action == 'Checkout')) ||
                        ($settings['seller_verification_mandatory'] && ($controller == 'Listing' && $action == 'Create'))
                    ) {
                        $url = $bootstrap->getResource('view')->url(array(
                            'module'     => 'members',
                            'controller' => 'user',
                            'action'     => 'verification'
                        ));

                        $this->getResponse()
                            ->setRedirect($url, 302)
                            ->sendHeaders();
                    }
                }
            }
        }
    }

}

