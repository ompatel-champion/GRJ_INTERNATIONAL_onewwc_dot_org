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
 * members module - offers management controller
 * (buyer, seller can access these actions)
 *
 * browse offers
 * delete invoices
 */

namespace Members\Controller;

use Members\Controller\Action\AbstractAction,
    Ppb\Service,
    Cube\Paginator;

class Offers extends AbstractAction
{

    /**
     *
     * offers service
     *
     * @var \Ppb\Service\Offers
     */
    protected $_offers;

    /**
     *
     * offer types to display ('selling', 'buying')
     *
     * @var string
     */
    protected $_type;

    public function init()
    {
        $this->_offers = new Service\Offers();
        $this->_type = $this->getRequest()->getParam('type', 'selling');
    }

    public function Browse()
    {
        $filter = $this->getRequest()->getParam('filter', 'all');
        $keywords = $this->getRequest()->getParam('keywords');
        $listingId = $this->getRequest()->getParam('listing_id');

        $listingsService = new Service\Listings();

        $select = $listingsService->select(Service\Listings::SELECT_SIMPLE, array(
            'listing_id' => $listingId,
            'keywords'   => $keywords,
            'filter'     => array('active', 'offers'),
        ));

        switch ($this->_type) {
            case 'buying':
                // user posts an offer
                $select->where('o.user_id = ?', $this->_user['id']);
                break;
            default:
                // user receives an offer
                $select->where('o.receiver_id = ?', $this->_user['id']);
                break;
        }

        if (in_array($filter, array('pending', 'accepted', 'declined', 'withdrawn'))) {
            $select->where('o.status = ?', $filter);
        }

        $paginator = new Paginator(
            new Paginator\Adapter\DbTableSelect($select, $listingsService->getTable()));

        $pageNumber = $this->getRequest()->getParam('page');
        $paginator->setPageRange(5)
            ->setItemCountPerPage(10)
            ->setCurrentPageNumber($pageNumber);

        return array(
            'controller'    => ($this->_type == 'selling') ? 'Selling' : 'Buying',
            'filter'        => $filter,
            'keywords'      => $keywords,
            'listingId'     => $listingId,
            'type'          => $this->_type,
            'paginator'     => $paginator,
            'offersService' => $this->_offers,
            'messages'      => $this->_flashMessenger->getMessages(),
            'params'        => $this->getRequest()->getParams(),
        );
    }

    public function Accept()
    {
        /** @var \Ppb\Db\Table\Row\Offer $offer */
        $offer = $this->_offers->findBy('id', (int)$this->getRequest()->getParam('id'));
        $result = $offer->accept();

        $translate = $this->getTranslate();

        if ($result === true) {
            $this->_flashMessenger->setMessage(array(
                'msg'   => sprintf($translate->_("Offer #%s has been accepted."), $offer['id']),
                'class' => 'alert-success',
            ));

            $this->_helper->redirector()->redirect('details', null, null, array('type' => $this->_type, 'id' => $offer['topic_id']));
        }
        else {
            $this->_flashMessenger->setMessage(array(
                'msg'   => $this->_("There was an error in accepting the offer: there is not enough "
                    . "quantity available or you are not the owner of the item."),
                'class' => 'alert-danger',
            ));
        }

        $this->_helper->redirector()->redirect('browse');
    }

    public function Decline()
    {
        /** @var \Ppb\Db\Table\Row\Offer $offer */
        $offer = $this->_offers->findBy('id', (int)$this->getRequest()->getParam('id'));
        $result = $offer->decline();

        $translate = $this->getTranslate();

        if ($result === true) {
            $this->_flashMessenger->setMessage(array(
                'msg'   => sprintf($translate->_("Offer #%s has been declined."), $offer['id']),
                'class' => 'alert-success',
            ));

            $this->_helper->redirector()->redirect('details', null, null, array('type' => $this->_type, 'id' => $offer['topic_id']));
        }
        else {
            $this->_flashMessenger->setMessage(array(
                'msg'   => $this->_("Error: the offer cannot be declined."),
                'class' => 'alert-danger',
            ));
        }

        $this->_helper->redirector()->redirect('browse');
    }

    public function Withdraw()
    {
        /** @var \Ppb\Db\Table\Row\Offer $offer */
        $offer = $this->_offers->findBy('id', (int)$this->getRequest()->getParam('id'));
        $result = $offer->withdraw();

        $translate = $this->getTranslate();

        if ($result === true) {
            $this->_flashMessenger->setMessage(array(
                'msg'   => sprintf($translate->_("Offer #%s has been withdrawn."), $offer['id']),
                'class' => 'alert-success',
            ));

            $this->_helper->redirector()->redirect('details', null, null, array('type' => $this->_type, 'id' => $offer['topic_id']));
        }
        else {
            $this->_flashMessenger->setMessage(array(
                'msg'   => $this->_("Error: the offer cannot be withdrawn."),
                'class' => 'alert-danger',
            ));
        }

        $this->_helper->redirector()->redirect('browse');
    }

    public function Counter()
    {
        /** @var \Ppb\Db\Table\Row\Offer $offer */
        $offer = $this->_offers->findBy('id', (int)$this->getRequest()->getParam('id'));

        $canCounter = false;
        if ($offer) {
            /** @var \Ppb\Db\Table\Row\Listing $listing */
            $listing = $offer->findParentRow('\Ppb\Db\Table\Listings');

            if ($offer->canCounter($listing)) {
                $canCounter = true;
            }
        }

        if (!$canCounter) {
            $this->_flashMessenger->setMessage(array(
                'msg'   => $this->_("Error: cannot make a counteroffer for this offer."),
                'class' => 'alert-danger',
            ));
            $this->_helper->redirector()->redirect('browse');
        }

        $buyerId = $offer->getBuyerId($listing);

        $usersService = new Service\Users();
        /** @var \Ppb\Db\Table\Row\User $buyer */
        $buyer = $usersService->findBy('id', $buyerId);
        $buyer->setAddress(
            $this->getRequest()->getParam('shipping_address_id'));

        $form = new \Listings\Form\Purchase($offer['type'], $listing, $buyer);
        $form->setType('counter');

        $form->setData(array(
            'summary'            => 1,
            'quantity'           => $offer['quantity'],
            'offer_amount'       => $offer['amount'],
            'product_attributes' => \Ppb\Utility::unserialize($offer['product_attributes']),
        ));


        if ($form->isPost(
            $this->getRequest())
        ) {
            $form->setData(
                $this->getRequest()->getParams());

            if ($form->isValid() === true) {
                $productAttributes = (array)$this->getRequest()->getParam('product_attributes');

                $offer->counter();
                $data = array(
                    'topic_id'           => $offer['topic_id'], // IMPORTANT
                    'receiver_id'        => $offer['user_id'],
                    'quantity'           => $this->getRequest()->getParam('quantity'),
                    'amount'             => $form->getData('offer_amount'),
                    'product_attributes' => (count($productAttributes) > 0) ? serialize($productAttributes) : null,
                );

                $message = $listing->placeBid($data, $offer['type']);

                $this->_flashMessenger->setMessage(array(
                    'msg'   => $this->_($message),
                    'class' => 'alert-success',
                ));

                $this->_helper->redirector()->redirect('details', null, null, array('type' => $this->_type, 'id' => $offer['topic_id']));
            }
            else {
                $this->_flashMessenger->setMessage(array(
                    'msg'   => $form->getMessages(),
                    'class' => 'alert-danger',
                ));
            }
        }

        return array(
            'headline'        => $this->_('Make a Counter Offer'),
            'form'            => $form,
            'listing'         => $listing,
            'isMembersModule' => false,
            'messages'        => $this->_flashMessenger->getMessages(),
        );
    }

    public function Details()
    {
        $id = $this->getRequest()->getParam('id');

        $table = $this->_offers->getTable();

        /** @var \Ppb\Db\Table\Row\Listing $listing */
        $listing = $this->_offers->findBy('id', $id)
            ->findParentRow('\Ppb\Db\Table\Listings');

        $select = $this->_offers->getTable()->select()
            ->where('listing_id = ?', $listing->getData('id'))
            ->where('(user_id = ? OR receiver_id = ?)', $this->_user['id'])
            ->order('created_at DESC');

        $paginator = new Paginator(
            new Paginator\Adapter\DbTableSelect($select, $table));

        $pageNumber = $this->getRequest()->getParam('page');
        $paginator->setPageRange(5)
            ->setItemCountPerPage(10)
            ->setCurrentPageNumber($pageNumber);

        if (!$paginator->getPages()->totalItemCount) {
            $this->_helper->redirector()->notFound();
        }

        $controller = ($listing->isOwner()) ? 'Selling' : 'Buying';

        return array(
            'controller' => $controller,
            'headline'   => $this->_('Offer Details'),
            'listing'    => $listing,
            'type'          => $this->_type,
            'paginator'  => $paginator,
            'messages'   => $this->_flashMessenger->getMessages(),
        );

    }


}
