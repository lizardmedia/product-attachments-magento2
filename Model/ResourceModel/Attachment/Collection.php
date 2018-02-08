<?php

declare(strict_types = 1);

/**
 * File: Collection.php
 *
 * @author Bartosz Kubicki bartosz.kubicki@lizardmedia.pl>
 * @copyright Copyright (C) 2018 Lizard Media (http://lizardmedia.pl)
 */

namespace LizardMedia\ProductAttachment\Model\ResourceModel\Attachment;

use \LizardMedia\ProductAttachment\Model\Attachment;
use \LizardMedia\ProductAttachment\Model\ResourceModel\Attachment as AttachmentResource;
use \LizardMedia\ProductAttachment\Model\ResourceModel\Db\Collection\EavAttributeJoiner;
use \Magento\Catalog\Api\Data\ProductAttributeInterface;
use \Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use \Magento\Framework\Data\Collection\EntityFactoryInterface;
use \Magento\Framework\DB\Adapter\AdapterInterface;
use \Magento\Framework\Event\ManagerInterface;
use \Magento\Framework\EntityManager\MetadataPool;
use \Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use \Psr\Log\LoggerInterface;

/**
 * Class Collection
 * @package LizardMedia\ProductAttachment\Model\ResourceModel\Attachment
 */
class Collection extends AbstractCollection
{
    /**
     * @var \LizardMedia\ProductAttachment\Model\ResourceModel\Db\Collection\EavAttributeJoiner
     */
    private $eavAttributeJoiner;


    /**
     * @var \Magento\Framework\EntityManager\MetadataPool
     */
    private $metadataPool;


    /**
     * @param \LizardMedia\ProductAttachment\Model\ResourceModel\Db\Collection\EavAttributeJoiner $eavAttributeJoiner
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\EntityManager\MetadataPool $metadataPool
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\DB\Adapter\AdapterInterface | null $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb | null $resource
     */
    public function __construct(
        EavAttributeJoiner $eavAttributeJoiner,
        FetchStrategyInterface $fetchStrategy,
        EntityFactoryInterface $entityFactory,
        ManagerInterface $eventManager,
        MetadataPool $metadataPool,
        LoggerInterface $logger,
        AdapterInterface $connection = null,
        AbstractDb $resource = null
    ) {
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $connection,
            $resource
        );

        $this->eavAttributeJoiner = $eavAttributeJoiner;
        $this->metadataPool = $metadataPool;
    }


    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            Attachment::class,
            AttachmentResource::class
        );
    }


    /**
     * @param array $productIds
     *
     * @throws \Exception
     *
     * @return \LizardMedia\ProductAttachment\Model\ResourceModel\Attachment\Collection
     */
    public function addProductToFilter(array $productIds) : Collection
    {
        if (empty($productIds)) {
            $this->addFieldToFilter(Attachment::PRODUCT_ID, '');
        } else {
            $this->addFieldToFilter(Attachment::PRODUCT_ID, ['in' => $productIds]);
        }

        return $this;
    }


    /**
     * @param int $storeId
     *
     * @return \LizardMedia\ProductAttachment\Model\ResourceModel\Attachment\Collection $this
     */
    public function addTitleToResult(int $storeId = 0) : Collection
    {
        $ifNullDefaultTitle = $this->getConnection()->getIfNullSql('st.title', 'd.title');
        $this->getSelect()->joinLeft(
            ['d' => $this->getTable(Attachment::TITLE_TABLE)],
            'd.attachment_id = main_table.id AND d.store_id = 0',
            ['default_title' => 'title']
        )->joinLeft(
            ['st' => $this->getTable(Attachment::TITLE_TABLE)],
            'st.attachment_id = main_table.id AND st.store_id = ' . $storeId,
            ['store_title' => 'title', 'title' => $ifNullDefaultTitle]
        );

        return $this;
    }



    /**
     * @param int $storeId
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     *
     * @return \LizardMedia\ProductAttachment\Model\ResourceModel\Attachment\Collection $this
     */
    public function joinProductTitle(int $storeId = 0) : Collection
    {
        $this->eavAttributeJoiner->joinScopeable(
            $this,
            Attachment::PRODUCT_ID,
            ProductAttributeInterface::ENTITY_TYPE_CODE,
            ['product_name' => 'name'],
            $storeId
        );

        return $this;
    }
}
