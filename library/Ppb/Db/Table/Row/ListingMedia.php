<?php

/**
 *
 * PHP Pro Bid
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2019 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     8.2 [rev.8.2.01]
 */

/**
 * listings media table row object model
 */

namespace Ppb\Db\Table\Row;

use Cube\Controller\Front,
    Cube\Validate\Url as UrlValidator,
    Ppb\Model\Uploader,
    Ppb\Service;

class ListingMedia extends AbstractRow
{

    /**
     *
     * check if object is of type image
     *
     * @return bool
     */
    public function isImage()
    {
        return ($this->getData('type') == Service\ListingsMedia::TYPE_IMAGE) ? true : false;
    }

    /**
     *
     * check if object is of type video
     *
     * @return bool
     */
    public function isVideo()
    {
        return ($this->getData('type') == Service\ListingsMedia::TYPE_VIDEO) ? true : false;
    }

    /**
     *
     * check if object is of type download
     *
     * @return bool
     */
    public function isDownload()
    {
        return ($this->getData('type') == Service\ListingsMedia::TYPE_DOWNLOAD) ? true : false;
    }

    /**
     *
     * check if object is of type csv
     *
     * @return bool
     */
    public function isCsv()
    {
        return ($this->getData('type') == Service\ListingsMedia::TYPE_CSV) ? true : false;
    }

    /**
     *
     * get image absolute path or return false if the object is not of type image
     *
     * @return bool|string
     */
    public function imageAbsolutePath()
    {
        if ($this->isImage()) {
            $settings = $this->getSettings();
            $urlValidator = new UrlValidator();
            $image = $this->getData('value');
            $urlValidator->setValue($image);

            if ($urlValidator->isValid()) {
                return $image;
            }

            $sitePath = $settings['site_path'] . Front::getInstance()->getRequest()->getBaseUrl();

            if (preg_match('#^uplimg/(.*)+$#i', $image)) {
                // we have a v6 image - add base url
                return $sitePath . \Ppb\Utility::URI_DELIMITER . $image;
            }
            else {
                // we have a v7 image - add base url and uploads folder
                return $sitePath . \Ppb\Utility::URI_DELIMITER
                    . \Ppb\Utility::getFolder('uploads') . \Ppb\Utility::URI_DELIMITER
                    . $image;
            }
        }

        return false;
    }

    /**
     *
     * delete row from listings media table, and also delete the corresponding uploaded file
     *
     * @return int
     */
    public function delete()
    {
        $fileName = $this->getData('value');
        $uploadType = $this->getData('type');

        $result = parent::delete();

        $uploader = new Uploader();
        $uploader->remove($fileName, $uploadType);

        return $result;
    }
}

