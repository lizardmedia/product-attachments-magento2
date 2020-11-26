<?php

declare(strict_types=1);

/**
 * File: DownloadProcessor.php
 *
 * @author Bartosz Kubicki bartosz.kubicki@lizardmedia.pl>
 * @copyright Copyright (C) 2020 Lizard Media (http://lizardmedia.pl)
 */

namespace LizardMedia\ProductAttachment\Controller;

use Exception;
use LizardMedia\ProductAttachment\Api\Data\AttachmentInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem\Io\File;

/**
 * Class DownloadProcessor
 * @package LizardMedia\ProductAttachment\Controller
 */
class DownloadProcessor
{
    /**
     * @var DownloadResourceResolver
     */
    private $downloadResourceResolver;

    /**
     * @var FileFactory
     */
    private $fileFactory;

    /**
     * @var File
     */
    private $file;

    /**
     * DownloadProcessor constructor.
     * @param DownloadResourceResolver $downloadResourceResolver
     * @param FileFactory $fileFactory
     * @param File $file
     */
    public function __construct(
        DownloadResourceResolver $downloadResourceResolver,
        FileFactory $fileFactory,
        File $file
    ) {
        $this->downloadResourceResolver = $downloadResourceResolver;
        $this->fileFactory = $fileFactory;
        $this->file = $file;
    }

    /**
     * @param AttachmentInterface $attachment
     * @return void
     * @throws FileSystemException
     */
    public function processDownload(AttachmentInterface $attachment): void
    {
        try {
            $name = basename($this->downloadResourceResolver->resolveResource($attachment));
            $this->fileFactory->create(
                $name,
                [
                    'type' => 'string',
                    'value' => $this->readFile($attachment),
                    'rm' => true
                ],
                DirectoryList::TMP
            );
        } catch (Exception $exception) {
            throw new FileSystemException(__('File could not be downloaded'));
        }
    }

    /**
     * @param AttachmentInterface $attachment
     * @return string
     * @throws FileSystemException
     */
    private function readFile(AttachmentInterface $attachment): string
    {
        $fileContent = $this->file->read($this->downloadResourceResolver->resolveResource($attachment));
        if ($fileContent === false ) {
            throw new FileSystemException(__('File could not be read'));
        }

        return $fileContent;
    }
}
