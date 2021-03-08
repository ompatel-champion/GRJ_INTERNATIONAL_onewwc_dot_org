<?php

/**
 *
 * PHP Pro Bid
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2020 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     8.2 [rev.8.2.01]
 */

/**
 * advertising table service class
 */

namespace Ppb\Service;

use Ppb\Db\Table\Advertising as AdvertisingTable;

class Advertising extends AbstractService
{

    /**
     *
     * default advert sections - will return in case
     * the active theme doesnt have a valid "adverts.txt" file set
     *
     * @var array
     */
    protected $_defaultSections = array(
        'header' => 'Site Header',
        'footer' => 'Site Footer',
    );

    /**
     *
     * class constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->setTable(
            new AdvertisingTable());
    }

    /**
     *
     * save settings
     *
     * @param array $data
     *
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function saveSettings(array $data)
    {
        if (!isset($data['id'])) {
            throw new \InvalidArgumentException("The form must use an element with the name 'id'.");
        }

        $table = $this->getTable();
        $columns = array_keys($data);

        foreach ((array)$data['id'] as $key => $value) {
            $row = $table->fetchRow($table->getAdapter()->quoteInto('id = ?', $value));

            $input = array();
            foreach ($columns as $column) {
                if (is_array($data[$column]) && isset($data[$column][$key])) {
                    $input[$column] = $data[$column][$key];
                }
            }

            $input = parent::_prepareSaveData($input);

            if ($row !== null) {
                $table->update($input,
                    $table->getAdapter()->quoteInto('id = ?', $value));
            }
        }

        return $this;
    }

    /**
     *
     * get the available advertising sections for the currently active theme
     *
     * @return array
     */
    public function getSections()
    {
        $settings = $this->getSettings();

        $fileName = \Ppb\Utility::getPath('themes') . DIRECTORY_SEPARATOR . $settings['default_theme'] . DIRECTORY_SEPARATOR . 'adverts.txt';

        if (file_exists($fileName)) {
            $output = array();
            if (($handle = fopen($fileName, "r")) !== false) {
                while (($data = fgetcsv($handle)) !== false) {
                    $output[$data[0]] = $data[1];
                }
                fclose($handle);
            }

            return $output;
        }

        return $this->_defaultSections;
    }

}

