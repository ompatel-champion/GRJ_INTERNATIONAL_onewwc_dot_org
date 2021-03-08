<?php

/**
 *
 * PHP Pro Bid
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2020 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     9.0 [rev.9.0.01]
 */

/**
 * utility class
 */

namespace Ppb;

use Cube\Controller\Front,
    Cube\Controller\Request,
    Cube\Controller\Router,
    Cube\Application,
    Cube\ModuleManager,
    Cube\Locale;

final class Utility
{
    const URI_DELIMITER = '/';

    /**
     * global config folder
     */
    const GLOBAL_CONFIG_FOLDER = 'config';

    /**
     *
     * current software version
     */
    const VERSION = '8.2';

    /**
     *
     * application folders (relative paths) as defined in global.config.php
     *
     * @var array
     */
    protected static $_folders = null;

    /**
     *
     * application absolute paths as defined in global.config.php
     *
     * @var array
     */
    protected static $_paths = null;

    /**
     *
     * folders the skins selector should skip
     *
     * @var array
     */
    protected static $_themesToSkip = array('.', '..', 'admin');

    /**
     *
     * country iso mapping
     *
     * iso 3166 alpha 2 : alpha 3
     *
     * @var array
     */
    protected static $_countryIsoMapping = array(
        'AF' => 'AFG',
        'AX' => 'ALA',
        'AL' => 'ALB',
        'DZ' => 'DZA',
        'AS' => 'ASM',
        'AD' => 'AND',
        'AO' => 'AGO',
        'AI' => 'AIA',
        'AQ' => 'ATA',
        'AG' => 'ATG',
        'AR' => 'ARG',
        'AM' => 'ARM',
        'AW' => 'ABW',
        'AU' => 'AUS',
        'AT' => 'AUT',
        'AZ' => 'AZE',
        'BS' => 'BHS',
        'BH' => 'BHR',
        'BD' => 'BGD',
        'BB' => 'BRB',
        'BY' => 'BLR',
        'BE' => 'BEL',
        'BZ' => 'BLZ',
        'BJ' => 'BEN',
        'BM' => 'BMU',
        'BT' => 'BTN',
        'BO' => 'BOL',
        'BA' => 'BIH',
        'BW' => 'BWA',
        'BV' => 'BVT',
        'BR' => 'BRA',
        'VG' => 'VGB',
        'IO' => 'IOT',
        'BN' => 'BRN',
        'BG' => 'BGR',
        'BF' => 'BFA',
        'BI' => 'BDI',
        'KH' => 'KHM',
        'CM' => 'CMR',
        'CA' => 'CAN',
        'CV' => 'CPV',
        'KY' => 'CYM',
        'CF' => 'CAF',
        'TD' => 'TCD',
        'CL' => 'CHL',
        'CN' => 'CHN',
        'HK' => 'HKG',
        'MO' => 'MAC',
        'CX' => 'CXR',
        'CC' => 'CCK',
        'CO' => 'COL',
        'KM' => 'COM',
        'CG' => 'COG',
        'CD' => 'COD',
        'CK' => 'COK',
        'CR' => 'CRI',
        'CI' => 'CIV',
        'HR' => 'HRV',
        'CU' => 'CUB',
        'CY' => 'CYP',
        'CZ' => 'CZE',
        'DK' => 'DNK',
        'DJ' => 'DJI',
        'DM' => 'DMA',
        'DO' => 'DOM',
        'EC' => 'ECU',
        'EG' => 'EGY',
        'SV' => 'SLV',
        'GQ' => 'GNQ',
        'ER' => 'ERI',
        'EE' => 'EST',
        'ET' => 'ETH',
        'FK' => 'FLK',
        'FO' => 'FRO',
        'FJ' => 'FJI',
        'FI' => 'FIN',
        'FR' => 'FRA',
        'GF' => 'GUF',
        'PF' => 'PYF',
        'TF' => 'ATF',
        'GA' => 'GAB',
        'GM' => 'GMB',
        'GE' => 'GEO',
        'DE' => 'DEU',
        'GH' => 'GHA',
        'GI' => 'GIB',
        'GR' => 'GRC',
        'GL' => 'GRL',
        'GD' => 'GRD',
        'GP' => 'GLP',
        'GU' => 'GUM',
        'GT' => 'GTM',
        'GG' => 'GGY',
        'GN' => 'GIN',
        'GW' => 'GNB',
        'GY' => 'GUY',
        'HT' => 'HTI',
        'HM' => 'HMD',
        'VA' => 'VAT',
        'HN' => 'HND',
        'HU' => 'HUN',
        'IS' => 'ISL',
        'IN' => 'IND',
        'ID' => 'IDN',
        'IR' => 'IRN',
        'IQ' => 'IRQ',
        'IE' => 'IRL',
        'IM' => 'IMN',
        'IL' => 'ISR',
        'IT' => 'ITA',
        'JM' => 'JAM',
        'JP' => 'JPN',
        'JE' => 'JEY',
        'JO' => 'JOR',
        'KZ' => 'KAZ',
        'KE' => 'KEN',
        'KI' => 'KIR',
        'KP' => 'PRK',
        'KR' => 'KOR',
        'KW' => 'KWT',
        'KG' => 'KGZ',
        'LA' => 'LAO',
        'LV' => 'LVA',
        'LB' => 'LBN',
        'LS' => 'LSO',
        'LR' => 'LBR',
        'LY' => 'LBY',
        'LI' => 'LIE',
        'LT' => 'LTU',
        'LU' => 'LUX',
        'MK' => 'MKD',
        'MG' => 'MDG',
        'MW' => 'MWI',
        'MY' => 'MYS',
        'MV' => 'MDV',
        'ML' => 'MLI',
        'MT' => 'MLT',
        'MH' => 'MHL',
        'MQ' => 'MTQ',
        'MR' => 'MRT',
        'MU' => 'MUS',
        'YT' => 'MYT',
        'MX' => 'MEX',
        'FM' => 'FSM',
        'MD' => 'MDA',
        'MC' => 'MCO',
        'MN' => 'MNG',
        'ME' => 'MNE',
        'MS' => 'MSR',
        'MA' => 'MAR',
        'MZ' => 'MOZ',
        'MM' => 'MMR',
        'NA' => 'NAM',
        'NR' => 'NRU',
        'NP' => 'NPL',
        'NL' => 'NLD',
        'AN' => 'ANT',
        'NC' => 'NCL',
        'NZ' => 'NZL',
        'NI' => 'NIC',
        'NE' => 'NER',
        'NG' => 'NGA',
        'NU' => 'NIU',
        'NF' => 'NFK',
        'MP' => 'MNP',
        'NO' => 'NOR',
        'OM' => 'OMN',
        'PK' => 'PAK',
        'PW' => 'PLW',
        'PS' => 'PSE',
        'PA' => 'PAN',
        'PG' => 'PNG',
        'PY' => 'PRY',
        'PE' => 'PER',
        'PH' => 'PHL',
        'PN' => 'PCN',
        'PL' => 'POL',
        'PT' => 'PRT',
        'PR' => 'PRI',
        'QA' => 'QAT',
        'RE' => 'REU',
        'RO' => 'ROU',
        'RU' => 'RUS',
        'RW' => 'RWA',
        'BL' => 'BLM',
        'SH' => 'SHN',
        'KN' => 'KNA',
        'LC' => 'LCA',
        'MF' => 'MAF',
        'PM' => 'SPM',
        'VC' => 'VCT',
        'WS' => 'WSM',
        'SM' => 'SMR',
        'ST' => 'STP',
        'SA' => 'SAU',
        'SN' => 'SEN',
        'RS' => 'SRB',
        'SC' => 'SYC',
        'SL' => 'SLE',
        'SG' => 'SGP',
        'SK' => 'SVK',
        'SI' => 'SVN',
        'SB' => 'SLB',
        'SO' => 'SOM',
        'ZA' => 'ZAF',
        'GS' => 'SGS',
        'SS' => 'SSD',
        'ES' => 'ESP',
        'LK' => 'LKA',
        'SD' => 'SDN',
        'SR' => 'SUR',
        'SJ' => 'SJM',
        'SZ' => 'SWZ',
        'SE' => 'SWE',
        'CH' => 'CHE',
        'SY' => 'SYR',
        'TW' => 'TWN',
        'TJ' => 'TJK',
        'TZ' => 'TZA',
        'TH' => 'THA',
        'TL' => 'TLS',
        'TG' => 'TGO',
        'TK' => 'TKL',
        'TO' => 'TON',
        'TT' => 'TTO',
        'TN' => 'TUN',
        'TR' => 'TUR',
        'TM' => 'TKM',
        'TC' => 'TCA',
        'TV' => 'TUV',
        'UG' => 'UGA',
        'UA' => 'UKR',
        'AE' => 'ARE',
        'GB' => 'GBR',
        'US' => 'USA',
        'UM' => 'UMI',
        'UY' => 'URY',
        'UZ' => 'UZB',
        'VU' => 'VUT',
        'VE' => 'VEN',
        'VN' => 'VNM',
        'VI' => 'VIR',
        'WF' => 'WLF',
        'EH' => 'ESH',
        'YE' => 'YEM',
        'ZM' => 'ZMB',
        'ZW' => 'ZWE'
    );

    /**
     *
     * get a value from the folders array in global.config.php
     *
     * @param string $key
     *
     * @return string
     * @throws \InvalidArgumentException
     */
    public static function getFolder($key)
    {
        if (self::$_folders === null) {
            self::$_folders = Front::getInstance()->getOption('folders');
        }

        if (isset(self::$_folders[$key])) {
            return self::$_folders[$key];
        }

        throw new \InvalidArgumentException(
            sprintf("There is no '%s' key in the folders array.", $key));

    }

    /**
     *
     * get a value from the paths array in global.config.php
     *
     * @param string $key
     *
     * @return string
     * @throws \InvalidArgumentException
     */
    public static function getPath($key)
    {
        if (self::$_paths === null) {
            self::$_paths = Front::getInstance()->getOption('paths');
        }

        if (isset(self::$_paths[$key])) {
            return self::$_paths[$key];
        }

        throw new \InvalidArgumentException(
            sprintf("There is no '%s' key in the paths array.", $key));

    }

    /**
     *
     * get base path of the application
     * needed for loading mod files
     *
     * @return string
     */
    public static function getBasePath()
    {
        $path = self::getPath('base');

        return str_replace(self::GLOBAL_CONFIG_FOLDER . '/..', '', $path);
    }

    /**
     *
     * a method that will unserialize variables
     *
     * @9.0 we will first use json_decode on the variable, as we want to switch to json fields instead of serialized fields
     *
     * @param mixed  $variable input variable
     * @param mixed  $default  value to output if variable cannot be unserialized
     * @param int    $flags    flags to pass to html_entity_decode
     * @param string $encoding encoding to pass to html_entity_decode (defaulted to UTF-8 to avoid issues if php ver. < 5.4.0)
     *
     * @return array|string|false
     */
    public static function unserialize($variable, $default = false, $flags = ENT_COMPAT, $encoding = 'UTF-8')
    {
        $array = false;

        if (is_string($variable)) {
            $json = json_decode($variable, true);
            $array = (is_array($json)) ? $json : @unserialize($variable);
        }

        if (is_object($array)) {
            return $array;
        }

        if ($array !== false) {
            if (!is_array($array)) {
                $array = array($array);
            }

            foreach ($array as $key => $value) {
                if (is_string($value)) {
                    $array[$key] = html_entity_decode($value, $flags, $encoding);
                }
            }

            return $array;
        }

        if ($default === false) {
            $default = $variable;
        }

        return (false !== ($array = @unserialize(html_entity_decode($variable, $flags,
                $encoding)))) ? $array : $default;
    }

    /**
     *
     * get site themes (folder => name) format
     *
     * @return array
     */
    public static function getThemes()
    {
        self::_checkLicenseSecretKey();

        $themesFolder = self::getPath('themes');

        $skins = array();

        if ($handle = opendir($themesFolder)) {
            while (false !== ($folder = readdir($handle))) {
                if (is_dir($themesFolder . DIRECTORY_SEPARATOR . $folder) && !in_array($folder, self::$_themesToSkip)) {
                    $name = @file_get_contents($themesFolder . DIRECTORY_SEPARATOR . $folder . DIRECTORY_SEPARATOR . 'name.txt');
                    $skins[$folder] = ($name) ? $name : $folder;
                }
            }
            closedir($handle);
            clearstatcache();
        }

        return $skins;
    }

    /**
     *
     * get country iso mapping array
     *
     * @param bool $arrayFlip
     *
     * @return array
     */
    public static function getCountryIsoMapping($arrayFlip = false)
    {
        $countryIsoMapping = self::$_countryIsoMapping;

        if ($arrayFlip) {
            $countryIsoMapping = array_flip($countryIsoMapping);
        }

        return $countryIsoMapping;
    }

    /**
     *
     * enter alpha 3 code and get alpha 2 code
     *
     * @param string $countryCode
     *
     * @return string|null
     */
    public static function getCountryIsoAlpha2($countryCode)
    {
        $countryIsoMapping = self::getCountryIsoMapping(true);

        return (array_key_exists($countryCode, $countryIsoMapping)) ? $countryIsoMapping[$countryCode] : null;
    }

    /**
     *
     * enter alpha 2 code and get alpha 3 code
     *
     * @param string $countryCode
     *
     * @return string|null
     */
    public static function getCountryIsoAlpha3($countryCode)
    {
        $countryIsoMapping = self::getCountryIsoMapping();

        return (array_key_exists($countryCode, $countryIsoMapping)) ? $countryIsoMapping[$countryCode] : null;
    }

    /**
     *
     * get site languages (file name => name) format
     *
     * @return array
     */
    public static function getLanguages()
    {
        $langFolder = self::getPath('languages');

        $languages = array();

        if ($handle = opendir($langFolder)) {
            while (false !== ($file = readdir($handle))) {
                if ($file != '.' && $file != '..') {
                    $extension = pathinfo($file, PATHINFO_EXTENSION);
                    if ($extension == 'php') {
                        $lang = str_replace('.php', '', $file);
                        $languages[$lang] = Locale::getLocaleName($lang);
                    }
                }
            }

            closedir($handle);
            clearstatcache();
        }

        return $languages;
    }

    /**
     *
     * get system emails (absolute path => name) format
     *
     * @return array
     */
    public static function getSystemEmails()
    {
        $modules = \Cube\ModuleManager::getInstance()->getPaths();

        $emails = array();

        foreach ($modules as $module) {
            $folder = APPLICATION_PATH . '/' . $module . '/../view/emails';

            if ($handle = @opendir($folder)) {
                while (false !== ($file = readdir($handle))) {
                    if ($file != '.' && $file != '..') {
                        $emails[$folder . '/' . $file] = $file;
                    }
                }
                closedir($handle);
                clearstatcache();
            }
        }

        return $emails;
    }

    /**
     *
     * check whether mod rewrite is enabled on the server
     * works for apache mod_php and apache fastcgi
     *
     * @return bool
     */
    public static function checkModRewrite()
    {
        $baseUrl = sprintf(
            "%s://%s%s",
            isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http',
            $_SERVER['SERVER_NAME'],
            str_replace(array('index.php', 'cron.php', 'enable-installer.php'), 'test-mod-rewrite', $_SERVER['PHP_SELF'])
        );

        if (stristr($baseUrl, 'test-mod-rewrite')) {
            $contents = @file_get_contents($baseUrl);

            if (strpos($contents, 'SUCCESS') !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     *
     * activate standard router
     *
     * @return void
     */
    public static function activateStandardRouter()
    {
        $request = new Request\Standard();
        $request->setModule($request->getParam('module'));

        ModuleManager::getInstance()
            ->resetProperties()
            ->setRouteClass('\Cube\Controller\Router\Route\Standard')
            ->setModules(Application::getInstance()->getOption('modules'))
            ->setNamespaces(Application::getInstance()->getOption('namespaces'))
            ->setActiveModule($request->getModule());

        $router = new Router\Standard(
            ModuleManager::getInstance()->getRoutes());

        Front::getInstance()
            ->setRouter($router)
            ->setRequest($request);
    }

    /**
     *
     * get maximum file upload size in bytes (integer
     *
     * @param string $suffix
     *
     * @return int
     */
    public static function getMaximumFileUploadSize($suffix = null)
    {
        $size = min(
            self::_convertPHPSizeToBytes(ini_get('post_max_size')),
            self::_convertPHPSizeToBytes(ini_get('upload_max_filesize'))
        );

        switch (strtoupper($suffix)) {
            case 'P':
                $size /= 1024;
            case 'T':
                $size /= 1024;
            case 'G':
                $size /= 1024;
            case 'M':
                $size /= 1024;
            case 'K':
                $size /= 1024;
                break;
        }

        return $size;
    }

    /**
     *
     * var dump
     *
     * @param $var
     */
    public static function var_dump($var)
    {
        echo '<pre>';
        var_dump($var);
        echo '</pre>';
    }

    /**
     *
     * 7.8: checks if the saved license is valid - used on the installer module
     *
     * @return bool
     */
    public static function isValidLicense()
    {
        return self::_checkLicenseSecretKey(true);
    }


    /**
     *
     * convert php size to bytes
     *
     * @param $input
     *
     * @return bool|int|string
     */
    private static function _convertPHPSizeToBytes($input)
    {
        if (is_numeric($input)) {
            return $input;
        }

        $suffix = substr($input, -1);
        $value = substr($input, 0, -1);

        switch (strtoupper($suffix)) {
            case 'P':
                $value *= 1024;
            case 'T':
                $value *= 1024;
            case 'G':
                $value *= 1024;
            case 'M':
                $value *= 1024;
            case 'K':
                $value *= 1024;
                break;
        }

        return $value;
    }

    /**
     *
     * generate random string key
     *
     * @param int $length
     *
     * @return string
     */
    public static function generateRandomKey($length = 8)
    {
        return substr(str_shuffle(str_repeat("abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ", $length)), 0, $length);
    }

    /**
     *
     * license check method
     *
     * @param bool $checkOnly
     *
     * @return bool
     */
    private static function _checkLicenseSecretKey($checkOnly = false)
    {
        return true;
    }

}

