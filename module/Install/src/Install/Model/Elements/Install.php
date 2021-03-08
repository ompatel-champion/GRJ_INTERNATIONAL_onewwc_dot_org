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

namespace Install\Model\Elements;

use Ppb\Model\Elements\AbstractElements;

class Install extends AbstractElements
{

    /**
     *
     * v7 installation types available
     * for now its null as we do not have any v7 versions
     *
     * @var array
     */
    protected $_installationTypes = array();

    /**
     *
     * available versions to update from
     *
     * @var array
     */
    protected $_upgradeVersions = array(
        '700' => '7.0',
        '701' => '7.1',
        '702' => '7.2',
        '703' => '7.3',
        '704' => '7.4',
        '705' => '7.5',
        '706' => '7.6',
        '707' => '7.7',
        '708' => '7.8',
        '709' => '7.9',
        '710' => '7.10',
        '800' => '8.0',
        '801' => '8.1',
    );

    /**
     *
     * class constructor
     *
     * @param mixed $formId
     */
    public function __construct($formId = null)
    {
        parent::__construct();

        $this->setFormId($formId);
    }

    /**
     *
     * get form elements
     *
     * @return array
     */
    public function getElements()
    {
        $modsSQLFiles = $this->_getModsSQLFiles();

        $sitePath = rtrim(sprintf(
            "%s://%s%s",
            isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http',
            $_SERVER['HTTP_HOST'],
            dirname($_SERVER['PHP_SELF'])
        ), '/');

        $array = array(
            /**
             * ++++++++++++++
             * v7 UPGRADE
             * ++++++++++++++
             */
//            array(
//                'form_id'     => 'upgraded',
//                'id'          => 'upgrade_error',
//                'element'     => 'description',
//                'label'       => $this->_('Installation Error'),
//                'description' => $this->_('Installation is not possible because you have already tried to install previously. In order to repeat the installation process, '
//                    . 'please copy <strong>global.config-original.php</strong> over <strong>global.config.php</strong> and refresh this page.'),
//                'required'    => true,
//            ),
            /**
             * ++++++++++++++
             * FRESH INSTALLATION FIELDS
             * ++++++++++++++
             */
            array(
                'form_id'     => 'install',
                'subtitle'    => $this->_('Database Connection'),
                'id'          => 'db_host',
                'element'     => 'text',
                'label'       => $this->_('Server Name'),
                'description' => $this->_('Please enter the name of your database server.'),
                'required'    => true,
                'attributes'  => array(
                    'class' => 'form-control input-large',
                ),
                'value'       => 'localhost',
                'validators'  => array(
                    'NoHtml',
                ),
            ),
            array(
                'form_id'    => 'install',
                'id'         => 'db_name',
                'element'    => 'text',
                'label'      => $this->_('Database Name'),
                'required'   => true,
                'attributes' => array(
                    'class' => 'form-control input-large',
                ),
                'validators' => array(
                    'NoHtml',
                ),
            ),
            array(
                'form_id'    => 'install',
                'id'         => 'db_username',
                'element'    => 'text',
                'label'      => $this->_('Connection Username'),
                'required'   => true,
                'attributes' => array(
                    'class' => 'form-control input-large',
                ),
                'validators' => array(
                    'NoHtml',
                ),
            ),
            array(
                'form_id'    => 'install',
                'id'         => 'db_password',
                'element'    => 'text',
                'label'      => $this->_('Connection Password'),
//                'required'   => true,
                'attributes' => array(
                    'class' => 'form-control input-large',
                ),
                'validators' => array(
                    'NoHtml',
                ),
            ),
            array(
                'form_id'     => 'install',
                'id'          => 'tables_prefix',
                'element'     => 'text',
                'label'       => $this->_('Tables Prefix'),
                'required'    => true,
                'value'       => 'ppb_',
                'description' => $this->_('Important: If your selected database contains PHP Pro Bid v6.x tables in it, please enter a different prefix for your '
                    . 'v7 tables to avoid naming conflicts.'),
                'attributes'  => array(
                    'class' => 'form-control input-large',
                ),
                'validators'  => array(
                    'Alphanumeric',
                ),
            ),
            /**
             * ++++++++++++++
             * UPGRADE FROM v6 FIELDS - ONLY WHEN DOING A FRESH INSTALLATION
             * ++++++++++++++
             */
            array(
                'form_id'      => 'v6_importer',
                'id'           => 'v6_importer',
                'element'      => 'checkbox',
                'label'        => $this->_('Import PPB 6.x Data'),
                'description'  => $this->_('Check the above checkbox if you wish to import data from a PHP Pro Bid v6.x installation.<br>'
                    . 'You will need to use the same database, but will need to enter a different prefix for the v7 tables than the v6.x tables have.'),
                'multiOptions' => array(
                    1 => null,
                ),
                'attributes'   => array(
                    'class' => 'field-changeable',
                ),
                'bodyCode'     => "
                    <script type=\"text/javascript\">
                        function checkFormFields()
                        {
                            if ($('input:checkbox[name=\"v6_importer\"]').is(':checked')) {
                                $('.importer-field').closest('.form-group').show();
                            }
                            else {
                                $('.importer-field').closest('.form-group').hide();
                            }
                        }

                        checkFormFields();

                        $(document).on('change', '.field-changeable', function() {
                            checkFormFields();
                        });
                    </script>"
            ),
            array(
                'form_id'    => 'v6_importer',
                'id'         => 'v6_tables_prefix',
                'element'    => 'text',
                'label'      => $this->_('v6.x Tables Prefix'),
                'required'   => ($this->getData('v6_importer')) ? true : false,
                'value'      => 'probid_',
                'attributes' => array(
                    'class' => 'form-control input-large importer-field',
                ),
            ),
            /**
             * ++++++++++++++
             * SECURITY SETTINGS
             * ++++++++++++++
             */
            array(
                'form_id'    => 'install',
                'subtitle'   => $this->_('Security Settings'),
                'id'         => 'session_namespace',
                'element'    => 'text',
                'label'      => $this->_('Session Namespace'),
                'required'   => true,
                'attributes' => array(
                    'class' => 'form-control input-large',
                ),
                'value'      => \Ppb\Utility::generateRandomKey(),
                'validators' => array(
                    'Alphanumeric',
                ),
            ),
            array(
                'form_id'    => 'install',
                'id'         => 'session_secret',
                'element'    => 'text',
                'label'      => $this->_('Security Secret Key'),
                'required'   => true,
                'attributes' => array(
                    'class' => 'form-control input-large',
                ),
                'value'      => \Ppb\Utility::generateRandomKey(),
                'validators' => array(
                    'NoHtml',
                ),
            ),
            /**
             * ++++++++++++++
             * ADMIN ACCOUNT
             * ++++++++++++++
             */
            array(
                'form_id'    => 'install',
                'subtitle'   => 'Create Administrator Account',
                'id'         => 'admin_username',
                'element'    => 'text',
                'label'      => $this->_('Username'),
                'required'   => true,
                'attributes' => array(
                    'class' => 'form-control input-large',
                ),
                'validators' => array(
                    'Alphanumeric',
                ),
            ),
            array(
                'form_id'    => 'install',
                'id'         => 'admin_password',
                'element'    => 'password',
                'label'      => $this->_('Password'),
                'required'   => true,
                'attributes' => array(
                    'class' => 'form-control input-large',
                ),
                'validators' => array(
                    'NoHtml',
                ),
            ),
            array(
                'form_id'    => 'install',
                'id'         => 'admin_password_confirm',
                'element'    => 'password',
                'label'      => $this->_('Confirm Password'),
                'attributes' => array(
                    'class' => 'form-control input-large',
                ),
                'validators' => array(
                    'NoHtml',
                ),
            ),
            /**
             * ++++++++++++++
             * SITE SETTINGS
             * ++++++++++++++
             */
            array(
                'form_id'     => 'install',
                'subtitle'    => $this->_('Site Settings'),
                'id'          => 'site_path',
                'element'     => 'text',
                'label'       => $this->_('Site URL'),
                'description' => $this->_('The URL must have the following format: http://www.yoursite.com<br>'
                    . 'If you have SSL available you can set your URL using https:// rather than http:// (Optional)'),
                'required'    => true,
                'attributes'  => array(
                    'class' => 'form-control input-xlarge',
                ),
                'validators'  => array(
                    'Url',
                ),
                'value'       => $sitePath,
            ),
            array(
                'form_id'     => 'install',
                'id'          => 'site_name',
                'element'     => 'text',
                'label'       => $this->_('Site Name'),
                'description' => $this->_('Enter your site\'s name. The name will be used for generating dynamic meta titles, and it will appear in all the emails sent by and through the site.'),
                'attributes'  => array(
                    'class' => 'form-control input-large',
                ),
                'required'    => true,
                'validators'  => array(
                    'NoHtml'
                ),
            ),
            array(
                'form_id'     => 'install',
                'id'          => 'admin_email',
                'element'     => 'text',
                'label'       => $this->_('Admin Email Address'),
                'description' => $this->_('This address will be used in the "From" field by all system emails. It will be used as the email address for the main admin account.'),
                'required'    => true,
                'attributes'  => array(
                    'class' => 'form-control input-large',
                ),
                'validators'  => array(
                    'Email',
                ),
            ),
            array(
                'form_id'      => 'install',
                'id'           => 'populate_tables',
                'element'      => 'checkbox',
                'label'        => $this->_('Sample Data'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check the above checkbox to populate your advertising, users and listings tables with sample data.'),
            ),
            /**
             * ++++++++++++++
             * LICENSE ACTIVATION
             * ++++++++++++++
             */
            array(
                'form_id'     => 'licensing',
                'id'          => 'license_key',
                'subtitle'    => $this->_('Activation Key'),
                'element'     => 'textarea',
                'label'       => $this->_('Enter Key'),
                'description' => $this->_('Important: Each license is valid for a single installation.'),
                'required'    => true,
                'attributes'  => array(
                    'rows'  => 8,
                    'class' => 'form-control input-xlarge textarea-code',
                ),
                'validators'  => array(
                    'NoHtml',
                ),
            ),
            /**
             * ++++++++++++++
             * V7 UPGRADE
             * ++++++++++++++
             */
            array(
                'form_id'      => 'upgrade',
                'id'           => 'current_version',
                'element'      => 'select',
                'label'        => $this->_('Current Version'),
                'required'     => true,
                'attributes'   => array(
                    'class' => 'form-control input-medium',
                ),
                'multiOptions' => $this->_upgradeVersions,
                'description'  => $this->_('Select the current version of the PHP Pro Bid database installed on your server.')
            ),
            /**
             * ++++++++++++++
             * MODS - RUN SQL
             * ++++++++++++++
             */
            array(
                'form_id'      => 'mods',
                'id'           => 'file_name',
                'element'      => 'checkbox',
                'label'        => $this->_('SQL File(s)'),
                'required'     => true,
                'multiOptions' => $modsSQLFiles,
                'description'  => $this->_('Select the mods SQL files that you want to parse.')
            ),
            /**
             * ++++++++++++++
             * ADMIN LOGIN DETAILS - v6.x Importer, v7 Upgrade, License Activation
             * ++++++++++++++
             */
            array(
                'form_id'    => array('licensing', 'v6_importer', 'upgrade', 'mods'),
                'id'         => 'licensing_username',
                'subtitle'   => $this->_('Verify Admin Login Credentials'),
                'element'    => 'text',
                'label'      => $this->_('Admin Username'),
                'required'   => true,
                'attributes' => array(
                    'class' => 'form-control input-medium',
                ),
                'validators' => array(
                    'NoHtml',
                ),
            ),
            array(
                'form_id'     => array('licensing', 'v6_importer', 'upgrade', 'mods'),
                'id'          => 'licensing_password',
                'element'     => 'password',
                'label'       => $this->_('Admin Password'),
                'description' => $this->_('Please enter your admin login details in order to submit this form.'),
                'required'    => true,
                'attributes'  => array(
                    'class' => 'form-control input-medium',
                ),
                'validators'  => array(
                    'NoHtml',
                ),
            ),

        );


        return $array;
    }

    /**
     *
     * mods sql files need to start with "mod" or "custom" in order to be parsable
     *
     * @return array
     */
    protected function _getModsSQLFiles()
    {
        $result = array();

        $files = glob(APPLICATION_PATH . '/SQL/*.sql');

        foreach ($files as $file) {
            if (is_file($file)) {
                $fileName = basename($file);
                if (preg_match('#^(mod|custom)#', $fileName)) {
                    $result[$fileName] = $fileName;
                }
            }
        }

        return $result;
    }
}


