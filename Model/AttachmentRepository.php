<?php

declare(strict_types = 1);

/**
 * File: AttachmentRepository.php
 *
 * @author Bartosz Kubicki bartosz.kubicki@lizardmedia.pl>
 * @copyright Copyright (C) 2018 Lizard Media (http://lizardmedia.pl)
 */

namespace LizardMedia\ProductAttachment\Model;

use \LizardMedia\ProductAttachment\Api\Data\AttachmentFactoryInterface;
use \LizardMedia\ProductAttachment\Api\Data\AttachmentInterface;
use \LizardMedia\ProductAttachment\Api\Data\File\ContentUploaderInterface;
use \LizardMedia\ProductAttachment\Api\AttachmentRepositoryInterface;
use \LizardMedia\ProductAttachment\Model\Attachment\ContentValidator;
use \LizardMedia\ProductAttachment\Model\AttachmentRepository\SearchResultProcessor;
use \LizardMedia\ProductAttachment\Model\Product\TypeHandler\Attachment as AttachmentHandler;
use \LizardMedia\ProductAttachment\Model\ResourceModel\Attachment\Collection as AttachmentCollection;
use \LizardMedia\ProductAttachment\Model\ResourceModel\Attachment\Collection;
use \LizardMedia\ProductAttachment\Model\ResourceModel\Attachment\CollectionFactory as AttachmentCollectionFactory;
use \Magento\Catalog\Api\Data\ProductInterface;
use \Magento\Catalog\Api\ProductRepositoryInterface;
use \Magento\Downloadable\Helper\Download;
use \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use \Magento\Framework\Api\SearchCriteriaInterface;
use \Magento\Framework\Api\SearchResultsInterface;
use \Magento\Framework\Api\SearchResultsInterfaceFactory;
use \Magento\Framework\EntityManager\MetadataPool;
use \Magento\Framework\Exception\InputException;
use \Magento\Framework\Exception\NoSuchEntityException;
use \Magento\Framework\Exception\StateException;
use \Magento\Framework\Serialize\Serializer\Json as JsonSeliarizer;

/**
 * Class AttachmentRepository
 * @package LizardMedia\ProductAttachment\Model
 */
class AttachmentRepository implements AttachmentRepositoryInterface
{
    /**
     * @var \LizardMedia\ProductAttachment\Api\Data\AttachmentFactoryInterface
     */
    private $attachmentFactory;


    /**
     * @var \LizardMedia\ProductAttachment\Api\Data\File\ContentUploaderInterface
     */
    private $fileContentUploader;


    /**
     * @var \LizardMedia\ProductAttachment\Model\Attachment\ContentValidator
     */
    private $contentValidator;


    /**
     * @var \LizardMedia\ProductAttachment\Model\AttachmentRepository\SearchResultProcessor
     */
    private $searchResultProcessor;


    /**
     * @var \LizardMedia\ProductAttachment\Model\Product\TypeHandler\Attachment
     */
    private $attachmentTypeHandler;


    /**
     * @var \LizardMedia\ProductAttachment\Model\ResourceModel\Attachment\CollectionFactory
     */
    private $attachmentCollectionFactory;


    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;


    /**
     * @var \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface
     */
    private $extensionAttributesJoinProcessor;


    /**
     * @var \Magento\Framework\Api\SearchResultsInterfaceFactory
     */
    private $searchResultFactory;


    /**
     * @var \Magento\Framework\EntityManager\MetadataPool
     */
    private $metadataPool;


    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $jsonSeliarizer;


    /**
     * @param \LizardMedia\ProductAttachment\Api\Data\AttachmentFactoryInterface $attachmentFactory
     * @param \LizardMedia\ProductAttachment\Api\Data\File\ContentUploaderInterface $fileContentUploader
     * @param \LizardMedia\ProductAttachment\Model\Attachment\ContentValidator $contentValidator
     * @param \LizardMedia\ProductAttachment\Model\AttachmentRepository\SearchResultProcessor $searchResultProcessor
     * @param \LizardMedia\ProductAttachment\Model\Product\TypeHandler\Attachment $attachmentTypeHandler
     * @param \LizardMedia\ProductAttachment\Model\ResourceModel\Attachment\CollectionFactory $attachmentCollectionFactory
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param \Magento\Framework\Api\SearchResultsInterfaceFactory $searchResultsFactory
     * @param \Magento\Framework\EntityManager\MetadataPool $metadataPool
     * @param \Magento\Framework\Serialize\Serializer\Json $jsonSeliarizer
     */
    public function __construct(
        AttachmentFactoryInterface $attachmentFactory,
        ContentUploaderInterface $fileContentUploader,
        ContentValidator $contentValidator,
        SearchResultProcessor $searchResultProcessor,
        AttachmentHandler $attachmentTypeHandler,
        AttachmentCollectionFactory $attachmentCollectionFactory,
        ProductRepositoryInterface $productRepository,
        JoinProcessorInterface $extensionAttributesJoinProcessor,
        SearchResultsInterfaceFactory $searchResultsFactory,
        MetadataPool $metadataPool,
        JsonSeliarizer $jsonSeliarizer
    ) {
        $this->attachmentFactory = $attachmentFactory;
        $this->fileContentUploader = $fileContentUploader;
        $this->contentValidator = $contentValidator;
        $this->searchResultProcessor = $searchResultProcessor;
        $this->attachmentTypeHandler = $attachmentTypeHandler;
        $this->attachmentCollectionFactory = $attachmentCollectionFactory;
        $this->productRepository = $productRepository;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
        $this->searchResultFactory = $searchResultsFactory;
        $this->metadataPool = $metadataPool;
        $this->jsonSeliarizer = $jsonSeliarizer;
    }


    /**
     * @param int $id
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     *
     * @return \LizardMedia\ProductAttachment\Api\Data\AttachmentInterface
     */
    public function getById($id) : AttachmentInterface
    {
        $attachment = $this->attachmentFactory->create();
        $attachment->getResource()->load($attachment, $id);
        if (!$attachment->getId()) {
            throw new NoSuchEntityException(__('Unable to find attachment with id "%1"', $id));
        }
        return $attachment;
    }


    /**
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @param int $storeId
     *
     * @return \Magento\Framework\Api\SearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria, int $storeId = 0) : SearchResultsInterface
    {
        $collection = $this->attachmentCollectionFactory->create();
        /** @var $collection \LizardMedia\ProductAttachment\Model\ResourceModel\Attachment\Collection */

        $this->searchResultProcessor->addFiltersToCollection($searchCriteria, $collection);
        $this->searchResultProcessor->addSortOrdersToCollection($searchCriteria, $collection);
        $this->searchResultProcessor->addPagingToCollection($searchCriteria, $collection);

        $collection->load();

        $searchResult = $this->buildSearchResult($searchCriteria, $collection);
        $searchResultRebuilt = [];

        foreach ($searchResult->getItems() as $attachment) {
            $attachment->setStoreId($storeId);
            $attachment->getResource()->loadItemTitle($attachment);
            $searchResultRebuilt[] = $this->buildAttachment($attachment);
        }

        return $searchResult->setItems($searchResultRebuilt);
    }


    /**
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @param \LizardMedia\ProductAttachment\Model\ResourceModel\Attachment\Collection $collection
     *
     * @return \Magento\Framework\Api\SearchResultsInterface $searchResults
     */
    private function buildSearchResult(SearchCriteriaInterface $searchCriteria, AttachmentCollection $collection)
    {
        $searchResults = $this->searchResultFactory->create();

        /** @var $searchResults \Magento\Framework\Api\SearchResultsInterface */

        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());

        return $searchResults;
    }


    /**
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     *
     * @throws \Exception
     *
     * @return \LizardMedia\ProductAttachment\Api\Data\AttachmentInterface[]
     */
    public function getAttachmentsByProduct(ProductInterface $product) : array
    {
        $attachmentList = [];
        $attachments = $this->getAttachments($product);

        foreach ($attachments as $attachment) {
            $attachmentList[] = $this->buildAttachment($attachment);
        }

        return $attachmentList;
    }


    /**
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     *
     * @throws \Exception
     *
     * @return \LizardMedia\ProductAttachment\Model\ResourceModel\Attachment\Collection
     */
    private function getAttachments(ProductInterface $product) : ?Collection
    {
        if ($product->getProductAttachments() === null) {
            $attachmentCollection = $this->attachmentCollectionFactory->create()
                ->addProductToFilter([$product->getEntityId()])
                ->addTitleToResult((int) $product->getStoreId());

            $this->extensionAttributesJoinProcessor->process($attachmentCollection);
            $product->setProductAttachments($attachmentCollection);
        }

        return $product->getProductAttachments();
    }


    /**
     * Attachment is build using data from different tables.
     *
     * @param \LizardMedia\ProductAttachment\Api\Data\AttachmentInterface $resourceData
     *
     * @return \LizardMedia\ProductAttachment\Api\Data\AttachmentInterface
     */
    protected function buildAttachment($resourceData) : AttachmentInterface
    {
        $attachment = $this->attachmentFactory->create();
        $this->setBasicFields($resourceData, $attachment);
        return $attachment;
    }


    /**
     * @param \LizardMedia\ProductAttachment\Api\Data\AttachmentInterface $resourceData
     * @param \LizardMedia\ProductAttachment\Api\Data\AttachmentInterface $dataObject
     *
     * @return void
     */
    protected function setBasicFields(AttachmentInterface $resourceData, AttachmentInterface $dataObject) : void
    {
        $dataObject->setId($resourceData->getId());

        $storeTitle = $resourceData->getStoreTitle();
        $title = $resourceData->getTitle();

        if (!empty($storeTitle)) {
            $dataObject->setTitle($storeTitle);
        } else {
            $dataObject->setTitle($title);
        }

        $dataObject->setSortOrder($resourceData->getSortOrder());
        $dataObject->setProductId($resourceData->getProductId());
        $dataObject->setAttachmentType($resourceData->getAttachmentType());

        if ($resourceData->getAttachmentFile()) {
            $dataObject->setAttachmentFile($resourceData->getAttachmentFile());
        }

        if ($resourceData->getAttachmentUrl()) {
            $dataObject->setAttachmentUrl($resourceData->getAttachmentUrl());
        }
    }


    /**
     * @param string $sku
     * @param \LizardMedia\ProductAttachment\Api\Data\AttachmentInterface $attachment
     * @param bool $isGlobalScopeContent
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Exception
     *
     * @return int
     */
    public function save(string $sku, AttachmentInterface $attachment, bool $isGlobalScopeContent = true) : int
    {
        $product = $this->productRepository->get($sku, true);

        $id = (int) $attachment->getId();

        if ($id) {
            return $this->updateAttachment($product, $attachment, $isGlobalScopeContent);
        } else {
            $validateAttachmentContent = !($attachment->getAttachmentType() === Download::LINK_TYPE_FILE
                && $attachment->getAttachmentFile());

            if (!$this->contentValidator->isValid($attachment, $validateAttachmentContent)) {
                throw new InputException(__('Provided attachment information is invalid.'));
            }

            if (!in_array(
                $attachment->getAttachmentType(),
                [Download::LINK_TYPE_URL, Download::LINK_TYPE_FILE],
                true
            )) {
                throw new InputException(__('Invalid attachment type.'));
            }

            $title = $attachment->getTitle();

            if (empty($title)) {
                throw new InputException(__('Attachment title cannot be empty.'));
            }

            return $this->saveAttachment($product, $attachment, $isGlobalScopeContent);
        }
    }


    /**
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @param \LizardMedia\ProductAttachment\Api\Data\AttachmentInterface $attachment
     * @param bool $isGlobalScopeContent
     *
     * @return int
     */
    protected function saveAttachment(
        ProductInterface $product,
        AttachmentInterface $attachment,
        bool $isGlobalScopeContent
    ) : int {
        $attachmentData = [
            Attachment::ID => (int) $attachment->getId(),
            'is_delete' => 0,
            Attachment::ATTACHMENT_TYPE => $attachment->getAttachmentType(),
            Attachment::SORT_ORDER => $attachment->getSortOrder(),
            Attachment::TITLE => $attachment->getTitle(),
        ];

        if ($attachment->getAttachmentType() === Download::LINK_TYPE_FILE
            && $attachment->getAttachmentFile() === null) {
            $attachmentData[Download::LINK_TYPE_FILE] = $this->jsonSeliarizer->serialize(
                [
                    $this->fileContentUploader->upload($attachment->getAttachmentFileContent(), 'attachment'),
                ]
            );
        } elseif ($attachment->getAttachmentType() === Download::LINK_TYPE_URL) {
            $attachmentData[Attachment::ATTACHMENT_URL] = $attachment->getAttachmentUrl();
        } else {
            $attachmentData[Download::LINK_TYPE_FILE] = $this->jsonSeliarizer->serialize(
                [
                    [
                        Download::LINK_TYPE_FILE => $attachment->getAttachmentFile(),
                        'status' => 'old',
                    ],
                ]
            );
        }

        $downloadableData = ['attachment' => [$attachmentData]];

        if ($isGlobalScopeContent) {
            $product->setStoreId(0);
        }

        $this->attachmentTypeHandler->save($product, $downloadableData);
        return $product->getLastAddedAttachmentId();
    }


    /**
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @param \LizardMedia\ProductAttachment\Api\Data\AttachmentInterface $attachment
     * @param bool $isGlobalScopeContent
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Exception
     *
     * @return int
     */
    protected function updateAttachment(
        ProductInterface $product,
        AttachmentInterface $attachment,
        bool $isGlobalScopeContent
    ) : int {
        $id = (int) $attachment->getId();

        $existingAttachment = $this->attachmentFactory->create()->load($id);
        /** @var $existingAttachment \LizardMedia\ProductAttachment\Api\Data\AttachmentInterface */

        if (!$existingAttachment->getId()) {
            throw new NoSuchEntityException(__('There is no attachment with provided ID.'));
        }

        $linkFieldValue = $product->getData(
            $this->metadataPool->getMetadata(ProductInterface::class)->getLinkField()
        );

        if ($existingAttachment->getProductId() != $linkFieldValue) {
            throw new InputException(__('Provided attachment is not related to given product.'));
        }

        $validateFileContent = $attachment->getAttachmentFileContent() === null ? false : true;

        if (!$this->contentValidator->isValid($attachment, $validateFileContent)) {
            throw new InputException(__('Provided attachment information is invalid.'));
        }

        if ($isGlobalScopeContent) {
            $product->setStoreId(0);
        }

        $title = $attachment->getTitle();

        if (empty($title)) {
            if ($isGlobalScopeContent) {
                throw new InputException(__('Attachment title cannot be empty.'));
            }

            $existingAttachment->setTitle(null);
        } else {
            $existingAttachment->setTitle($attachment->getTitle());
        }

        if ($attachment->getAttachmentType() === Download::LINK_TYPE_FILE
            && $attachment->getAttachmentFileContent() === null) {
            $attachment->setAttachmentFile($existingAttachment->getAttachmentFile());
        }

        $this->saveAttachment($product, $attachment, $isGlobalScopeContent);

        return $existingAttachment->getId();
    }


    /**
     * @param int $id
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\StateException
     *
     * @return bool
     */
    public function delete(int $id) : bool
    {
        /** @var $attachment \LizardMedia\ProductAttachment\Api\Data\AttachmentInterface */
        $attachment = $this->attachmentFactory->create()->load($id);

        if (!(int) $attachment->getId()) {
            throw new NoSuchEntityException(__('There is no attachment with provided ID.'));
        }

        try {
            $attachment->delete();
        } catch (\Exception $exception) {
            throw new StateException(__('Cannot delete attachment with id "%1"', $attachment->getId()), $exception);
        }

        return true;
    }
}
