<?php

declare(strict_types = 1);

/**
 * File: Preview.php
 *
 * @author Bartosz Kubicki bartosz.kubicki@lizardmedia.pl>
 * @copyright Copyright (C) 2018 Lizard Media (http://lizardmedia.pl)
 */

namespace LizardMedia\ProductAttachment\Controller\Adminhtml\Attachment\File;

use \LizardMedia\ProductAttachment\Api\Data\AttachmentInterface;
use \LizardMedia\ProductAttachment\Api\AttachmentRepositoryInterface;
use \LizardMedia\ProductAttachment\Controller\DownloadProcessor;
use \LizardMedia\ProductAttachment\Model\Attachment;
use \Magento\Backend\App\Action;
use \Magento\Backend\App\Action\Context;
use \Magento\Downloadable\Helper\Download as DownloadHelper;
use \Magento\Downloadable\Helper\File as FileHelper;
use \Magento\Framework\Controller\ResultInterface;
use \Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class Preview
 * @package LizardMedia\ProductAttachment\Controller\Adminhtml\Attachment\File
 */
class Preview extends Action
{
    /**
     * @var string
     */
    private $resource;


    /**
     * @var string
     */
    private $resourceType;


    /**
     * @var \LizardMedia\ProductAttachment\Controller\DownloadProcessor
     */
    private $downloadProcessor;


    /**
     * @var \LizardMedia\ProductAttachment\Api\AttachmentRepositoryInterface
     */
    private $attachmentRepository;


    /**
     * @var \Magento\Downloadable\Helper\File
     */
    private $fileHelper;


    /**
     * @param \LizardMedia\ProductAttachment\Api\AttachmentRepositoryInterface $attachmentRepository
     * @param \LizardMedia\ProductAttachment\Controller\DownloadProcessor $downloadProcessor
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Downloadable\Helper\File $fileHelper
     */
    public function __construct(
        AttachmentRepositoryInterface $attachmentRepository,
        DownloadProcessor $downloadProcessor,
        Context $context,
        FileHelper $fileHelper
    ) {
        parent::__construct($context);
        $this->attachmentRepository = $attachmentRepository;
        $this->downloadProcessor = $downloadProcessor;
        $this->fileHelper = $fileHelper;
    }


    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute() : ResultInterface
    {
        $attachmentId = $this->getRequest()->getParam(Attachment::ID, 0);
        $attachment = $this->loadAttachmentById((int) $attachmentId);
        if ($attachment instanceof AttachmentInterface) {
            if ($attachment->getAttachmentType() == DownloadHelper::LINK_TYPE_URL) {
                $this->processUrlType($attachment);
            } elseif ($attachment->getAttachmentType() == DownloadHelper::LINK_TYPE_FILE) {
                $this->processFileType($attachment);
            }

            try {
                $this->downloadProcessor->processDownload(
                    $this->getResponse(),
                    (string) $this->resource,
                    (string) $this->resourceType
                );
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__('Something went wrong while getting the requested content.'));
            }
        }
    }


    /**
     * @param int $id
     *
     * @return mixed \LizardMedia\ProductAttachment\Api\Data\AttachmentInterface | null
     */
    private function loadAttachmentById(int $id) : ?AttachmentInterface
    {
        try {
            return $this->attachmentRepository->getById($id);
        } catch (NoSuchEntityException $e) {
            $this->messageManager->addErrorMessage(__('Attachment not found.'));
        }
    }


    /**
     * @param \LizardMedia\ProductAttachment\Api\Data\AttachmentInterface $attachment
     *
     * @return void
     */
    private function processUrlType(AttachmentInterface $attachment) : void
    {
        $this->resource = $attachment->getAttachmentUrl();
        $this->resourceType = DownloadHelper::LINK_TYPE_URL;
    }


    /**
     * @param \LizardMedia\ProductAttachment\Api\Data\AttachmentInterface $attachment
     *
     * @return void
     */
    private function processFileType(AttachmentInterface $attachment) : void
    {
        $this->resource = $this->fileHelper->getFilePath(
            $attachment->getBasePath(),
            $attachment->getAttachmentFile()
        );

        $this->resourceType = DownloadHelper::LINK_TYPE_FILE;
    }
}
