<?php

declare(strict_types = 1);

/**
 * File: Attachments.php
 *
 * @author Bartosz Kubicki bartosz.kubicki@lizardmedia.pl>
 * @copyright Copyright (C) 2018 Lizard Media (http://lizardmedia.pl)
 */

namespace LizardMedia\ProductAttachment\Ui\DataProvider\Product\Form\Modifier\Data;

use \LizardMedia\ProductAttachment\Api\Data\AttachmentInterface;
use \LizardMedia\ProductAttachment\Api\AttachmentRepositoryInterface;
use \LizardMedia\ProductAttachment\Api\SettingsInterface;
use \LizardMedia\ProductAttachment\Model\Attachment as AttachmentModel;
use \Magento\Catalog\Model\Locator\LocatorInterface;
use \Magento\Downloadable\Helper\File as DownloadableFile;
use \Magento\Framework\Escaper;
use \Magento\Framework\UrlInterface;
use \Magento\Store\Model\ScopeInterface;

/**
 * Class Attachments
 * @package LizardMedia\ProductAttachment\Ui\DataProvider\Product\Form\Modifier\Data
 */
class Attachments
{
    /**
     * @var \LizardMedia\ProductAttachment\Api\AttachmentRepositoryInterface
     */
    private $attachmentRepository;


    /**
     * @var \LizardMedia\ProductAttachment\Api\SettingsInterface
     */
    private $settings;


    /**
     * @var \LizardMedia\ProductAttachment\Model\Attachment
     */
    private $attachmentModel;


    /**
     * @var \Magento\Catalog\Model\Locator\LocatorInterface
     */
    private $locator;


    /**
     * @var \Magento\Downloadable\Helper\File
     */
    private $downloadableFile;


    /**
     * @var \Magento\Framework\Escaper
     */
    private $escaper;


    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $urlBuilder;


    /**
     * @param \LizardMedia\ProductAttachment\Api\AttachmentRepositoryInterface $attachmentRepository
     * @param \LizardMedia\ProductAttachment\Api\SettingsInterface $settings
     * @param \LizardMedia\ProductAttachment\Model\Attachment $attachmentModel
     * @param \Magento\Catalog\Model\Locator\LocatorInterface $locator
     * @param \Magento\Downloadable\Helper\File $downloadableFile
     * @param \Magento\Framework\Escaper $escaper
     * @param \Magento\Framework\UrlInterface $urlBuilder
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
     * @param \LizardMedia\ProductAttachment\Api\Data\AttachmentInterface $attachment
     *
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
                    'url' => $this->urlBuilder->addSessionParam()->getUrl(
                        'adminhtml/attachment_file/preview',
                        [AttachmentModel::ID => $attachment->getId(), '_secure' => true]
                    ),
                ];
            }
        }

        return $attachmentData;
    }
}
