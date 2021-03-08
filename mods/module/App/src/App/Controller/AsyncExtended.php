<?php

/**
 *
 * PHP Pro Bid
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2015 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.7
 */
/**
 * async extended controller
 */
/**
 * MOD:- EBAY IMPORTER
 *
 * @version 2.0
 */

namespace App\Controller;

use Cube\Controller\Front;
use Ppb\Service;

class AsyncExtended extends Async
{

    public function EbayImportTotalListings()
    {
        $username = $this->getRequest()->getParam('username');
        $marketplace = $this->getRequest()->getParam('marketplace');

        $translate = $this->getTranslate();

        $output = array(
            'username' => $username,
            'listings' => 0,
            'message'  => null,
            'token'    => false,
        );

        if (!empty($username)) {
            $ebayAPIService = new Service\EbayAPI();

            $totalListings = $ebayAPIService->getTotalListings($username, $marketplace);

            $output['listings'] = $totalListings;

            $output['message'] = sprintf(
                $translate->_('<div>The parser has found a total of <strong>%s</strong> listings to be imported.</div>'), intval($totalListings));

            if ($totalListings) {
                $output['message'] .= $translate->_('<div>Important: Duplicates may be included!</div>');
                $output['message'] .= $translate->_('<div>To start the process, click the "Upload" button.</div>');
            }

            if ($this->_settings['ebay_account_verification']) {
                $ebayUsersService = new Service\EbayUsers();

                $ebayUser = $ebayUsersService->findUser($username, $this->_user['id']);

                if (!empty($ebayUser['ebay_token'])) {
                    $output['message'] .= '<p><span class="label label-success">Has Token</span></p>';
                    $output['token'] = true;
                }
                else {
                    $output['message'] .= '<p><span class="label label-danger">No Token</span></p>';
                }

                $params = array(
                    'marketplace' => $marketplace,
                );

                $query = array(
                    'RuName' => $ebayAPIService->getRuName()
                );

                $query['SessID'] = $params['SessionID'] =
                    $ebayAPIService->callTradeAPI('GetSessionID', "\n  <RuName>{$ebayAPIService->getRuName()}</RuName>\n", 'SessionID');

                if (isset($params)) {
                    $query['ruparams'] = http_build_query($params);
                }

                $url = Service\EbayAPI::SIGN_IN_URL . http_build_query($query);

                $output['message'] .= '<div><a href="' . $url . '" class="btn btn-default btn-sm">' . $translate->_('Retrieve Token') . '</a></div>';
            }
            else {
                $output['token'] = true;
            }
        }

        $this->getResponse()->setHeader('Content-Type: application/json');

        $this->_view->setContent(
            json_encode($output));

        return $this->_view;
    }
}

