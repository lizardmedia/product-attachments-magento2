<?php

declare(strict_types = 1);

/**
 * File: PurchasedItemsAttachmentProvider.php
 *
 * @author Bartosz Kubicki bartosz.kubicki@lizardmedia.pl>
 * @copyright Copyright (C) 2018 Lizard Media (http://lizardmedia.pl)
 */

namespace LizardMedia\ProductAttachment\Model\Customer;

use \LizardMedia\ProductAttachment\Api\PurchasedItemsAttachmentProviderInterface;
use \LizardMedia\ProductAttachment\Model\Attachment;
use \LizardMedia\ProductAttachment\Model\ResourceModel\Attachment\Collection;
use \LizardMedia\ProductAttachment\Model\ResourceModel\Attachment\CollectionFactory;
use \Magento\Customer\Api\Data\CustomerInterface;
use \Magento\Framework\Api\SearchCriteriaBuilder;
use \Magento\Framework\Api\SearchCriteriaInterface;
use \Magento\Framework\Data\Collection as DataCollection;
use \Magento\Sales\Api\OrderItemRepositoryInterface;
use \Magento\Sales\Api\OrderRepositoryInterface;
use \Magento\Store\Model\StoreManagerInterface;

/**
 * Class PurchasedItemsAttachmentProvider
 * @package LizardMedia\ProductAttachment\Model
 */
class PurchasedItemsAttachmentProvider implements PurchasedItemsAttachmentProviderInterface
{
    /**
     * @var \LizardMedia\ProductAttachment\Model\ResourceModel\Attachment\CollectionFactory
     */
    private $collectionFactory;


    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;


    /**
     * @var \Magento\Sales\Api\OrderItemRepositoryInterface
     */
    private $orderItemRepository;


    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    private $orderRepository;


    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;


    /**
     * @param \LizardMedia\ProductAttachment\Model\ResourceModel\Attachment\CollectionFactory $collectionFactory
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Sales\Api\OrderItemRepositoryInterface $orderItemRepository
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        OrderItemRepositoryInterface $orderItemRepository,
        OrderRepositoryInterface $orderRepository,
        StoreManagerInterface $storeManager
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->orderItemRepository = $orderItemRepository;
        $this->orderRepository = $orderRepository;
        $this->storeManager = $storeManager;
    }


    /**
     *
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     *
     * @throws \Exception
     *
     * @return mixed \LizardMedia\ProductAttachment\Model\ResourceModel\Attachment\Collection | null
     */
    public function get(CustomerInterface $customer) : Collection
    {
        $orders = $this->getCustomerOrders($customer);

        if (empty($orders)) {
            return $this->returnEmptyAttachmentCollection();
        }

        $orderIds = [];
        foreach ($orders as $order) {
            $orderIds[] = $order->getEntityId();
        }

        $orderItems = $this->getOrderItems($orderIds);

        if (empty($orderItems)) {
            return $this->returnEmptyAttachmentCollection();
        }

        $productIds = [];
        foreach ($orderItems as $orderItem) {
            $productIds[] = $orderItem->getProductId();
        }

        return $this->getAttachments($productIds);
    }


    /**
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     *
     * @return \Magento\Sales\Api\Data\OrderInterface[]
     */
    private function getCustomerOrders(CustomerInterface $customer) : array
    {
        $searchCriteria = $this->prepareCustomerOrdersSearchCriteria($customer);
        $searchResult = $this->orderRepository->getList($searchCriteria);
        if ($searchResult->getTotalCount() > 0) {
            return $searchResult->getItems();
        }

        return [];
    }


    /**
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     *
     * @return \Magento\Framework\Api\SearchCriteriaInterface
     */
    private function prepareCustomerOrdersSearchCriteria(CustomerInterface $customer) : SearchCriteriaInterface
    {
        $this->searchCriteriaBuilder->addFilter('customer_id', $customer->getId(), 'eq');
        return $this->searchCriteriaBuilder->create();
    }


    /**
     * @param array $orderIds
     *
     * @return \Magento\Sales\Api\Data\OrderItemInterface[]
     */
    private function getOrderItems(array $orderIds) : array
    {
        $searchCriteria = $this->prepareOrderItemsSearchCriteria($orderIds);
        $searchResult = $this->orderItemRepository->getList($searchCriteria);
        if ($searchResult->getTotalCount() > 0) {
            return $searchResult->getItems();
        }

        return [];
    }


    /**
     * @param array $orderItemsIds
     *
     * @return \Magento\Framework\Api\SearchCriteriaInterface
     */
    private function prepareOrderItemsSearchCriteria(array $orderItemsIds) : SearchCriteriaInterface
    {
        $this->searchCriteriaBuilder->addFilter('order_id', $orderItemsIds, 'in');
        return $this->searchCriteriaBuilder->create();
    }


    /**
     * @throws \Exception
     *
     * @return \LizardMedia\ProductAttachment\Model\ResourceModel\Attachment\Collection
     */
    private function returnEmptyAttachmentCollection()
    {
        $collection = $this->instantiateAttachmentCollection();
        $collection->addProductToFilter([]);
        return $collection;
    }


    /**
     * @param array $productIds
     *
     * @throws \Exception
     *
     * @return \LizardMedia\ProductAttachment\Model\ResourceModel\Attachment\Collection
     */
    private function getAttachments(array $productIds) : Collection
    {
        $collection = $this->instantiateAttachmentCollection();
        $collection->addProductToFilter($productIds)
                   ->addTitleToResult((int) $this->storeManager->getStore()->getId());

        $collection->joinProductTitle((int) $this->storeManager->getStore()->getId());
        $collection->setOrder(Attachment::PRODUCT_ID, DataCollection::SORT_ORDER_DESC);

        return $collection;
    }


    /**
     * @return \LizardMedia\ProductAttachment\Model\ResourceModel\Attachment\Collection
     */
    private function instantiateAttachmentCollection() : Collection
    {
        return $this->collectionFactory->create();
    }
}
