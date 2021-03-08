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
 * members module - invoices management controller
 * (buyer, seller, admin can access these actions)
 *
 * browse invoices
 * view invoices (&print)
 * edit invoices
 * combine purchases
 * update shipping & payment statuses
 * delete invoices
 */
/**
 * MOD:- ESCROW PAYMENTS
 */

namespace Members\Controller;

use Members\Controller\Action\AbstractAction,
    Members\Form,
    Ppb\Service,
    Ppb\Db\Table\Row\Sale as SaleModel,
    Ppb\Db\Expr\DateTime,
    Cube\Paginator,
    Cube\Controller\Front,
    Cube\View;

class Invoices extends AbstractAction
{

    /**
     *
     * sales service
     *
     * @var \Ppb\Service\Sales
     */
    protected $_sales;

    /**
     *
     * invoice types to display ('sold', 'bought')
     *
     * @var string
     */
    protected $_type;

    public function init()
    {
        $this->_sales = new Service\Sales();
        $this->_type = $this->getRequest()->getParam('type', 'sold');
    }

    public function Browse()
    {
        $inAdmin = $this->_loggedInAdmin();

        if ($this->getRequest()->isPost()) {
            $id = $this->getRequest()->getParam('sale_id');
            $option = $this->getRequest()->getParam('option');

            $ids = array_filter(
                array_values((array)$id));

            $counter = null;
            $messages = array();

            if (count($ids) > 0) {
                $where = $this->_sales->getTable()->getAdapter()->quoteInto("id IN (?)", $ids);

                /** @var \Ppb\Db\Table\Rowset\Sales $sales */
                $sales = $this->_sales->fetchAll($where);
                $messages = $sales->changeStatus($option, $inAdmin);

                $counter = $sales->getCounter();

                $this->getRequest()->clearParam('sale_id');
            }

            if ($counter > 0) {
                $this->_flashMessenger->setMessage(array(
                    'msg'   => sprintf($this->_sales->getStatusMessage($option, $counter), $counter),
                    'class' => 'alert-success',
                ));
            }
            else {
                $this->_flashMessenger->setMessage(array(
                    'msg'   => $this->_('No invoices have been updated.'),
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
        
        $filter = $this->getRequest()->getParam('filter', 'all');
        $saleId = $this->getRequest()->getParam('sale_id');
        $listingId = $this->getRequest()->getParam('listing_id');
        $username = $this->getRequest()->getParam('username');
        $summary = $this->getRequest()->getParam('summary');

        $dateFrom = urldecode($this->getRequest()->getParam('date_from'));
        $dateTo = urldecode($this->getRequest()->getParam('date_to'));

        ## -- START :: ADD -- [ MOD:- ESCROW PAYMENTS ]
        $inAdmin = $this->_loggedInAdmin();

        $option = $this->getRequest()->getParam('option');

        if ($saleId && $option) {
            $shippingFlagUpdated = false;
            /** @var \Ppb\Db\Table\Row\Sale $sale */
            $sale = $this->_sales->findBy('id', $saleId);

            if ($sale->getData('enable_escrow')) {
                switch ($option) {
                    case 'shipping-seller-admin':
                        if ($sale->isSeller() && $sale->canMarkShippingAsSent()) {
                            $sale->save(array(
                                'flag_shipping' => SaleModel::SHIPPING_SENT_TO_ADMIN,
                            ));
                            $shippingFlagUpdated = true;
                        }
                        break;
                    case 'shipping-seller-buyer':
                        if ($sale->isSeller() && $sale->canMarkShippingAsSent()) {
                            $sale->save(array(
                                'flag_shipping' => SaleModel::SHIPPING_SENT,
                            ));
                            $shippingFlagUpdated = true;
                        }
                        break;
                    case 'shipping-buyer-received':
                        if ($sale->isBuyer()) {
                            $sale->save(array(
                                'flag_shipping' => SaleModel::SHIPPING_RECEIVED,
                            ));
                            $shippingFlagUpdated = true;
                        }
                        break;
                }

                if ($shippingFlagUpdated) {
                    $this->_flashMessenger->setMessage(array(
                        'msg'   => $this->_('The shipping flag has been updated.'),
                        'class' => 'alert-success',
                    ));
                }
            }
        }
        ## -- END :: ADD -- [ MOD:- ESCROW PAYMENTS ]

        $table = $this->_sales->getTable();
        $select = $table->getAdapter()
            ->select()
            ->from(array('s' => 'sales'), '*')
            ->where('s.pending = ?', 0)
            ->order(array('s.updated_at DESC', 's.created_at DESC'));

        if ($inAdmin) {
            $this->_type = 'all';
            $controller = ($summary) ? 'Index' : 'Listings';
        }
        else {
            $controller = ($this->_type == 'sold') ? 'Selling' : 'Buying';
        }

        switch ($this->_type) {
            case 'all': // only the administrator can view all invoices
                break;
            case 'bought': // invoices of bought items
                $select->where('s.buyer_id = ?', $this->_user['id'])
                    ->where('s.buyer_deleted = ?', 0);
                break;
            default: // invoices of sold items
                $select->where('s.seller_id = ?', $this->_user['id'])
                    ->where('s.seller_deleted = ?', 0);
                break;
        }

        if ($saleId) {
            $select->where('s.id = ?', (int)$saleId);
        }

        if ($listingId) {
            $select->join(array('sl' => 'sales_listings'), 's.id = sl.sale_id', 'sl.id AS sale_listing_id')
                ->where('sl.listing_id = ?', intval($listingId))
                ->group('s.id');
        }

        if ($username) {
            $condition = ($this->_type == 'bought') ? 's.seller_id = u.id' : 's.buyer_id = u.id';
            $select->join(array('u' => 'users'), $condition, 'u.username AS username')
                ->where('u.username LIKE ?', '%' . $username . '%')
                ->group('s.id');
        }

        if ($dateFrom) {
            $select->where('IFNULL(s.updated_at, s.created_at) > ?', new DateTime($dateFrom));
        }

        if ($dateTo) {
            $select->where('IFNULL(s.updated_at, s.created_at) < ?', new DateTime($dateTo));
        }

        switch ($filter) {
            case 'paid':
                $select->where('s.flag_payment > ?', 0);
                break;
            case 'unpaid':
                $select->where('s.flag_payment = ?', 0);
                break;
            case 'posted_sent':
                $select->where('s.flag_shipping = ?', SaleModel::SHIPPING_SENT);
                break;
            ## -- START :: ADD -- [ MOD:- ESCROW PAYMENTS ]
            case 'escrow':
                $select->where('s.sale_data REGEXP \'"enable_escrow";s:[[:digit:]]+:"1"\'');
                break;
            ## -- END :: ADD -- [ MOD:- ESCROW PAYMENTS ]
        }

        $paginator = new Paginator(
            new Paginator\Adapter\DbTableSelect($select, $table));

        $pageNumber = $this->getRequest()->getParam('page');
        $paginator->setPageRange(5)
            ->setItemCountPerPage(10)
            ->setCurrentPageNumber($pageNumber);

        return array(
            'filter'     => $filter,
            'saleId'     => $saleId,
            'listingId'  => $listingId,
            'username'   => $username,
            'dateFrom'   => $dateFrom,
            'dateTo'     => $dateTo,
            'controller' => $controller,
            'type'       => $this->_type,
            'paginator'  => $paginator,
            'messages'   => $this->_flashMessenger->getMessages(),
            'params'     => $this->getRequest()->getParams(),
            'summary'    => $summary,
            'inAdmin'    => $inAdmin,
        );
    }

    public function View()
    {
        $id = $this->getRequest()->getParam('id');

        $translate = $this->getTranslate();

        /* @var \Ppb\Db\Table\Row\Sale $sale */
        $sale = $this->_sales->findBy('id', $id);

        $inAdmin = $this->_loggedInAdmin();

        $display = false;
        if ($sale instanceof SaleModel) {
            if ($sale->canView() || $inAdmin) {
                $display = true;
            }
        }

        if (!$display) {
            $this->_helper->redirector()->redirect('not-found', 'error', null, array());
        }

        return array(
            'sale'     => $sale,
            'headline' => sprintf($translate->_('Sale Invoice - ID: #%s'), $id),
        );
    }

    public function Edit()
    {
        $form = $sales = null;

        $translate = $this->getTranslate();

        $salesListingsService = new Service\Table\SalesListings();

        $ids = array_filter(
            (array)$this->getRequest()->getParam('sale_id'));

        sort($ids);

        if (count($ids) > 0) {
        /* @var \Ppb\Db\Table\Rowset\Sales $sales */
        $sales = $this->_sales->fetchAll(
            $this->_sales->getTable()->select()
                ->where('id IN (?)', $ids));

        $inAdmin = $this->_loggedInAdmin();

        $sales->setAdmin($inAdmin);

            $canEdit = $sales->canEdit();

            if ($canEdit) {
            $form = new Form\Invoices($salesListingsService, $sales);

                if ($form->isPost(
                    $this->getRequest())
                ) {
            $params = $this->getRequest()->getParams();

                    $form->setData($params, true);

                if ($form->isValid() === true) {
                    /** @var \Ppb\Db\Table\Row\Sale $sale */
                    $sale = $sales->getRow(0);

                        $isSeller = $sale->isSeller($inAdmin);

                    $data = array(
                        'id'                  => $sale['id'],
                        'buyer_id'            => $sale['buyer_id'],
                        'seller_id'           => $sale['seller_id'],
                        'shipping_address_id' => $this->getRequest()->getParam('shipping_address_id'),
                        'postage_id'          => (int)$this->getRequest()->getParam('postage_id'),
                        'apply_insurance'     => (bool)$this->getRequest()->getParam('apply_insurance'),
                        'listings'            => $this->_flipArray($params),
                            'edit_invoice'        => true,
                    );

                    if ($isSeller) {
                            if (array_key_exists('postage_amount', $params)) {
                        $data['postage_amount'] = $params['postage_amount'];
                            }
                            if (array_key_exists('insurance_amount', $params)) {
                        $data['insurance_amount'] = $params['insurance_amount'];
                            }
                        $data['tax_rate'] = (!empty($params['tax_rate'])) ? $params['tax_rate'] : null;
                            $data['edit_locked'] = $params['edit_locked'];
                    }
                        else {
                            // buyers cannot edit prices or quantities of sales_listings rows
                            foreach ($data['listings'] as $k => $saleListing) {
                                $data['listings'][$k] = array(
                                    'id'         => $saleListing['id'],
                                    'sale_id'    => $sale['id'],
                                    'listing_id' => $saleListing['listing_id']
                                );
                            }
                        }

                        // we cannot add any sales_listings rows using a post request, can only update existing sales_listings rows
                        $this->_sales->save($data, false);

                    $this->_flashMessenger->setMessage(array(
                        'msg'   => $this->_('The invoice has been saved successfully.'),
                        'class' => 'alert-success',
                    ));

                        if ($inAdmin) {
                            $this->_helper->redirector()->redirect('sales', 'listings', 'admin',
                                array('sale_id' => $sale['id']));
                        }
                        else {
                        $this->_helper->redirector()->redirect('browse', 'invoices', null,
                            array(
                                'type'    => ($isSeller) ? 'sold' : 'bought',
                                    'sale_id' => $sale['id']
                                ));
                    }
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
                'msg'   => (count($ids) > 1) ?
                    $this->_("The selected invoices cannot be combined.") :
                    $this->_("The selected invoice cannot be edited."),
                'class' => 'alert-danger',
            ));
        }
        }
        else {
            $this->_flashMessenger->setMessage(array(
                'msg'   => $this->_("No invoice ids have been selected."),
                'class' => 'alert-danger',
            ));
        }

        $headline = (count($ids) > 1) ? $translate->_('Combine Invoices - IDs: %s') : $translate->_('Edit Invoice - ID: %s');

        return array(
            'headline'   => sprintf($headline, implode(', ', array_map(
                function ($id) {
                    return ('#' . $id);
                }, $ids))),
            'form'       => $form,
            'isMembersModule' => false,
            'messages'   => $this->_flashMessenger->getMessages()
        );
    }

    public function UpdateStatus()
    {
        $form = null;
        $view = Front::getInstance()->getBootstrap()->getResource('view');
        $view->setNoLayout();

        /** @var \Cube\View\Helper\Script $scriptHelper */
        $scriptHelper = $view->getHelper('script');
        $scriptHelper->clearHeaderCode()
            ->clearBodyCode();

        $translate = $this->getTranslate();

        $saleId = $this->getRequest()->getParam('sale_id');

        /* @var \Ppb\Db\Table\Row\Sale $sale */
        $sale = $this->_sales->findBy('id', $saleId);

        $inAdmin = $this->_loggedInAdmin();

        ## -- ONE LINE :: CHANGE -- [ MOD:- ESCROW PAYMENTS ]
        if ($sale->isActive(false) && $sale->isSeller($inAdmin) && !$sale->getData('enable_escrow')) {
            $form = new Form\UpdateStatus();

            $form->setData($sale->getData() + array('type' => $this->getRequest()->getParam('type')));

            if ($this->getRequest()->isPost()) {
                $params = $this->getRequest()->getParams();

                $form->setData($params);

                if ($form->isValid() === true) {
                    $sale->updateStatus($params);

                    $this->_flashMessenger->setMessage(array(
                        'msg'   => sprintf($translate->_('Invoice #%s has been updated successfully.'), $sale['id']),
                        'class' => 'alert-success',
                    ));

                    if ($this->getRequest()->getParam('update_buyer')) {
                        // send email notification
                        $mail = new \Members\Model\Mail\User();
                        /** @var \Ppb\Db\Table\Row\User $buyer */
                        $buyer = $sale->findParentRow('\Ppb\Db\Table\Users', 'Buyer');
                        $mail->saleUpdateBuyerNotification($sale, $buyer)->send();

                        $this->_flashMessenger->setMessage(array(
                            'msg'   => $translate->_('The buyer has been notified of the invoice changes by email.'),
                            'class' => 'alert-success',
                        ));
                    }

                    $form->clearElements();
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
                'msg'   => $this->_("You cannot update this invoice."),
                'class' => 'alert-danger',
            ));
        }

        return array(
            'headline' => $this->_('Update Status'),
            'form'     => $form,
            'messages' => $this->_flashMessenger->getMessages()
        );
    }

    public function Delete()
    {
        $result = false;

        /** @var \Ppb\Db\Table\Row\Sale $sale */
        $sale = $this->_sales->findBy('id', (int)$this->getRequest()->getParam('sale_id'));

        if ($sale->canDelete()) {
            $result = $sale->delete();
        }

        $translate = $this->getTranslate();

        if ($result) {
            $this->_flashMessenger->setMessage(array(
                'msg'   => sprintf($translate->_("The sale invoice #%s has been deleted."), $sale['id']),
                'class' => 'alert-success',
            ));
        }
        else {
            $this->_flashMessenger->setMessage(array(
                'msg'   => $this->_("Error: the sale invoice cannot be deleted."),
                'class' => 'alert-danger',
            ));
        }

        $this->_helper->redirector()->redirect('browse', 'invoices', null,
            array(
                'type'    => $this->getRequest()->getParam('type'),
                'sale_id' => null));
    }

    public function UpdateDownloadLinks()
    {
        $salesListingsService = new Service\Table\SalesListings();

        /** @var \Ppb\Db\Table\Row\SaleListing $saleListing */
        $saleListing = $salesListingsService->findBy('id', $this->getRequest()->getParam('sale_listing_id'));

        /** @var \Ppb\Db\Table\Row\Sale $sale */
        $sale = $saleListing->findParentRow('\Ppb\Db\Table\Sales');

        if ($sale->isSeller()) {
            $saleListing->save(array(
                'downloads_active' => !$saleListing->getData('downloads_active')
            ));

            $this->_flashMessenger->setMessage(array(
                'msg'   => $this->_('The statuses of the download links have been updated.'),
                'class' => 'alert-success',
            ));
        }

        $saleId = null;
        $this->_helper->redirector()->redirect('browse', 'invoices', null,
            array(
                'type'    => 'sold',
                'sale_id' => null));
    }

    public function InvoiceTotals()
    {
        $view = Front::getInstance()->getBootstrap()->getResource('view');

        $salesListingsService = new Service\Table\SalesListings();

        $ids = array_filter(
            (array)$this->getRequest()->getParam('sale_id'));

        /** @var \Ppb\Db\Table\Rowset\Sales $sales */
        $sales = $this->_sales->fetchAll(
            $this->_sales->getTable()->select()
                ->where('id IN (?)', $ids));

        $form = new Form\Invoices($salesListingsService, $sales);
        $form->setData(
            $this->getRequest()->getParams());

        $invoiceTotals = $view->partial('partials/invoice-totals.phtml', array(
            'invoicesForm' => $form));

        $this->getResponse()->setHeader('Content-Type: application/json');

        $outputView = new View();

        $outputView->setContent(
            json_encode(array(
                'invoiceTotals' => $invoiceTotals,
            )));

        return $outputView;
    }

    /**
     *
     * flip array to save the edit/combine invoices form
     * will only take array values into consideration and skip form elements that dont contain multiple values
     *
     * @param array $array
     *
     * @return array
     */
    protected function _flipArray(array $array)
    {
        $output = array();

        foreach ($array as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $k => $v) {
                    $output[$k][$key] = $v;
                }
            }
        }

        return $output;
    }

    protected function _prepareSaleFromPostData(SaleModel $sale)
    {
        $params = $this->getRequest()->getParams();
        $array = $this->_flipArray($params);

        $sale->setSalesListings($array);

        foreach ($params as $key => $value) {
            if (is_string($value)) {
                $sale->{$key} = $value;
            }
        }

        return $sale;
    }

}
