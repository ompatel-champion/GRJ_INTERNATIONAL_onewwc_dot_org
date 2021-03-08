<?php

/**
 *
 * Cube Framework
 *
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2019 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 *
 * @version     2.2 [rev.2.2.02]
 */

/**
 * abstract request class
 * all request classes should extend this class or at a minimum implement the request interface
 */

namespace Cube\Controller\Request;

abstract class AbstractRequest implements RequestInterface
{

    const URI_DELIMITER = '/';
    const METHOD_POST = 'POST';
    const METHOD_GET = 'GET';

    /**
     *
     * formatted request uri
     *
     * @var string
     */
    protected $_requestUri;

    /**
     *
     * the base URL of the application (from the request uri)
     *
     * @var string
     */
    protected $_baseUrl = null;

    /**
     *
     * base path of the application (the location on the server)
     *
     * @var string
     */
    protected $_basePath;

    /**
     *
     * the module requested (optional)
     *
     * @var string
     */
    protected $_module;

    /**
     *
     * the controller requested
     *
     * @var string
     */
    protected $_controller;

    /**
     *
     * the action requested
     *
     * @var string
     */
    protected $_action;

    /**
     *
     * query string in key => value pairs
     *
     * @var array
     */
    protected $_params = array();

    /**
     *
     * headers
     *
     * @var array
     */
    protected $_headers = array();

    /**
     *
     * request dispatched flag
     *
     * @var bool
     */
    protected $_dispatched;

    /**
     *
     * class constructor
     *
     * the constructor will initialize the base path and the formatted request uri
     */
    public function __construct()
    {
        $this->setBasePath()
            ->setRequestUri();
        $this->setRequestParams();
        $this->setHeaders(
            $this->_getAllHeaders());
    }

    /**
     *
     * get the formatted request uri, set if its not already set
     *
     * @return string
     */
    public function getRequestUri()
    {
        if (empty($this->_requestUri)) {
            $this->setRequestUri();
        }

        return $this->_requestUri;
    }

    /**
     *
     * set the request uri and format it so that it can be used by the router
     * (remove the base url of the application from the uri and remove any GET variables as well)
     *
     * @param string $requestUri
     *
     * @return $this
     */
    public function setRequestUri($requestUri = null)
    {
        if ($requestUri === null) {
            // we can retrieve more ways if needed
            // we replace the first occurrence of the base url (used if installing the app in a sub-domain)

            if (array_key_exists('REQUEST_URI', $_SERVER)) {
                $uri = explode('?', $_SERVER['REQUEST_URI']);

                $baseUrl = $this->getBaseUrl();
                if ($baseUrl) {
                    $requestUri = @preg_replace($this->getBaseUrl() . self::URI_DELIMITER, '', $uri[0], 1);
                }
                else {
                    $requestUri = $uri[0];
                }

                $requestUri = str_ireplace('//', '/', $requestUri);
            }
        }

        $this->_requestUri = $requestUri;

        return $this;
    }

    /**
     *
     * get the base url of the application
     *
     * @param bool $forceDelimiter
     *
     * @return string
     */
    public function getBaseUrl($forceDelimiter = false)
    {
        if ($this->_baseUrl === null) {
            if (array_key_exists('SCRIPT_NAME', $_SERVER)) {
                $baseUrl = rtrim(
                    dirname($_SERVER['SCRIPT_NAME']), self::URI_DELIMITER);
                $this->setBaseUrl($baseUrl);
            }
        }

        if ($forceDelimiter === true && empty($this->_baseUrl)) {
            return self::URI_DELIMITER;
        }

        return $this->_baseUrl;
    }

    /**
     *
     * set the base url of the application
     *
     * @param string $baseUrl
     *
     * @return $this
     */
    public function setBaseUrl($baseUrl)
    {
        $this->_baseUrl = $this->filterInput($baseUrl);

        return $this;
    }

    /**
     *
     * set the base path of the application
     * the base path is the actual location of the application on the server
     *
     * @return string
     */
    public function getBasePath()
    {
        if (empty($this->_basePath)) {
            $this->setBasePath();
        }

        return $this->_basePath;
    }

    /**
     *
     * set the base path of the application
     * @8.0: use APPLICATION_PATH RATHER THAN SERVER[SCRIPT_FILENAME] as the latter is not consistent
     *
     * @param string $basePath
     *
     * @return $this
     */
    public function setBasePath($basePath = null)
    {
        if ($basePath === null) {
            if (defined('APPLICATION_PATH')) {
                $basePath = APPLICATION_PATH;
            }
            else if (array_key_exists('SCRIPT_FILENAME', $_SERVER)) {
                $basePath = dirname($_SERVER['SCRIPT_FILENAME']);
            }
        }

        $this->_basePath = $basePath;

        return $this;
    }

    /**
     *
     * get one or all variables from the $_GET super-global
     * the order will be: first routed variables, then variables in the query string
     *
     * @param string $name
     * @param mixed  $default Default value to use if key not found
     *
     * @return mixed Returns null if key does not exist
     */
    public function getQuery($name = null, $default = null)
    {
        if ($name === null) {
            $get = $this->filterInput($_GET);

            $parts = array();
            if (isset($_SERVER['QUERY_STRING'])) {
                $parts = array_filter(explode('&', $_SERVER['QUERY_STRING']), 'strlen');
            }

            $vars = array();

            foreach ((array)$parts as $part) {
                $data = explode('=', $part);
                $key = (isset($data[0])) ? urlencode($data[0]) : '';
                $val = (isset($data[1])) ? urlencode($data[1]) : '';
                $vars[(string)$this->filterInput($key)] = $this->filterInput($val);
            }

            $get = array_diff_key($get, $vars);

            return array_merge($get, $vars);
        }


        if (isset($_GET[$name])) {
            return $this->filterInput($_GET[$name]);
        }

        return $default;
    }

    /**
     *
     * add variables that have been passed through the url address
     *
     * @param array|string $data
     * @param string|null  $value
     *
     * @return $this
     */
    public function setQuery($data, $value = null)
    {
        if (($value === null) && is_array($data)) {
            foreach ($data as $key => $value) {
                $this->setQuery($key, $value);
            }

            return $this;
        }

        $_GET[(string)$data] = $value;

        return $this;
    }

    /**
     *
     * set request params
     *
     * @return $this
     */
    public function setRequestParams()
    {
        if (count($_POST) > 0) {
            $this->setParams($_POST);
        }
        if (count($_GET) > 0) {
            $this->setParams($_GET);
        }

        return $this;
    }

    /**
     *
     * get headers
     *
     * @return array
     */
    public function getHeaders()
    {
        return $this->_headers;
    }

    /**
     *
     * set headers
     *
     * @param array $headers
     *
     * @return $this
     */
    public function setHeaders($headers)
    {
        $this->_headers = $headers;

        return $this;
    }

    /**
     *
     * get header variable
     *
     * @param string $key
     * @param null   $default
     *
     * @return mixed|null
     */
    public function getHeader($key, $default = null)
    {
        if (isset($this->_headers[$key])) {
            return $this->_headers[$key];
        }

        return $default;
    }

    /**
     *
     * set header variable
     *
     * @param string $key
     * @param string $value
     *
     * @return $this
     */
    public function setHeader($key, $value)
    {
        $this->_headers[$key] = $value;

        return $this;
    }

    /**
     *
     * get the request params
     *
     * @param array $skip array of parameters to skip
     *
     * @return array
     */
    public function getParams(array $skip = null)
    {
        if ($skip !== null) {
            $params = $this->_params;

            foreach ($skip as $key) {
                unset($params[$key]);
            }

            return $params;
        }

        return $this->_params;
    }

    /**
     *
     * set multiple request params
     *
     * @param array $params
     *
     * @return $this
     */
    public function setParams(array $params)
    {
        foreach ($params as $key => $value) {
            $this->setParam($key, $value);
        }

        return $this;
    }

    /**
     *
     * clear request params
     *
     * @return $this
     */
    public function clearParams()
    {
        $this->_params = array();

        return $this;
    }

    /**
     *
     * clear a single request variable
     *
     * @param string $key the key of the variable
     *
     * @return $this
     */
    public function clearParam($key)
    {
        if (isset($this->_params[$key])) {
            unset($this->_params[$key]);
        }

        return $this;
    }

    /**
     *
     * returns the value of a variable from a request
     *
     * @param string $key     the name of the variable
     * @param mixed  $default a default value in case the variable is not set
     *
     * @return string         return the formatted value of the variable
     */
    public function getParam($key, $default = null)
    {
        if (isset($this->_params[$key])) {
            return $this->_params[$key];
        }

        return $default;
    }

    /**
     *
     * set the value of a request param
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return $this
     */
    public function setParam($key, $value)
    {
        if (($value === null) && isset($this->_params[$key])) {
            unset($this->_params[$key]);
        }
        else if ($value !== null && $key === preg_replace("/[^a-zA-Z0-9_-]/", '', $key)) {
            $this->_params[$key] = $this->filterInput($value);
        }

        return $this;
    }

    /**
     *
     * check if a post request has been made
     *
     * @return bool
     */
    public function isPost()
    {
        if (stristr($_SERVER['REQUEST_METHOD'], self::METHOD_POST)) {
            return true;
        }

        return false;
    }

    /**
     *
     * check if a get request has been made
     *
     * @return bool
     */
    public function isGet()
    {
        if (stristr($_SERVER['REQUEST_METHOD'], self::METHOD_GET)) {
            return true;
        }

        return false;
    }

    /**
     *
     * get the module from the routed request
     *
     * @return string
     */
    public function getModule()
    {
        return $this->_module;
    }

    /**
     *
     * clear request method
     *
     * @return $this
     */
    public function clearRequestMethod()
    {
        $_SERVER['REQUEST_METHOD'] = '';

        return $this;
    }

    /**
     *
     * set the name of the module for the routed request (all module names start with a capital letter)
     *
     * @param string $module
     *
     * @return $this
     */
    public function setModule($module)
    {
        $this->_module = $this->normalize($module);

        return $this;
    }

    /**
     *
     * get the name of the routed action controller
     *
     * @return string
     */
    public function getController()
    {
        return $this->_controller;
    }

    /**
     *
     * set the name of the action controller for the routed request (all module names start with a capital letter)
     *
     * @param string $controller
     *
     * @return $this
     */
    public function setController($controller)
    {
        $this->_controller = $this->normalize($controller);

        return $this;
    }

    /**
     *
     * get the name of the routed controller action
     *
     * @return string
     */
    public function getAction()
    {
        return $this->_action;
    }

    /**
     *
     * set the name of the action from the routed request, in the following formats:
     * hello-world-today => HelloWorldToday
     * hello_world-today => HelloworldToday
     * heLlO => Hello
     * helloWorld => Helloworld
     *
     * @param string $action
     *
     * @return $this
     */
    public function setAction($action)
    {
        $this->_action = $this->normalize($action);

        return $this;
    }

    /**
     *
     * get dispatched status
     *
     * @return bool
     */
    public function isDispatched()
    {
        return (bool)$this->_dispatched;
    }

    /**
     *
     * set dispatched status
     *
     * @param bool $dispatched
     */
    public function setDispatched($dispatched)
    {
        $this->_dispatched = (bool)$dispatched;
    }

    /**
     *
     * remove special characters method, recursive in case of arrays
     * it cleans both keys and values in a request
     *
     * @param string|array $input
     *
     * @return mixed
     */
    public function filterInput($input)
    {
        $output = null;

        if (is_array($input)) {
            foreach ($input as $key => $value) {
                $key = preg_replace("/[^a-zA-Z0-9_-]/", '', $key);
                $output[$key] = $this->filterInput($value);
            }

            if (!is_array($output)) {
                $output = (array)$output;
            }
        }
        else if ($input !== null) {
            $output = trim(str_ireplace(
                array("'", '"', '<', '>'), array('&#039;', '&quot;', '&lt;', '&gt;'),
                stripslashes(rawurldecode($input))));
        }

        return $output;
    }

    /**
     *
     * match an uri string to the request uri variable
     *
     * @param string $uri
     * @param bool   $equal checks for equal strings only
     *
     * @return bool
     */
    public function matchRequestUri($uri, $equal = true)
    {
        $uri = trim($uri, self::URI_DELIMITER);
        $requestUri = trim($this->getRequestUri(), self::URI_DELIMITER);

        if ($equal === true && strcmp($uri, $requestUri) === 0) {
            return true;
        }
        else if ($equal !== true && stristr($requestUri, $uri)) {
            return true;
        }

        return false;
    }

    /**
     *
     * normalize url parts for modules/controllers/actions in order for them to be parsable by the router
     *
     * - will first convert the input to lowercase
     * - will remove any non alpha-numeric and '-' characters from the value
     * - will convert '-' to camel cased and will capitalize the first letter of the string
     *
     * Examples:
     * hello-world-today => HelloWorldToday
     * hello_world-today => HelloworldToday
     * heLlO => Hello
     * helloWorld => Helloworld
     *
     * if reverse is set to true, we convert a router router parsable string into a url string
     *
     * @param string $input
     * @param bool   $reverse
     *
     * @return string
     */
    public function normalize($input, $reverse = false)
    {
        if ($reverse) {
            return strtolower(
                preg_replace("/([a-z])([A-Z])/", '$1-$2', $input));
        }
        else {
            $input = str_replace('-', ' ', strtolower(
                preg_replace("/[^a-zA-Z0-9\_\-]/", '', $input)));

            return str_replace(' ', '', ucwords($input));
        }
    }

    /**
     *
     * get all headers
     *
     * @return array|false
     */
    protected function _getAllHeaders()
    {
        if (!function_exists('getallheaders')) {

            $headers = [];
            foreach ($_SERVER as $name => $value) {
                if (substr($name, 0, 5) == 'HTTP_') {
                    $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
                }
            }

            return $headers;
        }

        return getallheaders();
    }

}

