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
 * language selector widget view helper class
 * - the url will never change, just that we will add a param (lang = code) which will
 *   then trigger the language change plugin
 */
/**
 * MOD:- GOOGLE TRANSLATE
 *
 * @version 2.0
 */

namespace App\View\Helper;

use Ppb\View\Helper\AbstractHelper,
    Cube\Controller\Front;
## -- ADD 1L -- [ MOD:- GOOGLE TRANSLATE ]
use Cube\ModuleManager;

class Language extends AbstractHelper
{

    /**
     *
     * the view partial to be used
     *
     * @var string
     */
    protected $_partial = 'partials/languages.phtml';

    /**
     * Language constructor.
     */
    public function __construct()
    {
        $settings = $this->getSettings();

        if ($settings['user_languages']) {
            $view = $this->getView();

            /** @var \Cube\View\Helper\Script $helper */
            $helper = $view->getHelper('script');
            $helper->addHeaderCode('<link href="' . $view->baseUrl . '/css/flag-icons/css/flag-icon.min.css" rel="stylesheet" type="text/css">');
        }
    }

    /**
     *
     * languages helper initialization class
     *
     * @param string $partial
     *
     * @return $this
     */
    public function language($partial = null)
    {
        if ($partial !== null) {
            $this->setPartial($partial);
        }

        return $this;
    }

    /**
     *
     * flag class
     *
     * @param string $locale
     *
     * @return string
     */
    public function flagClass($locale)
    {
        $array = explode('_', $locale);

        return strtolower(end($array));
    }

    /**
     *
     * render partial
     *
     * @return string
     */
    public function render()
    {
        $frontController = Front::getInstance();
        $settings = $this->getSettings();

        $languages = array();

        ## -- ADD 1L -- [ MOD:- GOOGLE TRANSLATE ]
        $activeModule = ModuleManager::getInstance()->getActiveModule();
        
        if ($settings['user_languages']) {
            ## -- CHANGE 1L -- [ MOD:- GOOGLE TRANSLATE ]
            if ($activeModule == 'Admin' || !$settings['google_translate']) {
                $view = $this->getView();

                $translateOption = $frontController->getOption('translate');
                if (array_key_exists('translations', $translateOption)) {
                    $languages = $translateOption['translations'];
                }

                $view->setVariables(array(
                    'languages'    => $languages,
                    'activeLocale' => $this->getTranslate()->getLocale(),
                'object'       => $this,
                ));

                return $view->process(
                    $this->getPartial(), true);
            }
            ## -- ADD -- [ MOD:- GOOGLE TRANSLATE ]
            else if ($settings['google_translate']) {
                $view = $this->getView();
                return $view->googleTranslate()->render();
            }
            ## -- ./ADD -- [ MOD:- GOOGLE TRANSLATE ]
        }

        return '';
    }

}

