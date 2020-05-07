<?php

declare(strict_types = 1);

/**
 * File: Attachment.php
 *
 * @author Bartosz Kubicki bartosz.kubicki@lizardmedia.pl>
 * @copyright Copyright (C) 2018 Lizard Media (http://lizardmedia.pl)
 */

namespace LizardMedia\ProductAttachment\Model\Product\TypeHandler;

use Exception;
use LizardMedia\ProductAttachment\Api\Data\AttachmentFactoryInterface;
use LizardMedia\ProductAttachment\Api\Data\AttachmentInterface;
use LizardMedia\ProductAttachment\Helper\Version;
use LizardMedia\ProductAttachment\Model\Attachment as AttachmentModel;
use LizardMedia\ProductAttachment\Model\ResourceModel\AttachmentFactory;
use Magento\Catalog\Model\Product;
use Magento\Downloadable\Helper\Download;
use Magento\Downloadable\Helper\File;
use Magento\Downloadable\Model\ComponentInterface;
use Magento\Downloadable\Model\Product\TypeHandler\AbstractTypeHandler;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Json\Helper\Data;

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
     * @var AttachmentFactoryInterface
     */
    private $attachmentFactory;

    /**
     * @var AttachmentFactory
     */
    private $attachmentResourceFactory;

    /**
     * @var Version
     */
    private $version;

    /**
     * @param AttachmentFactoryInterface $attachmentFactory
     * @param AttachmentFactory $attachmentResourceFactory
     * @param Version $version
     * @param File $downloadableFile
     * @param Data $jsonHelper
     */
    public function __construct(
        AttachmentFactoryInterface $attachmentFactory,
        AttachmentFactory $attachmentResourceFactory,
        Version $version,
        File $downloadableFile,
        Data $jsonHelper
    ) {
        parent::__construct($jsonHelper, $downloadableFile);
        $this->attachmentFactory = $attachmentFactory;
        $this->attachmentResourceFactory = $attachmentResourceFactory;
        $this->version = $version;
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
     * @return AttachmentInterface
     */
    protected function createItem() : AttachmentInterface
    {
        return $this->attachmentFactory->create();
    }

    /**
     * @param ComponentInterface $component
     * @param array $data
     * @param Product $product
     * @return void
     * @throws Exception
     */
    protected function setDataToModel(ComponentInterface $component, array $data, Product $product) : void
    {
        $component->setData(
            $data
        )->setAttachmentType(
            $data[AttachmentModel::ATTACHMENT_TYPE]
        )->setProductId(
            (int) $product->getData($this->version->getLinkFieldValue())
        );
        $component->setStoreId(
            (int) $product->getStoreId()
        );
    }

    /**
     * @param ComponentInterface $component
     * @param array $files
     * @return void
     * @throws LocalizedException
     *
     */
    protected function setFiles(ComponentInterface $component, array $files) : void
    {
        if ($component->getAttachmentType() === Download::LINK_TYPE_FILE) {
            $fileName = $this->downloadableFile->moveFileFromTmp(
                $component->getBaseTmpPath(),
                $component->getBasePath(),
                $files
            );
            $component->setAttachmentFile($fileName);
        }
    }


    /**
     * @param ComponentInterface $component
     * @param Product $product
     *
     * @return void
     */
    protected function linkToProduct(ComponentInterface $component, Product $product) : void
    {
        $product->setLastAddedAttachmentId((int) $component->getId());
    }
}
