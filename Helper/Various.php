<?php

declare(strict_types = 1);

/**
 * File: Various.php
 *
 * @author Bartosz Kubicki bartosz.kubicki@lizardmedia.pl>
 * @copyright Copyright (C) 2018 Lizard Media (http://lizardmedia.pl)
 */

namespace LizardMedia\ProductAttachment\Helper;


use \Magento\Catalog\Api\Data\ProductInterface;
use \Magento\Framework\App\Helper\AbstractHelper;
use \Magento\Framework\App\ProductMetadataInterface;
use \Magento\Framework\EntityManager\MetadataPool;

/**
 * Various Helper
 * @package LizardMedia\ProductAttachment\Helper
 */
class Various extends AbstractHelper
{
    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    private $productMetadata;


   /**
     * @var \Magento\Framework\EntityManager\MetadataPool
     */
    private $metadataPool;


    /**
     * @param \Magento\Framework\App\ProductMetadataInterface $productMetadata
     * @param \Magento\Framework\EntityManager\MetadataPool $metadataPool
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
     */
    public function getLinkFieldValue()
    {
        return  (
            $this->productMetadata->getEdition() !== 'Community'
            && version_compare($this->productMetadata->getVersion(), '2.1.0', '>=')
        )
            ? 'entity_id'
            : $this->metadataPool->getMetadata(ProductInterface::class)->getLinkField();
    }
}
