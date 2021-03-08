<?php

/**
 *
 * Cube Framework
 *
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2019 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 *
 * @version     2.2 [rev.2.2.01]
 */

/**
 * modules manager class
 */

namespace Cube;

use Cube\Controller\Request,
    Cube\Loader\Autoloader;

class ModuleManager
{
    /**
     * uri delimiter
     */

    const URI_DELIMITER = '/';

    /**
     * name and location of a module config file
     */
    const MODULE_CONFIG = 'config/*.config.php';

    /**
     * location of a module's classes
     */
    const MODULE_FILES = 'src';

    /**
     *
     * location of the application's modules
     */
    const MODULES_PATH = 'module';

    /**
     *
     * holds an instance of the object
     *
     * @var \Cube\ModuleManager
     */
    private static $_instance;

    /**
     *
     * an array that holds the names of the modules defined in the application
     *
     * @var array
     */
    protected $_modules = array();

    /**
     *
     * an array that holds the names of the namespaces used by the application
     *
     * @var array
     */
    protected $_namespaces = array();

    /**
     *
     * get the paths of the application modules
     *
     * @var array
     */
    protected $_paths = array();

    /**
     *
     * holds the route objects that are generated from the modules configs
     * the first active route in the stack will give the active module
     *
     * @var array
     */
    protected $_routes = array();

    /**
     *
     * the request uri, used for matching route objects and retrieving the active module
     *
     * @var string
     */
    protected $_requestUri;

    /**
     *
     * get active module from routing the request
     *
     * @var string
     */
    protected $_activeModule;

    /**
     *
     * class to be use for routing
     * must implement \Cube\Controller\Router\Route\RouteInterface
     *
     * @var string
     */
    protected $_routeClass = '\Cube\Controller\Router\Route\Rewrite';

    /**
     *
     * class constructor
     */
    protected function __construct()
    {
        $this->addPath('', self::MODULES_PATH);
    }

    /**
     *
     * returns an instance of the object and creates it if it wasnt instantiated yet
     *
     * @return \Cube\ModuleManager
     */
    public static function getInstance()
    {

        if (!self::$_instance instanceof self) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     *
     * reset all module properties
     *
     * @return $this
     */
    public function resetProperties()
    {
        $this->_modules = array();
        $this->_namespaces = array();

        $this->_paths = array();

        $this->_routes = array();

        $this->_requestUri = '';
        $this->_activeModule = '';

        return $this;
    }

    /**
     *
     * get modules paths
     *
     * @return array
     */
    public function getPaths()
    {
        return $this->_paths;
    }

    /**
     *
     * add single module path
     *
     * @param string $key
     * @param string $path
     *
     * @return $this
     */
    public function addPath($key, $path)
    {
        if (!in_array($path, $this->_paths)) {
            $this->_paths[$key] = $path;
        }

        return $this;
    }

    /**
     *
     * get the names of the registered modules
     *
     * @return array
     * @throws \DomainException
     */
    public function getModules()
    {
        if (empty($this->_modules)) {
            throw new \DomainException("No modules have been defined for the application.");
        }

        return $this->_modules;
    }

    /**
     *
     * add multiple modules
     *
     * @param array $modules
     *
     * @return $this
     */
    public function setModules($modules = array())
    {
        if (!empty($modules)) {
            foreach ($modules as $module) {
                $this->setModule($module);
            }
        }

        return $this;
    }

    /**
     *
     * add a single module to the array, and save its config array as well
     *
     * @param string $module
     *
     * @return $this
     */
    public function setModule($module)
    {
        if (!in_array($module, $this->_modules) && !empty($module)) {
            array_push($this->_modules, $module);

            $config = $this->getConfig($module);

            if (isset($config['routes'])) {
                $this->setRoutes($config['routes'], $module);
            }

            $this->addPath(
                $module,
                self::MODULES_PATH . DIRECTORY_SEPARATOR
                . $module . DIRECTORY_SEPARATOR
                . self::MODULE_FILES);
        }

        return $this;
    }

    /**
     *
     * get namespaces
     *
     * @return array
     */
    public function getNamespaces()
    {
        return $this->_namespaces;
    }

    /**
     *
     * set namespaces
     *
     * @param array $namespaces
     *
     * @return $this
     */
    public function setNamespaces($namespaces)
    {
        foreach ($namespaces as $namespace) {
            $this->setNamespace($namespace);
        }

        return $this;
    }

    /**
     *
     * set namespace
     *
     * @param string $namespace
     *
     * @return $this
     */
    public function setNamespace($namespace)
    {
        if (!in_array($namespace, $this->_namespaces) && !empty($namespace)) {
            array_push($this->_namespaces, $namespace);
        }

        return $this;
    }

    /**
     *
     * load module config files
     * @1.9: all files that are placed in the config folder and end in .config.php will be included
     *
     * @param string $module the name of the module
     *
     * @return array            returns the module config array or an empty array if the file doesnt exist
     * @throws \OutOfRangeException
     */
    public function getConfig($module)
    {
        $result = array();
        if (!in_array($module, $this->_modules)) {
            throw new \OutOfRangeException(
                sprintf("Cannot load the config file for module '%s' because the module was not initialized.", $module));
        }

        $files = array_merge(
            glob(__DIR__
                . '/../../'
                . self::MODULES_PATH . DIRECTORY_SEPARATOR
                . $module . DIRECTORY_SEPARATOR
                . self::MODULE_CONFIG),
            glob(__DIR__
                . '/../../'
                . Autoloader::getInstance()->getModsPath() . DIRECTORY_SEPARATOR
                . self::MODULES_PATH . DIRECTORY_SEPARATOR
                . $module . DIRECTORY_SEPARATOR
                . self::MODULE_CONFIG));

        foreach ($files as $file) {
            if (file_exists($file)) {
                $data = (array)include $file;
                $result = array_replace_recursive(
                    array_merge_recursive($result, $data), $data);
            }
        }

        return $result;
    }

    /**
     *
     * get the active module based the request uri and the route objects defined
     *
     * @return string       the name of the active module
     */
    public function getActiveModule()
    {
        if (!isset($this->_activeModule)) {

            foreach ($this->_routes as $route) {
                /** @var \Cube\Controller\Router\Route\AbstractRoute $route */
                if ($route->match($this->getRequestUri()) !== false) {
                    $this->_activeModule = $route->getModule();

                    return $this->_activeModule;
                }
            }

            $split = array_filter(explode(self::URI_DELIMITER, preg_replace('/^\//', '', $this->getRequestUri())));

            $moduleName = null;
            if (is_array($split)) {
                $moduleName = reset($split);
                $moduleName = ucfirst($moduleName);
            }

            $modules = $this->getModules();
            if (in_array($moduleName, $modules)) {
                $this->_activeModule = $moduleName;
            }
            else {
                // we init a standard request object and get the module variable if available
                $request = new Request\Standard();
                if ($module = $request->getParam('module')) {
                    $this->_activeModule = $request->normalize($module);
                }
                else {
                    $this->_activeModule = $modules[0];
                }
            }
        }

        return $this->_activeModule;
    }

    /**
     *
     * override the active module parameter
     *
     * @param string $module
     *
     * @return $this
     */
    public function setActiveModule($module)
    {
        if (in_array($module, $this->_modules)) {
            $this->_activeModule = $module;
        }

        return $this;
    }

    /**
     *
     * clear active module variable
     *
     * @return $this
     */
    public function clearActiveModule()
    {
        $this->_activeModule = null;

        return $this;
    }

    /**
     *
     * get route class
     *
     * @return string
     */
    public function getRouteClass()
    {
        return $this->_routeClass;
    }

    /**
     *
     * set route class
     *
     * @param string $routeClass
     *
     * @return $this
     */
    public function setRouteClass($routeClass)
    {
        if (class_exists($routeClass)) {
            $this->_routeClass = $routeClass;
        }

        return $this;
    }

    /**
     *
     * return the routes array
     *
     * @return array
     */
    public function getRoutes()
    {
        return $this->_routes;
    }

    /**
     *
     * create route objects from the arrays defined in the module config files,
     * and match routes to the request uri as well
     * we get the request uri by creating a new request object
     *
     * @param array  $routes
     * @param string $module the name of the module the routes belong to
     *
     * @return $this
     */
    public function setRoutes(array $routes, $module)
    {
        foreach ($routes as $name => $options) {

            $path = (isset($options[0])) ? $options[0] : null;
            $defaults = (isset($options[1])) ? (array)$options[1] : array();
            $conditions = (isset($options[2])) ? (array)$options[2] : array();

            /** @var \Cube\Controller\Router\Route\AbstractRoute $route */
            $route = new $this->_routeClass($path, $defaults, $conditions);

            $route->setName($name)
                ->setModule($module);

            array_push($this->_routes, $route);
        }

        return $this;
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
     * set the request uri and format it so that it can be used for matching route objects
     * (remove the base url of the application from the uri and remove any GET variables as well)
     *
     * @param string $requestUri
     *
     * @return $this
     */
    public function setRequestUri($requestUri = null)
    {
        if ($requestUri === null) {
            $request = new Request();
            $requestUri = $request->getRequestUri();
        }

        $this->_requestUri = $requestUri;

        return $this;
    }

}
