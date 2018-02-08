<?php

declare(strict_types = 1);

/**
 * File: AttachmentInterface.php
 *
 * @author Bartosz Kubicki bartosz.kubicki@lizardmedia.pl>
 * @copyright Copyright (C) 2018 Lizard Media (http://lizardmedia.pl)
 */

namespace LizardMedia\ProductAttachment\Api\Data;

use \LizardMedia\ProductAttachment\Api\Data\AttachmentExtensionInterface;
use \Magento\Downloadable\Api\Data\File\ContentInterface;
use \Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Interface AttachmentInterface
 * @package LizardMedia\ProductAttachment\Api\Data
 */
interface AttachmentInterface extends ExtensibleDataInterface
{
    /**
     * @return int
     */
    public function getProductId() : int;


    /**
     * @param int $id
     *
     * @return \LizardMedia\ProductAttachment\Api\Data\AttachmentInterface
     */
    public function setProductId(int $id) : AttachmentInterface;


    /**
     * @return string
     */
    public function getTitle() : string;


    /**
     * @param string $title
     *
     * @return \LizardMedia\ProductAttachment\Api\Data\AttachmentInterface
     */
    public function setTitle(string $title) : AttachmentInterface;


    /**
     * @return int
     */
    public function getSortOrder() : int;


    /**
     * @param int $sortOrder
     *
     * @return \LizardMedia\ProductAttachment\Api\Data\AttachmentInterface
     */
    public function setSortOrder(int $sortOrder) : AttachmentInterface;


    /**
     * @return string
     */
    public function getAttachmentType() : string;


    /**
     * @param string $attachmentType
     *
     * @return \LizardMedia\ProductAttachment\Api\Data\AttachmentInterface
     */
    public function setAttachmentType(string $attachmentType) : AttachmentInterface;


    /**
     * Relative file path
     *
     * @return string|null
     */
    public function getAttachmentFile() : ?string;


    /**
     * @param string $attachmentFile
     *
     * @return \LizardMedia\ProductAttachment\Api\Data\AttachmentInterface
     */
    public function setAttachmentFile(string $attachmentFile) : AttachmentInterface;


    /**
     * @return \Magento\Downloadable\Api\Data\File\ContentInterface|null
     */
    public function getAttachmentFileContent() : ?ContentInterface;


    /**
     * @param \Magento\Downloadable\Api\Data\File\ContentInterface $attachmentFileContent
     *
     * @return \LizardMedia\ProductAttachment\Api\Data\AttachmentInterface
     */
    public function setAttachmentFileContent(ContentInterface $attachmentFileContent = null) : AttachmentInterface;


    /**
     * @return string|null
     */
    public function getAttachmentUrl() : ?string;


    /**
     * @param string $attachmentUrl
     *
     * @return \LizardMedia\ProductAttachment\Api\Data\AttachmentInterface
     */
    public function setAttachmentUrl(string $attachmentUrl) : AttachmentInterface;


    /**
     * @return \LizardMedia\ProductAttachment\Api\Data\AttachmentExtensionInterface | null
     */
    public function getExtensionAttributes() : ?AttachmentExtensionInterface;


    /**
     * @param \LizardMedia\ProductAttachment\Api\Data\AttachmentExtensionInterface $extensionAttributes
     * @return \LizardMedia\ProductAttachment\Api\Data\AttachmentInterface
     */
    public function setExtensionAttributes(AttachmentExtensionInterface $extensionAttributes) : AttachmentInterface;
}
