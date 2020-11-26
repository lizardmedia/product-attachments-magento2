<?php

declare(strict_types=1);

/**
 * File: Attachment.php
 *
 * @author Bartosz Kubicki bartosz.kubicki@lizardmedia.pl>
 * @copyright Copyright (C) 2018 Lizard Media (http://lizardmedia.pl)
 */

namespace LizardMedia\ProductAttachment\Controller\Download;

use LizardMedia\ProductAttachment\Api\AttachmentRepositoryInterface;
use LizardMedia\ProductAttachment\Api\Data\AttachmentInterface;
use LizardMedia\ProductAttachment\Controller\DownloadProcessor;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Exception\FileSystemException;
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
     * Attachment constructor.
     * @param AttachmentRepositoryInterface $attachmentRepository
     * @param DownloadProcessor $downloadProcessor
     * @param Context $context
     */
    public function __construct(
        AttachmentRepositoryInterface $attachmentRepository,
        DownloadProcessor $downloadProcessor,
        Context $context
    ) {
        parent::__construct($context);
        $this->attachmentRepository = $attachmentRepository;
        $this->downloadProcessor = $downloadProcessor;
    }

    /**
     * @return ResponseInterface
     */
    public function execute(): ResponseInterface
    {
        $attachmentId = (int) $this->getRequest()->getParam('id', 0);
        $attachment = $this->loadAttachmentById($attachmentId);

        if ($attachment instanceof AttachmentInterface) {
            try {
                $this->downloadProcessor->processDownload($attachment);
                return $this->_response;
            } catch (FileSystemException $exception) {
                $this->messageManager->addErrorMessage(__('Sorry, there was an error getting requested content.'));
            }
        }

        $this->_redirect->redirect($this->_response, $this->_redirect->getRefererUrl());
        return $this->_response;
    }

    /**
     * @param int $id
     * @return AttachmentInterface|null
     */
    private function loadAttachmentById(int $id): ?AttachmentInterface
    {
        try {
            return $this->attachmentRepository->getById($id);
        } catch (NoSuchEntityException $e) {
            $this->messageManager->addErrorMessage(__('Sorry, there was an error getting requested content.'));
            return null;
        }
    }
}
