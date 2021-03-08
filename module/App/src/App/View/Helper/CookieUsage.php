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
 * cookie usage view helper class
 */

namespace App\View\Helper;

use Ppb\View\Helper\AbstractHelper,
    Cube\Controller\Front;

class CookieUsage extends AbstractHelper
{

    /**
     * cookie name
     */
    const COOKIE_USAGE = 'CookieUsage';

    /**
     *
     * the view partial to be used
     *
     * @var string
     */
    protected $_partial = 'partials/cookie-usage.phtml';

    /**
     *
     * cookie usage initialization class
     *
     * @param string $partial
     *
     * @return $this
     */
    public function cookieUsage($partial = null)
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

        if ($settings['enable_cookie_usage_confirmation']) {
            $view = $this->getView();

            $bootstrap = Front::getInstance()->getBootstrap();
            /** @var \Cube\Session $session */
            $session = $bootstrap->getResource('session');

            if (!$session->getCookie(self::COOKIE_USAGE)) {
                /** @var \Cube\View\Helper\Script $helper */
                $helper = $view->getHelper('script');

                $cookieKey = $session->getCookieKey(self::COOKIE_USAGE);

                $cookiePath = (!empty($view->baseUrl)) ? $view->baseUrl : $view::URI_DELIMITER;

                $helper->addBodyCode('<script type="text/javascript" src="' . $view->baseUrl . '/js/cookie.js"></script>')
                    ->addBodyCode("
                        <script type=\"text/javascript\">
                            $('.btn-cookie-confirm').on('click', function() {
                                $.cookie('" . $cookieKey . "', '1', {path: '" . $cookiePath . "', expires: 30});
                                $('.cookie-usage').remove();
                            });
                        </script>");

                $view->set('cookieUsageMessage', $settings['cookie_usage_message']);

                return $view->process(
                    $this->getPartial(), true);
            }
        }

        return '';
    }

}

