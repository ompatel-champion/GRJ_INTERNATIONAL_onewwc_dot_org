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
 * content sections table service class
 */

namespace Ppb\Service\Table\Relational;

use Cube\Db\Select,
    Cube\Db\Expr,
    Ppb\Db\Table\ContentSections as ContentSectionsTable,
    Ppb\Db\Table\Row\ContentSection as ContentSectionModel,
    Cube\Navigation;

class ContentSections extends AbstractServiceTableRelational
{

    /**
     *
     * class constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->setInsertRows(3)
            ->setTable(
                new ContentSectionsTable());
    }

    /**
     *
     * set content sections table data.
     * This data will be used for traversing the content sections tree
     *
     * @param string|\Cube\Db\Select $where           SQL where clause, or a select object
     * @param array|\Traversable     $data            Optional. Custom categories data
     * @param int                    $activeSectionId Active section id
     *
     * @return $this
     */
    public function setData($where = null, array $data = null, $activeSectionId = null)
    {
        if ($data === null) {
            $sections = $this->_table->fetchAll($where, array('parent_id ASC', '-order_id DESC', 'name ASC'));

            $data = array();

            $order = 0;

            /** @var \Ppb\Db\Table\Row\ContentSection $section */
            foreach ($sections as $section) {
                $data[$section['parent_id']][] = array(
                    'className'       => '\Ppb\Navigation\Page\ContentSection',
                    'id'              => $section['id'],
                    'label'           => $section['name'],
                    'slug'            => $section->link(),
                    'activeSectionId' => $activeSectionId,
                    'order'           => $order++,
                    'params'          => array(
                        'id' => $section['id'],
                    ),
                );
            }

            reset($data);

            $tree = $this->_createTree($data, current($data));

            $this->_data = new Navigation($tree);
        }
        else {
            $this->_data = $data;
        }

        return $this;
    }

    /**
     *
     * -- unused --
     *
     * @return array
     */
    public function getColumns()
    {
        return array();
    }

    /**
     *
     * -- unused --
     *
     * @return array
     */
    public function getElements()
    {
        return array();
    }

    /**
     *
     * save data in the table (update if an id exists or insert otherwise)
     *
     * @param array $data
     *
     * @return $this
     */
    public function save($data)
    {
        if (array_key_exists('order_id', $data)) {
            foreach ($data['id'] as $key => $value) {
                if (empty($data['order_id'][$key])) {
                    $data['order_id'][$key] = new Expr('null');
                }
            }
        }

        parent::save($data);

        return $this;
    }

    /**
     *
     * create or update an entry
     *
     * @param array $data
     *
     * @return $this
     */
    public function saveSingle(array $data)
    {
        $row = null;

        $table = $this->getTable();

        $data = $this->_prepareSaveDataSingle($data);

        if (array_key_exists('id', $data)) {
            $select = $table->select()
                ->where("id = ?", $data['id']);

            unset($data['id']);

            $row = $table->fetchRow($select);
        }

        if ($row !== null) {
            $id = $row['id'];
            $table->update($data,
                $table->getAdapter()->quoteInto('id = ?', $row['id']));
        }
        else {
            $id = $table->insert($data);
        }

        $this->_generateUris($id);

        return $this;
    }

    /**
     *
     * get content sections multi options array
     *
     * @param string|array|\Cube\Db\Select $where    SQL where clause, a select object, or an array of ids
     * @param string|array                 $order
     * @param bool                         $default  whether to add a default value field in the data
     * @param bool                         $fullName display full branch name (with parents)
     *
     * @return array
     */
    public function getMultiOptions($where = null, $order = null, $default = false, $fullName = false)
    {
        if (!$where instanceof Select) {
            $select = $this->_table->select();

            if ($where !== null) {
                if (is_array($where)) {
                    $select->where("id IN (?)", $where);
                }
                else {
                    $select->where("parent_id = ?", $where);
                }
            }

            $where = $select;
        }

        $data = parent::getMultiOptions($where, $order, $default, $fullName);

        asort($data);

        return $data;
    }

    /**
     *
     * prepare content section data for when saving to the table
     *
     * @param array $data
     *
     * @return array
     */
    protected function _prepareSaveDataSingle($data = array())
    {
        if (empty($data['parent_id'])) {
            $data['parent_id'] = null;
        }
        else {
            $data['type'] = ContentSectionModel::TYPE_TREE;
        }

        if (empty($data['order_id'])) {
            $data['order_id'] = null;
        }

        $data = parent::_prepareSaveData($data);

        return $data;
    }

    /**
     *
     * generate uris for the selected section and all subsections
     *
     * @param int $id
     *
     * @return $this
     */
    protected function _generateUris($id)
    {
        $sectionsIds = array_keys($this->getChildren($id, true));

        foreach ($sectionsIds as $sectionId) {
            $slugs = array();

            $section = $this->findBy('id', $sectionId);

            $id = $sectionId;

            do {
                $row = $this->findBy('id', $id);

                $id = 0;
                if ($row !== null) {
                    $slugs[] = $row['slug'];
                    $id = $row['parent_id'];
                }
            } while ($id > 0);

            $section->save(array(
                'uri' => implode('/', array_reverse($slugs)),
            ));
        }

        return $this;

    }

}

