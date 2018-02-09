<?php

declare(strict_types = 1);

/**
 * File: Attachment.php
 *
 * @author Bartosz Kubicki bartosz.kubicki@lizardmedia.pl>
 * @copyright Copyright (C) 2018 Lizard Media (http://lizardmedia.pl)
 */

namespace LizardMedia\ProductAttachment\Model\ResourceModel;

use \LizardMedia\ProductAttachment\Model\Attachment as AttachmentModel;
use \Magento\Catalog\Api\Data\ProductInterface;
use \Magento\Framework\EntityManager\MetadataPool;
use \Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use \Magento\Framework\Model\ResourceModel\Db\Context;

/**
 * Class Attachment
 * @package LizardMedia\ProductAttachment\Model\ResourceModel
 */
class Attachment extends AbstractDb
{
    /**
     * @var \Magento\Framework\EntityManager\MetadataPool
     */
    private $metadataPool;


    /**
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Framework\EntityManager\MetadataPool $metadataPool
     * @param string $connectionName
     */
    public function __construct(
        MetadataPool $metadataPool,
        Context $context,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->metadataPool = $metadataPool;
    }


    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(AttachmentModel::MAIN_TABLE, AttachmentModel::ID);
    }


    /**
     * @param \LizardMedia\ProductAttachment\Model\Attachment $attachment
     *
     * @return \LizardMedia\ProductAttachment\Model\ResourceModel\Attachment
     */
    public function saveItemTitle(AttachmentModel $attachment) : Attachment
    {
        $connection = $this->getConnection();
        $attachmentTitleTable = $this->getTable(AttachmentModel::TITLE_TABLE);
        $bind = [':id' => (int) $attachment->getId(), ':store_id' => (int) $attachment->getStoreId()];

        $select = $connection->select()->from(
            $attachmentTitleTable
        )->where(
            'id = :id AND store_id = :store_id'
        );

        if ($connection->fetchOne($select, $bind)) {
            $where = [
                'id = ?' => (int) $attachment->getId(),
                'store_id = ?' => (int) $attachment->getStoreId(),
            ];
            if ($attachment->getUseDefaultTitle()) {
                $connection->delete($attachmentTitleTable, $where);
            } else {
                $connection->update($attachmentTitleTable, [AttachmentModel::TITLE => $attachment->getTitle()], $where);
            }
        } else {
            if (!$attachment->getUseDefaultTitle()) {
                $connection->insert(
                    $attachmentTitleTable,
                    [
                        AttachmentModel::ATTACHMENT_ID => (int) $attachment->getId(),
                        AttachmentModel::STORE_ID => (int) $attachment->getStoreId(),
                        AttachmentModel::TITLE => $attachment->getTitle()
                    ]
                );
            }
        }

        return $this;
    }


    /**
     * @param \LizardMedia\ProductAttachment\Model\Attachment $attachment
     *
     * @return \LizardMedia\ProductAttachment\Model\ResourceModel\Attachment
     */
    public function loadItemTitle(AttachmentModel $attachment) : Attachment
    {
        $connection = $this->getConnection();
        $attachmentTitleTable = $this->getTable(AttachmentModel::TITLE_TABLE);
        $bindPerStore = [':attachment_id' => (int) $attachment->getId(), ':store_id' => (int) $attachment->getStoreId()];
        $defaultBind = [':attachment_id' => (int) $attachment->getId(), ':store_id' => 0];

        $selectStore = $connection->select()->from(
            $attachmentTitleTable,
            AttachmentModel::TITLE
        )->where(
            'attachment_id = :attachment_id AND store_id = :store_id'
        );

        $select = $connection->select()->from(
            $attachmentTitleTable,
            AttachmentModel::TITLE
        )->where(
            'attachment_id = :attachment_id'
        )->where('store_id = :store_id');


        if ($storeTitle = $connection->fetchOne($selectStore, $bindPerStore)) {
            $attachment->setTitle($storeTitle);
            $attachment->setStoreTitle($storeTitle);
        }

        if ($title = $connection->fetchOne($select, $defaultBind)) {
            $attachment->setTitle($title);
        }

        return $this;
    }


    /**
     * Retrieve attachments searchable data
     *
     * @param int $productId
     * @param int $storeId
     *
     * @throws \Exception
     *
     * @return array
     */
    public function getSearchableData(int $productId, int $storeId) : array
    {
        $connection = $this->getConnection();
        $ifNullDefaultTitle = $connection->getIfNullSql('st.title', 'd.title');
        $select = $connection->select()->from(
            ['m' => $this->getMainTable()],
            null
        )->join(
            ['d' => $this->getTable(AttachmentModel::TITLE_TABLE)],
            'd.attachment_id = m.id AND d.store_id = 0',
            []
        )->join(
            ['cpe' => $this->getTable('catalog_product_entity')],
            sprintf(
                'cpe.entity_id = m.product_id',
                $this->metadataPool->getMetadata(ProductInterface::class)->getLinkField()
            ),
            []
        )->joinLeft(
            ['st' => $this->getTable(AttachmentModel::TITLE_TABLE)],
            'st.attachment_id = m.id AND st.store_id = :store_id',
            [AttachmentModel::TITLE => $ifNullDefaultTitle]
        )->where(
            'cpe.entity_id = :product_id',
            $productId
        );

        $bind = [':store_id' => $storeId, ':product_id' => $productId];

        return $connection->fetchCol($select, $bind);
    }
}
