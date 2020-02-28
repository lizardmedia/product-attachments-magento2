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
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Controller\Result\Raw;
use Magento\Framework\Controller\Result\RawFactory;
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
     * @var RawFactory
     */
    private $rawFactory;

    /**
     * @var File
     */
    private $file;

    /**
     * DownloadProcessor constructor.
     * @param DownloadResourceResolver $downloadResourceResolver
     * @param FileFactory $fileFactory
     * @param RawFactory $rawFactory
     * @param File $file
     */
    public function __construct(
        DownloadResourceResolver $downloadResourceResolver,
        FileFactory $fileFactory,
        RawFactory $rawFactory,
        File $file
    ) {
        $this->downloadResourceResolver = $downloadResourceResolver;
        $this->fileFactory = $fileFactory;
        $this->rawFactory = $rawFactory;
        $this->file = $file;
    }

    /**
     * @param AttachmentInterface $attachment
     * @return Raw
     * @throws FileSystemException
     */
    public function processDownload(AttachmentInterface $attachment): Raw
    {
        /** @var $raw Raw */
        try {
            $raw = $this->rawFactory->create();
            $response = $this->fileFactory->create(
                basename($this->downloadResourceResolver->resolveResource($attachment)),
                $this->readFile($attachment)
            );
            return $raw->renderResult($response);
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