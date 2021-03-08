<?php

/**
 *
 * PHP Pro Bid
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2018 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     8.0 [rev.8.0.03]
 */

/**
 * social network links display view helper class
 * displays links for a certain listing if the listing is specified, or general site links otherwise
 */

namespace App\View\Helper;

use Ppb\View\Helper\AbstractHelper,
    Ppb\Db\Table\Row\Listing as ListingModel,
    Ppb\Db\Table\Row\User as UserModel;

class Social extends AbstractHelper
{

    /**
     *
     * sharing options
     *
     * @var array
     */
    protected $_sharingOptions = array(
        'Email'     => array(
            'link'   => '[EMAIL_FRIEND_URL]',
            'target' => '_self',
        ),
        'Facebook'  => array(
            'link' => 'http://www.facebook.com/sharer.php?u=[URL]',
        ),
        'Twitter'   => array(
            'link' => 'http://twitter.com/intent/tweet?text=[TEXT]&amp;url=[URL]',
        ),
        'LinkedIn'  => array(
            'link' => 'https://www.linkedin.com/shareArticle?mini=true&url=[URL]&title=[TEXT]&summary=[DESC]',
        ),
        'Pinterest' => array(
            'link' => 'http://pinterest.com/pin/create/button/?url=[URL]&amp;media=[IMG]&amp;description=[TEXT]',
        ),
    );

    /**
     *
     * get sharing options
     *
     * @return array
     */
    public function getSharingOptions()
    {
        return $this->_sharingOptions;
    }

    /**
     *
     * set social networks array
     *
     * @param array $sharingOptions
     *
     * @return $this
     */
    public function setSharingOptions(array $sharingOptions)
    {
        $this->_sharingOptions = $sharingOptions;

        return $this;
    }

    /**
     *
     * add a sharing option
     *
     * @param string $name
     * @param array  $option
     *
     * @return $this
     */
    public function addSharingOption($name, $option)
    {
        $this->_sharingOptions[$name] = $option;

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
    public function social($partial = null)
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
     * @param \Ppb\Db\Table\Row\Listing $listing
     *
     * @return string
     */
    public function listingShareLinks(ListingModel $listing)
    {
        $settings = $this->getSettings();

        if ($settings['enable_listings_sharing']) {
            $partial = $this->getPartial();
            if (empty($partial)) {
                $partial = 'partials/listing-share-links.phtml';
            }

            $view = $this->getView();

            $sitePath = $settings['site_path'];
            $sharingOptions = array();

            $url = urlencode($sitePath . $this->getView()->url($listing->link(), null, false, null, false));
            $text = urlencode($listing->getData('name'));
            $img = urlencode($listing->getMainImage(true));
            $desc = urlencode($listing->shortDescription(150));
            $emailFriendUrl = $view->url(array('module' => 'listings', 'controller' => 'listing', 'action' => 'email-friend', 'id' => $listing->getData('id')));
            $rssUrl = null;

            foreach ($this->_sharingOptions as $name => $option) {
                $href = str_replace(
                    array('[URL]', '[TEXT]', '[IMG]', '[DESC]', '[RSS_URL]', '[EMAIL_FRIEND_URL]'),
                    array($url, $text, $img, $desc, $rssUrl, $emailFriendUrl),
                    $option['link']);

                $target = (isset($option['target'])) ? $option['target'] : '_blank';

                if ($name == 'Email' && !$settings['enable_email_friend']) {
                }
                else {
                    $sharingOptions[$name] = array(
                        'name'   => $name,
                        'href'   => $href,
                        'target' => $target,
                    );
                }
            }

            $view->set('sharingOptions', $sharingOptions);

            return $view->process(
                $partial, true);
        }

        return '';
    }

    /**
     *
     * render partial
     *
     * @param \Ppb\Db\Table\Row\User $user
     *
     * @return string
     */
    public function socialMediaLinks(UserModel $user = null)
    {
        $settings = $this->getSettings();

        $enabled = ($user instanceof UserModel) ? $user->displaySocialMediaLinks() : $settings['enable_social_media_widget'];

        if ($enabled) {
            $partial = $this->getPartial();
            if (empty($partial)) {
                $partial = 'partials/social-media-links.phtml';
            }

            $view = $this->getView();

            if ($user instanceof UserModel) {
                $socialMediaLinks = array(
                    'Facebook'  => $user->getGlobalSettings('social_media_link_facebook'),
                    'Twitter'   => $user->getGlobalSettings('social_media_link_twitter'),
                    'LinkedIn'  => $user->getGlobalSettings('social_media_link_linkedin'),
                    'Instagram' => $user->getGlobalSettings('social_media_link_instagram'),
                );
            }
            else {
                $socialMediaLinks = array(
                    'Facebook'  => (!empty($settings['social_media_link_facebook'])) ? $settings['social_media_link_facebook'] : null,
                    'Twitter'   => (!empty($settings['social_media_link_twitter'])) ? $settings['social_media_link_twitter'] : null,
                    'LinkedIn'  => (!empty($settings['social_media_link_linkedin'])) ? $settings['social_media_link_linkedin'] : null,
                    'Instagram' => (!empty($settings['social_media_link_instagram'])) ? $settings['social_media_link_instagram'] : null,
                    'RSS'       => (!empty($settings['enable_rss'])) ? $this->getView()->url(array('module' => 'app', 'controller' => 'rss', 'action' => 'index')) : null,
                );
            }

            $view->set('socialMediaLinks', $socialMediaLinks);

            return $view->process(
                $partial, true);
        }

        return '';
    }

}

