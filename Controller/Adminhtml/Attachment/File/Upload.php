<?php

declare(strict_types = 1);

/**
 * File: Upload.php
 *
 * @author Bartosz Kubicki bartosz.kubicki@lizardmedia.pl>
 * @copyright Copyright (C) 2018 Lizard Media (http://lizardmedia.pl)
 */

namespace LizardMedia\ProductAttachment\Controller\Adminhtml\Attachment\File;

use \LizardMedia\ProductAttachment\Api\Data\AttachmentInterface;
use \LizardMedia\ProductAttachment\Model\Attachment;
use \Magento\Backend\App\Action\Context;
use \Magento\Downloadable\Controller\Adminhtml\Downloadable\File;
use \Magento\Downloadable\Helper\File as FileHelper;
use \Magento\MediaStorage\Helper\File\Storage\Database;
use \Magento\MediaStorage\Model\File\UploaderFactory;
use \Magento\Framework\Controller\ResultFactory;
use \Magento\Framework\Controller\ResultInterface;

/**
 * Class Upload
 * @package LizardMedia\ProductAttachment\Controller\Adminhtml\Attachment\File
 */
class Upload extends File
{
    /**
     * @var \LizardMedia\ProductAttachment\Api\Data\AttachmentInterface
     */
    private $attachment;


    /**
     * @var \Magento\Downloadable\Helper\File
     */
    private $fileHelper;


    /**
     * @var \Magento\MediaStorage\Helper\File\Storage\Database
     */
    private $storageDatabase;


    /**
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     */
    private $uploaderFactory;


    /**
     * @param \LizardMedia\ProductAttachment\Api\Data\AttachmentInterface $attachment
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Downloadable\Helper\File $fileHelper
     * @param \Magento\MediaStorage\Helper\File\Storage\Database $storageDatabase
     * @param \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory
     */
    public function __construct(
        AttachmentInterface $attachment,
        Context $context,
        FileHelper $fileHelper,
        Database $storageDatabase,
        UploaderFactory $uploaderFactory
    ) {
        parent::__construct($context);
        $this->attachment = $attachment;
        $this->fileHelper = $fileHelper;
        $this->uploaderFactory = $uploaderFactory;
        $this->storageDatabase = $storageDatabase;
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute() : ResultInterface
    {
        $type = $this->getRequest()->getParam(Attachment::ATTACHMENT_TYPE);
        $tmpPath = '';

        if ($type == 'attachments') {
            $tmpPath = $this->attachment->getBaseTmpPath();
        }

        try {
            $uploader = $this->uploaderFactory->create(['fileId' => $type]);
            $result = $this->fileHelper->uploadFromTmp($tmpPath, $uploader);

            if (!$result) {
                throw new \Exception(__('File can not be moved from temporary folder to the destination folder.'));
            }

            unset($result['tmp_name'], $result['path']);

            if (isset($result[Attachment::ATTACHMENT_FILE])) {
                $relativePath = rtrim(
                    $tmpPath,
                    DIRECTORY_SEPARATOR
                )
                . DIRECTORY_SEPARATOR . ltrim($result[Attachment::ATTACHMENT_FILE], DIRECTORY_SEPARATOR);

                $this->storageDatabase->saveFile($relativePath);
            }

            $result['cookie'] = [
                'name' => $this->_getSession()->getName(),
                'value' => $this->_getSession()->getSessionId(),
                'lifetime' => $this->_getSession()->getCookieLifetime(),
                'path' => $this->_getSession()->getCookiePath(),
                'domain' => $this->_getSession()->getCookieDomain(),
            ];
        } catch (\Exception $e) {
            $result = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
        }

        return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData($result);
    }
}
