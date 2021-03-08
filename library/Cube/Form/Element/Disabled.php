<?php

/**
 *
 * Cube Framework
 *
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2019 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 *
 * @version     2.1 [rev.2.1.01]
 */

/**
 * disabled field with hidden element attached
 */

namespace Cube\Form\Element;

class Disabled extends Hidden
{

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
     * required is always false
     *
     * @param bool $required
     *
     * @return $this|Hidden
     */
    public function setRequired($required = true)
    {
        $this->_required = false;

        return $this;
    }

    /**
     *
     * a disabled element will have no validators
     *
     * @param array|\Cube\Validate\AbstractValidate|string $validator
     *
     * @return $this|Hidden
     */
    public function addValidator($validator)
    {
        return $this;
    }

    /**
     *
     * a disabled element will have no validators
     *
     * @param array $validators
     *
     * @return $this|Hidden
     */
    public function setValidators(array $validators)
    {
        $this->clearValidators();

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
        return '';
    }

}
