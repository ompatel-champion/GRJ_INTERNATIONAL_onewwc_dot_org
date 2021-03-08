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
 * text form element generator class
 */

namespace Cube\Form\Element;

use Cube\Form\Element;

class Text extends Element
{
    const TYPE = 'text';

    /**
     *
     * class constructor
     *
     * @param string $name
     */
    public function __construct($name)
    {
        parent::__construct(self::TYPE, $name);
    }

    /**
     *
     * renders the html form element
     * @1.9: force type = text
     *
     * @return string
     */
    public function render()
    {
        $this->setType(self::TYPE);

        return parent::render();
    }
}

