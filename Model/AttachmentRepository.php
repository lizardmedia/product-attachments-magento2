<?php

declare(strict_types = 1);

/**
 * File: AttachmentRepository.php
 *
 * @author Bartosz Kubicki bartosz.kubicki@lizardmedia.pl>
 * @copyright Copyright (C) 2018 Lizard Media (http://lizardmedia.pl)
 */

namespace LizardMedia\ProductAttachment\Model;

use Exception;
use LizardMedia\ProductAttachment\Api\AttachmentRepositoryInterface;
use LizardMedia\ProductAttachment\Api\Data\AttachmentFactoryInterface;
use LizardMedia\ProductAttachment\Api\Data\AttachmentInterface;
use LizardMedia\ProductAttachment\Api\Data\File\ContentUploaderInterface;
use LizardMedia\ProductAttachment\Helper\Various as VariousHelper;
use LizardMedia\ProductAttachment\Model\AttachmentRepository\SearchResultProcessor;
use LizardMedia\ProductAttachment\Model\Attachment\ContentValidator;
use LizardMedia\ProductAttachment\Model\Product\TypeHandler\Attachment as AttachmentHandler;
use LizardMedia\ProductAttachment\Model\ResourceModel\Attachment\Collection as AttachmentCollection;
use LizardMedia\ProductAttachment\Model\ResourceModel\Attachment\Collection;
use LizardMedia\ProductAttachment\Model\ResourceModel\Attachment\CollectionFactory as AttachmentCollectionFactory;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Downloadable\Helper\Download;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Api\SearchResultsInterfaceFactory;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Serialize\Serializer\Json as JsonSeliarizer;

/**
 * Class AttachmentRepository
 * @package LizardMedia\ProductAttachment\Model
 */
class AttachmentRepository implements AttachmentRepositoryInterface
{
    /**
     * @var AttachmentFactoryInterface
     */
    private $attachmentFactory;

    /**
     * @var ContentUploaderInterface
     */
    private $fileContentUploader;

    /**
     * @var ContentValidator
     */
    private $contentValidator;

    /**
     * @var SearchResultProcessor
     */
    private $searchResultProcessor;

    /**
     * @var AttachmentHandler
     */
    private $attachmentTypeHandler;

    /**
     * @var AttachmentCollectionFactory
     */
    private $attachmentCollectionFactory;


    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;


    /**
     * @var JoinProcessorInterface
     */
    private $extensionAttributesJoinProcessor;


    /**
     * @var SearchResultsInterfaceFactory
     */
    private $searchResultFactory;


    /**
     * @var JsonSeliarizer
     */
    private $jsonSeliarizer;


    /**
     * @var VariousHelper
     */
    private $variousHelper;


    /**
     * @param AttachmentFactoryInterface $attachmentFactory
     * @param ContentUploaderInterface $fileContentUploader
     * @param ContentValidator $contentValidator
     * @param SearchResultProcessor $searchResultProcessor
     * @param AttachmentHandler $attachmentTypeHandler
     * @param AttachmentCollectionFactory $attachmentCollectionFactory
     * @param ProductRepositoryInterface $productRepository
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param SearchResultsInterfaceFactory $searchResultsFactory
     * @param JsonSeliarizer $jsonSeliarizer
     * @param VariousHelper $variousHelper
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
        JsonSeliarizer $jsonSeliarizer,
        VariousHelper $variousHelper
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
        $this->jsonSeliarizer = $jsonSeliarizer;
        $this->variousHelper = $variousHelper;
    }


    /**
     * @param int $id
     * @return AttachmentInterface
     * @throws NoSuchEntityException
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
     * @param SearchCriteriaInterface $searchCriteria
     * @param int $storeId
     *
     * @return SearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria, int $storeId = 0) : SearchResultsInterface
    {
        $collection = $this->attachmentCollectionFactory->create();
        /** @var $collection AttachmentCollection */

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
     * @param SearchCriteriaInterface $searchCriteria
     * @param AttachmentCollection $collection
     *
     * @return SearchResultsInterface $searchResults
     */
    private function buildSearchResult(SearchCriteriaInterface $searchCriteria, AttachmentCollection $collection)
    {
        $searchResults = $this->searchResultFactory->create();

        /** @var $searchResults SearchResultsInterface */

        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());

        return $searchResults;
    }


    /**
     * @param ProductInterface $product
     * @return AttachmentInterface[]
     * @throws Exception
     *
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
     * @param ProductInterface $product
     * @return AttachmentCollection
     * @throws Exception
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
     * @param AttachmentInterface $resourceData
     * @return AttachmentInterface
     */
    protected function buildAttachment($resourceData) : AttachmentInterface
    {
        $attachment = $this->attachmentFactory->create();
        $this->setBasicFields($resourceData, $attachment);
        return $attachment;
    }


    /**
     * @param AttachmentInterface $resourceData
     * @param AttachmentInterface $dataObject
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
     * @param AttachmentInterface $attachment
     * @param bool $isGlobalScopeContent
     * @return int
     * @throws InputException
     * @throws Exception
     *
     * @throws NoSuchEntityException
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
     * @param ProductInterface $product
     * @param AttachmentInterface $attachment
     * @param bool $isGlobalScopeContent
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
     * @param ProductInterface $product
     * @param AttachmentInterface $attachment
     * @param bool $isGlobalScopeContent
     * @return int
     * @throws InputException
     * @throws Exception
     * @throws NoSuchEntityException
     */
    protected function updateAttachment(
        ProductInterface $product,
        AttachmentInterface $attachment,
        bool $isGlobalScopeContent
    ): int {
        $id = (int) $attachment->getId();

        $existingAttachment = $this->attachmentFactory->create()->load($id);
        /** @var $existingAttachment AttachmentInterface */

        if (!$existingAttachment->getId()) {
            throw new NoSuchEntityException(__('There is no attachment with provided ID.'));
        }

        $linkFieldValue = $this->variousHelper->getLinkFieldValue();

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

        return (int) $existingAttachment->getId();
    }

    /**
     * @param int $id
     * @return bool
     * @throws StateException
     * @throws NoSuchEntityException
     */
    public function delete(int $id) : bool
    {
        /** @var $attachment AttachmentInterface */
        $attachment = $this->attachmentFactory->create()->load($id);

        if (!(int) $attachment->getId()) {
            throw new NoSuchEntityException(__('There is no attachment with provided ID.'));
        }

        try {
            $attachment->delete();
        } catch (Exception $exception) {
            throw new StateException(__('Cannot delete attachment with id "%1"', $attachment->getId()), $exception);
        }

        return true;
    }
}
