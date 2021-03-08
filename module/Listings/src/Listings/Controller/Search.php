<?php

/**
 *
 * PHP Pro Bid
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2018 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     8.0 [rev.8.0.01]
 */

namespace Listings\Controller;

use Ppb\Controller\Action\AbstractAction,
    Cube\Controller\Front,
    Ppb\Service,
    Listings\Form;

class Search extends AbstractAction
{

    public function Advanced()
    {
        $form = new Form\Search(array('advanced', 'item'));

        $params = $this->getRequest()->getParams();
        $form->setData($params);

        $view = Front::getInstance()->getBootstrap()->getResource('view');

        $formAction = $view->url(array('module' => 'listings', 'controller' => 'browse', 'action' => 'index'));
        $form->setAction($formAction);

        return array(
            'form'     => $form,
            'headline' => $form->getTitle(),
        );
    }

    public function Stores()
    {
        $form = new Form\Search(array('stores'));
        $form->setData(
            $this->getRequest()->getParams())
            ->generateBasicForm();

        return array(
            'form' => $form,
        );
    }

}

