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
 * navigation application resource
 */

namespace Cube\Application\Resource;

use Cube\Navigation as NavigationObject,
    Cube\Loader\Autoloader;

class Navigation extends AbstractResource
{

    const CONFIG_NAMESPACE = '\\Cube\\Config\\';
    const DEFAULT_CONFIG = 'ArrayConfig';

    /**
     *
     * the type of the navigation file
     *
     * @var string
     */
    protected $_type;

    /**
     *
     * the location of the navigation file
     *
     * @var string
     */
    protected $_file;

    /**
     *
     * navigation object
     *
     * @var \Cube\Navigation
     */
    protected $_container;

    /**
     *
     * get navigation container
     *
     * @return \Cube\Navigation
     */
    public function getContainer()
    {
        return $this->_container;
    }

    /**
     *
     * set navigation container
     *
     * @param \Cube\Navigation $container
     *
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function setContainer($container)
    {
        if (!$container instanceof Navigation) {
            throw new \InvalidArgumentException(
                sprintf("'%s' must be an instance of \Cube\Navigation.", $container));
        }

        $this->_container = $container;

        return $this;
    }

    /**
     *
     * set navigation file type
     *
     * @param string $type
     *
     * @return $this
     */
    public function setType($type)
    {
        $this->_type = $type;

        return $this;
    }

    /**
     *
     * set navigation data file
     *
     * @param string $file
     *
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function setFile($file)
    {
        if (isset($file) && file_exists($file)) {
            $this->_file = $file;
        }
        else {
            throw new \InvalidArgumentException(sprintf("The navigation file '%s' does not exist.", $file));
        }

        return $this;
    }

    /**
     *
     * the resource will require for the location of a navigation file to be entered
     * the location of the view files corresponding to the navigation object can also be entered (optional)
     * if the type of the file is not entered, array will be assumed
     *
     * @1.4: if we have a navigation file in the mods folder, include it automatically as well and extend the original
     * navigation file
     *
     * @1.9: we retrieve all files matching the default file's extension and merge their contents
     *
     * @return \Cube\Navigation
     * @throws \InvalidArgumentException
     */
    public function init()
    {
        if (!($this->_container instanceof NavigationObject)) {
            $navigationFile = $this->_options['navigation']['data_file'];
            $basePath = $this->_options['paths']['base'];

            $pattern = str_replace('navigation.', '*.', $navigationFile);

            $additionalFiles = array_unique(array_merge(
                glob($pattern),
                glob(str_replace($basePath, $basePath . DIRECTORY_SEPARATOR . Autoloader::getInstance()->getModsPath(), $pattern))
            ));

            $configClass = self::CONFIG_NAMESPACE
                . ((isset($this->_options['navigation']['data_type'])) ?
                    ucfirst($this->_options['navigation']['data_type']) : self::DEFAULT_CONFIG);

            /** @var \Cube\Config\AbstractConfig $configObject */

            if (class_exists($configClass)) {
                $configObject = new $configClass(
                    $navigationFile);

                foreach ($additionalFiles as $additionalFile) {
                    if (file_exists($additionalFile) && $additionalFile != $navigationFile) {
                        $configObject->addData($additionalFile);
                    }
                }
            }
            else {
                throw new \InvalidArgumentException(sprintf("Class '%s' does not exist.", $configClass));
            }

            $this->_container = new NavigationObject($configObject);

            if (isset($this->_options['navigation']['views_path'])) {
                $this->_container->setPath($this->_options['navigation']['views_path']);
            }
        }

        return $this->_container;
    }

}

