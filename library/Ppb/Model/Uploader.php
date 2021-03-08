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
 * A class used to upload and process uploaded files
 *
 * If an uploaded file is an image, the image will be resized based on settings from the config file.
 * If a file is not an image, it is only uploaded.
 *
 * All files will be renamed to avoid file name conflicts.
 *
 */

namespace Ppb\Model;

use Cube\Controller\Front,
    Cube\Db\Expr,
    Ppb\Service,
    Ppb\View\Helper\Thumbnail as ThumbnailHelper;

class Uploader
{

    /**
     * prohibited extension replacement
     */
    const PROHIBITED_EXTENSION_REPLACEMENT = 'invalid';

    /**
     *
     * prohibited extensions
     *
     * @var array
     */
    protected $_prohibitedExtensions = array(
        'php', 'exe', 'htm', 'js', 'pl', 'cgi', 'wml', 'perl'
    );

    /**
     *
     * the image upload process
     * if we have an image, we will resize it to a default maximum size
     *
     * @param string      $tempFile
     * @param string      $rawFileName
     * @param string      $uploadType
     * @param string|null $watermarkText watermark text
     *
     * @return string|null     return the file name or null if the upload was unsuccessful
     */
    public function upload($tempFile, $rawFileName, $uploadType = null, $watermarkText = null)
    {
        $fileName = $this->_generateFileName($rawFileName, $uploadType);
        $targetFile = $this->_generateTargetPath($fileName, $uploadType);

        $result = move_uploaded_file($tempFile, $targetFile);

        if ($result) {
            $image = new ThumbnailHelper();

            if ($image->isImage($targetFile)) {
                $image->setWidth(ThumbnailHelper::MAX_WIDTH)
                    ->setHeight(false);

                $image->imageSmartRotate($targetFile);

                list($imgWidth, $imgHeight, $imgType) = @getimagesize($targetFile);

                if ($imgWidth > ThumbnailHelper::MAX_WIDTH) {
                    $output = $image->createResizedImage($targetFile, false);

                    $image->imageOutputFunction($output, $targetFile);
                }

                if (!empty($watermarkText)) {
                    $image->addWatermark($targetFile, $watermarkText);
                }
            }

            return $fileName;
        }

        return false;
    }

    /**
     *
     * remove a local file
     * only delete if the file is not used anymore.
     *
     * @param string $fileName
     * @param string $uploadType
     *
     * @return $this
     */
    public function remove($fileName, $uploadType)
    {
        $listingsMediaService = new Service\ListingsMedia();

        $uploadType = preg_replace('#[^a-z]+#i', '', $uploadType);

        $select = $listingsMediaService->getTable()
            ->select(array('nb_rows' => new Expr('count(*)')))
            ->where('value = ?', $fileName)
            ->where('type = ?', $uploadType);

        $nbUploads = $listingsMediaService->getTable()->getAdapter()->fetchOne($select);

        $targetPath = $this->_generateTargetPath($fileName, $uploadType);

        if ($uploadType == 'image') {
            $this->_removeCacheFiles($fileName, $targetPath);
        }

        // remove file
        if (!$nbUploads) {
            @unlink($targetPath);
        }

        return $this;
    }

    /**
     *
     * remove cache files corresponding to the target image
     *
     * @param string $fileName
     * @param string $targetPath
     *
     * @return $this
     */
    protected function _removeCacheFiles($fileName, $targetPath = null)
    {
        if ($targetPath === null) {
            $targetPath = $this->_generateTargetPath($fileName, 'image');
        }

        // remove cache files for images
        $directory = APPLICATION_PATH . DIRECTORY_SEPARATOR . \Ppb\Utility::getFolder('cache');
        $handler = opendir($directory);

        $pathInfo = pathinfo($targetPath);
        $baseName = (isset($pathInfo['filename'])) ? $pathInfo['filename'] : null;
        if ($baseName) {
            while ($file = readdir($handler)) {
                if ($file != "." && $file != "..") {
                    if (strpos($file, $baseName) === 0) {
                        @unlink($directory . DIRECTORY_SEPARATOR . $file);
                    }
                }
            }
        }

        return $this;
    }

    /**
     *
     * get the target path of an uploaded file
     *
     * @param string $fileName
     * @param string $uploadType
     *
     * @return string
     */
    protected function _generateTargetPath($fileName, $uploadType = null)
    {
        // for now we only have images and videos, that go in the "uploads" folder
        $uploadType = preg_replace('#[^a-z]+#i', '', $uploadType);

        switch ($uploadType) {
            case 'download':
                $settings = Front::getInstance()->getBootstrap()->getResource('settings');
                $targetPath = APPLICATION_PATH . DIRECTORY_SEPARATOR . $settings['digital_downloads_folder'] . DIRECTORY_SEPARATOR;
                break;
            default:
                $targetPath = \Ppb\Utility::getPath('uploads') . DIRECTORY_SEPARATOR;
                break;
        }

        return str_replace('//', '/', $targetPath) . $fileName;
    }

    /**
     *
     * set a unique file name for the uploaded file, so that no files are overwritten
     *
     * @param string $rawFileName
     * @param string $uploadType
     *
     * @return string
     */
    protected function _generateFileName($rawFileName, $uploadType = null)
    {
        $pathInfo = pathinfo($rawFileName);
        $tempName = preg_replace("/[^a-zA-Z0-9_-]/", '', $pathInfo['filename']);
        $fileExtension = $pathInfo['extension'];

        foreach ($this->_prohibitedExtensions as $prohibitedExtension) {
            if (stristr($fileExtension, $prohibitedExtension)) {
                $fileExtension = self::PROHIBITED_EXTENSION_REPLACEMENT;
            }
        }

        if (strpos($tempName, 'image') === 0 || $uploadType == 'download') {
            $tempName .= '-' . (int)(microtime(true) * 100);
        }

        $fileName = $tempName . '.' . $fileExtension;

        while (file_exists($this->_generateTargetPath($fileName))) {
            if (preg_match('#\((\d+)\)#', $fileName, $matches)) {
                $fileName = preg_replace('#\((\d+)\)#', '(' . ($matches[1] + 1) . ')', $fileName);
            }
            else {
                $fileName = $tempName . '-(1)' . '.' . $fileExtension;
            }

        }

        return $fileName;
    }

}

