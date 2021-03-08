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

namespace Admin\Controller;

use Ppb\Controller\Action\AbstractAction,
    Admin\Form\Settings as SettingsForm,
    Ppb\Service\Settings as SettingsService;

class Settings extends AbstractAction
{

    protected $_skipFields = array('page', 'csrf', 'submit', 'nb_uploads', 'file-site_logo_path', 'file-favicon', 'file-invoice_logo_path');

    public function Index()
    {
        $page = $this->getRequest()->getParam('page', 'site_setup');

        $form = new SettingsForm($page);
        $settingsService = new SettingsService();

        if ($this->getRequest()->isPost()) {
            $params = $this->getRequest()->getParams();

            $form->setData(
                $params);

            if ($form->isValid() === true) {
                $params = $form->getData();

                foreach ($this->_skipFields as $key) {
                    if (array_key_exists($key, $params)) {
                        unset($params[$key]);
                    }
                }

                $settingsService->save($params);

                $this->_flashMessenger->setMessage(array(
                    'msg'   => 'The settings have been saved.',
                    'class' => 'alert-success',
                ));

                switch ($page) {
                    case 'site_invoices':
                        if (empty($params['invoice_logo_path'])) {
                            $settingsService->save(array(
                                'invoice_logo_path' => ''
                            ));
                        }
                        break;
                    case 'stores_settings':
                        $enableStores = $this->getRequest()->getParam('enable_stores');
                        if (!$this->_settings['enable_products'] && $this->_settings['enable_auctions'] && $enableStores) {
                            $settingsService->save(array(
                                'enable_auctions_in_stores' => 1
                            ));
                        }
                        break;
                    case 'caching':
                        $adapter = '\\Cube\\Cache\\Adapter\\Files';
                        $routes = 'false';
                        $queries = 'false';
                        $namespace = \Ppb\Utility::generateRandomKey(16);

                        if (in_array($params['caching_engine'], array('Files', 'Table', 'Apc', 'Memcache'))) {
                            $adapter = '\\Cube\\Cache\\Adapter\\' . $params['caching_engine'];
                            $routes = 'true';
                            $queries = 'true';
                        }

                        $content = "<?php                                          " . "\n"
                            . "return array(                                       " . "\n"
                            . "    'cache' => array(                               " . "\n"
                            . "        'adapter'  => '%ADAPTER%',                  " . "\n"
                            . "        'folder'   => __DIR__ . '/../cache',        " . "\n"
                            . "        'table'    => '\\Ppb\\Db\\Table\\Cache',    " . "\n"
                            . "        'routes'   => %ROUTES%,                     " . "\n"
                            . "        'queries'  => %QUERIES%,                    " . "\n"
                            . "        'metadata' => true,                         " . "\n"
                            . "        'namespace' => '%NAMESPACE%',               " . "\n"
                            . "    ),                                              " . "\n"
                            . "); ";

                        $content = str_replace(
                            array('%ADAPTER%', '%ROUTES%', '%QUERIES%', '%NAMESPACE%'),
                            array($adapter, $routes, $queries, $namespace), $content);

                        $cacheConfigPath = APPLICATION_PATH . '/config/cache.config.php';

                        file_put_contents($cacheConfigPath, $content);
                        break;
                }

                // redirect after save to avoid multiple writes on refresh
                $redirectUrl = $this->getRequest()->getBaseUrl() .
                    $this->getRequest()->getRequestUri();
                $this->_helper->redirector()->gotoUrl($redirectUrl);
            }
            else {
                $this->_flashMessenger->setMessage(array(
                    'msg'   => $form->getMessages(),
                    'class' => 'alert-danger',
                ));
            }
        }
        else {
            $form->setData(
                $settingsService->get());
        }

        return array(
            'form'     => $form,
            'messages' => $this->_flashMessenger->getMessages()
        );
    }

}

