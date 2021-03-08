<?php

/**
 *
 * Cube Framework
 *
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2020 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 *
 * @version     2.2 [rev.2.2.01]
 */

/**
 * autoloader class
 */

namespace Cube\Loader;

class Autoloader
{
    /**
     *
     * location of the framework and application libraries
     */

    const LIBRARIES_PATH = 'library';

    /**
     *
     * 3rd party vendor libraries path
     */
    const VENDOR_PATH = 'vendor';

    /**
     *
     * we will allow mods to override classes
     */
    const MODS_PATH = 'mods';

    /**
     *
     * holds the array of autoloader paths
     *
     * @var array
     */
    private $_paths = array();

    /**
     *
     * the extension for the files to be autoloaded
     *
     * @var string
     */
    private $_fileExtension = '.php';

    /**
     *
     * mods path variable
     *
     * @var string
     */
    private $_modsPath = self::MODS_PATH;

    /**
     *
     * holds an instance of the object
     *
     * @var \Cube\Loader\Autoloader
     */
    private static $_instance;

    /**
     * class constructor
     *
     * set the folder path for the default library
     */
    protected function __construct()
    {
        $this->addPaths(array(
            self::LIBRARIES_PATH,
            self::VENDOR_PATH,
        ));
    }

    /**
     *
     * returns an instance of the object and creates it if it wasnt instantiated yet
     *
     * @return \Cube\Loader\Autoloader
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
     * get mods path variable
     *
     * @return string
     */
    public function getModsPath()
    {
        return $this->_modsPath;
    }

    /**
     *
     * set a custom mods path variable
     *
     * @param string $modsPath
     *
     * @return $this
     */
    public function setModsPath($modsPath)
    {
        $this->_modsPath = $modsPath;

        return $this;
    }

    /**
     *
     * get autoloader paths
     *
     * @return array
     */
    public function getPaths()
    {
        return $this->_paths;
    }

    /**
     *
     * add multiple autoloader paths
     *
     * @param array $paths
     *
     * @return $this
     */
    public function addPaths($paths = array())
    {
        if (empty($this->_paths)) {
            $this->_paths = (array)$paths;
        }
        else if (!empty($paths)) {
            foreach ($paths as $path) {
                $this->addPath($path);
            }
        }

        return $this;
    }

    /**
     *
     * add single autoloader path
     *
     * @param string $path
     *
     * @return $this
     */
    public function addPath($path)
    {
        if (!in_array($path, $this->_paths)) {
            array_push($this->_paths, $path);
        }

        return $this;
    }

    /**
     *
     * the method will parse the data from the 'modules' key in the configuration array, and auto load all classes from the
     * folders defined, plus the classes from the include path (in the include path we have included the
     * folder where the framework is located
     *
     * @return $this
     */
    public function register()
    {
        spl_autoload_register(array($this, 'load'));

        return $this;
    }

    /**
     *
     * autoloader method
     *
     * @param string $class
     */
    public function load($class)
    {
        $pathInfo = pathinfo(
            str_replace('\\', DIRECTORY_SEPARATOR, $class));

        $included = false;

        foreach ((array)$this->_paths as $path) {
            if ($included === false) {
                $classFile = __DIR__ . '/../../../'
                    . $path . DIRECTORY_SEPARATOR
                    . $pathInfo['dirname'] . DIRECTORY_SEPARATOR
                    . $pathInfo['filename'] . $this->_fileExtension;

                $extendedClassFile = __DIR__ . '/../../../'
                    . $this->getModsPath() . DIRECTORY_SEPARATOR
                    . $path . DIRECTORY_SEPARATOR
                    . $pathInfo['dirname'] . DIRECTORY_SEPARATOR
                    . $pathInfo['filename'] . $this->_fileExtension;

                if (file_exists($extendedClassFile)) {
                    require_once $extendedClassFile;
                    $included = true;
                }
                else if (file_exists($classFile)) {
                    require_once $classFile;
                    $included = true;
                }
            }
        }
    }

}

