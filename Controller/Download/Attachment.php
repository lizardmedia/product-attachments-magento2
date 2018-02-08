<?php

declare(strict_types = 1);

/**
 * File: Attachment.php
 *
 * @author Bartosz Kubicki bartosz.kubicki@lizardmedia.pl>
 * @copyright Copyright (C) 2018 Lizard Media (http://lizardmedia.pl)
 */

namespace LizardMedia\ProductAttachment\Controller\Download;

use \LizardMedia\ProductAttachment\Api\AttachmentRepositoryInterface;
use \LizardMedia\ProductAttachment\Api\Data\AttachmentInterface;
use \LizardMedia\ProductAttachment\Controller\DownloadProcessor;
use \Magento\Downloadable\Helper\Download as DownloadHelper;
use \Magento\Downloadable\Helper\File as FileHelper;
use \Magento\Framework\App\Action\Action;
use \Magento\Framework\App\Action\Context;
use \Magento\Framework\Controller\ResultInterface;
use \Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class Attachment
 * @package LizardMedia\ProductAttachment\Controller\Download
 */
class Attachment extends Action
{
    /**
     * @var \LizardMedia\ProductAttachment\Api\AttachmentRepositoryInterface
     */
    private $attachmentRepository;


    /**
     * @var \LizardMedia\ProductAttachment\Controller\DownloadProcessor
     */
    private $downloadProcessor;


    /**
     * @var \Magento\Downloadable\Helper\File
     */
    private $fileHelper;


    /**
     * @param \LizardMedia\ProductAttachment\Api\AttachmentRepositoryInterface $attachmentRepository
     * @param \LizardMedia\ProductAttachment\Controller\DownloadProcessor $downloadProcessor
     * @param \Magento\Downloadable\Helper\File $fileHelper
     * @param \Magento\Framework\App\Action\Context $context
     */
    public function __construct(
        AttachmentRepositoryInterface $attachmentRepository,
        DownloadProcessor $downloadProcessor,
        FileHelper $fileHelper,
        Context $context
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
        $attachmentId = (int) $this->getRequest()->getParam('id', 0);
        $attachment = $this->loadAttachmentById($attachmentId);

        if ($attachment instanceof AttachmentInterface) {
            $resource = '';
            $resourceType = '';

            if ($attachment->getAttachmentType() == DownloadHelper::LINK_TYPE_URL) {
                $resource = $attachment->getAttachmentUrl();
                $resourceType = DownloadHelper::LINK_TYPE_URL;
            } elseif ($attachment->getAttachmentType() == DownloadHelper::LINK_TYPE_FILE) {
                /** @var \Magento\Downloadable\Helper\File $helper */
                $resource = $this->fileHelper->getFilePath(
                    $attachment->getBasePath(),
                    $attachment->getAttachmentFile()
                );
                $resourceType = DownloadHelper::LINK_TYPE_FILE;
            }
            try {
                $this->downloadProcessor->processDownload($this->getResponse(), $resource, $resourceType);
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__('Sorry, there was an error getting requested content.'));
            }
        }

        return $this->getResponse()->setRedirect($this->_redirect->getRedirectUrl());
    }


    /**
     * @param int $id
     *
     * @return mixed LizardMedia\ProductAttachment\Api\Data\AttachmentInterface | void
     */
    private function loadAttachmentById(int $id) : ?AttachmentInterface
    {
        try {
            return $this->attachmentRepository->getById($id);
        } catch (NoSuchEntityException $e) {
            $this->messageManager->addErrorMessage(__('Sorry, there was an error getting requested content.'));
        }
    }
}
