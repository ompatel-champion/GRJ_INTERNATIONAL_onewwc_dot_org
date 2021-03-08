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
 * upload translation files form
 */

namespace Admin\Form;

use Ppb\Form\AbstractBaseForm,
    Ppb\Form\Element\MultiUpload;

class UploadTranslation extends AbstractBaseForm
{

    const BTN_SUBMIT = 'submit';

    /**
     *
     * submit buttons values
     *
     * @var array
     */
    protected $_buttons = array(
        self::BTN_SUBMIT => 'Upload',
    );

    /**
     *
     * class constructor
     *
     * @param string $action the form's action
     * @param string $translationLocale
     */
    public function __construct($action = null)
    {
        parent::__construct($action);

        $this->setMethod(self::METHOD_POST);

        $translate = $this->getTranslate();

        ## LOCALE
        $locale = $this->createElement('\\Ppb\\Form\\Element\\DescriptionHidden', 'locale');
        $locale->setLabel('Locale');
        $this->addElement($locale);
        ## /LOCALE


        ## PO FILE
        $poFile = new MultiUpload('po');
        $poFile->setLabel('.po File')
            ->setDescription('Select translation .po file.')
            ->setRequired()
            ->setCustomData(array(
                'buttonText'      => $translate->_('Select File'),
                'acceptFileTypes' => '/(\.|\/)(po)$/i',
                'formData'        => array(
                    'uploadType'    => 'csv',
                    'fileSizeLimit' => (50 * 1024 * 1024), // 50 MB.
                    'uploadLimit'   => 1,
                ),
            ));
        $this->addElement($poFile);
        ## /PO FILE


        ## MO FILE
        $moFile = new MultiUpload('mo');
        $moFile->setLabel('.mo File')
            ->setDescription('Select translation .mo file.')
            ->setRequired()
            ->setCustomData(array(
                'buttonText'      => $translate->_('Select File'),
                'acceptFileTypes' => '/(\.|\/)(mo)$/i',
                'formData'        => array(
                    'uploadType'    => 'csv',
                    'fileSizeLimit' => (50 * 1024 * 1024), // 50 MB.
                    'uploadLimit'   => 1,
                ),
            ));
        $this->addElement($moFile);
        ## /MO FILE


        $this->addSubmitElement($this->_buttons[self::BTN_SUBMIT], self::BTN_SUBMIT);

        $this->setPartial('forms/generic-horizontal.phtml');
    }

}