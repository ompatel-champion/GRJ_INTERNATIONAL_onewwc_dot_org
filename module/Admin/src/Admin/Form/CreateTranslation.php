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
 * create translation form
 */

namespace Admin\Form;

use Ppb\Form\AbstractBaseForm,
    Ppb\Service\Translations as TranslationsService,
    Cube\Locale;

class CreateTranslation extends AbstractBaseForm
{

    const BTN_SUBMIT = 'submit';

    /**
     *
     * submit buttons values
     *
     * @var array
     */
    protected $_buttons = array(
        self::BTN_SUBMIT => 'Create',
    );

    /**
     *
     * class constructor
     *
     * @param string $action the form's action
     */
    public function __construct($action = null)
    {
        parent::__construct($action);

        $this->setMethod(self::METHOD_POST);

        $translationsService = new TranslationsService();

        ## LOCALE
        $locales = array_flip(Locale::getData());

        foreach ($locales as $key => $value) {
            if (stristr($key, '_') === false) {
                unset($locales[$key]);
            }
        }

        $activeTranslations = $translationsService->fetchTranslations(true);

        foreach ($activeTranslations as $activeTranslation) {
            $locale = $activeTranslation['locale'];
            unset($locales[$locale]);
        }

        $locale = $this->createElement('select', 'locale');
        $locale->setLabel('Locale')
            ->setDescription('Select the locale you wish to create the translation files for.')
            ->setMultiOptions($locales)
            ->setRequired()
            ->setAttributes(array(
                'class' => 'form-control input-medium',
            ));
        $this->addElement($locale);
        ## /LOCALE


        $this->addSubmitElement($this->_buttons[self::BTN_SUBMIT], self::BTN_SUBMIT);

        $this->setPartial('forms/generic-horizontal.phtml');
    }

}