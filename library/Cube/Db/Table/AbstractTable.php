<?php

/**
 *
 * Cube Framework
 *
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2020 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 *
 * @version     2.2 [rev.2.2.02]
 */

namespace Cube\Db\Table;

use Cube\Db,
    Cube\Db\Select,
    Cube\Db\Adapter\AbstractAdapter,
    Cube\Db\Expr,
    Cube\Controller\Front,
    Cube\Cache\Adapter\AbstractAdapter as CacheAdapter;

abstract class AbstractTable
{
    /**
     * class constants
     */

    const NAME = 'name';
    const COLS = 'cols';
    const PRIMARY = 'primary';
    const METADATA = 'metadata';
    const REFERENCE_MAP = 'referenceMap';
    const DEPENDENT_TABLES = 'dependentTables';
    const COLUMNS = 'columns';
    const REF_TABLE_CLASS = 'refTableClass';
    const REF_COLUMNS = 'refColumns';

    const QUERIES_CACHE_EXPIRES = 300; // 5 minutes

    /**
     *
     * table name
     *
     * @var string
     */
    protected $_name;

    /**
     *
     * table prefix
     * (set in configuration)
     *
     * @var string
     */
    protected $_prefix;

    /**
     *
     * database adapter
     *
     * @var \Cube\Db\Adapter\AbstractAdapter
     */
    protected $_adapter;

    /**
     * table column names derived from
     * \Cube\Db\Adapter\AbstractAdapter::describeTable()
     *
     * @var array
     */
    protected $_cols = null;

    /**
     *
     * primary key column(s)
     * A compound key should be declared as an array
     *
     * @var string|array
     */
    protected $_primary = null;

    /**
     *
     * information provided by the adapter's describeTable() method
     *
     * @var array
     */
    protected $_metadata = array();

    /**
     *
     * columns to remove when calling the update method
     *
     * @var array
     */
    protected $_removeOnUpdate = array();

    /**
     *
     * class name for row
     *
     * @var string
     */
    protected $_rowClass = '\Cube\Db\Table\Row';

    /**
     *
     * class name for rowset
     *
     * @var string
     */
    protected $_rowsetClass = '\Cube\Db\Table\Rowset';

    /**
     * Associative array map of declarative referential integrity rules.
     * This array has one entry per foreign key in the current table.
     * Each key is a mnemonic name for one reference rule.
     *
     * Each value is also an associative array, with the following keys:
     * - columns       = array of names of column(s) in the child table.
     * - refTableClass = class name of the parent table.
     * - refColumns    = array of names of column(s) in the parent table,
     *                   in the same order as those in the 'columns' entry.
     * - onDelete      = "cascade" means that a delete in the parent table also
     *                   causes a delete of referencing rows in the child table.
     * - onUpdate      = "cascade" means that an update of primary key values in
     *                   the parent table also causes an update of referencing
     *                   rows in the child table.
     *
     * @var array
     */
    protected $_referenceMap = array();

    /**
     * Simple array of class names of tables that are "children" of the current
     * table, in other words tables that contain a foreign key to this one.
     * Array elements are not table names; they are class names of classes that
     * extend Zend_Db_Table_Abstract.
     *
     * @var array
     */
    protected $_dependentTables = array();

    /**
     *
     * flag that sets if table queries are cacheable
     * used to determine if queries cache is to be purged on a create/update/delete operation
     *
     * @var bool
     */
    protected $_cacheableQueries = false;

    /**
     *
     * cache object
     *
     * @var \Cube\Cache|false       false if caching is disabled
     */
    protected $_cache = false;

    /**
     *
     * class constructor
     *
     * @param \Cube\Db\Adapter\AbstractAdapter $adapter
     *
     * @throws \RuntimeException
     */
    public function __construct(AbstractAdapter $adapter = null)
    {
        $bootstrap = Front::getInstance()->getBootstrap();

        $this->_cache = $bootstrap->getResource('cache');

        if ($adapter === null) {
            $adapter = $bootstrap->getResource('db');
        }

        if (!$adapter instanceof AbstractAdapter) {
            throw new \RuntimeException("Could not create table. 
                The database adapter must be an instance of \Cube\Db\Adapter\AbstractAdapter");
        }

        if (empty($this->_name)) {
            $this->_name = strtolower(get_class());
        }

        $adapterConfig = $adapter->getConfig();

        if (isset($adapterConfig['prefix'])) {
            $this->_prefix = $adapterConfig['prefix'];
        }

        $this->setAdapter($adapter);
    }

    /**
     *
     * get table name
     *
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     *
     * get table prefix
     *
     * @return string
     */
    public function getPrefix()
    {
        return $this->_prefix;
    }

    /**
     *
     * set table prefix
     *
     * @param string $prefix
     *
     * @return $this
     */
    public function setPrefix($prefix = null)
    {
        $this->_prefix = $prefix;

        return $this;
    }

    /**
     *
     * get database adapter
     *
     * @return \Cube\Db\Adapter\AbstractAdapter
     */
    public function getAdapter()
    {
        return $this->_adapter;
    }

    /**
     *
     * set database adapter
     *
     * @param \Cube\Db\Adapter\AbstractAdapter $adapter
     *
     * @return $this
     * @throws \RuntimeException
     */
    public function setAdapter($adapter)
    {
        if (!$adapter instanceof AbstractAdapter) {
            throw new \RuntimeException("Could not create table. 
                The database adapter must be an instance of \Cube\Db\Adapter\AbstractAdapter");
        }

        $this->_adapter = $adapter;

        return $this;
    }

    /**
     *
     * get remove on update columns
     *
     * @return array
     */
    public function getRemoveOnUpdate()
    {
        return $this->_removeOnUpdate;
    }

    /**
     *
     * set remove on update columns
     *
     * @param array $removeOnUpdate
     *
     * @return $this
     */
    public function setRemoveOnUpdate($removeOnUpdate)
    {
        $this->_removeOnUpdate = $removeOnUpdate;

        return $this;
    }

    /**
     *
     * get row object class
     *
     * @return string
     */
    public function getRowClass()
    {
        return $this->_rowClass;
    }

    /**
     *
     * set row object class
     *
     * @param string $rowClass
     *
     * @return $this
     */
    public function setRowClass($rowClass)
    {
        $this->_rowClass = (string)$rowClass;

        return $this;
    }

    /**
     *
     * get rowset object class
     *
     * @return string
     */
    public function getRowsetClass()
    {
        return $this->_rowsetClass;
    }

    /**
     *
     * set rowset object class
     *
     * @param string $rowsetClass
     *
     * @return $this
     */
    public function setRowsetClass($rowsetClass)
    {
        $this->_rowsetClass = (string)$rowsetClass;

        return $this;
    }

    /**
     *
     * get the reference between the table and a requested table
     *
     * @param string $refTableClass
     * @param string $ruleKey
     *
     * @return array
     * @throws \RuntimeException
     */
    public function getReference($refTableClass, $ruleKey = null)
    {
        if ($ruleKey !== null) {
            if (!isset($this->_referenceMap[$ruleKey])) {
                throw new \RuntimeException(
                    sprintf("A reference rule with the name '%s' does not exist in the definition of '%s'.", $ruleKey,
                        get_class($this)));
            }
            if ($this->_referenceMap[$ruleKey][self::REF_TABLE_CLASS] != $refTableClass) {
                throw new \RuntimeException(
                    sprintf("The reference rule '%s' does not reference the table '%s'.", $ruleKey, $refTableClass));
            }

            return $this->_referenceMap[$ruleKey];
        }


        foreach ($this->_referenceMap as $reference) {
            if ($reference[self::REF_TABLE_CLASS] == $refTableClass) {
                return $reference;
            }
        }

        throw new \RuntimeException(
            sprintf("There is no reference from table '%s' to table '%s'.", get_class($this), $refTableClass));
    }

    /**
     *
     * add a reference to the reference map of the table
     *
     * @param string $ruleKey
     * @param mixed  $columns
     * @param string $refTableClass
     * @param mixed  $refColumns
     *
     * @return $this
     */
    public function setReference($ruleKey, $columns, $refTableClass, $refColumns)
    {
        $reference = array(self::COLUMNS         => (array)$columns,
                           self::REF_TABLE_CLASS => $refTableClass,
                           self::REF_COLUMNS     => (array)$refColumns);


        $this->_referenceMap[$ruleKey] = $reference;

        return $this;
    }

    /**
     *
     * set the reference map of the table
     *
     * @param array $referenceMap
     *
     * @return $this
     */
    public function setReferenceMap(array $referenceMap)
    {
        $this->_referenceMap = $referenceMap;

        return $this;
    }

    /**
     *
     * get dependent tables
     *
     * @return array
     */
    public function getDependentTables()
    {
        return $this->_dependentTables;
    }

    /**
     *
     * set dependent tables
     *
     * @param array $dependentTables
     *
     * @return $this
     */
    public function setDependentTables(array $dependentTables)
    {
        $this->_dependentTables = $dependentTables;

        return $this;
    }

    /**
     *
     * create an instance of the select object
     *
     * @param array|string|\Cube\Db\Expr $cols The columns to select from this table.
     *
     * @return \Cube\Db\Select
     */
    public function select($cols = '*')
    {

        $select = new Select($this->_adapter);
        $select->setPrefix($this->getPrefix())
            ->from($this->_name, $cols);

        return $select;
    }

    /**
     *
     * Inserts a table row with specified data.
     *
     * @param array $data Column-value pairs.
     *
     * @return int the id of the inserted column.
     */
    public function insert(array $data)
    {
        $this->_purgeQueriesCache();

        $data = $this->_prepareData($data, true);

        $this->_adapter->insert($this->_prefix . $this->_name, $data);

        return $this->lastInsertId();
    }

    /**
     *
     * Updates table rows with specified data based on a WHERE clause.
     *
     * @param array $data  Column-value pairs.
     * @param mixed $where UPDATE WHERE clause(s).
     *
     * @return int          The number of affected rows.
     */
    public function update(array $data, $where)
    {
        $data = $this->_prepareData($data);

        $result = $this->_adapter->update($this->_prefix . $this->_name, $data, $where);

        if ($result > 0) {
            $this->_purgeQueriesCache();
        }

        return $result;
    }

    /**
     *
     * delete table rows based on a WHERE clause.
     *
     * @param mixed $where DELETE WHERE clause(s).
     *
     * @return int          The number of affected rows.
     */
    public function delete($where)
    {
        $result = $this->_adapter->delete($this->_prefix . $this->_name, $where);

        if ($result > 0) {
            $this->_purgeQueriesCache();
        }

        return $result;
    }

    /**
     *
     * fetches all matched rows
     *
     * @param string|\Cube\Db\Select $where SQL where clause, or a select object
     * @param string|array           $order
     * @param int                    $count
     * @param int                    $offset
     * @param string|array           $cache
     *
     * @return \Cube\Db\Table\Rowset\AbstractRowset
     */
    public function fetchAll($where = null, $order = null, $count = null, $offset = null, $cache = null)
    {
        if (!$where instanceof Select) {
            $select = $this->select()
                ->where($where)
                ->order($order)
                ->limit($count, $offset);
        }
        else {
            $select = $where;
        }

//        if (!$select->getPart(Select::ORDER)) {
//            $select->order($order);
//        }
//
//        if (!$select->getPart(Select::LIMIT_COUNT) && !$select->getPart(Select::LIMIT_OFFSET)) {
//            $select->limit($count, $offset);
//        }

        $cachedData = false;
        $cacheFile = null;
        $rows = null;

        // cacheQueries: if requested in method, if enabled for the table and if enabled globally
        $cacheQueries = ($cache !== null) ? $this->_getCache('cacheQueries') : false;

        if ($cacheQueries !== false) {
            $cacheFile = md5($select->assemble());

            if (($data = $this->_cache->read($cacheFile, CacheAdapter::QUERIES)) !== false) {
                $cacheCol = (is_array($cache)) ? $cache[CacheAdapter::CACHE_COL] : $cache;

                $data = empty($data) ? array(0) : $data;

                $select->reset(Select::WHERE)
                    ->where("{$cacheCol} IN (?)", $data);

                $cachedData = true;
            }
        }

        $stmt = $this->_adapter->query($select);
        $rows = $stmt->fetchAll(Db::FETCH_ASSOC);

        if ($cachedData === false) {
            if ($cacheQueries !== false) {
                $data = array();

                $cacheWhere = (is_array($cache)) ? $cache[CacheAdapter::CACHE_WHERE] : $cache;

                foreach ($rows as $row) {
                    $data[] = $row[$cacheWhere];
                }

                $this->_cache->write($cacheFile, CacheAdapter::QUERIES, $data, self::QUERIES_CACHE_EXPIRES);
            }
        }

        $data = array(
            'table' => $this,
            'data'  => $rows,
        );

        return new $this->_rowsetClass($data);
    }

    /**
     *
     * fetch a single matched row from a result set
     *
     * @param string|\Cube\Db\Select $where SQL where clause, or a select object
     * @param string|array           $order
     * @param int                    $offset
     * @param string                 $cacheId
     *
     * @return \Cube\Db\Table\Row\AbstractRow|null
     */
    public function fetchRow($where = null, $order = null, $offset = null, $cacheId = null)
    {
        return $this->fetchAll($where, $order, 1, $offset, $cacheId)->getRow(0);
    }

    /**
     *
     * get the id resulted from an insert operation
     *
     * @return int
     */
    public function lastInsertId()
    {
        return $this->_adapter->lastInsertId();
    }

    /**
     *
     * returns table information
     *
     * @param string $key specific info part to return
     *
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public function info($key = null)
    {
        $this->_getPrimary();

        $info = array(
            self::NAME             => $this->_name,
            self::COLS             => $this->_getCols(),
            self::PRIMARY          => (array)$this->_primary,
            self::METADATA         => $this->_metadata,
            self::REFERENCE_MAP    => $this->_referenceMap,
            self::DEPENDENT_TABLES => $this->_dependentTables,
        );

        if ($key === null) {
            return $info;
        }

        if (!array_key_exists($key, $info)) {
            throw new \InvalidArgumentException(
                sprintf("There is no table information for the key '%s'.", $key));
        }

        return $info[$key];
    }

    /**
     *
     * get table metadata
     * use cache if caching is enabled
     *
     * @return array
     */
    protected function _getMetadata()
    {
        if (!count($this->_metadata)) {
            $cachedData = false;
            $cacheMetadata = $this->_getCache('cacheMetadata');
            $cacheFile = null;

            if ($cacheMetadata !== false) {
                $cacheFile = md5("DESCRIBE " . $this->_prefix . $this->_name);
            }

            if ($cacheMetadata !== false) {
                if (($data = $this->_cache->read($cacheFile, CacheAdapter::METADATA)) !== false) {
                    $this->_metadata = $data;
                    $cachedData = true;
                }
            }

            if ($cachedData === false) {
                $this->_metadata = $this->_adapter->describeTable($this->_prefix . $this->_name);

                if ($cacheMetadata !== false) {
                    $this->_cache->write($cacheFile, CacheAdapter::METADATA, $this->_metadata);
                }
            }
        }

        return $this->_metadata;
    }

    /**
     *
     * get cache
     *
     * @param string $key
     *
     * @return mixed
     */
    protected function _getCache($key = null)
    {
        if ($this->_cache !== false) {
            if ($key === null) {
                return $this->_cache;
            }
            else {
                $methodName = 'get' . ucfirst($key);

                if (method_exists($this->_cache, $methodName)) {
                    if ($key != 'cacheQueries' || $this->_cacheableQueries) {
                        return $this->_cache->$methodName();
                    }
                }
            }
        }

        return false;
    }

    /**
     *
     * purge queries cache
     *
     * @return $this
     */
    protected function _purgeQueriesCache()
    {
        $cacheQueries = $this->_getCache('cacheQueries');

        if ($cacheQueries !== false) {
            $this->_cache->getAdapter()->purge(CacheAdapter::QUERIES, true);
        }

        return $this;
    }

    /**
     *
     * get table columns
     *
     * @return array
     */
    protected function _getCols()
    {
        if ($this->_cols === null) {
            $this->_cols = array_keys(
                $this->_getMetadata());
        }

        return $this->_cols;
    }

    /**
     *
     * get primary key(s)
     *
     * @return array
     * @throws \RuntimeException
     */
    protected function _getPrimary()
    {
        if (!$this->_primary) {
            $this->_getMetadata();
            $this->_primary = array();
            foreach ($this->_metadata as $col) {
                if ($col['PRIMARY']) {
                    $this->_primary[$col['PRIMARY_POSITION']] = $col['COLUMN_NAME'];
                }
            }
        }
        else if (!is_array($this->_primary)) {
            $this->_primary = array(1 => $this->_primary);
        }
        else if (isset($this->_primary[0])) {
            array_unshift($this->_primary, null);
            unset($this->_primary[0]);
        }

        $cols = $this->_getCols();
        if (!array_intersect((array)$this->_primary, $cols) == (array)$this->_primary) {
            throw new \RuntimeException(
                sprintf("Invalid primary key column(s): %s.", implode(',', (array)$this->_primary)));
        }

        return $this->_primary;
    }

    /**
     *
     * prepares data for an insert or update operation, by removing any keys that
     * do not correspond to columns in the selected table
     * serialize all arrays before saving them in the database
     *
     * @param array $data
     * @param bool  $insert
     *
     * @return array
     */
    protected function _prepareData($data, $insert = false)
    {
        $tableMetadata = $this->_getMetadata();
        $primary = $this->_getPrimary();

        if ($insert) {
            $columns = array_map(function () {
                return null;
            }, array_flip($this->_getCols()));

            $data = array_merge($columns, $data);
        }

        foreach ($data as $key => $value) {
            $unset = true;
            if (array_key_exists($key, $tableMetadata)) {
                if (!in_array($key, $primary)) {
                    $type = (array_key_exists('DATA_TYPE', $tableMetadata[$key])) ? $tableMetadata[$key]['DATA_TYPE'] : 'UNDEFINED';
                    $default = (array_key_exists('DEFAULT', $tableMetadata[$key])) ? $tableMetadata[$key]['DEFAULT'] : null;
                    $nullable = (array_key_exists('NULLABLE', $tableMetadata[$key])) ? $tableMetadata[$key]['NULLABLE'] : false;

                    $data[$key] = $this->_prepareValue($value, $type, $default, $nullable);

                    $unset = false;
                }
            }

            if ($unset || ($insert === false && in_array($key, $this->getRemoveOnUpdate()))) {
                unset($data[$key]);
            }
        }

        return $data;
    }

    /**
     *
     * prepare insert / update value for saving
     * @9.0 if we have a serialized value or a json encoded value, dont apply the html_entity_decode syntax
     *
     * @param mixed  $value
     * @param string $type
     * @param mixed  $default
     * @param bool   $nullable
     *
     * @return float|int|string|null
     */
    protected function _prepareValue($value, $type, $default = null, $nullable = false)
    {
        $input = $value;

        if (is_array($value)) {
            $value = array_filter($value);
            $value = (!empty($value)) ? serialize($value) : '';
        }
        else if (is_string($value)) {
            $json = json_decode($value, true);
            $array = (is_array($json)) ? $json : @unserialize($value);

            if (!is_array($array)) {
                $value = html_entity_decode($value);
            }
        }

        if (!$value instanceof Expr) {
            switch (strtoupper($type)) {
                case 'TINYINT':
                case 'SMALLINT':
                case 'MEDIUMINT':
                case 'INT':
                case 'BIGINT':
                    $value = intval($value);
                    break;
                case 'DECIMAL':
                case 'FLOAT':
                case 'DOUBLE':
                    $value = floatval($value);
                    break;
                case 'CHAR':
                case 'VARCHAR':
                case 'TEXT':
                case 'MEDIUMTEXT':
                case 'LONGTEXT':
                case 'BINARY':
                case 'VARBINARY':
                case 'TINYBLOB':
                case 'BLOB':
                case 'MEDIUMBLOB':
                case 'LONGBLOB':
                case 'TINYTEXT':
                case 'ENUM':
                case 'SET':
                    $value = strval($value);
                    break;
                case 'DATE':
                case 'TIME':
                case 'DATETIME':
                case 'TIMESTAMP':
                case 'YEAR':
                    if ($value === null && $nullable === false) {
                        $value = new Expr('now()');
                    }
                    break;
            }
        }

        /**
         * possible values that return true for the empty function:
         *
         * ""       (an empty string)
         * 0        (0 as an integer)
         * 0.0      (0 as a float)
         * "0"      (0 as a string)
         * NULL
         * FALSE
         * array()  (an empty array)
         */
        if (empty($value)) {
            if ($nullable === true) {
                $value = null;
            }
            else if ($default !== null && $input !== 0 && $input !== "") {
                $value = $default;
            }
        }

        return $value;
    }
}

