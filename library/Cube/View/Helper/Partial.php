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
 * partial view helper
 */

namespace Cube\View\Helper;

class Partial extends AbstractHelper
{

    /**
     *
     * data to be used by the partial
     *
     * @var array
     */
    protected $_data = array();

    /**
     *
     * path where to search for view partial files
     *
     * @var string
     */
    protected $_path;

    /**
     *
     * get view partials path
     *
     * @return string
     */
    public function getPath()
    {
        return $this->_path;
    }

    /**
     *
     * set view partials path
     *
     * @param string $path
     *
     * @return $this
     */
    public function setPath($path)
    {
        $this->_path = rtrim($path, DIRECTORY_SEPARATOR);

        return $this;
    }

    /**
     *
     * process view partial and return output
     *
     * @param string $partial view partial name/path
     * @param array  $data    input data to be handled by the view partial
     * @param string $path    view partials path
     *
     * @return string|$this           partial output
     */
    public function partial($partial = null, array $data = null, $path = null)
    {
        if ($partial === null) {
            return $this;
        }

        $this->setPath($path);
        $this->setPartial($partial);

        $view = $this->getView();

        $variables = $view->getVariables();

        $view->clearVariables();

        if ($data !== null) {
            $view->setVariables($data);
        }

        $file = $this->getPath() . DIRECTORY_SEPARATOR . $this->getPartial();

        $output = $view->process(
            $file, true);

        $view->setVariables($variables, true);

        return $output;
    }

}

