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

namespace Admin\Controller;

use Ppb\Controller\Action\AbstractAction,
    Ppb\Service\Listings as ListingsService,
    Ppb\Service\Sales as SalesService,
    Ppb\Service\Table\SalesListings as SalesListingsService,
    Cube\Paginator,
    Listings\Form\Listing as ListingForm,
    Ppb\Db\Table\Row\Listing as ListingModel,
    Ppb\Db\Table\Row\Sale as SaleModel;

class Listings extends AbstractAction
{

    /**
     *
     * listings service
     *
     * @var \Ppb\Service\Listings
     */
    protected $_listings;

    public function init()
    {
        $this->_listings = new ListingsService();
    }

    public function Browse()
    {
        $select = $this->_listings->select(ListingsService::SELECT_SIMPLE, $this->getRequest()->getParams());

        if ($this->getRequest()->isPost()) {
            $id = $this->getRequest()->getParam('id');
            $option = $this->getRequest()->getParam('option');

            $ids = array_filter(
                array_values((array)$id));

            $counter = null;
            $messages = array();

            if (count($ids) > 0) {
                $where = $this->_listings->getTable()->getAdapter()->quoteInto("id IN (?)", $ids);
                $listings = $this->_listings->fetchAll($where);
                $messages = $listings->changeStatus($option, true);

                $counter = $listings->getCounter();
            }

            if ($counter > 0) {
                $this->_flashMessenger->setMessage(array(
                    'msg'   => sprintf($this->_listings->getStatusMessage($option, $counter), $counter),
                    'class' => 'alert-success',
                ));
            }
            else {
                $this->_flashMessenger->setMessage(array(
                    'msg'   => $this->_('No listings have been updated.'),
                    'class' => 'alert-danger',
                ));
            }

            if (count($messages) > 0) {
                $this->_flashMessenger->setMessage(array(
                    'msg'   => $messages,
                    'class' => 'alert-info',
                ));
            }
        }

        $itemsPerPage = $this->getRequest()->getParam('limit', 20);
        $itemsPerPage = ($itemsPerPage > 80) ? 80 : $itemsPerPage;

        $pageNumber = $this->getRequest()->getParam('page');

        $paginator = new Paginator(
            new Paginator\Adapter\DbTableSelect($select, $this->_listings->getTable()));
        $paginator->setPageRange(5)
            ->setItemCountPerPage($itemsPerPage)
            ->setCurrentPageNumber($pageNumber);

        return array(
            'paginator'    => $paginator,
            'messages'     => $this->_flashMessenger->getMessages(),
            'type'         => $this->getRequest()->getParam('type'),
            'filter'       => $this->getRequest()->getParam('filter'),
            'keywords'     => $this->getRequest()->getParam('keywords'),
            'params'       => $this->getRequest()->getParams(),
            'itemsPerPage' => $itemsPerPage,
            'listingId'    => $this->getRequest()->getParam('listing_id'),
        );
    }

    public function Edit()
    {
        $redirect = false;

        $id = $this->getRequest()->getParam('id');

        $params = $this->_listings->findBy('id', $id, false, true)->toArray();
        $userId = (isset($params['user_id'])) ? $params['user_id'] : null;

        $form = new ListingForm(null, null, $userId);

        if ($params !== null) {
            if ($this->getRequest()->isPost()) {
                $params = array_merge(
                    $params, $this->getRequest()->getParams());
            }

            $form->setData($params);

            $formData = array_filter($form->getData());

            // needed for form element filters
            $params = array_merge($params, $formData);

            $form->setData($params)
                ->generateEditForm($id);

            if ($form->isPost(
                $this->getRequest())
            ) {
                if ($form->isValid() === true) {

                    $this->_listings->save(
                        $form->getData());

                    $redirect = true;

                    $translate = $this->getTranslate();

                    $this->_flashMessenger->setMessage(array(
                        'msg'   => sprintf($translate->_("Listing ID: #%s has been edited successfully."), $id),
                        'class' => 'alert-success',
                    ));
                }
                else {
                    $this->_flashMessenger->setMessage(array(
                        'msg'   => $form->getMessages(),
                        'class' => 'alert-danger',
                    ));
                }
            }
        }
        else {
            $this->_flashMessenger->setMessage(array(
                'msg'   => $this->_('The listing you are trying to edit does not exist.'),
                'class' => 'alert-danger',
            ));

            $redirect = true;
        }

        if ($redirect) {
            $this->_helper->redirector()->redirect('browse');
        }

        return array(
            'form'        => $form,
            'messages'    => $this->_flashMessenger->getMessages(),
            'currentStep' => null,
        );
    }

    public function Delete()
    {
        $id = $this->getRequest()->getParam('id');
        $listing = $this->_listings->findBy('id', (int)$id);

        if ($listing instanceof ListingModel) {
            $listing->delete(true);

            $translate = $this->getTranslate();

            $this->_flashMessenger->setMessage(array(
                'msg'   => sprintf($translate->_("Listing ID: #%s has been deleted."), $id),
                'class' => 'alert-success',
            ));
        }
        else {
            $this->_flashMessenger->setMessage(array(
                'msg'   => $this->_('Deletion failed. The listing could not be found.'),
                'class' => 'alert-danger',
            ));
        }

        $this->_helper->redirector()->redirect('browse', null, null, $this->getRequest()->getParams());
    }

    public function Sales()
    {
        $this->_forward('browse', 'invoices', 'members');
    }

    public function DeleteInvoice()
    {
        $salesService = new SalesService();

        $saleId = (int)$this->getRequest()->getParam('sale_id');

        /** @var \Ppb\Db\Table\Row\Sale $sale */
        $sale = $salesService->findBy('id', $saleId);

        if ($sale instanceof SaleModel) {
            $result = $sale->delete(true);

            if ($result) {
                $translate = $this->getTranslate();

                $this->_flashMessenger->setMessage(array(
                    'msg'   => sprintf($translate->_("The sale invoice #%s has been deleted."), $saleId),
                    'class' => 'alert-success',
                ));
            }
            else {
                $this->_flashMessenger->setMessage(array(
                    'msg'   => $this->_("Error: the sale invoice cannot be deleted."),
                    'class' => 'alert-danger',
                ));
            }
        }
        else {
            $this->_flashMessenger->setMessage(array(
                'msg'   => $this->_("Error: the invoice could not be found."),
                'class' => 'alert-danger',
            ));
        }

        $this->_helper->redirector()->redirect('sales', null, null, $this->getRequest()->getParams(array('sale_id')));
    }

    public function ViewInvoice()
    {
        $this->_forward('view', 'invoices', 'members');
    }

    public function UpdateInvoiceStatus()
    {
        $this->_forward('update-status', 'invoices', 'members');
    }

    public function EditInvoice()
    {
        $request = $this->getRequest();
        $saleId = $request->getParam('sale_id');

        $this->_forward('edit', 'invoices', 'members', array('sale_id' => $saleId));
    }

    public function UpdateDownloadLinks()
    {
        $salesListingsService = new SalesListingsService();

        /** @var \Ppb\Db\Table\Row\SaleListing $saleListing */
        $saleListing = $salesListingsService->findBy('id', $this->getRequest()->getParam('sale_listing_id'));

        /** @var \Ppb\Db\Table\Row\Sale $sale */
        $sale = $saleListing->findParentRow('\Ppb\Db\Table\Sales');

        $saleListing->save(array(
            'downloads_active' => !$saleListing->getData('downloads_active')
        ));

        $this->_flashMessenger->setMessage(array(
            'msg'   => $this->_('The statuses of the download links have been updated.'),
            'class' => 'alert-success',
        ));

        $this->_helper->redirector()->redirect('sales', null, null, array('sale_id' => $sale->getData('id')));
    }
}