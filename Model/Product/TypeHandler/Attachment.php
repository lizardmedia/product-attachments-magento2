<?php

declare(strict_types = 1);

/**
 * File: Attachment.php
 *
 * @author Bartosz Kubicki bartosz.kubicki@lizardmedia.pl>
 * @copyright Copyright (C) 2018 Lizard Media (http://lizardmedia.pl)
 */

namespace LizardMedia\ProductAttachment\Model\Product\TypeHandler;

use \LizardMedia\ProductAttachment\Api\Data\AttachmentFactoryInterface;
use \LizardMedia\ProductAttachment\Api\Data\AttachmentInterface;
use \LizardMedia\ProductAttachment\Model\ResourceModel\AttachmentFactory;
use \LizardMedia\ProductAttachment\Model\Attachment as AttachmentModel;
use \Magento\Catalog\Api\Data\ProductInterface;
use \Magento\Catalog\Model\Product;
use \Magento\Downloadable\Helper\Download;
use \Magento\Downloadable\Helper\File;
use \Magento\Downloadable\Model\ComponentInterface;
use \Magento\Downloadable\Model\Product\TypeHandler\AbstractTypeHandler;
use \Magento\Framework\Json\Helper\Data;

/**
 * Class Attachment
 * @package LizardMedia\ProductAttachment\Model\Product\TypeHandler
 */
class Attachment extends AbstractTypeHandler
{
    /**
     * @var string
     */
    const DATA_KEY = 'attachment';
    const IDENTIFIER_KEY = 'id';

    /**
     * @var \LizardMedia\ProductAttachment\Api\Data\AttachmentFactoryInterface
     */
    private $attachmentFactory;


    /**
     * @var \LizardMedia\ProductAttachment\Model\ResourceModel\AttachmentFactory
     */
    private $attachmentResourceFactory;


    /**
     * @param \LizardMedia\ProductAttachment\Api\Data\AttachmentFactoryInterface $attachmentFactory
     * @param \LizardMedia\ProductAttachment\Model\ResourceModel\AttachmentFactory $attachmentResourceFactory
     * @param \Magento\Downloadable\Helper\File $downloadableFile
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     */
    public function __construct(
        AttachmentFactoryInterface $attachmentFactory,
        AttachmentFactory $attachmentResourceFactory,
        File $downloadableFile,
        Data $jsonHelper
    ) {
        parent::__construct($jsonHelper, $downloadableFile);
        $this->attachmentFactory = $attachmentFactory;
        $this->attachmentResourceFactory = $attachmentResourceFactory;
    }


    /**
     * @return string
     */
    public function getDataKey() : string
    {
        return self::DATA_KEY;
    }


    /**
     * @return string
     */
    public function getIdentifierKey() : string
    {
        return self::IDENTIFIER_KEY;
    }


    /**
     * @return void
     */
    protected function processDelete() : void
    {
        if ($this->deletedItems) {
            $this->attachmentResourceFactory->create()->deleteItems($this->deletedItems);
        }
    }


    /**
     * @return \LizardMedia\ProductAttachment\Api\Data\AttachmentInterface
     */
    protected function createItem() : AttachmentInterface
    {
        return $this->attachmentFactory->create();
    }


    /**
     * @param \Magento\Downloadable\Model\ComponentInterface $component
     * @param array $data
     * @param \Magento\Catalog\Model\Product $product
     *
     * @throws \Exception
     *
     * @return void
     */
    protected function setDataToModel(ComponentInterface $component, array $data, Product $product) : void
    {
        $component->setData(
            $data
        )->setAttachmentType(
            $data[AttachmentModel::ATTACHMENT_TYPE]
        )->setProductId(
            (int) $product->getData(
                $this->getMetadataPool()->getMetadata(ProductInterface::class)->getLinkField()
            )
        );
        $component->setStoreId(
            (int) $product->getStoreId()
        );
    }


    /**
     * @param \Magento\Downloadable\Model\ComponentInterface $component
     * @param array $files
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     *
     * @return void
     */
    protected function setFiles(ComponentInterface $component, array $files) : void
    {
        if ($component->getAttachmentType() == Download::LINK_TYPE_FILE) {
            $fileName = $this->downloadableFile->moveFileFromTmp(
                $component->getBaseTmpPath(),
                $component->getBasePath(),
                $files
            );
            $component->setAttachmentFile($fileName);
        }
    }


    /**
     * @param \Magento\Downloadable\Model\ComponentInterface $component
     * @param \Magento\Catalog\Model\Product $product
     *
     * @return void
     */
    protected function linkToProduct(ComponentInterface $component, Product $product) : void
    {
        $product->setLastAddedAttachmentId((int) $component->getId());
    }
}
