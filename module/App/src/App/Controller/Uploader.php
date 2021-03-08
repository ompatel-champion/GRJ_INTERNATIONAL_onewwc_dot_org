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
 * thumbnail generator controller
 */

namespace App\Controller;

use Ppb\Controller\Action\AbstractAction,
    Cube\Controller\Front,
    Cube\View,
    Cube\Crypt,
    Ppb\Model\Uploader as UploaderEngine,
    Ppb\Form\Element\MultiUpload;

class Uploader extends AbstractAction
{

    /**
     *
     * view object
     *
     * @var \Cube\View
     */
    protected $_view;

    /**
     *
     * uploader class
     *
     * @var \Ppb\Model\Uploader
     */
    protected $_uploader;

    /**
     *
     * number of current uploads in the widget
     *
     * @var int
     */
    protected $_nbUploads;

    public function init()
    {
        $this->_uploader = new UploaderEngine();

        $this->_view = new View();

        $this->_nbUploads = $this->getRequest()->getParam('nbUploads');

    }

    public function Upload()
    {
        $output = array();

        $translate = $this->getTranslate();

        foreach ($_FILES as $files) {
            if (isset($files['name'])) {
                if (is_array($files['name'])) {
                    foreach ($files['name'] as $key => $fileName) {
                        $output[] = $this->_processFile($fileName, $files['tmp_name'][$key], $files['size'][$key], $files['error'][$key]);
                    }
                }
                else {
                    $output[] = $this->_processFile($files['name'], $files['tmp_name'], $files['size'], $files['error']);
                }
            }
            else {
                $output[] = $result = array(
                    'name'  => null,
                    'size'  => null,
                    'error' => $translate->_('There are no files to upload.')
                );
            }
        }

        $this->getResponse()->setHeader('Content-Type: text/plain');


        /**
         * we will return the file names and locations or false if a file was not saved, which can then be parsed by the jquery script
         */
        $this->_view->setContent(
            json_encode(array('files' => $output)));

        return $this->_view;
    }

    public function Success()
    {
        $name = $this->getRequest()->getParam('element');
        $value = $this->getRequest()->getParam('image');
        $multiple = (bool)$this->getRequest()->getParam('multiple');

        $element = new MultiUpload($name);
        $element->setValue($value)
            ->setMultiple($multiple);

        $this->_view->setContent(
            $element->renderThumb());

        return $this->_view;
    }

    public function Remove()
    {
        $options = Front::getInstance()->getOption('session');

        $crypt = new Crypt();
        $crypt->setKey($options['secret']);

        $fileName = $this->getRequest()->getParam('value');
        $uploadType = $this->getRequest()->getParam('element');
        $encryptionKey = str_replace(' ', '+', $_REQUEST['key']);

        $array = explode(
            MultiUpload::KEY_SEPARATOR, $crypt->decrypt($encryptionKey));
        $encryptedFileName = isset($array[0]) ? $array[0] : null;

        if ($encryptedFileName == $fileName) {
            $this->_uploader->remove($fileName, $uploadType);
            $this->_view->setContent(
                $this->getTranslate()->_("The file has been removed"));
        }

        return $this->_view;
    }

    private function _processFile($fileName, $tmpName, $fileSize, $fileError)
    {
        $uploadType = $this->getRequest()->getParam('uploadType');
        $fileSizeLimit = min(
            $this->getRequest()->getParam('fileSizeLimit'),
            \Ppb\Utility::getMaximumFileUploadSize()
        );
        $uploadLimit = $this->getRequest()->getParam('uploadLimit');
        $watermark = $this->getRequest()->getParam('watermark');
        $acceptFileTypes = urldecode($_REQUEST['acceptFileTypes']);

        $translate = $this->getTranslate();

        $fileSizeDisplay = number_format(($fileSize / 1048576), 2);
        $fileSizeLimitDisplay = number_format(($fileSizeLimit / 1048576), 2);

        $result = array(
            'name' => $fileName,
            'size' => null,
            'error' =>  $translate->_('An unknown file upload error has occurred')
        );

        switch ($fileError) {
            case UPLOAD_ERR_OK:
                if (!empty($acceptFileTypes) && !preg_match($acceptFileTypes, $fileName)) {
                    $result = array(
                        'name'  => $fileName,
                        'size'  => $fileSize,
                        'error' => sprintf(
                            $translate->_('Allowed extensions: %s'),
                            trim(str_replace('|', ', ', preg_replace('/[^a-zA-Z0-9\|\?]+/', '', substr($acceptFileTypes, 0, -1))), ','))
                    );
                }
                else if ($fileSize <= $fileSizeLimit && $this->_nbUploads < $uploadLimit) {
                    $error = null;

                    $name = $this->_uploader->upload($tmpName, $fileName, $uploadType, $watermark);
                    if ($name === false) {
                        $name = $fileName;
                        $error = $translate->_("Please try again or contact the administrator");
                    }
                    else {
                        $this->_nbUploads++;
                    }

                    $result = array(
                        'name'  => $name,
                        'size'  => $fileSize,
                        'error' => $error,
                    );
                }
                else if ($fileSize > $fileSizeLimit) {
                    $result = array(
                        'name'  => $fileName,
                        'size'  => $fileSize,
                        'error' => sprintf(
                            $translate->_('The file size is %s MB, and exceeds the maximum allowed limit of %s MB'),
                            $fileSizeDisplay, $fileSizeLimitDisplay)
                    );
                }
                else if ($this->_nbUploads >= $uploadLimit) {
                    $result = array(
                        'name'  => $fileName,
                        'size'  => $fileSize,
                        'error' => sprintf(
                            $translate->_('The maximum number of uploads allowed (%s) has been reached'),
                            $this->_nbUploads),
                    );
                }

                break;
            case UPLOAD_ERR_INI_SIZE:
                $result['error'] = sprintf(
                    $translate->_('The uploaded file exceeds the maximum allowed size of %s MB'),
                     $fileSizeLimitDisplay);
                break;
            case UPLOAD_ERR_FORM_SIZE:
                $result['error'] = $translate->_('The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form');
                break;
            case UPLOAD_ERR_PARTIAL:
                $result['error'] = $translate->_('The uploaded file was only partially uploaded');
                break;
            case UPLOAD_ERR_NO_FILE:
                $result['error'] = $translate->_('No file was uploaded');
                break;
            case UPLOAD_ERR_NO_TMP_DIR:
                $result['error'] = $translate->_('Missing a temporary folder');
                break;
            case UPLOAD_ERR_CANT_WRITE:
                $result['error'] = $translate->_('Failed to write file to disk');
                break;
            case UPLOAD_ERR_EXTENSION:
                $result['error'] = $translate->_('File upload stopped by extension');
                break;
        }

        return $result;
    }

}

