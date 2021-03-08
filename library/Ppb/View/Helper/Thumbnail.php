<?php

/**
 *
 * PHP Pro Bid
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2019 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     8.1 [rev.8.1.01]
 */

/**
 * thumbnail view helper class
 *
 * this view helper will generate thumbnails for images, and if the data input is of a different type, then it will
 * add placeholders
 *
 * 7.5: added image cropping options and the ability to create rectangular thumbnails, not only square ones
 * 7.5: smaller images will now be enlarged to the desired dimensions
 * 7.10: moved image manipulation functions from uploader model
 */

namespace Ppb\View\Helper;

use Cube\Controller\Front,
    Ppb\Service;

class Thumbnail extends AbstractHelper
{
    const DEFAULT_EXTENSION = 'png';

    /**
     * the path for the "no image" pic
     */
    const IMAGE_PLACEHOLDER = 'image.png';

    /**
     * video file placeholder
     */
    const VIDEO_PLACEHOLDER = 'video.png';

    /**
     * digital download file placeholder
     */
    const DOWNLOAD_PLACEHOLDER = 'download.png';

    /**
     * csv file placeholder
     */
    const CSV_PLACEHOLDER = 'csv.png';

    /**
     * translation files placeholder
     */
    const LANGUAGE_PLACEHOLDER = 'language.png';

    /**
     * the path for the "broken image" pic
     */
    const BROKEN_IMAGE = 'broken.gif';


    /**
     * minimum width in pixels of a thumbnail image
     */
    const MIN_WIDTH = 30;

    /**
     * maximum width in pixels of a thumbnail images
     */
    const MAX_WIDTH = 2560;

    /**
     *
     * this variable will hold the name of the file which will be displayed
     *
     * @var string
     */
    protected $_name;

    /**
     *
     * the extension of the file
     *
     * @var string
     */
    protected $_extension = self::DEFAULT_EXTENSION;

    /**
     *
     * destination thumbnail width
     *
     * @var integer
     */
    protected $_width = 80;

    /**
     *
     * destination thumbnail height
     * - if true then we have a square
     * - if null we shrink based on original aspect ratio
     * - if set we use a forced aspect ratio
     *
     * @var integer|bool|null
     */
    protected $_height = true;

    /**
     *
     * application base url
     *
     * @var string
     */
    protected $_baseUrl = null;

    /**
     *
     * crop images to aspect ratio
     * if false, will add horizontal/vertical white bars
     *
     * @var bool
     */
    protected $_crop = false;

    /**
     *
     * enable / disable lazy load plugin
     *
     * @var bool
     */
    protected $_lazyLoad = false;

    /**
     *
     * allowed extensions
     *
     * @var array
     */
    protected $_extensions = array(
        'gif', 'jpg', 'jpeg', 'png', 'img',
        'avi', 'mpg', 'mpeg', 'mov', 'mp4', 'flv', 'csv'
    );

    /**
     *
     * reserved params
     *
     * @var array
     */
    protected $_reservedParams = array(
        'type', 'zoom', 'crop', 'lazyLoad'
    );

    /**
     *
     * class constructor
     */
    public function __construct()
    {
        $settings = $this->getSettings();

        $this->setCrop($settings['crop_images'])
            ->setLazyLoad($settings['lazy_load_images']);
    }

    /**
     *
     * get file name
     *
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     *
     * set file name
     *
     * @param string $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $name = preg_replace("/[^a-zA-Z0-9_-]/", '', $name);
        $this->_name = (string)$name;

        return $this;
    }

    /**
     *
     * get file extension
     *
     * @return string
     */
    public function getExtension()
    {
        return $this->_extension;
    }

    /**
     *
     * set file extension
     *
     * @param string $extension
     *
     * @return $this
     */
    public function setExtension($extension)
    {
        if (!in_array($extension, $this->_extensions)) {
            $extension = self::DEFAULT_EXTENSION;
        }

        $this->_extension = (string)$extension;

        return $this;
    }

    /**
     *
     * get destination thumbnail width
     *
     * @return integer
     */
    public function getWidth()
    {
        return $this->_width;
    }

    /**
     *
     * set destination thumbnail width
     *
     * @param int $width
     *
     * @return $this
     */
    public function setWidth($width)
    {
        $width = intval($width);

        if ($width < self::MIN_WIDTH) {
            $width = self::MIN_WIDTH;
        }

        if ($width > self::MAX_WIDTH && self::MAX_WIDTH > 0) {
            $width = self::MAX_WIDTH;
        }

        $this->_width = $width;

        return $this;
    }

    /**
     *
     * get destination thumbnail height
     *
     * @return bool
     */
    public function getHeight()
    {
        return $this->_height;
    }

    /**
     *
     * set destination thumbnail height
     *
     * @param integer|boolean $height
     *
     * @return $this
     */
    public function setHeight($height = true)
    {
        $this->_height = $height;

        return $this;
    }


    /**
     *
     * set square option (legacy method)
     *
     * @param bool $square
     *
     * @return $this
     */
    public function setSquare($square = true)
    {
        $this->setHeight((bool)$square);

        return $this;
    }

    /**
     *
     * get crop option
     *
     * @return boolean
     */
    public function getCrop()
    {
        return $this->_crop;
    }

    /**
     *
     * set crop option
     *
     * @param boolean $crop
     *
     * @return $this
     */
    public function setCrop($crop = true)
    {
        $this->_crop = (bool)$crop;

        return $this;
    }

    /**
     *
     * check lazy load plugin flag
     *
     * @return bool
     */
    public function isLazyLoad()
    {
        return $this->_lazyLoad;
    }

    /**
     *
     * set lazy load plugin flag
     *
     * @param bool $lazyLoad
     *
     * @return $this
     */
    public function setLazyLoad($lazyLoad)
    {
        $this->_lazyLoad = $lazyLoad;

        return $this;
    }


    /**
     *
     * get base url
     *
     * @return string
     */
    public function getBaseUrl()
    {
        if ($this->_baseUrl === null) {
            $this->setBaseUrl(
                Front::getInstance()->getRequest()->getBaseUrl());
        }

        return $this->_baseUrl;
    }

    /**
     *
     * set base url
     *
     * @param string $baseUrl
     *
     * @return $this
     */
    public function setBaseUrl($baseUrl)
    {
        $this->_baseUrl = $baseUrl;

        return $this;
    }

    /**
     *
     * generate cached thumbnail and return image link
     * (has backwards compatibility for v6's uplimg folder)
     *
     * @param string        $image
     * @param int|null      $width
     * @param bool|int|null $height
     * @param array         $params
     *
     * @return string
     */
    public function generateLink($image, $width = null, $height = null, $params = array())
    {
        $this->setWidth($width)
            ->setHeight($height);

        $type = (isset($params['type'])) ? $params['type'] : Service\ListingsMedia::TYPE_IMAGE;
        $crop = (isset($params['crop'])) ? $params['crop'] : null;
        $sitePath = (isset($params['sitePath'])) ? $params['sitePath'] : null;

        $localImagePath = null;

        switch ($type) {
            case Service\ListingsMedia::TYPE_VIDEO:
                $image = \Ppb\Utility::getPath('img') . \Ppb\Utility::URI_DELIMITER . self::VIDEO_PLACEHOLDER;
                break;
            case Service\ListingsMedia::TYPE_DOWNLOAD:
                $image = \Ppb\Utility::getPath('img') . \Ppb\Utility::URI_DELIMITER . self::DOWNLOAD_PLACEHOLDER;
                break;
            case Service\ListingsMedia::TYPE_CSV:
                $image = \Ppb\Utility::getPath('img') . \Ppb\Utility::URI_DELIMITER . self::CSV_PLACEHOLDER;
                break;
            case Service\Translations::TRANSLATION_PO:
            case Service\Translations::TRANSLATION_MO:
                $image = \Ppb\Utility::getPath('img') . \Ppb\Utility::URI_DELIMITER . self::LANGUAGE_PLACEHOLDER;
                break;
            case Service\Settings::TYPE_FAVICON:
                $localImagePath = \Ppb\Utility::getFolder('uploads') . \Ppb\Utility::URI_DELIMITER . $image;
                break;

            default:
                if ($image == null) {
                    $image = \Ppb\Utility::getPath('img') . \Ppb\Utility::URI_DELIMITER . self::IMAGE_PLACEHOLDER;
                }
                else {
                    if (!preg_match('#^http(s)?://(.*)+$#i', $image) && !preg_match('#^uplimg/(.*)+$#i', $image)) {
                        $image = \Ppb\Utility::getFolder('uploads') . \Ppb\Utility::URI_DELIMITER . $image;
                    }
                }
                break;
        }

        return (($sitePath) ? $sitePath : $this->getBaseUrl()) . \Ppb\Utility::URI_DELIMITER
            . (($localImagePath === null) ?
                (\Ppb\Utility::getFolder('cache') . \Ppb\Utility::URI_DELIMITER . $this->_generateThumb($image, $crop)) : $localImagePath);
    }

    /**
     *
     * generates a path to a media file
     * if we have a remote image or a v6 image, return the path unmodified
     *
     * @param string $image
     *
     * @return string
     */
    public function generateImagePath($image)
    {
        if (preg_match('#^uplimg/(.*)+$#i', $image)) {
            // we have a v6 image - add base url
            return $this->getBaseUrl() . '/' . $image;
        }
        else if (!preg_match('#^http(s)?://(.*)+$#i', $image)) {
            // we have a v7 image - add base url and uploads folder
            return $this->getBaseUrl() . \Ppb\Utility::URI_DELIMITER
                . \Ppb\Utility::getFolder('uploads') . \Ppb\Utility::URI_DELIMITER
                . $image;
        }

        return $image;
    }

    /**
     *
     * check whether the uploaded file is an image
     *
     * @param string $fileName
     *
     * @return bool
     */
    public function isImage($fileName)
    {
        $fileInfo = @getimagesize($fileName);

        if ($fileInfo === false || $fileInfo[2] > 3) {
            return false;
        }

        return true;
    }

    /**
     *
     * output image using corresponding function based on mime
     *
     * @param mixed  $output
     * @param string $fileName
     * @param string $mime
     *
     * @return $this
     */
    public function imageOutputFunction($output, $fileName, $mime = null)
    {
        if ($mime === null) {
            $imgInfo = @getimagesize($fileName);
            $mime = $imgInfo['mime'];
        }

        switch ($mime) {
            case 'image/gif':
                $imageOutputFunction = 'imagegif';
                break;
            case 'image/png':
                $imageOutputFunction = 'imagepng';
                break;
            default:
                $imageOutputFunction = 'imagejpeg';
                break;
        }

        touch($fileName);
        $imageOutputFunction($output, $fileName);
        imagedestroy($output);

        return $this;
    }

    /**
     *
     * image create function based on source image type
     *
     * @param string $fileName
     * @param string $mime
     *
     * @return mixed
     */
    public function imageCreateFunction($fileName, $mime = null)
    {
        if ($mime === null) {
            $imgInfo = @getimagesize($fileName);
            $mime = $imgInfo['mime'];
        }

        switch ($mime) {
            case 'image/gif':
                $imageCreateFunction = 'ImageCreateFromGIF';
                break;
            case 'image/png':
                $imageCreateFunction = 'ImageCreateFromPNG';
                break;
            default:
                $imageCreateFunction = 'ImageCreateFromJPEG';
                break;
        }

        return $imageCreateFunction($fileName);
    }

    /**
     *
     * create resized image
     *
     * @param string    $srcImage
     * @param bool|null $crop
     *
     * @return resource
     */
    public function createResizedImage($srcImage, $crop = null)
    {
        $fileName = null;

        $imgInfo = @getimagesize($srcImage);
        list($srcWidth, $srcHeight, $imgType) = $imgInfo;

        if ($imgInfo === false || $imgType > 3) {
            $srcImage = \Ppb\Utility::getPath('img') . \Ppb\Utility::URI_DELIMITER . self::BROKEN_IMAGE;
            $imgInfo = @getimagesize($srcImage);
            list($srcWidth, $srcHeight, $imgType) = $imgInfo;
        }

        if ($crop === null) {
            $crop = $this->getCrop();
        }

        $dstWidth = $tmpWidth = $this->getWidth();
        $dstHeight = $this->getHeight();

        $tmpHeight = round(($dstWidth * $srcHeight) / $srcWidth);

        if ($dstHeight === true) {
            $dstHeight = $dstWidth;
        }
        else if (!is_numeric($dstHeight)) {
            $dstHeight = $tmpHeight;
        }

        $image = $this->imageCreateFunction($srcImage, $imgInfo['mime']);

        if (!$image) {
            $output = imagecreate($dstWidth, $dstHeight); /* Create a blank image */

            $white = imagecolorallocate($output, 255, 255, 255);
            $black = imagecolorallocate($output, 0, 0, 0);

            imagefilledrectangle($output, 0, 0, 150, 30, $white);

            imagestring($output, 1, 5, 5, 'Error loading ' . $srcImage, $black); /* Output an error message */
        }
        else {
            $srcAspect = $srcWidth / $srcHeight;
            $dstAspect = $dstWidth / $dstHeight;

            /**
             * cropping is disabled if source image has at least one side smaller than the destination image
             */
            if ($srcHeight < $dstHeight || $srcWidth < $dstWidth) {
                $crop = false;
            }

            /**
             * if crop, then cut to fit, otherwise add white background to fit
             */
            if (($crop && ($srcAspect > $dstAspect)) || (!$crop && ($srcAspect < $dstAspect))) {
                $tmpHeight = $dstHeight;
                $tmpWidth = round($dstHeight * $srcAspect);
            }
            else {
                $tmpWidth = $dstWidth;
                $tmpHeight = round($dstWidth / $srcAspect);
            }

            /**
             * first we resize original image to desired dimensions
             */
            $resized = imagecreatetruecolor($tmpWidth, $tmpHeight);
            $backgroundColor = imagecolorallocate($resized, 255, 255, 255);
            imagefill($resized, 0, 0, $backgroundColor);

            imagecopyresampled(
                $resized,
                $image,
                0, 0,
                0, 0,
                $tmpWidth, $tmpHeight,
                $srcWidth, $srcHeight
            );

            $output = imagecreatetruecolor($dstWidth, $dstHeight);

            /**
             * now we either crop or add white background and output resulting image
             */
            if ($crop) {
                $srcX = ($tmpWidth - $dstWidth) / 2;
                $srcY = ($tmpHeight - $dstHeight) / 2;

                imagecopy(
                    $output,
                    $resized,
                    0, 0,
                    $srcX, $srcY,
                    $dstWidth, $dstHeight
                );
            }
            else {
                $backgroundColor = imagecolorallocate($output, 255, 255, 255);
                imagefill($output, 0, 0, $backgroundColor);

                $dstX = ($dstWidth - $tmpWidth) / 2;
                $dstY = ($dstHeight - $tmpHeight) / 2;

                imagecopy(
                    $output,
                    $resized,
                    $dstX, $dstY,
                    0, 0,
                    $tmpWidth, $tmpHeight
                );
            }
        }

        return $output;
    }


    /**
     *
     * rotate uploaded image automatically based on exif orientation
     *
     * @param string $fileName
     *
     * @return $this
     */
    public function imageSmartRotate($fileName)
    {
        $output = null;

        if (function_exists('exif_read_data')) {
            $exif = @exif_read_data($fileName);
            if (!empty($exif['Orientation'])) {
                $image = $this->imageCreateFunction($fileName);

                switch ($exif['Orientation']) {
                    case 3:
                        $output = imagerotate($image, 180, 255);
                        break;
                    case 6:
                        $output = imagerotate($image, -90, 255);
                        break;
                    case 8:
                        $output = imagerotate($image, 90, 255);
                        break;
                }

                if ($output !== null) {
                    $this->imageOutputFunction($output, $fileName);
                }
            }
        }

        return $this;
    }

    /**
     *
     * add watermark to the image
     *
     * @param string $fileName
     * @param string $watermarkText
     *
     * @return $this
     */
    public function addWatermark($fileName, $watermarkText)
    {
        if ($this->isImage($fileName)) {
            $output = $this->imageCreateFunction($fileName);

            // Get identifier for white
            $white = imagecolorallocate($output, 255, 255, 255);
            $black = imagecolorallocate($output, 0, 0, 0);

            // Add text to image
            $font = APPLICATION_PATH . '/fonts/arial.ttf';

            $this->_imagettfstroketext($output, 20, 0, 15, imagesy($output) - 20, $white, $black, $font, $watermarkText, 2);

            // Save the image to file and free memory
            $this->imageOutputFunction($output, $fileName);
        }

        return $this;

    }

    /**
     *
     * the helper will display a thumbnail image,
     * or in case media is uploaded, it will add a default thumbnail
     *
     * @param string $image
     * @param int    $width
     * @param string $square (Y|N|null)
     * @param array  $params (reserved params: zoom, crop)
     *
     * @return mixed
     */
    public function thumbnail($image = null, $width = null, $square = null, $params = array())
    {
        $dataSrc = null;

        if ($image === null && $width === null && $square === null) {
            return $this;
        }

        $zoom = (isset($params['zoom'])) ? $params['zoom'] : false;

        if ($zoom === true) {
            $dataSrc = 'data-src="' . $this->generateImagePath($image) . '" ';

            // add js
            /** @var \Cube\View\Helper\Script $helper */
            $helper = $this->getView()->getHelper('script');
            $helper->addBodyCode('<script type="text/javascript" src="' . $this->getBaseUrl() . '/js/jquery.zoom.min.js"></script>')
                ->addBodyCode("<script type=\"text/javascript\">
                    $(document).ready(function() {
                        $('a.jq-zoom').zoom();
                    });
                </script>");
        }

        $lazyLoad = (isset($params['lazyLoad']) && $this->isLazyLoad()) ? $params['lazyLoad'] : false;

        if (!array_key_exists('class', $params)) {
            $params['class'] = 'img-thumbnail img-responsive';
        }

        $src = $this->generateLink($image, $width, $square, $params);
        if ($lazyLoad === true) {
            // add js
            /** @var \Cube\View\Helper\Script $helper */
            $helper = $this->getView()->getHelper('script');
            $helper->addBodyCode('<script type="text/javascript" src="' . $this->getBaseUrl() . '/js/blazy.min.js"></script>')
                ->addBodyCode('<script type="text/javascript">
                    $(document).ready(function() {
                        var bLazy = new Blazy();

                        $(\'a[data-toggle="tab"]\').on(\'shown.bs.tab\', function (e) {
                            var bLazy = new Blazy();
                        });
                        
                        $(\'.carousel\').on(\'slid.bs.carousel\', function () {
                            var bLazy = new Blazy();                       
                        });
                    });
                </script>');

            $params['class'] .= ((!empty($params['class'])) ? ' ' : '') . 'b-lazy';

            $dataSrc = 'data-src="' . $src . '" ';
            $src = "data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==";
        }

        foreach ($params as $key => $value) {
            if (!in_array($key, $this->_reservedParams)) {
                $dataSrc .= $key . '="' . str_replace('"', '', $value) . '" ';
            }
        }

        return '<img src="' . $src . '" '
            . $dataSrc
            . $this->_endTag;

    }


    /**
     *
     * generate cache file name
     *
     * @param string    $name
     * @param bool|null $crop
     *
     * @return string
     */
    protected function _generateCacheName($name, $crop = null)
    {
        $extension = $this->getExtension();
        $height = $this->getHeight();
        $width = $this->getWidth();

        if ($height === true) {
            $height = $width;
        }

        if ($crop === null) {
            $crop = $this->getCrop();
        }

        return $name . '-'
            . $width
            . (($height !== null) ? 'x' . $height : '')
            . (($crop === true) ? '-' . 'crop' : '')
            . '.'
            . $extension;
    }

    /**
     *
     * generates a thumbnail of a given image, using the width and height parameters
     *
     * @param string    $image
     * @param bool|null $crop
     *
     * @return string
     */
    protected function _generateThumb($image, $crop = null)
    {
        $fileName = null;

        $pathInfo = pathinfo($image);

        $baseName = (isset($pathInfo['filename'])) ? $pathInfo['filename'] : null;
        $extension = (isset($pathInfo['extension'])) ? $pathInfo['extension'] : null;

        if (preg_match('#^http(s)?://(.*)+$#i', $image)) {
            $baseName = preg_replace('/[^\da-z]/i', '', $image);
        }

        $this->setName($baseName)
            ->setExtension($extension);

        $cacheName = $this->_generateCacheName($baseName, $crop);

        $cacheFilePath = \Ppb\Utility::getPath('cache') . DIRECTORY_SEPARATOR . $cacheName;

        if (file_exists($cacheFilePath)) {
            return $cacheName;
        }
        else {
            $output = $this->createResizedImage($image, $crop);

            $this->imageOutputFunction($output, $cacheFilePath);

            $fileName = $cacheName;

            return $fileName;
        }
    }

    /**
     *
     * add border to text
     *
     * @param $image
     * @param $size
     * @param $angle
     * @param $x
     * @param $y
     * @param $textcolor
     * @param $strokecolor
     * @param $fontfile
     * @param $text
     * @param $px
     *
     * @return array|false
     */
    protected function _imagettfstroketext(&$image, $size, $angle, $x, $y, &$textcolor, &$strokecolor, $fontfile, $text, $px)
    {
        for ($c1 = ($x - abs($px)); $c1 <= ($x + abs($px)); $c1++)
            for ($c2 = ($y - abs($px)); $c2 <= ($y + abs($px)); $c2++)
                $bg = imagettftext($image, $size, $angle, $c1, $c2, $strokecolor, $fontfile, $text);

        return imagettftext($image, $size, $angle, $x, $y, $textcolor, $fontfile, $text);
    }

}

