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
    Cube\Controller\Front,
    Ppb\Service\Table,
    App\Form\Tables as TablesForm,
    Admin\Form;

class Tables extends AbstractAction
{

    protected $_skipFields = array('table', 'csrf', 'submit', 'delete', 'nb_uploads', 'controller', 'action');

    public function Index()
    {
        $table = $this->getRequest()->getParam('table');
        $parentId = $this->getRequest()->getParam('parent_id');

        $className = '\\Ppb\\Service\\Table\\' . ucfirst(str_replace('.', '\\', $table));

        if (!class_exists($className)) {
            $this->_helper->redirector()->redirect('index', 'index');
        }
        else {
            /** @var \Ppb\Service\Table\Relational\AbstractServiceTableRelational $service */
            $service = new $className();

            $inAdmin = $this->_loggedInAdmin();


            $select = $service->getTable()->select();

            // in admin we display standard or store categories,
            // and in the members area we display store categories from the logged in user
            if ($service instanceof Table\Relational\Categories\Members) {
                if ($inAdmin) {
                    $select->where('user_id is not null');
                    $service->setInsertRows(0);
                }
                else {
                    $select->where('user_id = ?', $this->_user['id']);
                }
            }
            else if ($service instanceof Table\Relational\Categories\Standard) {
                $select->where('user_id is null');
            }

            if ($service instanceof Table\Relational\AbstractServiceTableRelational) {
                $service->setParentId($parentId);
                if ($parentId) {
                    $select->where('parent_id = ?', $parentId);
                }
                else {
                    $select->where('parent_id is null');
                }
                $select->order(array('order_id ASC', 'name ASC'));
            }

            if ($service instanceof Table\LinkRedirects) {
                $select->order(array('order_id ASC', 'id ASC'));
            }

            $form = new TablesForm($service);

            if ($this->getRequest()->isPost()) {
                $params = ($service instanceof Table\LinkRedirects || $service instanceof Table\WordFilter) ? $_POST : $this->getRequest()->getParams();

                $form->setData(
                    array(
                        'csrf' => $this->getRequest()->getParam('csrf')));

                if ($form->isValid() === true) {
                    $delete = array();
                    if (isset($params['delete'])) {
                        $delete = $params['delete'];
                    }

                    foreach ($this->_skipFields as $key) {
                        if (array_key_exists($key, $params)) {
                            unset($params[$key]);
                        }
                    }

                    if (!$inAdmin && $service instanceof Table\Relational\Categories) {
                        $params['user_id'] = $this->_user['id'];
                    }

                    $service->save($params)->delete($delete);

                    $this->_flashMessenger->setMessage(array(
                        'msg'   => $this->_('The settings have been saved.'),
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

            $form->getView()->uri = $this->getRequest()->getRequestUri();


            $form->setData(
                $service->fetchAll($select)->toArray());

            return array(
                'table'    => $table,
                'form'     => $form,
                'parentId' => $parentId,
                'service'  => $service,
                'inAdmin'  => $inAdmin,
                'messages' => $this->_flashMessenger->getMessages()
            );
        }
    }

    public function CategoryOptions()
    {
        $form = null;
        $this->_setNoLayout();

        $view = Front::getInstance()->getBootstrap()->getResource('view');
        $view->setNoLayout();

        /** @var \Cube\View\Helper\Script $scriptHelper */
        $scriptHelper = $view->getHelper('script');
        $scriptHelper->clearHeaderCode()
            ->clearBodyCode();

        $categoryId = $this->getRequest()->getParam('id');

        $categories = new Table\Relational\Categories();

        /* @var \Cube\Db\Table\Row $category */
        $category = $categories->findBy('id', $categoryId);

        if ($category !== null) {
            $form = new Form\CategoryOptions();

            $form->setData($category->getData());

            if ($this->getRequest()->isPost()) {
                $params = $this->getRequest()->getParams();

                $form->setData($params);

                if ($form->isValid() === true) {
                    $category->save(array(
                        'logo_path'        => $this->getRequest()->getParam('logo_path'),
                        'meta_title'       => $params['meta_title'],
                        'meta_description' => $params['meta_description'],
                        'html_header'      => $params['html_header'],
                    ));

                    $translate = $this->getTranslate();

                    $this->_flashMessenger->setMessage(array(
                        'msg'   => sprintf($translate->_('The options for category ID #%s have been saved.'), $category['id']),
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
                'msg'   => $this->_("The category doesn't exist."),
                'class' => 'alert-danger',
            ));
        }

        return array(
            'headline' => 'Category Options',
            'form'     => $form,
            'messages' => $this->_flashMessenger->getMessages()
        );
    }

}