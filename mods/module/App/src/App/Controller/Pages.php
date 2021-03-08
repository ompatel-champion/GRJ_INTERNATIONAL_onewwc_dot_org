<?php

/**
 *
 * PHP Pro Bid
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2020 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     8.2 [rev.8.2.02]
 */

/**
 * custom pages controller
 * - will not be available with direct routing
 */
/**
 * MOD:- ADVANCED CLASSIFIEDS
 */

namespace App\Controller;

use Ppb\Controller\Action\AbstractAction,
    Cube\Controller\Front,
    Ppb\Service,
    Ppb\Db\Table\Row\User as UserModel,
    App\Form;

class Pages extends AbstractAction
{

    public function Contact()
    {
        $form = new Form\Contact();

        if ($this->_user instanceof UserModel) {
            $fullName = Front::getInstance()->getBootstrap()->getResource('view')->userDetails($this->_user)->displayFullName();
            $form->setData(array(
                'full_name' => $fullName,
                'email' => $this->_user->getData('email'),
                'phone'     => $this->_user->getData('phone'),
            ));
        }


        if ($form->isPost(
            $this->getRequest())
        ) {
            $form->setData($this->getRequest()->getParams());

            if ($form->isValid() === true) {
                $this->_flashMessenger->setMessage(array(
                    'msg'   => $this->_('The email has been sent successfully.'),
                    'class' => 'alert-success',
                ));

                $form->clearElements();

                $mail = new \Admin\Model\Mail\Admin();
                $mail->contact($this->getRequest())->send();
            }
            else {
                $this->_flashMessenger->setMessage(array(
                    'msg'   => $form->getMessages(),
                    'class' => 'alert-danger',
                ));
            }
        }

        return array(
            'form'     => $form,
            'messages' => $this->_flashMessenger->getMessages(),
        );
    }

    public function SiteFees()
    {
        $categoryId = $this->getRequest()->getParam('category_id');

        $translate = $this->getTranslate();

        $tabs = array(
            'general' => array(
                'name' => $this->_('General'),
            ),
        );

        if ($this->_settings['enable_auctions'] || $this->_settings['enable_products']) {
            $tabs['listings'] = array(
                'name' => $this->_('Listing Fees'),
            );
        }

        ## -- START :: ADD -- [ MOD:- ADVANCED CLASSIFIEDS ]
        if ($this->_settings['enable_classifieds']) {
            $tabs['classifieds'] = array(
                'name' => $this->_('Classifieds'),
            );
        }
        ## -- END :: ADD -- [ MOD:- ADVANCED CLASSIFIEDS ]

        if ($this->_settings['enable_stores']) {
            $tabs['stores'] = array(
                'name' => $this->_('Store Subscriptions'),
            );
        }

        $selectFeesCategories = "parent_id IS NULL AND custom_fees='1'";

        $storesSubscriptions = null;

        foreach ($tabs as $key => $tab) {
            $fees = array();
            $display = false;

            switch ($key) {
                case 'listings':
                    $display = true;

                    $services = array(
                        'ListingSetup',
                        'SaleTransaction',
                    );

//                    $selectFeesCategories .= " AND enable_auctions='1'";

                    foreach ($services as $name) {
                        $serviceName = '\\Ppb\\Service\\Fees\\' . $name;
                        /** @var \Ppb\Service\Fees $service */
                        $service = new $serviceName();

                        $select = $service->getTable()->select()
                            ->where('name IN (?)', array_keys($service->getFees()))
                            ->where('type = ?', 'default')
                            ->order(array('name ASC', 'tier_from ASC'));

                        if ($categoryId) {
                            $select->where('category_id = ?', $categoryId);
                        }
                        else {
                            $select->where('category_id is null');
                        }

                        $rowset = $service->fetchAll($select);

                        $feesArray = $service->getFees();

                        $feesSettings = array(
                            Service\Fees::ADDL_CATEGORY     => $this->_settings['addl_category_listing'],
                            Service\Fees::BUYOUT            => $this->_settings['enable_buyout'],
                            Service\Fees::RESERVE           => $this->_settings['enable_auctions'],
                            Service\Fees::DIGITAL_DOWNLOADS => $this->_settings['digital_downloads_max'],
                            Service\Fees::MAKE_OFFER        => $this->_settings['enable_make_offer'],
                            Service\Fees::IMAGES            => $this->_settings['images_max'],
                            Service\Fees::MEDIA             => $this->_settings['videos_max'],
                            Service\Fees::SHORT_DESCRIPTION => $this->_settings['enable_short_description'],
                        );

                        foreach ($rowset as $row) {
                            if (!array_key_exists($row['name'], $feesSettings) || ($feesSettings[$row['name']] > 0)) {
                                $row['desc'] = $feesArray[$row['name']];
                                $fees[] = $row;
                            }
                        }
                    }
                    break;
                ## -- START :: ADD -- [ MOD:- ADVANCED CLASSIFIEDS ]
                case 'classifieds':
                    $display = true;

                    $services = array(
                        'ClassifiedSetup',
                    );

//                    $selectFeesCategories .= " AND enable_classifieds='1'";

                    foreach ($services as $name) {
                        $serviceName = '\\Ppb\\Service\\Fees\\' . $name;
                        /** @var \Ppb\Service\Fees $service */
                        $service = new $serviceName();

                        $select = $service->getTable()->select()
                            ->where('name IN (?)', array_keys($service->getFees()))
                            ->where('type = ?', 'classified')
                            ->order(array('name ASC', 'tier_from ASC'));

                        if ($categoryId) {
                            $select->where('category_id = ?', $categoryId);
                        }
                        else {
                            $select->where('category_id is null');
                        }

                        $rowset = $service->fetchAll($select);

                        $feesArray = $service->getFees();
                        foreach ($rowset as $row) {
                            $row['desc'] = $feesArray[$row['name']];
                            $fees[] = $row;
                        }
                    }

                    break;
                ## -- END :: ADD -- [ MOD:- ADVANCED CLASSIFIEDS ]

                case 'stores':
                    $storesSubscriptionsService = new Service\Table\StoresSubscriptions();
                    $storesSubscriptions = $storesSubscriptionsService->fetchAll();

                    $fees = array();

                    if (count($storesSubscriptions) > 0) {
                        $display = true;
                    }
                    break;

                default:
                    $services = array(
                        'UserSignup',
                        'UserVerification',
                    );

                    foreach ($services as $name) {
                        $serviceName = '\\Ppb\\Service\\Fees\\' . $name;
                        /** @var \Ppb\Service\Fees $service */
                        $service = new $serviceName();

                        $rowset = $service->fetchAll(
                            $service->getTable()->select()
                                ->where('category_id is null')
                                ->where('name IN (?)', array_keys($service->getFees())));

                        $feesArray = $service->getFees();
                        foreach ($rowset as $row) {
                            $row['desc'] = $feesArray[$row['name']];
                            $fees[] = $row;
                        }
                    }

                    if (count($fees) > 0) {
                        $display = true;
                    }
                    break;
            }

            $tabs[$key]['fees'] = $fees;
            $tabs[$key]['display'] = $display;
        }

        $categoriesService = new Service\Table\Relational\Categories();
        $categoriesMultiOptions = $categoriesService->getMultiOptions($selectFeesCategories, null,
            $translate->_('Default'));

        return array(
            'categoryId'             => $categoryId,
            'tabs'                   => $tabs,
            'categoriesMultiOptions' => $categoriesMultiOptions,
            'fees'                   => $fees,
            'storesSubscriptions'    => $storesSubscriptions,
        );
    }

}

