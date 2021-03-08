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

namespace Cube\Validate\Db;

class RecordExists extends AbstractDb
{

    protected $_message = "No record matching '%value%' has been found.";

    /**
     *
     * check if the record exists
     *
     * @return bool
     */
    public function isValid()
    {
        $this->setMessage(
            str_replace('%value%', $this->_value, $this->getMessage()));

        $result = $this->_table->fetchRow(
            $this->getSelect());

        if ($result !== null) {
            if (count($result) > 0) {
                return true;
            }
        }

        return false;
    }

}

