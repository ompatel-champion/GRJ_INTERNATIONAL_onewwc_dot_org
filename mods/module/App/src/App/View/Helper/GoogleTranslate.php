<?php

/**
 *
 * PHP Pro Bid
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2018 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     8.0 [rev.8.0.01]
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

use Ppb\View\Helper\AbstractHelper;

class GoogleTranslate extends AbstractHelper
{

    /**
     *
     * the view partial to be used
     *
     * @var string
     */
    protected $_partial = 'partials/google-translate.phtml';

    /**
     * Language constructor.
     */
    public function __construct()
    {
        $settings = $this->getSettings();

        if ($settings['google_translate']) {
            $view = $this->getView();

            /** @var \Cube\View\Helper\Script $helper */
            $helper = $view->getHelper('script');
            $helper->addBodyCode("
                <script type=\"text/javascript\">
                    function googleTranslateElementInit() {
                        new google.translate.TranslateElement(
                            {
                                pageLanguage: 'en',
                                includedLanguages: 'af,sq,ar,be,bg,ca,zh-CN,zh-TW,hr,cs,da,nl,en,et,tl,fi,fr,gl,de,el,iw,hi,hu,is,id,ga,it,ja,ko,lv,lt,mk,ms,mt,no,fa,pl,pt,ro,ru,sr,es,sk,sl,sw,sv,th,tr,uk,vi,cy,yi',
                                layout: google.translate.TranslateElement.InlineLayout.SIMPLE
                            },
                            'googleTranslateElement'
                        );

                        //googObj.translator.init();
                    }

                </script>")
                ->addBodyCode('<script type="text/javascript" src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>')
                ->addHeaderCode('
                <style type="text/css">
                    #googleTranslateElement {
                        display: inline-block;
                    }
                    @media (max-width: 767px) {
                        body {
                            top: 0 !important;
                        }
                        .goog-te-banner-frame {
                            display: none;
                            z-index: 1;
                            position: relative;
                        }
                    }
                </style>');
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
    public function googleTranslate($partial = null)
    {
        if ($partial !== null) {
            $this->setPartial($partial);
        }

        return $this;
    }

    /**
     *
     * render partial
     *
     * @return string
     */
    public function render()
    {
        $settings = $this->getSettings();

        if ($settings['google_translate']) {
            $view = $this->getView();

            return $view->process(
                $this->getPartial(), true);
        }

        return '';
    }

}

