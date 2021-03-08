<?php

/**
 *
 * PHP Pro Bid
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2018 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     8.0 [rev.8.0.02]
 */

/**
 * url view helper
 *
 * IMPORTANT: dynamic routes will be set by slugs
 */

namespace Ppb\View\Helper;

use Cube\Controller\Front,
    Cube\View\Helper\Url as UrlHelper;

class Url extends UrlHelper
{

    /**
     *
     * force all urls to redirect to the site
     *
     * @var string
     */
    private $_sitePath = null;

    /**
     *
     * clean params
     *
     * @var array
     */
    private $_cleanParams = array();

    public function __construct($sitePath)
    {
        $this->setSitePath($sitePath);
    }

    /**
     *
     * get site path
     *
     * @return string
     */
    public function getSitePath()
    {
        return $this->_sitePath;
    }

    /**
     *
     * set site path
     *
     * @param string $sitePath
     *
     * @return $this
     */
    public function setSitePath($sitePath)
    {
        $this->_sitePath = $sitePath;

        return $this;
    }


    /**
     *
     * create an url based on a set of params and the router object
     * 7.5: added workaround for key/value pairs for which the cleaned value results in an empty string
     *
     * @param string|array $params       a string or an array of params
     * @param string       $name         the name of the specific route to use
     * @param bool         $addGetParams whether to attach params resulted from a previous get operation to the url
     * @param array        $skipParams   an array of params to be omitted when constructing the url
     * @param bool         $addBaseUrl   flag to add the base url param to the assembled route
     * @param bool         $cleanString  if true, will clean values of get variables (only if params is an array)
     *
     * @return string                   the url of the link href attribute
     */
    public function url($params, $name = null, $addGetParams = false, array $skipParams = null, $addBaseUrl = true, $cleanString = true)
    {
        $router = Front::getInstance()->getRouter();

        if (is_array($params)) {
            if ($skipParams !== null) {
                $skipParams = array_diff($skipParams, array_keys($params));
            }

            if ($cleanString === true) {
                foreach ($params as $key => $value) {
                    $uniqueKey = md5(json_encode($params) . $key);
                    $params[$key] = $this->_cleanParam($uniqueKey, $value);
                }
            }
        }

        return (($addBaseUrl) ? $this->getSitePath() : '') . $router->assemble($params, $name, false, $addGetParams, $skipParams);
    }

    /**
     *
     * clean param
     *
     * @param string $key
     * @param string $value
     *
     * @return string
     */
    private function _cleanParam($key, $value)
    {
        if (!array_key_exists($key, $this->_cleanParams)) {
            $this->_cleanParams[$key] = self::cleanString($value);
        }

        return $this->_cleanParams[$key];
    }

}

