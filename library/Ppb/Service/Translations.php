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
 * translations service class
 */

namespace Ppb\Service;

use Cube\Controller\Front,
    Cube\Loader\Autoloader,
    Cube\Locale,
    Cube\Http\Download;

class Translations extends AbstractService
{

    /**
     * default translation file names, without extension
     */
    const DEFAULT_TRANSLATION_NAME = 'default';

    /**
     * default translation files path
     */
    const DEFAULT_TRANSLATION_PATH = 'sources';

    /**
     * wrap sentences in lines of 75 characters
     */
    const WRAPPING_COLUMN = 75;

    /**
     * line ending char for when generating the .po file
     */
    const LINE_ENDING = "\n";

    /**
     * translation file extensions
     */
    const TRANSLATION_PO = 'po';
    const TRANSLATION_MO = 'mo';

    /**
     * english language files
     */
    const EN_US = 'en_US';

    /**
     *
     * these language files cannot be deleted
     *
     * @var array
     */
    protected $_defaultTranslations = array(
        self::EN_US,
    );

    /**
     *
     * dynamic sentences to be added to the .po file before download
     *
     * @var array
     */
    private static $_dynamicSentences = array(
        'xml'      => array(
            APPLICATION_PATH . '/module/Admin/config/data/navigation/navigation.xml',
            APPLICATION_PATH . '/module/App/config/data/navigation/navigation.xml',
        ),
        'table'    => array(
            '\Ppb\Service\Table\Relational\Categories'      => array('name', 'meta_title', 'meta_description'),
            '\Ppb\Service\Table\Relational\Locations'       => array('name'),
            '\Ppb\Service\Table\Relational\ContentSections' => array('name'),
            '\Ppb\Service\Table\OfflinePaymentMethods'      => array('name'),
            '\Ppb\Service\Table\Durations'                  => array('description'),
            '\Ppb\Service\Table\Currencies'                 => array('description'),
            '\Ppb\Service\Table\StoresSubscriptions'        => array('name'),
            '\Ppb\Service\Table\TaxTypes'                   => array('name', 'description'),
            '\Ppb\Service\CustomFields'                     => array('label', 'description', 'subtitle', 'prefix', 'suffix', 'multiOptions'),
        ),
        'settings' => array(
            'sitename',
            'meta_title',
            'meta_description',
//            'listing_terms_content',
            'cookie_usage_message',
        ),
        'array'    => array(
            APPLICATION_PATH . '/config/data/language/sources/extra/*.php',
        ),
    );

    /**
     *
     * active translations
     *
     * @var array
     */
    protected $_activeTranslations = array();

    public function __construct()
    {
        $translateOption = Front::getInstance()->getOption('translate');
        if (array_key_exists('translations', $translateOption)) {
            $this->_activeTranslations = $translateOption['translations'];
        }
    }

    /**
     *
     * fetch translations, all or active
     *
     * @param bool $active
     *
     * @return array
     */
    public function fetchTranslations($active = false)
    {
        if ($active) {
            return $this->_activeTranslations;
        }

        $path = \Ppb\Utility::getPath('languages');
        $files = glob($path . '/*.mo');

        $translations = array();

        foreach ($files as $file) {
            $locale = str_replace(array($path . '/', '.mo'), '', $file);

            $translations[] = array(
                'locale' => $locale,
                'path'   => APPLICATION_PATH . '/config/data/language/' . $locale,
                'img'    => 'flags/' . $locale . '.png',
                'name'   => Locale::getLocaleName($locale),
                'desc'   => $this->getTranslationLabel($locale),
                'active' => $this->isActive($locale),
                'date'   => filemtime($file),
            );
        }

        return $translations;
    }

    /**
     *
     * check if a translation is active
     *
     * @param string $locale
     *
     * @return bool
     */
    public function isActive($locale)
    {
        foreach ($this->_activeTranslations as $translation) {
            if ($translation['locale'] == $locale) {
                return true;
            }
        }

        return false;
    }

    /**
     *
     * check if a translation exists
     *
     * @param string $locale
     *
     * @return bool
     */
    public function translationExists($locale)
    {
        $translations = $this->fetchTranslations();

        foreach ($translations as $translation) {
            if ($translation['locale'] == $locale) {
                return true;
            }
        }

        return false;
    }

    /**
     *
     * get translation label
     *
     * @param string $locale
     *
     * @return null
     */
    public function getTranslationLabel($locale)
    {
        foreach ($this->_activeTranslations as $translation) {
            if ($translation['locale'] == $locale) {
                return $translation['desc'];
            }
        }

        return null;
    }

    /**
     *
     * create translation files, if they do not already exist
     *
     * @param string $locale
     *
     * @return bool
     */
    public function createTranslation($locale)
    {
        if (!$this->translationExists($locale)) {
            $path = \Ppb\Utility::getPath('languages');

            $source = $path . DIRECTORY_SEPARATOR . self::DEFAULT_TRANSLATION_PATH . DIRECTORY_SEPARATOR . self::DEFAULT_TRANSLATION_NAME;
            $destination = $path . DIRECTORY_SEPARATOR . $locale;

            $extensions = array('.mo', '.php', '.po');

            foreach ($extensions as $extension) {
                $sourceFile = $source . $extension;
                $destinationFile = $destination . $extension;
                copy($sourceFile, $destinationFile);

                // change locale in .po file
                if ($extension == '.po') {
                    $content = file_get_contents($destinationFile);
                    $content = str_replace('"Language: en_US\n"', '"Language: ' . $locale . '\n"', $content);
                    file_put_contents($destinationFile, $content);
                }
            }

            return true;
        }

        return false;
    }

    /**
     *
     * download po translation file
     * add all missing sentences to the file before downloading
     *
     * @param string $locale
     *
     * @return bool
     */
    public function downloadTranslation($locale)
    {
        if ($this->translationExists($locale)) {
            $filePath = \Ppb\Utility::getPath('languages') . DIRECTORY_SEPARATOR .
                $locale . '.po';

            $this->_updateTranslationFile($filePath);

            $download = new Download($filePath);
            $download->send();

            return true;
        }

        return false;
    }

    /**
     *
     * activate translation
     * add code to translate.config.php
     *
     * @param string $locale
     *
     * @return $this
     */
    public function activate($locale)
    {
        $translations = $this->fetchTranslations();

        foreach ($translations as $key => $translation) {
            if (!$translation['active'] && $translation['locale'] != $locale) {
                unset($translations[$key]);
            }
        }

        $this->_generateTranslationConfigFile($translations);

        return $this;
    }

    /**
     *
     * inactivate translation
     * remove code from translate.config.php
     *
     * @param string $locale
     *
     * @return $this
     */
    public function inactivate($locale)
    {
        $translations = $this->fetchTranslations();

        foreach ($translations as $key => $translation) {
            if (!$translation['active'] || $translation['locale'] == $locale) {
                unset($translations[$key]);
            }
        }

        $this->_generateTranslationConfigFile($translations);

        return $this;
    }

    /**
     *
     * save translation label
     *
     * @param string $locale
     * @param string $label
     *
     * @return $this
     */
    public function saveLabel($locale, $label)
    {
        $translations = $this->fetchTranslations(true);

        foreach ($translations as $key => $translation) {
            if (!$this->isActive($translation['locale'])) {
                unset($translations[$key]);
            }
            else if ($translation['locale'] == $locale) {
                $translations[$key]['desc'] = $label;
            }
        }

        $this->_generateTranslationConfigFile($translations);

        return $this;
    }

    /**
     *
     * check if the translation can be deleted
     *
     * @param string $locale
     *
     * @return bool
     */
    public function canDelete($locale)
    {
        if (in_array($locale, $this->_defaultTranslations) || $this->isActive($locale)) {
            return false;
        }

        return true;
    }

    /**
     *
     * delete translation files
     *
     * @param string $locale
     *
     * @return $this
     */
    public function delete($locale)
    {
        if ($this->canDelete($locale)) {
            $this->inactivate($locale);

            $path = \Ppb\Utility::getPath('languages');
            $files = glob($path . '/*');

            array_map(function ($value) {
                @unlink($value);
            }, preg_grep('/' . $locale . '\.(mo|php|po)$/i', $files));
        }

        return $this;
    }

    /**
     *
     * save changes
     *
     * @param array $data
     *
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function saveChanges(array $data)
    {
        if (!isset($data['id'])) {
            throw new \InvalidArgumentException("The form must use an element with the name 'id'.");
        }

        foreach ((array)$data['id'] as $key => $locale) {
            $active = $data['active'][$key];
            $label = $data['desc'][$key];

            if ($active) {
                $this->activate($locale);
            }
            else {
                $this->inactivate($locale);
            }

            $this->saveLabel($locale, $label);
        }

        return $this;
    }

    /**
     *
     * upload translation files
     *
     * @param string $locale
     * @param string $po
     * @param string $mo
     *
     * @return bool
     */
    public function uploadTranslation($locale, $po, $mo)
    {
        if ($this->translationExists($locale)) {
            if (!empty($po) && !empty($mo)) {
                $uploadsPath = \Ppb\Utility::getPath('uploads');
                $languagesPath = \Ppb\Utility::getPath('languages');

                $movePo = rename($uploadsPath . DIRECTORY_SEPARATOR . $po, $languagesPath . DIRECTORY_SEPARATOR . $locale . '.po');
                $moveMo = rename($uploadsPath . DIRECTORY_SEPARATOR . $mo, $languagesPath . DIRECTORY_SEPARATOR . $locale . '.mo');

                if ($movePo && $moveMo) {
                    return true;
                }
            }
        }

        return false;
    }


    /**
     *
     * generate an save translate config file
     *
     * @param array $data
     *
     * @return $this
     */
    protected function _generateTranslationConfigFile(array $data)
    {
        $template = "array(                                                         " . "\n"
            . " 'locale'  => '%LOCALE%',                                            " . "\n"
            . " 'path'    => __DIR__ . '/data/language/%LOCALE%',                   " . "\n"
            . " 'img'     => 'flags/%LOCALE%.png',                                  " . "\n"
            . " 'name'    => '%NAME%',                                              " . "\n"
            . " 'desc'    => '%LABEL%',                                             " . "\n"
            . " 'sources' => array(                                                 " . "\n"
            . "     array(                                                          " . "\n"
            . "         'adapter'   => '\\Cube\\Translate\\Adapter\\ArrayAdapter',  " . "\n"
            . "         'extension' => 'php',                                       " . "\n"
            . "     ),                                                              " . "\n"
            . "     array(                                                          " . "\n"
            . "         'adapter'   => '\\Cube\\Translate\\Adapter\\Gettext',       " . "\n"
            . "         'extension' => 'mo',                                        " . "\n"
            . "     ),                                                              " . "\n"
            . " ),                                                                  " . "\n"
            . "),                                                                   " . "\n";

        $translationsContent = $activeTranslations = array();

        foreach ($data as $key => $translation) {
            $translationsContent[] = str_replace(
                array('%LOCALE%', '%NAME%', '%LABEL%'),
                array($translation['locale'], $translation['name'], $translation['desc']), $template);

            $activeTranslations[] = array(
                'locale' => $translation['locale'],
                'name'   => $translation['name'],
                'desc'   => $translation['desc'],
            );
        }

        $content = "<?php                                                           " . "\n"
            . "return array(                                                        " . "\n"
            . "    'translate' => array(                                            " . "\n"
            . "        'adapter'  => '\\Ppb\\Translate\\Adapter\\Composite',        " . "\n"
            . "        'translations'   => array(                                   " . "\n"
            . "             %TRANSLATIONS%                                          " . "\n"
            . "        ),                                                           " . "\n"
            . "    ),                                                               " . "\n"
            . "); ";

        $content = str_replace('%TRANSLATIONS%', implode('', $translationsContent), $content);

        $translateConfigPath = APPLICATION_PATH . '/config/translate.config.php';

        file_put_contents($translateConfigPath, $content);

        $this->_activeTranslations = $activeTranslations;

        return $this;
    }

    protected function _updateTranslationFile($filePath)
    {
        $settings = $this->getSettings();

        $src = $this->_parsePoFile(
            \Ppb\Utility::getPath('languages') . DIRECTORY_SEPARATOR . self::DEFAULT_TRANSLATION_PATH . DIRECTORY_SEPARATOR . self::DEFAULT_TRANSLATION_NAME . '.po'
        );

        // add dynamic sentences
        foreach (self::$_dynamicSentences as $type => $array) {
            foreach ($array as $k => $v) {
                switch ($type) {
                    case 'xml':
                        $pattern = str_replace('navigation.', '*.', $v);

                        $additionalFiles = array_unique(array_merge(
                            glob($pattern),
                            glob(str_replace(APPLICATION_PATH, APPLICATION_PATH . DIRECTORY_SEPARATOR . Autoloader::getInstance()->getModsPath(), $pattern))
                        ));

                        $object = new \Cube\Config\Xml($v);

                        foreach ($additionalFiles as $additionalFile) {
                            if (file_exists($additionalFile) && $additionalFile != $v) {
                                $object->addData($additionalFile);
                            }
                        }

                        $ar = $object->getData();
                        array_walk_recursive($ar, function (&$value, &$key) use (&$src) {
                            if ($key == 'label') {
                                $val = trim(strval($value));
                                if (!empty($val)) {
                                    if (!in_array($val, $src)) {
                                        array_push($src, $val);
                                    }
                                }
                            }
                        });

                        break;
                    case 'table':
                        /** @var \Ppb\Service\AbstractService $service */
                        $service = new $k();
                        $rowset = $service->fetchAll($service->getTable()->select($v));
                        foreach ($rowset as $row) {
                            foreach ($v as $column) {
                                if ($column == 'multiOptions') {
                                    $multiOptions = \Ppb\Utility::unserialize($row[$column]);
                                    if (!empty($multiOptions['value'])) {
                                        $multiOptionsVal = $multiOptions['value'];
                                        foreach ($multiOptionsVal as $val) {
                                            $val = strval($val);
                                            if (!empty($val)) {
                                                if (!in_array($val, $src)) {
                                                    array_push($src, $val);
                                                }
                                            }
                                        }
                                    }
                                }
                                else {
                                    $val = strval($row[$column]);
                                    if (!empty($val)) {
                                        if (!in_array($val, $src)) {
                                            array_push($src, $val);
                                        }
                                    }
                                }
                            }
                        }
                        break;
                    case 'settings':
                        $string = strval($settings[$v]);
                        if (!empty($string)) {
                            if (!in_array($string, $src)) {
                                array_push($src, $string);
                            }
                        }
                        break;
                    case 'array':
                        $files = glob($v);

                        foreach ($files as $file) {
                            if (file_exists($file)) {
                                $arrayData = include $file;

                                foreach ($arrayData as $string) {
                                    if (!empty($string)) {
                                        if (!in_array($string, $src)) {
                                            array_push($src, $string);
                                        }
                                    }
                                }
                            }
                        }
                        break;
                }
            }
        }

        $dest = $this->_parsePoFile($filePath);


        // generate new sentences
        $sentences = array_filter($src, function ($element) use ($dest) {
            return !in_array($element, $dest);
        });

        // add them to the file to be downloaded
        $handle = fopen($filePath, 'a');

        foreach ($sentences as $sentence) {
            $sentence = $this->_prepareTranslationLine($sentence);

            $multiple = (count($sentence) > 1) ? true : false;
            if ($multiple) {
                fwrite($handle, 'msgid ""' . self::LINE_ENDING);
            }

            foreach ($sentence as $line) {
                $line = $this->_prepareExport($line, $multiple);
                fwrite($handle, $line . self::LINE_ENDING);
            }

            fwrite($handle, 'msgstr ""' . self::LINE_ENDING);
            fwrite($handle, self::LINE_ENDING);
        }

        fclose($handle);

        return $this;
    }

    /**
     *
     * convert string to formatted .po line
     *
     * @param string $input
     *
     * @return array
     */
    protected function _prepareTranslationLine($input)
    {
        $output = array();

        $wrap = wordwrap($input, self::WRAPPING_COLUMN, " " . "\n");
        $array = explode("\n", $wrap);

        $array = (is_array($array)) ? $array : array();

        if (count($array) > 1) {
            foreach ($array as $line) {
                $output[] = $line;
            }
        }
        else {
            $output[] = reset($array);
        }

        return $output;
    }

    /**
     *
     * prepare string before saving to file
     *
     * @param string $string
     * @param bool   $multiple
     *
     * @return string
     */
    protected function _prepareExport($string, $multiple = false)
    {
        $string = addslashes($string);
        $string = preg_replace("#\\'#", "'", $string);

        return (($multiple === false) ? 'msgid ' : '') . '"' . $string . '"';
    }

    /**
     *
     * parse .po file imported line
     *
     * @param string $string
     *
     * @return string
     */
    protected function _parseLine($string)
    {
        $string = trim(preg_replace('/^(?#~\s)msgid/', '', $string));
        $string = substr($string, 1, -1);

        return $string;
    }

    /**
     *
     * parse po file and return all msgid entries in in array format
     *
     * @param string $fileName
     *
     * @return array
     */
    protected function _parsePoFile($fileName)
    {
        $handle = @fopen($fileName, 'rb');

        $output = array();

        while (!feof($handle)) {
            $line = fgets($handle);
            $line = stripslashes(trim($line));

            if (
                strpos($line, 'msgid') === 0 ||
                strpos($line, '#~ msgid') === 0
            ) {
                $entry = array($this->_parseLine($line));
            }
            else if (strpos($line, '"') === 0) {
                if (isset($entry)) {
                    $entry[] = $this->_parseLine($line);
                }
            }
            else {
                if (isset($entry)) {
                    array_push($output, implode('', $entry));
                    unset($entry);
                }
            }

        }

        fclose($handle);

        return $output;
    }

}

