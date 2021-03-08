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
 * icon display view helper class
 */

namespace Ppb\View\Helper;

use Cube\Controller\Front;

class Icon extends AbstractHelper
{

    /**
     * icon types
     */
    const TYPE_FEATHER_SPAN = 'feather-span';
    const TYPE_FEATHER_IMG = 'feather-img';

    /**
     *
     * icon types html definitions
     *
     * @var array
     */
    public static $types = array(
        self::TYPE_FEATHER_SPAN => '<span data-feather="%name%" class="%class%" title="%title%">',
        self::TYPE_FEATHER_IMG  => '<img src="%name%" class="feather %class%" alt="%title%">',
    );

    /**
     *
     * feather icons svg code
     *
     * @var array
     */
    protected static $_featherIcons = array(
        'award'        => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-award %class%" title="%title%"><circle cx="12" cy="8" r="7"></circle><polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"></polyline></svg>',
        'check'        => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-check %class%" title="%title%"><polyline points="20 6 9 17 4 12"></polyline></svg>',
        'check-square' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-check-square %class%" title="%title%"><polyline points="9 11 12 14 22 4"></polyline><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path></svg>',
        'dollar-sign'  => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-dollar-sign %class%" title="%title%"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>',
        'download'     => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-download %class%" title="%title%"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>',
        'map-pin'      => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-map-pin %class%" title="%title%"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>',
        'shopping-bag' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-shopping-bag %class%" title="%title%"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path><line x1="3" y1="6" x2="21" y2="6"></line><path d="M16 10a4 4 0 0 1-8 0"></path></svg>',
        'star'         => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-star %class%" title="%title%"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>',
        'truck'        => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-truck %class%" title="%title%"><rect x="1" y="3" width="15" height="13"></rect><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon><circle cx="5.5" cy="18.5" r="2.5"></circle><circle cx="18.5" cy="18.5" r="2.5"></circle></svg>',
        'x'            => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x %class%" title="%title%"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>',
    );

    /**
     *
     * absolute path flag
     *
     * @var bool
     */
    protected $_absolutePath = false;

    /**
     *
     * icon type
     *
     * @var string
     */
    protected $_type = self::TYPE_FEATHER_SPAN;

    /**
     *
     * get absolute path flag
     *
     * @return bool
     */
    public function isAbsolutePath()
    {
        return $this->_absolutePath;
    }

    /**
     *
     * set absolute path flag
     *
     * @param bool $absolutePath
     *
     * @return $this
     */
    public function setAbsolutePath($absolutePath)
    {
        $this->_absolutePath = $absolutePath;

        return $this;
    }

    /**
     *
     * get icon type
     *
     * @return string
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     *
     * set icon type
     *
     * @param string $type
     *
     * @return $this
     */
    public function setType($type)
    {
        $this->_type = $type;

        return $this;
    }

    /**
     *
     * icon helper main class
     *
     * @param bool   $absolutePath
     * @param string $type
     *
     * @return $this
     */
    public function icon($absolutePath = null, $type = null)
    {
        if ($absolutePath !== null) {
            $this->setAbsolutePath($absolutePath);
        }

        if ($type !== null) {
            $this->setType($type);
        }

        return $this;
    }

    /**
     *
     * render icon
     *
     * @param string $name
     * @param string $title
     * @param string $class
     *
     * @return string
     */
    public function render($name, $title = null, $class = null)
    {
        $type = $this->getType();

        $string = self::$types[$type];

        switch ($type) {
            case self::TYPE_FEATHER_IMG:
                if (array_key_exists($name, self::$_featherIcons)) {
                    $string = self::$_featherIcons[$name];
                }
                else {
                    $name = $this->_getFeatherIconsPath() . \Ppb\Utility::URI_DELIMITER . $name . '.svg';
                }
                break;
        }

        $output = str_replace(
            array('%name%', '%title%', '%class%'),
            array($name, $title, $class),
            $string
        );

        return $output;
    }

    /**
     *
     * get feather svg icons path
     *
     * @return string
     */
    protected function _getFeatherIconsPath()
    {
        $settings = $this->getSettings();
        $baseUrl = Front::getInstance()->getRequest()->getBaseUrl();

        return (($this->isAbsolutePath() === true) ? $settings['site_path'] : $baseUrl)
            . \Ppb\Utility::URI_DELIMITER . \Ppb\Utility::getFolder('img')
            . \Ppb\Utility::URI_DELIMITER . 'feather';
    }
}

