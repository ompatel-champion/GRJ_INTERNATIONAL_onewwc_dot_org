<?php

/**
 *
 * Cube Framework
 *
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2017 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 *
 * @version     1.9 [rev.1.9.01]
 */
/**
 * routes management class, for when mod rewrite is available
 *
 * - routes that are defined will always be fixed (as in new params are added after the question mark)
 * - routes that are assembled as standard routes only contain GET params after the question mark
 *
 * TODO: for routing urls where module, controller & action is not specified, remove variables already included in the hardcoded route from the GET component
 */

namespace Cube\Controller\Router;

use Cube\Controller\Request\AbstractRequest,
    Cube\Controller\Front,
    Cube\Cache\Adapter\AbstractAdapter as CacheAdapter;

class Rewrite extends AbstractRouter
{

    const URI_DELIMITER = '/';
    const DEFAULT_CONTROLLER = 'Index';
    const DEFAULT_ACTION = 'Index';

    /**
     *
     * defined routes
     *
     * @var array
     */
    protected $_routes = array();

    /**
     *
     * the route class that corresponds to this router
     *
     * @var string
     */
    protected $_routeClass = '\Cube\Controller\Router\Route\Rewrite';

    /**
     *
     * cache an url only if the params in the array are included
     *
     * @var array
     */
    protected $_cacheableParams = array(
        'module', 'controller', 'action'
    );

    /**
     *
     * add a single route to the routes array
     *
     * @param array|\Cube\Controller\Router\Route\RouteInterface $route can be an array from the config array or an instance of RouteInterface
     *
     * @return $this
     * @throws \BadMethodCallException
     */
    public function addRoute($route)
    {
        if ($route instanceof Route\RouteInterface) {
            $this->_routes[] = $route;
        }
        else if (is_array($route)) {
            $route = $this->_setRouteFromArray($route);

            if ($route !== false) {
                $this->_routes[] = $route;
            }
        }
        else {
            throw new \BadMethodCallException('The route object must be an instance of Cube\Controller\Router\Route\AbstractRoute or an array');
        }

        return $this;
    }

    /**
     *
     * add multiple routes to the routes array
     *
     * @param array $routes
     *
     * @return $this
     */
    public function addRoutes(array $routes)
    {
        foreach ($routes as $route) {
            $this->addRoute($route);
        }

        return $this;
    }

    /**
     *
     * retrieve the routes array
     *
     * @return array
     */
    public function getRoutes()
    {
        return $this->_routes;
    }

    /**
     *
     * get a route object by its name
     *
     * @param string $name
     *
     * @return \Cube\Controller\Router\Route\AbstractRoute
     * @throws \OutOfBoundsException
     */
    public function getRoute($name)
    {
        foreach ($this->_routes as $route) {
            /** @var \Cube\Controller\Router\Route\AbstractRoute $route */
            if ($route->getName() == $name) {
                return $route;
            }
        }

        throw new \OutOfBoundsException(
            sprintf("The route named '%s' does not exist.", $name));
    }

    /**
     *
     * get cacheable params
     *
     * @return array
     */
    public function getCacheableParams()
    {
        return $this->_cacheableParams;
    }

    /**
     *
     * set cacheable params
     *
     * @param array $cacheableParams
     *
     * @return $this
     */
    public function setCacheableParams($cacheableParams)
    {
        $this->_cacheableParams = $cacheableParams;

        return $this;
    }


    /**
     *
     * routes a request and returns the routed request object
     * can match multiple routes, and will return the one that was matched last
     *
     * from the route's defaults array
     *
     * @param \Cube\Controller\Request\AbstractRequest $request
     *
     * @return \Cube\Controller\Request\AbstractRequest    returns the routed request
     */
    public function route(AbstractRequest $request)
    {
        $requestUri = $request->getRequestUri();

        $matched = false;

        foreach ($this->_routes as $route) {
            /** @var \Cube\Controller\Router\Route\AbstractRoute $route */
            if ($route->match($requestUri) === true) {
                $params = array_merge($route->getDefaults(), $route->getParams());

                $request->setModule($route->getModule())
                    ->setController($route->getController())
                    ->setAction($route->getAction())
                    ->setParams($params)
                    ->setQuery($params);

                $matched = true;
            }
        }

        if ($matched === false) {
            $request = $this->_getDefaultRoute($request);
        }

        return $request;
    }

    /**
     *
     * return a url string after processing the params and matching them to one of the existing routes
     * if a string is given, return it unmodified
     * if no route is specified
     *
     * @param mixed  $params
     * @param string $name         the name of a specific route to use
     * @param bool   $addBaseUrl   flag to add the base url param to the assembled route
     * @param bool   $addGetParams whether to attach params resulted from a previous get operation to the url
     * @param array  $skipParams   an array of params to be omitted when constructing the url
     *
     * @return string
     */
    public function assemble($params, $name = null, $addBaseUrl = true, $addGetParams = false, array $skipParams = null)
    {
        $cacheRoutes = false;
        $cacheFile = null;

        $url = null;

        // check if we have a named route or if params is a string
        // named of string routes are not cached
        if ($name !== null) {
            $route = $this->getRoute($name);

            $url = $route->assemble($params, true);
        }
        else if (is_string($params)) {
            $url = $params;
        }


        // use default route assembler first
        if ($url === null) {
            $paramsKeys = array_keys((array)$params);

            $params = $this->_getDefaultParams($params, $addGetParams, $skipParams);

            if (count(array_intersect($paramsKeys, $this->_cacheableParams)) > 0) {
                $cacheFileName = json_encode(array(
                    $params, $name, $addBaseUrl, $addGetParams, $skipParams
                ));

                // get assembled route from cache
                $cacheRoutes = $this->_getCache('cacheRoutes');

                if ($cacheRoutes !== false) {
                    $cacheFile = md5($cacheFileName);

                    if (($cachedUrl = $this->_cache->read($cacheFile, CacheAdapter::ROUTES)) !== false) {
                        return $cachedUrl;
                    }
                }
            }

            // if no cache, generate route
            foreach ($this->_routes as $route) {
                /** @var \Cube\Controller\Router\Route\AbstractRoute $route */
                $assembled = $route->assemble($params);

                if ($assembled !== null) {
                    $url = $assembled;
                }
            }
        }

        if (!isset($url)) {
            $url = $this->_defaultRouteAssemble($params);
        }

        if (!preg_match('#^[a-z]+://#', $url)) {
            $url = (($addBaseUrl) ? Front::getInstance()->getRequest()->getBaseUrl() : '')
                . self::URI_DELIMITER
                . ltrim($url, self::URI_DELIMITER);
        }

        // add assembled route to the cache
        if (!empty($url) && $cacheRoutes !== false) {
            $this->_cache->write($cacheFile, CacheAdapter::ROUTES, $url);
        }

        return $url;
    }

    /**
     *
     * create a route object from an input array
     *
     * @param array $route the route in array format
     *
     * @return \Cube\Controller\Router\Route\AbstractRoute|false      return a route object or false if invalid data was provided
     */
    protected function _setRouteFromArray(array $route)
    {
        if (!empty($route[0])) {
            return new Route\Rewrite($route[0], $route[1], $route[2]);
        }

        return false;
    }

    /**
     *
     * assemble a request when no routes match
     * for array params, they wont be added in the url, but after the ? character
     *
     * if no module, controller and action is specified in the params, return the current uri + params after ? character
     *
     * @param array $params
     *
     * @return string   the routed uri
     */
    protected function _defaultRouteAssemble(array $params = null)
    {
        $url = array();
        $get = array();

        foreach ((array)$params as $key => $value) {
            if (preg_match('#^[a-zA-Z0-9_-]+$#', $key)) {
                if (!is_array($value)) {
                    if (!in_array($key, array('module', 'controller', 'action'))) {
                        array_push($url, $key);
                    }
                    array_push($url, $value);
                }
                else {
                    foreach ((array)$value as $val) {
                        if (!empty($val)) {
                            $get[] = $key . '[]=' . $val;
                        }
                    }
                }
            }
        }

        $uri = implode(self::URI_DELIMITER, $url);

        if (count($get) > 0) {
            $uri .= self::URI_DELIMITER . '?' . implode('&', $get);
        }

        return $uri;
    }

}

