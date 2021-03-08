<?php

/**
 *
 * Cube Framework
 *
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2015 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 *
 * @version     1.4
 */
/**
 * exceptions class
 */

namespace Cube\Exception;

use Cube\Exception;

class Dispatcher extends Exception
{

    /**
     *
     * construct the exception
     *
     * @param string     $message
     * @param int        $code
     * @param \Exception $previous
     */
    public function __construct($message = null, $code = null, $previous = null)
    {
        parent::__construct($message, (int)$code, $previous);
    }

    /**
     *
     * display the exception
     *
     * @return string
     */
    public function display()
    {
        return '<div class="alert alert-danger">[Cube\Exception\Dispatcher]<br> ' . $this->getMessage() . '</div>';
    }

}

