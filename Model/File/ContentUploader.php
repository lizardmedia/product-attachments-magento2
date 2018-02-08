<?php

declare(strict_types = 1);

/**
 * File: ContentUploader.php
 *
 * @author Bartosz Kubicki bartosz.kubicki@lizardmedia.pl>
 * @copyright Copyright (C) 2018 Lizard Media (http://lizardmedia.pl)
 */

namespace LizardMedia\ProductAttachment\Model\File;

use \LizardMedia\ProductAttachment\Api\Data\AttachmentInterface;
use \LizardMedia\ProductAttachment\Api\Data\File\ContentUploaderInterface;
use \Magento\Downloadable\Api\Data\File\ContentInterface;
use \Magento\MediaStorage\Helper\File\Storage\Database;
use \Magento\MediaStorage\Helper\File\Storage;
use \Magento\MediaStorage\Model\File\Validator\NotProtectedExtension;
use \Magento\MediaStorage\Model\File\Uploader;
use \Magento\Framework\App\Filesystem\DirectoryList;
use \Magento\Framework\Filesystem;

/**
 * Class ContentUploader
 * @package LizardMedia\ProductAttachment\Model\File
 */
class ContentUploader extends Uploader implements ContentUploaderInterface
{
    /**
     * Default MIME type
     */
    const DEFAULT_MIME_TYPE = 'application/octet-stream';


    /**
     * @var string
     */
    protected $filePrefix = 'magento_api';


    /**
     * @var \LizardMedia\ProductAttachment\Api\Data\AttachmentInterface
     */
    private $attachmentConfig;


    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    protected $mediaDirectory;


    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    protected $systemTmpDirectory;


    /**
     * @param \LizardMedia\ProductAttachment\Api\Data\AttachmentInterface $attachment
     * @param \Magento\MediaStorage\Helper\File\Storage\Database $fileStorageDb
     * @param \Magento\MediaStorage\Helper\File\Storage $fileStorage
     * @param \Magento\MediaStorage\Model\File\Validator\NotProtectedExtension $validator
     * @param \Magento\Framework\Filesystem $filesystem
     *
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function __construct(
        AttachmentInterface $attachment,
        Database $fileStorageDb,
        Storage $fileStorage,
        NotProtectedExtension $validator,
        Filesystem $filesystem
    ) {
        $this->attachmentConfig = $attachment;
        $this->_coreFileStorageDb = $fileStorageDb;
        $this->_coreFileStorage = $fileStorage;
        $this->_validator = $validator;
        $this->mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->systemTmpDirectory = $filesystem->getDirectoryWrite(DirectoryList::SYS_TMP);
    }



    /**
     * @param \Magento\Downloadable\Api\Data\File\ContentInterface $fileContent
     * @param string $contentType
     *
     * @throws \InvalidArgumentException
     * @throws \Exception
     *
     * @return array
     */
    public function upload(ContentInterface $fileContent, string $contentType) : array
    {
        $this->_file = $this->decodeContent($fileContent);

        if (!file_exists($this->_file['tmp_name'])) {
            throw new \InvalidArgumentException(__('There was an error during file content upload.'));
        }

        $this->_fileExists = true;
        $this->_uploadType = self::SINGLE_STYLE;
        $this->setAllowRenameFiles(true);
        $this->setFilesDispersion(true);
        $result = $this->save($this->getDestinationDirectory($contentType));
        unset($result['path']);
        $result['status'] = 'new';
        $result['name'] = substr($result['file'], strrpos($result['file'], DIRECTORY_SEPARATOR) + 1);
        return $result;
    }


    /**
     * Decode base64 encoded content and save it in system tmp folder
     *
     * @param ContentInterface $fileContent
     *
     * @throws \Magento\Framework\Exception\FileSystemException
     *
     * @return array
     */
    protected function decodeContent(ContentInterface $fileContent)
    {
        $tmpFileName = $this->getTmpFileName();
        $fileSize = $this->systemTmpDirectory->writeFile($tmpFileName, base64_decode($fileContent->getFileData()));

        return [
            'name' => $fileContent->getName(),
            'type' => self::DEFAULT_MIME_TYPE,
            'tmp_name' => $this->systemTmpDirectory->getAbsolutePath($tmpFileName),
            'error' => 0,
            'size' => $fileSize,
        ];
    }

    /**
     * @return string
     */
    protected function getTmpFileName()
    {
        return uniqid($this->filePrefix, true);
    }


    /**
     * @param string $contentType
     *
     * @return string
     */
    protected function getDestinationDirectory($contentType)
    {
        return $this->mediaDirectory->getAbsolutePath($this->attachmentConfig->getBaseTmpPath());
    }
}
