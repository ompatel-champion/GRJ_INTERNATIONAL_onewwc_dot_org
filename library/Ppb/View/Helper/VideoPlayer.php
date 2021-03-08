<?php

/**
 *
 * PHP Pro Bid
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2017 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.10 [rev.7.10.01]
 */
/**
 * video player
 *
 * plays either:
 * - local files, in which case it will render the video tag
 * - remote files (embedded code) in which case it will only return the code to be displayed.
 *
 * Format   Support
 * mp4      Please visit http://caniuse.com/#feat=mpeg4 for comprehensive information
 * webm     Please visit http://caniuse.com/#feat=webm for comprehensive information
 * flv      All browsers that support Flash (version 10 or later)
 * YouTube  All browsers since it uses iframe tag
 */

namespace Ppb\View\Helper;

use Cube\View\Helper\AbstractHelper,
    Cube\Controller\Front,
    Ppb\Db\Table\Row\ListingMedia,
    Ppb\Service\ListingsMedia as ListingsMediaService;

class VideoPlayer extends AbstractHelper
{

    /**
     *
     * render the video player that will play the video
     *
     * @param string|\Ppb\Db\Table\Row\ListingMedia $media
     *
     * @return string
     */
    public function videoPlayer($media)
    {
        $video = $player = $videoId = null;
        if ($media instanceof ListingMedia) {
            if ($media->getData('type') == ListingsMediaService::TYPE_VIDEO) {
                $video = $media->getData('value');
                $videoId = 'video_' . $media->getData('id');
            }
        }
        else if (is_array($media)) {
            if ($media['type'] == ListingsMediaService::TYPE_VIDEO) {
                $video = $media['value'];
                $videoId = $media['id'];
            }
        }
        else if (is_string($media)) {
            $video = $media;
            $videoId = 'video_' . md5(uniqid(time()));
        }

        $video = $this->_decode($video);

        $video = $this->getView()->renderHtml($video);

        if (strcmp(strip_tags($video), $video) === 0 && !preg_match('#^http(s)?://(.*)+$#i', $video)) {
            $baseUrl = Front::getInstance()->getRequest()->getBaseUrl();

            /** @var \Cube\View\Helper\Script $scriptHelper */
            $scriptHelper = $this->getView()->getHelper('script');
            $scriptHelper->addHeaderCode('<link href="' . $baseUrl . '/js/videojs/video-js.min.css" media="screen" rel="stylesheet" type="text/css">')
                ->addBodyCode('<script type="text/javascript" src="' . $baseUrl . '/js/videojs/video.min.js"></script>');

            $baseUrl = Front::getInstance()->getRequest()->getBaseUrl();

            $video = $baseUrl . \Ppb\Utility::URI_DELIMITER
                . \Ppb\Utility::getFolder('uploads') . \Ppb\Utility::URI_DELIMITER
                . $video;

            $player = '
                <video id="' . $videoId . '"
                    class="video-js vjs-default-skin"
                    controls preload="auto" width="640" height="350">
                    <source src="' . $video . '" />
                </video>';
        }
        else {
            $player = $video;
        }

        return $player;
    }

    /**
     *
     * decode string if encoded
     *
     * @param $string
     *
     * @return bool|string
     */
    protected function _decode($string)
    {
        $decoded = base64_decode($string);

        return (base64_encode($decoded) === $string) ? $decoded : $string;
    }
}

