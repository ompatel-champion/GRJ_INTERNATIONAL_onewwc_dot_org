<?php

/**
 *
 * Cube Framework
 *
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2018 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 *
 * @version     2.0 [rev.2.0.01]
 */

/**
 * application class
 */

namespace Cube;

use Cube\Loader\Autoloader;

class Application
{
    /**
     *
     * the name of the module bootstrap files
     */

    const BOOTSTRAP = 'Bootstrap';

    /**
     *
     * holds the configuration options array
     *
     * @var array
     */
    protected $_options;

    /**
     * autoloader object
     *
     * @var \Cube\Loader\Autoloader
     */
    protected $_autoloader;

    /**
     *
     * requested module name
     *
     * @var \Cube\ModuleManager
     */
    protected $_moduleManager;

    /**
     *
     * bootstrap
     *
     * @var \Cube\Application\Bootstrap
     */
    protected $_bootstrap;

    /**
     *
     * returns an instance of the object and creates it if it wasnt instantiated yet
     *
     * @return \Cube\Application
     */
    private static $_instance;

    /**
     *
     * class constructor
     *
     * initialize autoloader
     *
     * @param array $options configuration array
     */
    protected function __construct($options = array())
    {
        require_once 'Debug.php';
        Debug::setTimeStart();
        Debug::setMemoryStart();
        Debug::setCpuUsageStart();

        require_once 'Loader/Autoloader.php';

        $this->_autoloader = Autoloader::getInstance()
            ->register();

        $this->setOptions($options);
    }

    /**
     *
     * initialize application as singleton
     *
     * @param array $options configuration array
     *
     * @return \Cube\Application
     */
    public static function init($options = array())
    {
        if (!self::$_instance instanceof self) {
            self::$_instance = new self($options);
        }

        return self::$_instance;
    }

    /**
     *
     * returns an instance of the application object
     *
     * @return \Cube\Application
     */
    public static function getInstance()
    {
        return self::$_instance;
    }

    /**
     *
     * get options array
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->_options;
    }

    /**
     *
     * set options array
     *
     * @param array $options
     *
     * @return $this
     */
    public function setOptions(array $options)
    {
        if (!empty($this->_options)) {
            $this->_options = array_replace_recursive(
                array_merge_recursive($this->_options, $options), $options);
        }
        else {
            $this->_options = $options;
        }

        return $this;
    }

    /**
     *
     * get a key from the options array
     *
     * @param string $key
     *
     * @return mixed|null
     */
    public function getOption($key)
    {
        if (isset($this->_options[$key])) {
            return $this->_options[$key];
        }

        return null;
    }

    /**
     *
     * set or unset a key in the options array
     *
     * @param string     $key
     * @param mixed|null $value
     *
     * @return $this
     */
    public function setOption($key, $value = null)
    {

        $this->_options[$key] = $value;

        return $this;
    }

    /**
     * get bootstrap object
     * we will first search for the requested module bootstrap and only if the file doesnt exist will we call the default bootstrap class from the library
     * the bootstrap class will only be created once
     *
     * @return \Cube\Application\Bootstrap
     */
    public function getBootstrap()
    {
        if (!($this->_bootstrap instanceof Application\Bootstrap)) {
            $bootstrap = $this->_moduleManager->getActiveModule() . '\\' . self::BOOTSTRAP;

            if (class_exists($bootstrap)) {
                $this->_bootstrap = new $bootstrap();
            }

            if ($this->_bootstrap === null) {
                $this->_bootstrap = new Application\Bootstrap();
            }
        }

        return $this->_bootstrap;
    }

    /**
     * initialize module manager and bootstrap application
     *
     * @throws \DomainException
     * @return $this
     */
    public function bootstrap()
    {
        $this->_moduleManager = ModuleManager::getInstance();

        if (!empty($this->_options['modules'])) {
            $this->_moduleManager->setModules($this->_options['modules']);

            $this->setOptions(
                $this->_moduleManager->getConfig(
                    $this->_moduleManager->getActiveModule()));

            $this->_autoloader->addPaths(
                $this->_moduleManager->getPaths());
        }
        else {
            throw new \DomainException('Modules not defined in the configuration file.');
        }

        if (!empty($this->_options['namespaces'])) {
            $this->_moduleManager->setNamespaces($this->_options['namespaces']);
        }
        else {
            throw new \DomainException('Namespaces not defined in the configuration file.');
        }

        $this->getBootstrap()->bootstrap();

        return $this;
    }

    /**
     * Run the application
     *
     * @return void
     */
    public function run()
    {
        $this->getBootstrap()->run();
    }

}

