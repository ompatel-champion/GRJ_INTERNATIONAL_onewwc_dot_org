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
 * members module - buying controller
 */

namespace Members\Controller;

use Members\Controller\Action\AbstractAction,
    Cube\Paginator,
    Cube\Crypt,
    Cube\Controller\Front,
    Cube\Http\Download,
    Ppb\Service,
    Ppb\Db\Table\Row\Bid as BidModel,
    Ppb\Db\Table\Row\SaleListing as SaleListingModel;

class Buying extends AbstractAction
{

    public function Bids()
    {
        $keywords = $this->getRequest()->getParam('keywords');
        $listingId = $this->getRequest()->getParam('listing_id');
        $summary = $this->getRequest()->getParam('summary');

        $listingsService = new Service\Listings();

        $select = $listingsService->select(Service\Listings::SELECT_SIMPLE, array(
            'listing_id' => $listingId,
            'keywords'   => $keywords,
            'filter'     => array('active', 'open', 'bids')
        ));
        $select->where('bids.user_id = ?', $this->_user['id']);

        $paginator = new Paginator(
            new Paginator\Adapter\DbTableSelect($select, $listingsService->getTable()));

        $pageNumber = $this->getRequest()->getParam('page');
        $paginator->setPageRange(5)
            ->setItemCountPerPage(10)
            ->setCurrentPageNumber($pageNumber);

        return array(
            'keywords'        => $keywords,
            'listingId'       => $listingId,
            'paginator'       => $paginator,
            'listingsService' => $listingsService,
            'messages'        => $this->_flashMessenger->getMessages(),
            'params'          => $this->getRequest()->getParams(),
            'summary'         => $summary,
        );
    }

    public function RetractBid()
    {
        $id = $this->getRequest()->getParam('id');

        $bidsService = new Service\Bids();

        /** @var \Ppb\Db\Table\Row\Bid $bid */
        $bid = $bidsService->findBy('id', $id);

        $translate = $this->getTranslate();

        $result = false;
        if ($bid instanceof BidModel) {
            $result = $bid->retract();
        }

        if ($result === true) {
            $this->_flashMessenger->setMessage(array(
                'msg'   => sprintf(
                    $translate->_("Your bid #%s has been retracted successfully."),
                    $id),
                'class' => 'alert-success',
            ));
        }
        else {
            $this->_flashMessenger->setMessage(array(
                'msg'   => $this->_("Error: the bid cannot be retracted."),
                'class' => 'alert-danger',
            ));
        }

        $this->_helper->redirector()->redirect('bids');
    }

    public function Download()
    {
        $this->_setNoLayout();
        $options = Front::getInstance()->getOption('session');

        $translate = $this->getTranslate();

        $crypt = new Crypt();
        $crypt->setKey($options['secret']);

        $key = str_replace(' ', '+', $_REQUEST['key']);

        $saleId = null;

        $array = explode(
            Service\Table\SalesListings::KEY_SEPARATOR, $crypt->decrypt($key));
        $listingMediaId = isset($array[0]) ? intval($array[0]) : null;
        $saleListingId = isset($array[1]) ? intval($array[1]) : null;

        if ($listingMediaId && $saleListingId) {
            $salesListingsService = new Service\Table\SalesListings();

            /** @var \Ppb\Db\Table\Row\SaleListing $saleListing */
            $saleListing = $salesListingsService->findBy('id', $saleListingId);
            $saleId = $saleListing->getData('sale_id');

            if ($saleListing instanceof SaleListingModel) {
                /** @var \Ppb\Db\Table\Row\Sale $sale */
                $sale = $saleListing->findParentRow('\Ppb\Db\Table\Sales');

                if ($sale->isBuyer()) {
                    $digitalDownload = $saleListing->getDigitalDownloads($listingMediaId);

                    if ($digitalDownload !== false) {
                        if ($digitalDownload['active']) {

                            $saleListing->countDownload($listingMediaId);

                            $filePath = \Ppb\Utility::getPath('base') . DIRECTORY_SEPARATOR .
                                $this->_settings['digital_downloads_folder'] . DIRECTORY_SEPARATOR .
                                $digitalDownload['value'];

                            $download = new Download($filePath);
                            $download->send();

                            $this->_flashMessenger->setMessage(array(
                                'msg'   => sprintf(
                                    $translate->_("Error: the file %s does not exist."),
                                    $digitalDownload['value']),
                                'class' => 'alert-danger',
                            ));

                        }
                    }
                }
            }
        }

        $this->_flashMessenger->setMessage(array(
            'msg'   => $this->_("Unable to download the requested file."),
            'class' => 'alert-danger',
        ));

        $this->_helper->redirector()->redirect('browse', 'invoices', null,
            array('type' => 'bought', 'sale_id' => $saleId));
    }
}

