<?php

declare(strict_types=1);

/**
 * File: Attachment.php
 *
 * @author Bartosz Kubicki bartosz.kubicki@lizardmedia.pl>
 * @copyright Copyright (C) 2018 Lizard Media (http://lizardmedia.pl)
 */

namespace LizardMedia\ProductAttachment\Controller\Download;

use Exception;
use LizardMedia\ProductAttachment\Api\AttachmentRepositoryInterface;
use LizardMedia\ProductAttachment\Api\Data\AttachmentInterface;
use LizardMedia\ProductAttachment\Controller\DownloadProcessor;
use Magento\Downloadable\Helper\Download as DownloadHelper;
use Magento\Downloadable\Helper\File as FileHelper;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class Attachment
 * @package LizardMedia\ProductAttachment\Controller\Download
 */
class Attachment extends Action
{
    /**
     * @var AttachmentRepositoryInterface
     */
    private $attachmentRepository;

    /**
     * @var DownloadProcessor
     */
    private $downloadProcessor;

    /**
     * @var FileHelper
     */
    private $fileHelper;

    /**
     * @param AttachmentRepositoryInterface $attachmentRepository
     * @param DownloadProcessor $downloadProcessor
     * @param FileHelper $fileHelper
     * @param Context $context
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
     * @return ResultInterface|ResponseInterface
     */
    public function execute()
    {
        $attachmentId = (int)$this->getRequest()->getParam('id', 0);
        $attachment = $this->loadAttachmentById($attachmentId);

        if ($attachment instanceof AttachmentInterface) {
            $resource = '';
            $resourceType = '';

            if ($attachment->getAttachmentType() === DownloadHelper::LINK_TYPE_URL) {
                $resource = $attachment->getAttachmentUrl();
                $resourceType = DownloadHelper::LINK_TYPE_URL;
            } elseif ($attachment->getAttachmentType() === DownloadHelper::LINK_TYPE_FILE) {
                /** @var FileHelper $helper */
                $resource = $this->fileHelper->getFilePath(
                    $attachment->getBasePath(),
                    $attachment->getAttachmentFile()
                );
                $resourceType = DownloadHelper::LINK_TYPE_FILE;
            }
            try {
                $this->downloadProcessor->processDownload($this->getResponse(), $resource, $resourceType);
            } catch (Exception $e) {
                $this->messageManager->addErrorMessage(__('Sorry, there was an error getting requested content.'));
            }
        }

        return $this->getResponse()->setRedirect($this->_redirect->getRedirectUrl());
    }

    /**
     * @param int $id
     *
     * @return AttachmentInterface|null
     */
    private function loadAttachmentById(int $id): ?AttachmentInterface
    {
        try {
            return $this->attachmentRepository->getById($id);
        } catch (NoSuchEntityException $e) {
            $this->messageManager->addErrorMessage(__('Sorry, there was an error getting requested content.'));
        }
    }
}
