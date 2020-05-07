<?php

declare(strict_types = 1);

/**
 * File: Version.php
 *
 * @author Bartosz Kubicki bartosz.kubicki@lizardmedia.pl>
 * @copyright Copyright (C) 2018 Lizard Media (http://lizardmedia.pl)
 */

namespace LizardMedia\ProductAttachment\Helper;

use Exception;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\App\ProductMetadata;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\EntityManager\MetadataPool;

/**
 * Various Helper
 * @package LizardMedia\ProductAttachment\Helper
 */
class Version
{
    /**
     * @var ProductMetadataInterface
     */
    private $productMetadata;

   /**
    * @var MetadataPool
    */
   private $metadataPool;

    /**
     * @param ProductMetadataInterface $productMetadata
     * @param MetadataPool $metadataPool
     */
    public function __construct(
        ProductMetadataInterface $productMetadata,
        MetadataPool $metadataPool
    ) {
        $this->productMetadata = $productMetadata;
        $this->metadataPool = $metadataPool;
    }

    /**
     * @return string
     * @throws Exception
     */
    public function getLinkFieldValue(): string
    {
        return  (
            $this->productMetadata->getEdition() !== ProductMetadata::EDITION_NAME
            && version_compare($this->productMetadata->getVersion(), '2.1.0', '>=')
        )
            ? 'entity_id'
            : $this->metadataPool->getMetadata(ProductInterface::class)->getLinkField();
    }
}
