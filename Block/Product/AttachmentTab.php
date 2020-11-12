<?php

declare(strict_types = 1);

/**
 * File: Attachments.php
 *
 * @author Bartosz Kubicki bartosz.kubicki@lizardmedia.pl>
 * @copyright Copyright (C) 2018 Lizard Media (http://lizardmedia.pl)
 */

namespace LizardMedia\ProductAttachment\Block\Product;

use LizardMedia\ProductAttachment\Api\AttachmentRepositoryInterface;
use LizardMedia\ProductAttachment\Api\Data\AttachmentInterface;
use LizardMedia\ProductAttachment\Api\SettingsInterface;
use LizardMedia\ProductAttachment\Model\Attachment;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

/**
 * Class Attachments
 * @package LizardMedia\ProductAttachment\Block\Product
 */
class AttachmentTab extends Template implements IdentityInterface
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
     * @var Registry
     */
    private $registry;

    /**
     * @param AttachmentRepositoryInterface $attachmentRepository
     * @param SettingsInterface $settings
     * @param Context $context
     * @param Registry $registry
     * @param array $data
     */
    public function __construct(
        AttachmentRepositoryInterface $attachmentRepository,
        SettingsInterface $settings,
        Context $context,
        Registry $registry,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->attachmentRepository = $attachmentRepository;
        $this->settings = $settings;
        $this->registry = $registry;
    }

    /**
     * @return AttachmentInterface[]
     */
    public function getAttachments() : array
    {
        if ($product = $this->getProduct()) {
            return $this->attachmentRepository->getAttachmentsByProduct($product);
        }

        return [];
    }

    /**
     * @return ProductInterface
     */
    public function getProduct(): ProductInterface
    {
        return $this->registry->registry('current_product');
    }

    /**
     * @param AttachmentInterface $attachment
     * @return string
     */
    public function getDownloadUrl(AttachmentInterface $attachment): string
    {
        return $this->getUrl(
            'downloadable/download/attachment',
            ['id' => $attachment->getId(), '_secure' => true]
        );
    }

    /**
     * @return bool
     */
    public function getIsOpenInNewWindow(): bool
    {
        return $this->settings->areLinksOpenedInNewWindow();
    }

    /**
     * @return array
     */
    public function getIdentities(): array
    {
        return [Attachment::CACHE_TAG];
    }
}
