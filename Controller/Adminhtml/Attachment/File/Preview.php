<?php

declare(strict_types = 1);

/**
 * File: Preview.php
 *
 * @author Bartosz Kubicki bartosz.kubicki@lizardmedia.pl>
 * @copyright Copyright (C) 2018 Lizard Media (http://lizardmedia.pl)
 */

namespace LizardMedia\ProductAttachment\Controller\Adminhtml\Attachment\File;

use LizardMedia\ProductAttachment\Api\AttachmentRepositoryInterface;
use LizardMedia\ProductAttachment\Api\Data\AttachmentInterface;
use LizardMedia\ProductAttachment\Controller\DownloadProcessor;
use LizardMedia\ProductAttachment\Model\Attachment;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class Preview
 * @package LizardMedia\ProductAttachment\Controller\Adminhtml\Attachment\File
 */
class Preview extends Action
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
        $attachmentId = $this->getRequest()->getParam(Attachment::ID, 0);
        $attachment = $this->loadAttachmentById((int) $attachmentId);
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
     * @return AttachmentInterface | null
     */
    private function loadAttachmentById(int $id) : ?AttachmentInterface
    {
        try {
            return $this->attachmentRepository->getById($id);
        } catch (NoSuchEntityException $e) {
            $this->messageManager->addErrorMessage(__('Attachment not found.'));
            return null;
        }
    }
}
