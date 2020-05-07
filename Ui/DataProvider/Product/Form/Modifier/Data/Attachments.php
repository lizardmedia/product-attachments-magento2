<?php

declare(strict_types = 1);

/**
 * File: Attachments.php
 *
 * @author Bartosz Kubicki bartosz.kubicki@lizardmedia.pl>
 * @copyright Copyright (C) 2018 Lizard Media (http://lizardmedia.pl)
 */

namespace LizardMedia\ProductAttachment\Ui\DataProvider\Product\Form\Modifier\Data;

use LizardMedia\ProductAttachment\Api\AttachmentRepositoryInterface;
use LizardMedia\ProductAttachment\Api\Data\AttachmentInterface;
use LizardMedia\ProductAttachment\Api\SettingsInterface;
use LizardMedia\ProductAttachment\Model\Attachment as AttachmentModel;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Downloadable\Helper\File as DownloadableFile;
use Magento\Framework\Escaper;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Attachments
 * @package LizardMedia\ProductAttachment\Ui\DataProvider\Product\Form\Modifier\Data
 */
class Attachments
{
    /**
     * @var AttachmentRepositoryInterface
     */
    private $attachmentRepository;

    /**
     * @var SettingsInterface
     */
    private $settings;

    /**
     * @var AttachmentModel
     */
    private $attachmentModel;

    /**
     * @var LocatorInterface
     */
    private $locator;

    /**
     * @var DownloadableFile
     */
    private $downloadableFile;

    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @param AttachmentRepositoryInterface $attachmentRepository
     * @param SettingsInterface $settings
     * @param AttachmentModel $attachmentModel
     * @param LocatorInterface $locator
     * @param DownloadableFile $downloadableFile
     * @param Escaper $escaper
     * @param UrlInterface $urlBuilder
     */
    public function __construct(
        AttachmentRepositoryInterface $attachmentRepository,
        SettingsInterface $settings,
        AttachmentModel $attachmentModel,
        LocatorInterface $locator,
        DownloadableFile $downloadableFile,
        Escaper $escaper,
        UrlInterface $urlBuilder
    ) {
        $this->attachmentRepository = $attachmentRepository;
        $this->settings = $settings;
        $this->attachmentModel = $attachmentModel;
        $this->locator = $locator;
        $this->downloadableFile = $downloadableFile;
        $this->escaper = $escaper;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * @return string
     */
    public function getAttachmentsTitle() : string
    {
        $product = $this->locator->getProduct();
        return $product->getAttachmentsTitle()
            ? $product->getAttachmentsTitle()
            : $this->settings->getAttachmentDefaultTitle(ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return array
     */
    public function getAttachmentsData() : array
    {
        $attachmentsData = [];

        $product = $this->locator->getProduct();

        if ($product) {
            $attachments = $this->attachmentRepository->getAttachmentsByProduct($product);
            /** @var AttachmentInterface $attachment */
            foreach ($attachments as $attachment) {
                $attachmentData = [];
                $attachmentData[AttachmentModel::ID] = (int) $attachment->getId() ?: 0;
                $attachmentData[AttachmentModel::TITLE] = $this->escaper->escapeHtml($attachment->getTitle());
                $attachmentData[AttachmentModel::ATTACHMENT_URL] = $attachment->getAttachmentUrl();
                $attachmentData[AttachmentModel::ATTACHMENT_TYPE] = $attachment->getAttachmentType();
                $attachmentData[AttachmentModel::SORT_ORDER] = $attachment->getSortOrder();

                $attachmentData = $this->addAttachmentFile($attachmentData, $attachment);

                $attachmentsData[] = $attachmentData;
            }
        }

        return $attachmentsData;
    }

    /**
     * @param array $attachmentData
     * @param AttachmentInterface $attachment
     * @return array
     */
    private function addAttachmentFile(array $attachmentData, AttachmentInterface $attachment) : array
    {
        $attachmentFile = $attachment->getAttachmentFile();

        if ($attachmentFile) {
            $file = $this->downloadableFile->getFilePath($this->attachmentModel->getBasePath(), $attachmentFile);

            if ($this->downloadableFile->ensureFileInFilesystem($file)) {
                $attachmentData['file'][0] = [
                    'file' => $attachmentFile,
                    'name' => $this->downloadableFile->getFileFromPathFile($attachmentFile),
                    'size' => $this->downloadableFile->getFileSize($file),
                    'status' => 'old',
                    'url' => $this->urlBuilder->getUrl(
                        'downloadable/attachment_file/preview',
                        [AttachmentModel::ID => $attachment->getId(), '_secure' => true]
                    ),
                ];
            }
        }

        return $attachmentData;
    }
}
