<?php

declare(strict_types = 1);

/**
 * File: EavAttributeJoiner.php
 *
 * @author Bartosz Kubicki bartosz.kubicki@lizardmedia.pl>
 * @copyright Copyright (C) 2018 Lizard Media (http://lizardmedia.pl)
 */

namespace LizardMedia\ProductAttachment\Model\ResourceModel\Db\Collection;

use \Magento\Eav\Api\AttributeRepositoryInterface;
use \Magento\Eav\Model\Entity\Type as EntityTypeModel;
use \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use \Magento\Store\Model\StoreManagerInterface;
use \Magento\Store\Model\Store;
use \Psr\Log\LoggerInterface;

/** Class can be used to join attributes to non-eav collection */
/**
 * When attribute is joined it has key in collection based on pattern 'entityTypeCode_attributeCode'.
 * If attribute can have scoped value, than method `joinScopeable()` with parameter `storeId` parameter should be used.
 * Then value for default scope 0 will be joined with key 'entityTypeCode_attributeCode_default'.
 *
 * In case of joining multiple attributes from the same entity every join has unique aliases for tables.
 * They are returned as array from function and can be used in further operations on collection.
 *
 *
 * In case of joining attribute basing on already joined field argument `$mainTable` may be useful, to start operation
 * from not very first table.
 *
 * In case of some not standard basic entity fields, argument `$entityForeignKeyName` may be used.
 */

/**
 * Class EavAttributeJoiner
 * @package LizardMedia\ProductAttachments\Model\ResourceModel\Db\Collection
 */
class EavAttributeJoiner
{
    /**
     * @string
     */
    const VALUE_FIELD = 'value';


    /**
     * @var int
     */
    private $aliasIndex;


    /**
     * @var array
     */
    private $joinReturnData = [];


    /**
     * @var \Magento\Eav\Model\Entity\Type
     */
    private $entityType;


    /**
     * @var string
     */
    private $entityTable;


    /**
     * @var \Magento\Framework\Model\ResourceModel\AbstractResource
     */
    private $entityBasicModel;


    /**
     * @var int
     */
    private $entityBasicModelIdField;


    /**
     * @var \Magento\Eav\Api\Data\AttributeInterface
     */
    private $attributeModel;


    /**
     * @var string
     */
    private $backendType;


    /** Dependencies */

    /**
     * @var \Magento\Eav\Api\AttributeRepositoryInterface
     */
    private $attributeRepository;


    /**
     * @var \Magento\Eav\Model\Entity\Type
     */
    private $entityTypeModel;


    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;


    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;


    /**
     * @param \Magento\Eav\Api\AttributeRepositoryInterface $attributeRepository
     * @param \Magento\Eav\Model\Entity\Type $entityTypeModel
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        AttributeRepositoryInterface $attributeRepository,
        EntityTypeModel $entityTypeModel,
        StoreManagerInterface $storeManager,
        LoggerInterface $logger
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->entityTypeModel = $entityTypeModel;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
    }


    /**
     * @param \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection $collection
     * @param string $foreignKeyName
     * @param string $entityTypeCode
     * @param mixed string | array $attributes
     * @param string $entityAlternativeKeyName
     * @param string $mainTable
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     *
     * @return array
     */
    public function join(
        AbstractCollection $collection,
        string $foreignKeyName,
        string $entityTypeCode,
        $attributes,
        string $entityAlternativeKeyName = '',
        string $mainTable = 'main_table'
    ) : array {
        $this->prepareEntityInformation($collection, $entityTypeCode, $entityAlternativeKeyName);

        $attributesTable = $this->convertAttributesArgument($attributes);

        foreach ($attributesTable as $attributeCode) {
            $this->aliasIndex++;
            $entityTableAlias = $this->generateAlias($this->entityTable);
            $this->prepareAttributeInformation($entityTypeCode, $attributeCode);

            if (!$this->attributeModel->getBackend()->isStatic()) {
                $typeTable = $this->buildTypeTableName();
                $typeTableAlias = $this->generateAlias($typeTable);

                $collection->getSelect()->joinLeft(
                    [$typeTableAlias => $typeTable],
                    $mainTable . '.' . $foreignKeyName . ' = ' . $typeTableAlias . '.' . $this->entityBasicModelIdField
                    . ' AND ' . $typeTableAlias . '.attribute_id = ' . $this->attributeModel->getAttributeId(),
                    [$entityTypeCode . '_' . $attributeCode => $typeTableAlias . '.' . self::VALUE_FIELD]
                );

                $this->addRecordToJoinReturnData($entityTypeCode, $attributeCode, $typeTableAlias, self::VALUE_FIELD);
            } else {
                $collection->getSelect()->joinLeft(
                    [$entityTableAlias => $this->entityTable],
                    $mainTable . '.' . $foreignKeyName . ' = ' . $entityTableAlias . '.' . $this->entityBasicModelIdField,
                    [$entityTypeCode . '_' . $attributeCode => $attributeCode]
                );

                $this->addRecordToJoinReturnData($entityTypeCode, $attributeCode, $entityTableAlias, $attributeCode);
            }
        }

        return $this->joinReturnData;
    }


    /**
     * @param \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection $collection
     * @param string $foreignKeyName
     * @param string $entityTypeCode
     * @param mixed string | array $attributes
     * @param int $storeId
     * @param string $entityAlternativeKeyName
     * @param string $mainTable
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     *
     * @return array
     */
    public function joinScopeable(
        AbstractCollection $collection,
        string $foreignKeyName,
        string $entityTypeCode,
        $attributes,
        int $storeId = 0,
        string $entityAlternativeKeyName = '',
        string $mainTable = 'main_table'
    ) : array {
        $this->prepareEntityInformation($collection, $entityTypeCode, $entityAlternativeKeyName);

        $attributesTable = $this->convertAttributesArgument($attributes);

        foreach ($attributesTable as $attributeCode) {
            $this->aliasIndex++;
            $entityTableAlias = $this->generateAlias($this->entityTable);
            $this->prepareAttributeInformation($entityTypeCode, $attributeCode);

            if (!$this->attributeModel->getBackend()->isStatic()) {
                $typeTable = $this->buildTypeTableName();
                $typeTableAlias = $this->generateAlias($typeTable);
                $typeTableAliasDefault = $this->generateAlias($typeTable, true);

                $collection->getSelect()->joinLeft(
                    [$typeTableAlias => $typeTable],
                    $mainTable . '.' . $foreignKeyName . ' = ' . $typeTableAlias . '.' . $this->entityBasicModelIdField
                    . ' AND ' . $typeTableAlias . '.attribute_id = ' . $this->attributeModel->getAttributeId()
                    . ' AND ' . $typeTableAlias . '.store_id = ' . $storeId,
                    [$entityTypeCode . '_' . $attributeCode => $typeTableAlias . '.' . self::VALUE_FIELD]
                )->joinLeft(
                    [$typeTableAliasDefault => $typeTable],
                    $mainTable . '.' . $foreignKeyName . ' = ' . $typeTableAliasDefault . '.' . $this->entityBasicModelIdField
                    . ' AND ' . $typeTableAliasDefault . '.attribute_id = ' . $this->attributeModel->getAttributeId()
                    . ' AND ' . $typeTableAliasDefault . '.store_id = ' . Store::DEFAULT_STORE_ID,
                    [
                        $entityTypeCode . '_' . $attributeCode . '_default'
                        => $typeTableAliasDefault . '.' . self::VALUE_FIELD
                    ]
                );


                $this->addRecordToJoinReturnData($entityTypeCode, $attributeCode, $typeTableAlias, self::VALUE_FIELD);
                $this->addRecordToJoinReturnData($entityTypeCode, $attributeCode, $typeTableAliasDefault, self::VALUE_FIELD);
            } else {
                $collection->getSelect()->joinLeft(
                    [$entityTableAlias => $this->entityTable],
                    $mainTable . '.' . $foreignKeyName . ' = ' . $entityTableAlias . '.' . $this->entityBasicModelIdField,
                    [$entityTypeCode . '_' . $attributeCode => $attributeCode]
                );

                $this->addRecordToJoinReturnData($entityTypeCode, $attributeCode, $entityTableAlias, $attributeCode);
            }
        }

        return $this->joinReturnData;
    }


    /**
     * @param \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection $collection
     * @param string $entityTypeCode
     * @param string $entityAlternativeKeyName
     *
     * @return void
     */
    private function prepareEntityInformation(
        AbstractCollection $collection,
        string $entityTypeCode,
        string $entityAlternativeKeyName = ''
    ) {
        $this->entityType = $this->entityTypeModel->loadByCode($entityTypeCode);
        $this->entityTable = $collection->getTable($this->entityType->getEntityTable());
        $this->entityBasicModel = $this->entityType->getEntity();
        $this->entityBasicModelIdField = $this->entityBasicModel->getEntityIdField();

        if ($entityAlternativeKeyName) {
            $this->entityBasicModelIdField = $entityAlternativeKeyName;
        }
    }


    /**
     * @param mixed string | array $attributes
     *
     * @return array $attributesTable
     */
    private function convertAttributesArgument($attributes) : array
    {
        if (!is_array($attributes)) {
            $attributesTable[] = $attributes;
        } else {
            $attributesTable = $attributes;
        }


        return $attributesTable;
    }


    /**
     * @param string $entityTypeCode
     * @param string $attributeCode
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     *
     * @return void
     */
    private function prepareAttributeInformation(string $entityTypeCode, string $attributeCode)
    {
        $this->attributeModel = $this->getAttributeByCode($entityTypeCode, $attributeCode);
        $this->backendType = $this->attributeModel->getBackendType();
    }


    /**
     * @param string $entityTypeCode
     * @param string $attributeCode
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     *
     * @return \Magento\Eav\Api\Data\AttributeInterface
     */
    private function getAttributeByCode($entityTypeCode, $attributeCode)
    {
        return $this->attributeRepository->get($entityTypeCode, $attributeCode);
    }


    /**
     * @return string
     */
    private function buildTypeTableName()
    {
        return $this->entityTable . '_' . $this->backendType;
    }


    /**
     * @param string $tableName
     * @param bool $useDefaultSuffix
     *
     * @return string
     */
    private function generateAlias(string $tableName, bool $useDefaultSuffix = false) : string
    {
        $alias = 'table_' . $this->aliasIndex . '_' . $tableName;

        if ($useDefaultSuffix === true) {
            $alias .= '_default';
        }

        return $alias;
    }


    /**
     * @param string $entityTypeCode
     * @param string $attributeCode
     * @param string $tableAlias
     * @param string $fieldName
     *
     * @return void
     */
    private function addRecordToJoinReturnData(
        string $entityTypeCode,
        string $attributeCode,
        string $tableAlias,
        string $fieldName
    ) {
        $this->joinReturnData[$entityTypeCode . '_' . $attributeCode] =
            ['table' => $tableAlias, 'field' => $fieldName];
    }
}
