<?php

/**
 *
 * PHP Pro Bid
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2018 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     8.0 [rev.8.0.02]
 */

/**
 * this plugin will initialize the live listing updates module
 */

namespace App\Controller\Plugin;

use Cube\Controller\Plugin\AbstractPlugin,
    Cube\Controller\Front,
    Ppb\View\Helper\Countdown as CountdownHelper;

class ListingUpdates extends AbstractPlugin
{

    const TIMEOUT = 10000; // 10 seconds

    /**
     *
     * we get the view and initialize the css and js for the plugin
     */
    public function preDispatch()
    {
        $bootstrap = Front::getInstance()->getBootstrap();

        $settings = $bootstrap->getResource('settings');

        $enableListingUpdates = isset($settings['enable_listing_updates']) ? $settings['enable_listing_updates'] : false;

        if ($enableListingUpdates) {
            $view = $bootstrap->getResource('view');

            /** @var \Cube\View\Helper\Script $scriptHelper */
            $scriptHelper = $view->getHelper('script');

            $countdownHelper = new CountdownHelper();

            $updatesInterval = isset($settings['listing_updates_interval']) ? abs($settings['listing_updates_interval']) : null;;
            $updatesInterval = ($updatesInterval > 0) ? $updatesInterval * 1000 : self::TIMEOUT;

            $scriptHelper->addBodyCode('
                <script type="text/javascript">
                    var updatesTimestamp = null;
                    var listingIds = [];
                    var listingBoxes = $("[data-listing-id]");

                    listingBoxes.each(function (i, element) {
                        var listingId = $(element).data("listing-id");

                        if($.inArray(listingId, listingIds) === -1) {
                            listingIds[i] = listingId;
                        }
                    });

                    function waitForListingUpdatesData() {
                        if (listingIds.length > 0) {
                            $.ajax({
                                type: "GET",
                                url: "' . $view->url(array('module' => 'app', 'controller' => 'async', 'action' => 'listing-updates')) . '",
                                data: {
                                    timestamp: updatesTimestamp,
                                    ids: listingIds
                                },
                                async: true,
                                cache: false,

                                success: function (data) {
                                    $.each(data.data, function (i, listing) {
                                        var box = $(\'[data-listing-id="\' + listing.id + \'"]\');

//                                        var elements = box.find("div[class^=\'myclass\'], div[class*=\' myclass\']");
//
//                                        $.each(elements, function(j, element) {
//                                            alert(element.prop("class"));
//                                        });
//
                                        box.find(".au-price").html(listing.price);
                                        box.find(".au-status").html(listing.status);
                                        box.find(".au-countdown").html(listing.countdown);
                                        box.find(".au-start-time").html(listing.startTime);
                                        box.find(".au-end-time").html(listing.endTime);
                                        box.find(".au-nb-bids").html(listing.nbBids);
                                        box.find(".au-nb-offers").html(listing.nbOffers);
                                        box.find(".au-nb-sales").html(listing.nbSales);
                                        box.find(".au-bids-history").html(listing.bidsHistory);
                                        box.find(".au-offers-history").html(listing.offersHistory);
                                        box.find(".au-sales-history").html(listing.salesHistory);
                                        box.find(".au-minimum-bid").html(listing.minimumBid);
                                        box.find(".au-your-bid").html(listing.yourBid);
                                        box.find(".au-your-bid-status").html(listing.yourBidStatus);
                                        box.find(".au-reserve").html(listing.reserve);
                                        box.find(".au-activity").html(listing.activity);
                                    });

                                    // initialize jquery countdown plugin
                                    ' . $countdownHelper->countdownJavascript() . '

                                    updatesTimestamp = data.timestamp;

                                    setTimeout("waitForListingUpdatesData()", ' . $updatesInterval . ');
                                },
                                error: function (XMLHttpRequest, textStatus, errorThrown) {
                                    setTimeout("waitForListingUpdatesData()", ' . $updatesInterval . ');
                                }
                            });
                        }
                    }

                    jQuery(document).ready(function ($) {
                        waitForListingUpdatesData();
                    });

                </script>');
        }
    }

}

