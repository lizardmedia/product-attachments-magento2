<?php

declare(strict_types = 1);

/**
 * File: DownloadProcessor.php
 *
 * @author Bartosz Kubicki bartosz.kubicki@lizardmedia.pl>
 * @copyright Copyright (C) 2018 Lizard Media (http://lizardmedia.pl)
 */

namespace LizardMedia\ProductAttachment\Controller;

use \LizardMedia\ProductAttachment\Api\SettingsInterface;
use \Magento\Downloadable\Helper\Download as DownloadHelper;
use \Magento\Downloadable\Helper\File as FileHelper;
use \Magento\Framework\App\ResponseInterface;

/**
 * Class DownloadProcessor
 * @package LizardMedia\ProductAttachment\Controller
 */
class DownloadProcessor
{
    /**
     * @var \LizardMedia\ProductAttachment\Api\SettingsInterface
     */
    private $settings;


    /**
     * @var \Magento\Downloadable\Helper\Download
     */
    private $downloadHelper;


    /**
     * @var \Magento\Downloadable\Helper\File
     */
    private $fileHelper;


    /**
     * @param \LizardMedia\ProductAttachment\Api\SettingsInterface $settings
     * @param \Magento\Downloadable\Helper\Download $downloadHelper
     * @param \Magento\Downloadable\Helper\File $fileHelper
     */
    public function __construct(
        SettingsInterface $settings,
        DownloadHelper $downloadHelper,
        FileHelper $fileHelper
    ) {
        $this->settings = $settings;
        $this->downloadHelper = $downloadHelper;
        $this->fileHelper = $fileHelper;
    }


    /**
     * @param \Magento\Framework\App\ResponseInterface $response
     * @param string $resource
     * @param string $resourceType
     *
     * @return void
     */
    public function processDownload(ResponseInterface $response, string $resource, string $resourceType)
    {
        $this->downloadHelper->setResource($resource, $resourceType);

        $fileName = $this->downloadHelper->getFilename();
        $contentType = $this->downloadHelper->getContentType();

        $response->setHttpResponseCode(
            200
        )->setHeader(
            'Pragma',
            'public',
            true
        )->setHeader(
            'Cache-Control',
            'must-revalidate, post-check=0, pre-check=0',
            true
        )->setHeader(
            'Content-type',
            $contentType,
            true
        );

        if ($fileSize = $this->downloadHelper->getFileSize()) {
            $response->setHeader('Content-Length', $fileSize);
        }

        if ($contentDisposition = $this->settings->getContentDisposition()) {
            $response
                ->setHeader('Content-Disposition', $contentDisposition . '; filename=' . $fileName);
        }

        $response->clearBody();
        $response->sendHeaders();
        $this->downloadHelper->output();
    }
}
