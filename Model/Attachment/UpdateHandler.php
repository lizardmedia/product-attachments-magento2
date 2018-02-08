<?php

declare(strict_types = 1);

/**
 * File: UpdateHandler.php
 *
 * @author Bartosz Kubicki bartosz.kubicki@lizardmedia.pl>
 * @copyright Copyright (C) 2018 Lizard Media (http://lizardmedia.pl)
 */

namespace LizardMedia\ProductAttachment\Model\Attachment;

use \LizardMedia\ProductAttachment\Api\AttachmentRepositoryInterface;
use \Magento\Framework\EntityManager\Operation\ExtensionInterface;

/**
 * Class UpdateHandler
 * @package LizardMedia\ProductAttachment\Model\Attachment
 */
class UpdateHandler implements ExtensionInterface
{
    /**
     * @var \LizardMedia\ProductAttachment\Api\AttachmentRepositoryInterface
     */
    private $attachmentRepository;


    /**
     * @param \LizardMedia\ProductAttachment\Api\AttachmentRepositoryInterface $attachmentRepository
     */
    public function __construct(AttachmentRepositoryInterface $attachmentRepository)
    {
        $this->attachmentRepository = $attachmentRepository;
    }

    /**
     * @param object $entity
     * @param array $arguments
     *
     * @return \Magento\Catalog\Api\Data\ProductInterface|object $entity
     */
    public function execute($entity, $arguments = [])
    {
        $attachments = $entity->getExtensionAttributes()->getProductAttachments() ?: [];
        /** @var $attachments \LizardMedia\ProductAttachment\Api\Data\AttachmentInterface[] */

        $updatedAttachments = [];
        $oldAttachments = $this->attachmentRepository->getAttachmentsByProduct($entity);

        foreach ($attachments as $attachment) {
            if ($attachment->getId()) {
                $updatedAttachments[(int) $attachment->getId()] = true;
            }
            $this->attachmentRepository->save($entity->getSku(), $attachment, !(bool) $entity->getStoreId());
        }

        /** @var \Magento\Catalog\Api\Data\ProductInterface $entity */
        foreach ($oldAttachments as $attachment) {
            if (!isset($updatedAttachments[(int) $attachment->getId()])) {
                $this->attachmentRepository->delete((int) $attachment->getId());
            }
        }

        return $entity;
    }
}
