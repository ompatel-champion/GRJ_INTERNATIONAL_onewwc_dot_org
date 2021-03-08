<?php

/**
 *
 * Cube Framework
 *
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2018 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 *
 * @version     2.0 [rev.2.0.02]
 */

/**
 * helper that renders a controller action
 */

namespace Cube\View\Helper;

use Cube\Controller\Front;

class Action extends AbstractHelper
{

    /**
     *
     * request object
     *
     * @var \Cube\Controller\Request\AbstractRequest
     */
    protected $_request;

    /**
     *
     * response object
     *
     * @var \Cube\Http\Response
     */
    protected $_response;

    /**
     *
     * dispatcher object
     *
     * @var \Cube\Controller\Dispatcher
     */
    protected $_dispatcher;

    /**
     *
     * class constructor
     */
    public function __construct()
    {
        $front = Front::getInstance();

        $this->setRequest(
            $front->getRequest())
            ->setResponse(
                $front->getResponse())
            ->setDispatcher(
                $front->getDispatcher());
    }

    /**
     *
     * get request
     *
     * @return \Cube\Controller\Request\AbstractRequest
     */
    public function getRequest()
    {
        return $this->_request;
    }

    /**
     *
     * set request
     *
     * @param \Cube\Controller\Request\AbstractRequest $request
     *
     * @return $this
     */
    public function setRequest($request)
    {
        $this->_request = $request;

        return $this;
    }

    /**
     *
     * get response
     *
     * @return \Cube\Http\Response
     */
    public function getResponse()
    {
        return $this->_response;
    }

    /**
     *
     * set response
     *
     * @param \Cube\Controller\Response\ResponseInterface $response
     *
     * @return $this
     */
    public function setResponse($response)
    {
        $this->_response = $response;

        return $this;
    }

    /**
     *
     * get dispatcher
     *
     * @return \Cube\Controller\Dispatcher
     */
    public function getDispatcher()
    {
        return $this->_dispatcher;
    }

    /**
     *
     * set dispatcher
     *
     * @param \Cube\Controller\Dispatcher\DispatcherInterface $dispatcher
     *
     * @return $this
     */
    public function setDispatcher($dispatcher)
    {
        $this->_dispatcher = $dispatcher;

        return $this;
    }

    /**
     * Retrieve rendered contents of a controller action
     *
     * If the action results in a forward or redirect, returns empty string.
     *
     * @param  string $action
     * @param  string $controller
     * @param  string $module Defaults to default module
     * @param  array  $params
     *
     * @return string
     * @throws \Cube\Exception\Dispatcher
     */
    public function action($action, $controller, $module = null, array $params = array())
    {
        $request = clone $this->getRequest();
        $response = clone $this->getResponse();
        $dispatcher = clone $this->getDispatcher();

        $response->clearBody()
            ->clearHeaders();

        if ($module === null) {
            $module = $request->getModule();
        }

        if ($params) {
            $request->clearParams();
        }

        $request->setParams($params)
            ->setModule($module)
            ->setController($controller)
            ->setAction($action)
            ->setDispatched(true);

        $dispatcher->dispatch($request, $response, true);

        if (!$request->isDispatched()) {
            return '';
        }

        return $response->getBody();
    }

}

