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
 * newsletter subscription box view helper class
 */

namespace App\View\Helper;

use Ppb\View\Helper\AbstractHelper;

class NewsletterSubscription extends AbstractHelper
{

    /**
     *
     * the view partial to be used
     *
     * @var string
     */
    protected $_partial = 'partials/newsletter-subscription.phtml';

    /**
     *
     * form id
     *
     * @var string
     */
    protected $_formId = 'form-newsletter-subscription';

    /**
     *
     * form post url
     *
     * @var array
     */
    protected $_postUrl = array('module' => 'app', 'controller' => 'async', 'action' => 'newsletter-subscription');

    /**
     *
     * get form id
     *
     * @return string
     */
    public function getFormId()
    {
        return $this->_formId;
    }

    /**
     *
     * set form id
     *
     * @param string $formId
     *
     * @return $this
     */
    public function setFormId($formId)
    {
        $this->_formId = $formId;

        return $this;
    }

    /**
     *
     * get post url
     *
     * @return array
     */
    public function getPostUrl()
    {
        return $this->_postUrl;
    }

    /**
     *
     * set post url
     *
     * @param array $postUrl
     *
     * @return $this
     */
    public function setPostUrl($postUrl)
    {
        $this->_postUrl = $postUrl;

        return $this;
    }

    /**
     *
     * social icons helper initialization class
     *
     * @param string $partial
     *
     * @return $this
     */
    public function newsletterSubscription($partial = null)
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

        if ($settings['newsletter_subscription_box']) {
            $view = $this->getView();

            $translate = $this->getTranslate();

            /** @var \Cube\View\Helper\Script $helper */
            $helper = $view->getHelper('script');

            $helper->addBodyCode("                       
                <script type=\"text/javascript\">
                    $(document).ready(function () {
                        // submit modal forms in the modal box, and replace the current html with the response html
                        $('#" . $this->getFormId() . "').find('[type=submit]').on('click', function (e) {
                            e.preventDefault();
                
                            var newsletterSubmitButton = $(this);
                            var newsletterSubmitValue = newsletterSubmitButton.text();
                
                            newsletterSubmitButton.attr('disabled', true).text('" . $translate->_('Please wait...') . "');
                
                            var newsletterSubscriptionForm = $(this).closest('form');
                
                            $.ajax({
                                type: newsletterSubscriptionForm.attr('method'),
                                url: newsletterSubscriptionForm.attr('action'),
                                data: newsletterSubscriptionForm.serialize(),
                
                                success: function (data) {
                                    newsletterSubmitButton.attr('disabled', false).text(newsletterSubmitValue);
                                    newsletterSubscriptionForm.find('.message').html(data.message);
                                    newsletterSubscriptionForm.find('[name=\"email\"]').val('');
                
                                    setTimeout(function() {
                                        newsletterSubscriptionForm.find('.message').html('');
                                    }, 5000);
                                }
                            });
                        });
                    });
                </script>");

            $view->setVariables(array(
                'postUrl' => $this->getPostUrl(),
                'formId'  => $this->getFormId(),
            ));

            return $view->process(
                $this->getPartial(), true);
        }

        return '';
    }

}

