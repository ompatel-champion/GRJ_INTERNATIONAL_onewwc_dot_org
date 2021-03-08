<?php

/**
 *
 * PHP Pro Bid
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2019 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     8.1 [rev.8.1.01]
 */
/**
 * description field with hidden element attached
 */

namespace Ppb\Form\Element;

use Cube\Form\Element\Hidden;

class DescriptionHidden extends Hidden
{

    /**
     *
     * string to output as the description, accepts html
     * if empty, the value will be displayed
     *
     * @var string
     */
    protected $_output;

    /**
     *
     * class constructor
     *
     * @param string $name
     */
    public function __construct($name)
    {
        parent::__construct($name);
        $this->setHidden(false);
    }

    /**
     *
     * get output string, or value if output is empty
     *
     * @return string
     */
    public function getOutput()
    {
        return (!empty($this->_output)) ? $this->_output : $this->getValue();
    }

    /**
     *
     * set output string
     *
     * @param string $output
     *
     * @return $this
     */
    public function setOutput($output)
    {
        $this->_output = $output;

        return $this;
    }

    /**
     *
     * render pseudo element
     *
     * @return string
     */
    public function render()
    {
        return $this->getPrefix() . ' '
        . $this->getOutput() . ' '
        . $this->getSuffix()
        . parent::render();
    }

}
