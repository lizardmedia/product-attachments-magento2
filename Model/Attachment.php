<?php

declare(strict_types = 1);

/**
 * File: Attachment.php
 *
 * @author Bartosz Kubicki bartosz.kubicki@lizardmedia.pl>
 * @copyright Copyright (C) 2018 Lizard Media (http://lizardmedia.pl)
 */

namespace LizardMedia\ProductAttachment\Model;

use \LizardMedia\ProductAttachment\Api\Data\AttachmentInterface;
use \LizardMedia\ProductAttachment\Api\Data\AttachmentExtensionInterface;
use \LizardMedia\ProductAttachment\Model\ResourceModel\Attachment as AttachmentResource;
use \Magento\Downloadable\Api\Data\File\ContentInterface;
use \Magento\Downloadable\Model\ComponentInterface;
use \Magento\Framework\DataObject\IdentityInterface;
use \Magento\Framework\Model\AbstractExtensibleModel;

/**
 * Class Attachment
 * @package LizardMedia\ProductAttachment\Model
 */
class Attachment extends AbstractExtensibleModel implements AttachmentInterface, ComponentInterface, IdentityInterface
{
    /**
     * @var string
     */
    const CACHE_TAG = 'lizardmedia_productattachment_attachment';


    /**
     * Tables
     *
     * @var string
     */
    const MAIN_TABLE = 'lizardmedia_product_attachment';
    const TITLE_TABLE = 'lizardmedia_product_attachment_title';


    /**
     * Main model field names
     *
     * @var string
     */
    const ID = 'id';
    const PRODUCT_ID = 'product_id';
    const SORT_ORDER = 'sort_order';
    const ATTACHMENT_TYPE = 'attachment_type';
    const ATTACHMENT_FILE = 'attachment_file';
    const ATTACHMENT_FILE_CONTENT = 'attachment_file_content';
    const ATTACHMENT_URL = 'attachment_url';


    /**
     * Title field names
     *
     * @var string
     */
    const TITLE_ID = 'id';
    const ATTACHMENT_ID = 'attachment_id';
    const TITLE = 'title';
    const STORE_ID = 'store_id';


    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(AttachmentResource::class);
        parent::_construct();
    }


    /**
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }


    /**
     * @return $this
     */
    public function afterSave()
    {
        $this->getResource()->saveItemTitle($this);
        return parent::afterSave();
    }


    /**
     * @return string
     */
    public function getUrl()
    {
        if ($this->getAttachmentUrl()) {
            return $this->getAttachmentUrl();
        } else {
            return $this->getAttachmentFile();
        }
    }


    /**
     * @return string
     */
    public function getBaseTmpPath()
    {
        return 'downloadable/tmp/attachment';
    }


    /**
     * @return string
     */
    public function getBasePath()
    {
        return 'downloadable/files/attachment';
    }


    /**
     * @param int $productId
     * @param int $storeId
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     *
     * @return array
     */
    public function getSearchableData($productId, $storeId)
    {
        return $this->_getResource()->getSearchableData($productId, $storeId);
    }


    /**
     * @return int
     */
    public function getProductId() : int
    {
        return (int) $this->getData(self::PRODUCT_ID);
    }


    /**
     * @param int $id
     *
     * @return \LizardMedia\ProductAttachment\Api\Data\AttachmentInterface
     */
    public function setProductId(int $id) : AttachmentInterface
    {
        return $this->setData(self::PRODUCT_ID, $id);
    }


    /**
     * @return string
     */
    public function getTitle() : string
    {
        return $this->getData(self::TITLE);
    }


    /**
     * @param string $title
     *
     * @return \LizardMedia\ProductAttachment\Api\Data\AttachmentInterface
     */
    public function setTitle(string $title) : AttachmentInterface
    {
        return $this->setData(self::TITLE, $title);
    }


    /**
     * @return int
     */
    public function getSortOrder() : int
    {
        return (int) $this->getData(self::SORT_ORDER);
    }


    /**
     * @param int $sortOrder
     *
     * @return \LizardMedia\ProductAttachment\Api\Data\AttachmentInterface
     */
    public function setSortOrder(int $sortOrder) : AttachmentInterface
    {
        return $this->setData(self::SORT_ORDER, $sortOrder);
    }


    /**
     * @return string
     */
    public function getAttachmentType() : string
    {
        return $this->getData(self::ATTACHMENT_TYPE);
    }


    /**
     * @param string $attachmentType
     *
     * @return \LizardMedia\ProductAttachment\Api\Data\AttachmentInterface
     */
    public function setAttachmentType(string $attachmentType) : AttachmentInterface
    {
        return $this->setData(self::ATTACHMENT_TYPE, $attachmentType);
    }


    /**
     * Relative file path
     *
     * @return string|null
     */
    public function getAttachmentFile() : ?string
    {
        return $this->getData(self::ATTACHMENT_FILE);
    }


    /**
     * @param string $attachmentFile
     *
     * @return \LizardMedia\ProductAttachment\Api\Data\AttachmentInterface
     */
    public function setAttachmentFile(string $attachmentFile) : AttachmentInterface
    {
        return $this->setData(self::ATTACHMENT_FILE, $attachmentFile);
    }


    /**
     * @return \Magento\Downloadable\Api\Data\File\ContentInterface|null
     */
    public function getAttachmentFileContent() : ?ContentInterface
    {
        return $this->getData(self::ATTACHMENT_FILE_CONTENT);
    }


    /**
     * @param \Magento\Downloadable\Api\Data\File\ContentInterface $attachmentFileContent
     *
     * @return \LizardMedia\ProductAttachment\Api\Data\AttachmentInterface
     */
    public function setAttachmentFileContent(ContentInterface $attachmentFileContent = null) : AttachmentInterface
    {
        return $this->setData(self::ATTACHMENT_FILE_CONTENT, $attachmentFileContent);
    }


    /**
     * @return string|null
     */
    public function getAttachmentUrl() : ?string
    {
        return $this->getData(self::ATTACHMENT_URL);
    }


    /**
     * @param string $attachmentUrl
     *
     * @return \LizardMedia\ProductAttachment\Api\Data\AttachmentInterface
     */
    public function setAttachmentUrl(string $attachmentUrl) : AttachmentInterface
    {
        return $this->setData(self::ATTACHMENT_URL, $attachmentUrl);
    }


    /**
     * @return \LizardMedia\ProductAttachment\Api\Data\AttachmentExtensionInterface | null
     */
    public function getExtensionAttributes() : ?AttachmentExtensionInterface
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * @param \LizardMedia\ProductAttachment\Api\Data\AttachmentExtensionInterface $extensionAttributes
     *
     * @return \LizardMedia\ProductAttachment\Api\Data\AttachmentInterface
     */
    public function setExtensionAttributes(AttachmentExtensionInterface $extensionAttributes) : AttachmentInterface
    {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
