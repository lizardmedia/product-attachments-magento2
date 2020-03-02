<?php

declare(strict_types=1);

/**
 * File: DownloadResourceResolver.php
 *
 * @author Bartosz Kubicki bartosz.kubicki@lizardmedia.pl>
 * @copyright Copyright (C) 2020 Lizard Media (http://lizardmedia.pl)
 */

namespace LizardMedia\ProductAttachment\Controller;

use LizardMedia\ProductAttachment\Api\Data\AttachmentInterface;
use Magento\Downloadable\Helper\Download as DownloadHelper;
use Magento\Downloadable\Helper\File as FileHelper;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Class DownloadResourceResolver
 * @package LizardMedia\ProductAttachment\Controller
 */
class DownloadResourceResolver
{
    /**
     * @var FileHelper
     */
    private $fileHelper;

    /**
     * @var DirectoryList
     */
    private $directoryList;

    /**
     * DownloadResourceResolver constructor.
     * @param FileHelper $fileHelper
     * @param DirectoryList $directoryList
     */
    public function __construct(FileHelper $fileHelper, DirectoryList $directoryList)
    {
        $this->fileHelper = $fileHelper;
        $this->directoryList = $directoryList;
    }

    /**
     * @param AttachmentInterface $attachment
     * @return string
     * @codeCoverageIgnore
     */
    public function resolveResourceType(AttachmentInterface $attachment): string
    {
        return $attachment->getAttachmentType();
    }

    /**
     * @param AttachmentInterface $attachment
     * @return string
     */
    public function resolveResource(AttachmentInterface $attachment): string
    {
        return $attachment->getAttachmentType() === DownloadHelper::LINK_TYPE_URL
            ? $attachment->getAttachmentUrl()
            : $this->buildFullFilePath($attachment);
    }

    /**
     * @param AttachmentInterface $attachment
     * @return string
     */
    private function buildFullFilePath(AttachmentInterface $attachment): string
    {
        return (string) $this->fileHelper->getFilePath(
            implode(
                DIRECTORY_SEPARATOR,
                [$this->directoryList->getPath(DirectoryList::MEDIA), $attachment->getBasePath()]
            ),
            $attachment->getAttachmentFile()
        );
    }
}