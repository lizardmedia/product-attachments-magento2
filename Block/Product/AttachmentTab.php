<?php

declare(strict_types = 1);

/**
 * File: Attachments.php
 *
 * @author Bartosz Kubicki bartosz.kubicki@lizardmedia.pl>
 * @copyright Copyright (C) 2018 Lizard Media (http://lizardmedia.pl)
 */

namespace LizardMedia\ProductAttachment\Block\Product;

use \LizardMedia\ProductAttachment\Api\Data\AttachmentInterface;
use \LizardMedia\ProductAttachment\Api\AttachmentRepositoryInterface;
use \LizardMedia\ProductAttachment\Api\SettingsInterface;
use \LizardMedia\ProductAttachment\Model\Attachment;
use \Magento\Framework\DataObject\IdentityInterface;
use \Magento\Framework\View\Element\Template;
use \Magento\Framework\View\Element\Template\Context;
use \Magento\Framework\Registry;

/**
 * Class Attachments
 * @package LizardMedia\ProductAttachment\Block\Product
 */
class AttachmentTab extends Template implements IdentityInterface
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
     * @var \Magento\Framework\Registry
     */
    private $registry;


    /**
     * @param \LizardMedia\ProductAttachment\Api\AttachmentRepositoryInterface $attachmentRepository
     * @param \LizardMedia\ProductAttachment\Api\SettingsInterface $settings
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $registry
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
     * In case of poor performance (not noticed so far) it can be delegated to
     * ajax controller, to load it later, when tab is clicked and selected.
     *
     * @return \LizardMedia\ProductAttachment\Api\Data\AttachmentInterface[]
     */
    public function getAttachments() : array
    {
        if ($product = $this->getProduct()) {
            return $this->attachmentRepository->getAttachmentsByProduct($product);
        }

        return [];
    }


    /**
     * @return \Magento\Catalog\Api\Data\ProductInterface
     */
    private function getProduct()
    {
        return $this->registry->registry('current_product');
    }


    /**
     * @param \LizardMedia\ProductAttachment\Api\Data\AttachmentInterface $attachment
     *
     * @return string
     */
    public function getDownloadUrl(AttachmentInterface $attachment) : string
    {
        return $this->getUrl('downloadable/download/attachment', ['id' => $attachment->getId(), '_secure' => true]);
    }


    /**
     * @return bool
     */
    public function getIsOpenInNewWindow() : bool
    {
        return $this->settings->areLinksOpenedInNewWindow();
    }


    /**
     * @return array
     */
    public function getIdentities() : array
    {
        return [Attachment::CACHE_TAG];
    }
}
