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

namespace Admin\Controller;

use Ppb\Controller\Action\AbstractAction,
    Cube\Paginator,
    Ppb\Service,
    Ppb\Service\Table\Relational\Categories as CategoriesService,
    Ppb\Db\Table\Row\ContentSection as ContentSectionModel;

class SiteContent extends AbstractAction
{

    /**
     *
     * content entries service
     *
     * @var \Ppb\Service\ContentEntries
     */
    protected $_entries;

    /**
     *
     * content sections service
     *
     * @var \Ppb\Service\Table\Relational\ContentSections
     */
    protected $_sections;

    /**
     *
     * content menus service
     *
     * @var \Ppb\Service\ContentMenus
     */
    protected $_menus;

    /**
     *
     * advertising service
     *
     * @var \Ppb\Service\Advertising
     */
    protected $_advertising;

    /**
     *
     * translations service
     *
     * @var \Ppb\Service\Translations
     */
    protected $_translations;

    public function init()
    {
        $this->_entries = new Service\ContentEntries();
        $this->_sections = new Service\Table\Relational\ContentSections();
        $this->_menus = new Service\ContentMenus();
        $this->_advertising = new Service\Advertising();
        $this->_translations = new Service\Translations();
    }

    public function Entries()
    {
        $title = $this->getRequest()->getParam('title');
        $handle = $this->getRequest()->getParam('handle');

        $select = $this->_entries->getTable()->getAdapter()->select()
            ->from(array('e' => 'content_entries'))
            ->order(array('e.order_id ASC', 'e.created_at DESC'));

        if ($title != null) {
            $params = '%' . str_replace(' ', '%', $title) . '%';
            $select->where('e.title LIKE ?', $params);
        }

        if ($handle != null) {
            $params = '%' . str_replace(' ', '%', $handle) . '%';
            $select->join(array('s' => 'content_sections'), 'e.section_id = s.id', 's.id AS section_id')
                ->where('s.handle LIKE ?', $params);
        }

        $paginator = new Paginator(
            new Paginator\Adapter\DbTableSelect($select, $this->_entries->getTable()));

        $pageNumber = $this->getRequest()->getParam('page');
        $paginator->setPageRange(5)
            ->setItemCountPerPage(20)
            ->setCurrentPageNumber($pageNumber);

        return array(
            'controller' => 'Site Content',
            'title'      => $title,
            'handle'     => $handle,
            'paginator'  => $paginator,
            'messages'   => $this->_flashMessenger->getMessages(),
        );
    }


    public function NewEntry()
    {
        $this->_forward('edit-entry');
    }

    public function EditEntry()
    {
        $id = $this->getRequest()->getParam('id');
        $sectionId = $this->getRequest()->getParam('section_id');

        $data = array();

        if ($id) {
            $data = $this->_entries->findBy('id', $id)->toArray();
            $sectionId = $data['section_id'];
        }

        $form = new \Admin\Form\ContentEntry(null, $id, $sectionId);

        if ($id) {
            $form->setData($data)
                ->generateEditForm($id);
        }

        if ($this->getRequest()->isPost()) {
            $params = $this->getRequest()->getParams();
            $form->setData($params);
            $params = $form->getData();

            if ($form->isValid() === true) {
                $this->_entries->save($params);

                $this->_flashMessenger->setMessage(array(
                    'msg'   => ($id) ?
                        $this->_('The entry has been edited successfully.') :
                        $this->_('The entry has been created successfully.'),
                    'class' => 'alert-success',
                ));

                $this->_helper->redirector()->redirect('entries', null, null, array());
            }
            else {
                $this->_flashMessenger->setMessage(array(
                    'msg'   => $form->getMessages(),
                    'class' => 'alert-danger',
                ));
            }
        }

        return array(
            'controller' => 'Site Content',
            'form'       => $form,
            'messages'   => $this->_flashMessenger->getMessages(),
        );
    }

    public function DeleteEntry()
    {
        $id = $this->getRequest()->getParam('id');

        $result = $this->_entries->delete($id);

        if ($result) {
            $translate = $this->getTranslate();

            $this->_flashMessenger->setMessage(array(
                'msg'   => sprintf($translate->_("Entry ID: #%s has been deleted."), $id),
                'class' => 'alert-success',
            ));
        }
        else {
            $this->_flashMessenger->setMessage(array(
                'msg'   => $this->_('Deletion failed. The entry could not be found.'),
                'class' => 'alert-danger',
            ));
        }

        $this->_helper->redirector()->redirect('entries', null, null, array());
    }

    public function Sections()
    {
        $sectionName = $this->getRequest()->getParam('section_name');
        $parentId = $this->getRequest()->getParam('parent_id');

        $id = (array)$this->getRequest()->getParam('id');

        if ($this->getRequest()->isPost() && count($id) > 0) {
            $this->_sections->save(
                $this->getRequest()->getParams());

            $this->_flashMessenger->setMessage(array(
                'msg'   => $this->_('The changes have been saved successfully.'),
                'class' => 'alert-success',
            ));
        }

        $select = $this->_sections->getTable()->select()
            ->order(array('parent_id ASC', '-order_id DESC', 'name ASC'));

        if ($sectionName != null) {
            $params = '%' . str_replace(' ', '%', $sectionName) . '%';
            $select->where('name LIKE ? OR handle LIKE ?', $params);
        }

        if ($parentId != null) {
            if (in_array($parentId, array_keys(ContentSectionModel::$sectionTypes))) {
                $select->where('type = ?', $parentId);
            }
            else {
                $select->where('parent_id = ?', $parentId);
            }
        }


        $paginator = new Paginator(
            new Paginator\Adapter\DbTableSelect($select, $this->_sections->getTable()));

        $pageNumber = $this->getRequest()->getParam('page');
        $paginator->setPageRange(5)
            ->setItemCountPerPage(20)
            ->setCurrentPageNumber($pageNumber);

        return array(
            'controller'             => 'Site Content',
            'sectionName'            => $sectionName,
            'parentId'               => $parentId,
            'paginator'              => $paginator,
            'messages'               => $this->_flashMessenger->getMessages(),
            'contentSectionsService' => $this->_sections,
        );
    }


    public function NewSection()
    {
        $this->_forward('edit-section');
    }

    public function EditSection()
    {
        $id = $this->getRequest()->getParam('id');
        $parentId = $this->getRequest()->getParam('parent_id');

        $form = new \Admin\Form\ContentSection(null, $id, $parentId);

        if ($id) {
            $data = $this->_sections->findBy('id', $id)->toArray();

            $form->setData($data)
                ->generateEditForm($id);
        }

        if ($this->getRequest()->isPost()) {
            $params = $this->getRequest()->getParams();
            $form->setData($params);

            if ($form->isValid() === true) {
                $this->_sections->saveSingle($params);

                $this->_flashMessenger->setMessage(array(
                    'msg'   => ($id) ?
                        $this->_('The section has been edited successfully.') :
                        $this->_('The section has been created successfully.'),
                    'class' => 'alert-success',
                ));

                $this->_helper->redirector()->redirect('sections', null, null, array('parent_id' => $parentId));
            }
            else {
                $this->_flashMessenger->setMessage(array(
                    'msg'   => $form->getMessages(),
                    'class' => 'alert-danger',
                ));
            }
        }

        return array(
            'controller' => 'Site Content',
            'form'       => $form,
            'messages'   => $this->_flashMessenger->getMessages(),
        );
    }

    public function DeleteSection()
    {
        $id = $this->getRequest()->getParam('id');
        $result = $this->_sections->delete((array)$id);

        if ($result) {
            $translate = $this->getTranslate();

            $this->_flashMessenger->setMessage(array(
                'msg'   => sprintf($translate->_("Section ID: #%s has been deleted."), $id),
                'class' => 'alert-success',
            ));
        }
        else {
            $this->_flashMessenger->setMessage(array(
                'msg'   => $this->_('Deletion failed. The section could not be found.'),
                'class' => 'alert-danger',
            ));
        }

        $params = $this->getRequest()->getParams(array('id'));


        $this->_helper->redirector()->redirect('sections', null, null, $params);
    }

    public function Menus()
    {
        $name = $this->getRequest()->getParam('name');

        $id = (array)$this->getRequest()->getParam('id');

        if ($this->getRequest()->isPost() && count($id) > 0) {
            $this->_menus->save(
                $this->getRequest()->getParams());

            $this->_flashMessenger->setMessage(array(
                'msg'   => $this->_('The changes have been saved successfully.'),
                'class' => 'alert-success',
            ));
        }

        $select = $this->_menus->getTable()->select()
            ->order(array('handle ASC'));

        if ($name != null) {
            $params = '%' . str_replace(' ', '%', $name) . '%';
            $select->where('name LIKE ? OR handle LIKE ?', $params);
        }


        $paginator = new Paginator(
            new Paginator\Adapter\DbTableSelect($select, $this->_menus->getTable()));

        $pageNumber = $this->getRequest()->getParam('page');
        $paginator->setPageRange(5)
            ->setItemCountPerPage(20)
            ->setCurrentPageNumber($pageNumber);

        return array(
            'controller'          => 'Site Content',
            'name'                => $name,
            'paginator'           => $paginator,
            'messages'            => $this->_flashMessenger->getMessages(),
            'contentMenusService' => $this->_menus,
        );
    }


    public function NewMenu()
    {
        $this->_forward('edit-menu');
    }

    public function EditMenu()
    {
        $id = $this->getRequest()->getParam('id');

        $form = new \Admin\Form\ContentMenu(null, $id);

        if ($id) {
            $data = $this->_menus->findBy('id', $id)->toArray();

            $form->setData($data)
                ->generateEditForm($id);
        }

        if ($this->getRequest()->isPost()) {
            $params = $this->getRequest()->getParams();
            $form->setData($params);

            if ($form->isValid() === true) {
                $this->_menus->save($params);

                $this->_flashMessenger->setMessage(array(
                    'msg'   => ($id) ?
                        $this->_('The menu has been edited successfully.') :
                        $this->_('The menu has been created successfully.'),
                    'class' => 'alert-success',
                ));

                $this->_helper->redirector()->redirect('menus', null, null, array());
            }
            else {
                $this->_flashMessenger->setMessage(array(
                    'msg'   => $form->getMessages(),
                    'class' => 'alert-danger',
                ));
            }
        }

        return array(
            'controller' => 'Site Content',
            'form'       => $form,
            'messages'   => $this->_flashMessenger->getMessages(),
        );
    }

    public function DeleteMenu()
    {
        $id = $this->getRequest()->getParam('id');
        $result = $this->_menus->delete($id);

        if ($result) {
            $translate = $this->getTranslate();

            $this->_flashMessenger->setMessage(array(
                'msg'   => sprintf($translate->_("Menu ID: #%s has been deleted."), $id),
                'class' => 'alert-success',
            ));
        }
        else {
            $this->_flashMessenger->setMessage(array(
                'msg'   => $this->_('Deletion failed. The menu could not be found.'),
                'class' => 'alert-danger',
            ));
        }

        $this->_helper->redirector()->redirect('menus', null, null, array());
    }

    public function Advertising()
    {
        if ($this->getRequest()->isPost()) {
            $this->_advertising->saveSettings(
                $this->getRequest()->getParams());

            $this->_flashMessenger->setMessage(array(
                'msg'   => $this->_('The adverts settings have been updated.'),
                'class' => 'alert-success',
            ));
        }

        $select = $this->_advertising->getTable()->select()
            ->order(array('created_at DESC'));

        $paginator = new Paginator(
            new Paginator\Adapter\DbTableSelect($select, $this->_advertising->getTable()));

        $pageNumber = $this->getRequest()->getParam('page');
        $paginator->setPageRange(5)
            ->setItemCountPerPage(10)
            ->setCurrentPageNumber($pageNumber);

        $sections = $this->_advertising->getSections();

        $categoriesService = new CategoriesService();

        return array(
            'paginator'         => $paginator,
            'messages'          => $this->_flashMessenger->getMessages(),
            'controller'        => 'Site Content',
            'categoriesService' => $categoriesService,
            'sections'          => $sections,
        );
    }

    public function CreateAdvert()
    {
        $this->_forward('edit-advert');
    }

    public function EditAdvert()
    {
        $params = array();

        $id = $this->getRequest()->getParam('id');

        if ($id) {
            $params = $this->_advertising->findBy('id', $id)->toArray();
        }

        if ($this->getRequest()->isPost()) {
            $params = array_merge(
                $params, $this->getRequest()->getParams());
        }

        $type = isset($params['type']) ? $params['type'] : null;
        $form = new \Admin\Form\Advert($type);

        if ($id) {
            $form->generateEditForm();
        }

        $form->setData($params);

        if ($form->isPost(
            $this->getRequest())
        ) {

            if ($form->isValid() === true) {
                $this->_advertising->save($params);

                $this->_flashMessenger->setMessage(array(
                    'msg'   => ($id) ?
                        $this->_('The advert has been edited successfully') :
                        $this->_('The advert has been created successfully.'),
                    'class' => 'alert-success',
                ));

                $this->_helper->redirector()->redirect('advertising');
            }
            else {
                $this->_flashMessenger->setMessage(array(
                    'msg'   => $form->getMessages(),
                    'class' => 'alert-danger',
                ));
            }
        }

        return array(
            'form'       => $form,
            'messages'   => $this->_flashMessenger->getMessages(),
            'controller' => 'Site Content',
        );
    }

    public function DeleteAdvert()
    {
        $id = $this->getRequest()->getParam('id');
        $result = $this->_advertising->delete($id);

        if ($result) {
            $translate = $this->getTranslate();

            $this->_flashMessenger->setMessage(array(
                'msg'   => sprintf($translate->_("Advert ID: #%s has been deleted."), $id),
                'class' => 'alert-success',
            ));
        }
        else {
            $this->_flashMessenger->setMessage(array(
                'msg'   => $this->_('Deletion failed. The advert could not be found.'),
                'class' => 'alert-danger',
            ));
        }

        $this->_helper->redirector()->redirect('advertising', null, null, $this->getRequest()->getParams());
    }

    public function PreviewAdvert()
    {
        $this->_setNoLayout();

        $adverts = $this->_advertising->fetchAll(
            $this->_advertising->getTable()->select()
                ->where('id = ?', $this->getRequest()->getParam('id'))
        );

        return array(
            'adverts' => $adverts,
        );
    }


    public function Emails()
    {
        $fileName = filter_input(INPUT_POST, 'email', FILTER_UNSAFE_RAW);

        $text = null;
        if ($fileName) {
            if ($this->getRequest()->getParam('save')) {
                $result = @file_put_contents($fileName, filter_input(INPUT_POST, 'text', FILTER_UNSAFE_RAW));

                if ($result) {
                    $translate = $this->getTranslate();

                    $this->_flashMessenger->setMessage(array(
                        'msg'   => sprintf($translate->_("The %s email file has been edited successfully."), $fileName),
                        'class' => 'alert-success',
                    ));
                }
                else {
                    $this->_flashMessenger->setMessage(array(
                        'msg'   => $this->_('Error: could not edit the email file. Please check for write permissions.'),
                        'class' => 'alert-danger',
                    ));
                }
            }

            $text = file_get_contents($fileName);
        }

        return array(
            'messages'   => $this->_flashMessenger->getMessages(),
            'controller' => 'Site Content',
            'email'      => $fileName,
            'text'       => $text,
        );
    }

    public function Translations()
    {
        if ($this->getRequest()->isPost()) {
            $this->_translations->saveChanges(
                $this->getRequest()->getParams());

            $this->_flashMessenger->setMessage(array(
                'msg'   => $this->_('The changes have been saved.'),
                'class' => 'alert-success',
            ));
        }

        $translations = $this->_translations->fetchTranslations();

        return array(
            'messages'            => $this->_flashMessenger->getMessages(),
            'translations'        => $translations,
            'translationsService' => $this->_translations,
        );
    }

    public function DownloadTranslation()
    {
        $this->_setNoLayout();

        $result = $this->_translations->downloadTranslation(
            $this->getRequest()->getParam('locale')
        );

        if (!$result) {
            $this->_flashMessenger->setMessage(array(
                'msg'   => $this->_('The translation file does not exist.'),
                'class' => 'alert-danger',
            ));

            $this->_helper->redirector()->redirect('translations');
        }
    }

    public function CreateTranslation()
    {
        $params = $this->getRequest()->getParams();

        $form = new \Admin\Form\CreateTranslation();

        $form->setData($params);

        if ($form->isPost(
            $this->getRequest())
        ) {

            if ($form->isValid() === true) {
                $result = $this->_translations->createTranslation($params['locale']);

                if ($result) {
                    $this->_flashMessenger->setMessage(array(
                        'msg'   => $this->_('The translation files have been created.'),
                        'class' => 'alert-success',
                    ));
                }
                else {
                    $this->_flashMessenger->setMessage(array(
                        'msg'   => $this->_('The translation files already exist.'),
                        'class' => 'alert-danger',
                    ));
                }

                $this->_helper->redirector()->redirect('translations');
            }
            else {
                $this->_flashMessenger->setMessage(array(
                    'msg'   => $form->getMessages(),
                    'class' => 'alert-danger',
                ));
            }
        }

        return array(
            'form'       => $form,
            'messages'   => $this->_flashMessenger->getMessages(),
            'controller' => 'Site Content',
        );
    }

    public function UploadTranslation()
    {
        $params = $this->getRequest()->getParams();

        $form = new \Admin\Form\UploadTranslation();

        $form->setData($params);

        if ($form->isPost(
            $this->getRequest())
        ) {

            if ($form->isValid() === true) {
                $result = $this->_translations->uploadTranslation(
                    $this->getRequest()->getParam('locale'),
                    $this->getRequest()->getParam('po'),
                    $this->getRequest()->getParam('mo')
                );

                if ($result) {
                    $this->_flashMessenger->setMessage(array(
                        'msg'   => $this->_('The translation files have been uploaded.'),
                        'class' => 'alert-success',
                    ));
                }
                else {
                    $this->_flashMessenger->setMessage(array(
                        'msg'   => $this->_('The upload process has failed.'),
                        'class' => 'alert-danger',
                    ));
                }

                $this->_helper->redirector()->redirect('translations');
            }
            else {
                $this->_flashMessenger->setMessage(array(
                    'msg'   => $form->getMessages(),
                    'class' => 'alert-danger',
                ));
            }
        }

        return array(
            'form'       => $form,
            'messages'   => $this->_flashMessenger->getMessages(),
            'controller' => 'Site Content',
        );
    }

    public function DeleteTranslation()
    {
        $locale = $this->getRequest()->getParam('locale');
        if ($this->_translations->canDelete($locale)) {
            $this->_translations->delete($locale);

            $this->_flashMessenger->setMessage(array(
                'msg'   => $this->_('The translation has been deleted.'),
                'class' => 'alert-success',
            ));
        }
        else {
            $this->_flashMessenger->setMessage(array(
                'msg'   => $this->_('The translation cannot be deleted.'),
                'class' => 'alert-danger',
            ));
        }

        $this->_helper->redirector()->redirect('translations');
    }

}

