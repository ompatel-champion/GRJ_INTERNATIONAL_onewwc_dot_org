<?php

/**
 *
 * Cube Framework
 *
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2017 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 *
 * @version     1.10 [rev.1.10.01]
 */

namespace Cube\Cache\Adapter;

use Cube\Db\Table\AbstractTable,
    Cube\Db\Expr;

class Table extends AbstractAdapter
{

    /**
     *
     * cache table object
     *
     * @var \Cube\Db\Table\AbstractTable
     */
    protected $_table;

    /**
     *
     * class constructor
     *
     * @param array $options configuration array
     *
     * @throws \RuntimeException
     */
    public function __construct($options = array())
    {
        parent::__construct($options);

        if (!isset($options['table'])) {
            throw new \RuntimeException("Cache table not specified.");
        }

        $tableClass = $options['table'];

        $this->setTable(
            new $tableClass());
    }

    /**
     *
     * get cache table
     *
     * @return string
     */
    public function getTable()
    {
        return $this->_table;
    }

    /**
     *
     * set cache table
     *
     * @param \Cube\Db\Table\AbstractTable $table
     *
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function setTable(AbstractTable $table)
    {
        $this->_table = $table;

        return $this;
    }

    /**
     *
     * read from cache table
     *
     * @param string $name
     * @param string $type
     *
     * @return string|false
     */
    public function read($name, $type)
    {
        $where = $this->_table->select()
            ->where('name = ?', $name)
            ->where('type = ?', $type);

        $row = $this->_table->fetchRow($where);

        if ($row !== null) {
            $contents = $row['data'];

            return ($this->_serialization === true) ? unserialize($contents) : $contents;
        }

        return false;
    }

    /**
     *
     * write to cache table
     *
     * @param string $name
     * @param string $type
     * @param mixed  $data
     * @param  int   $expires
     *
     * @return $this
     * @throws \RuntimeException
     */
    public function write($name, $type, $data, $expires = null)
    {
        $where = $this->_table->select()
            ->where('name = ?', $name)
            ->where('type = ?', $type);

        /** @var \Cube\Db\Table\Row $row */
        $row = $this->_table->fetchRow($where);

        if ($row === null) {
            if ($this->_serialization === true) {
                $data = serialize($data);
            }

            $this->_table->insert(array(
                'name'       => $name,
                'type'       => $type,
                'data'       => $data,
                'created_at' => new Expr('now()'),
            ));
        }

        return $this;
    }

    /**
     *
     * delete a variable from cache
     *
     * @param string $name
     * @param string $type
     *
     * @return boolean
     */
    public function delete($name, $type)
    {
        $adapter = $this->_table->getAdapter();

        $where = array(
            $adapter->quoteInto('name = ?', $name),
            $adapter->quoteInto('type = ?', $type),
        );

        $this->_table->delete($where);

        return true;
    }

    /**
     *
     * purge cache
     *
     * @param string  $type
     * @param boolean $force
     *
     * @return $this
     */
    public function purge($type, $force = false)
    {
        $adapter = $this->_table->getAdapter();

        $where[] = $adapter->quoteInto('type = ?', $type);

        if ($force !== true) {
            $where[] = $adapter->quoteInto('created_at < ?',
                new Expr('(now() - interval ' . intval($this->_expires) . ' second)'));
        }

        $this->_table->delete($where);

        return $this;
    }

    /**
     *
     * clear cache
     *
     * @return $this
     */
    public function clear()
    {
        $this->_table->delete('');

        return $this;
    }

}

