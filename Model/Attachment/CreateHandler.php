<?php

declare(strict_types = 1);

/**
 * File: CreateHandler.php
 *
 * @author Bartosz Kubicki bartosz.kubicki@lizardmedia.pl>
 * @copyright Copyright (C) 2018 Lizard Media (http://lizardmedia.pl)
 */

namespace LizardMedia\ProductAttachment\Model\Attachment;

use \LizardMedia\ProductAttachment\Api\AttachmentRepositoryInterface;
use \Magento\Framework\EntityManager\Operation\ExtensionInterface;

/**
 * Class CreateHandler
 * @package LizardMedia\ProductAttachment\Model\Attachment
 */
class CreateHandler implements ExtensionInterface
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
        /** @var $attachments \LizardMedia\ProductAttachment\Api\Data\AttachmentInterface[] */
        $attachments = $entity->getExtensionAttributes()->getProductAttachments() ?: [];

        foreach ($attachments as $attachment) {
            $attachment->setId(null);
            $this->attachmentRepository->save($entity->getSku(), $attachment, !$entity->getStoreId());
        }

        return $entity;
    }
}
