<?php

declare(strict_types = 1);

/**
 * File: SearchResultProcessor.php
 *
 * @author Bartosz Kubicki bartosz.kubicki@lizardmedia.pl>
 * @copyright Copyright (C) 2018 Lizard Media (http://lizardmedia.pl)
 */

namespace LizardMedia\ProductAttachment\Model\AttachmentRepository;

use \Magento\Framework\Api\SearchCriteriaInterface;
use \Magento\Framework\Api\SortOrder;
use \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class SearchResultProcessor
 * @package LizardMedia\ProductAttachments\Model\AttachmentRepository
 */
class SearchResultProcessor
{
    /**
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @param \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     *
     * @return void
     */
    public function addFiltersToCollection(
        SearchCriteriaInterface $searchCriteria,
        AbstractCollection $collection
    ) : void {
        foreach ($searchCriteria->getFilterGroups() as $filterGroup) {
            $fields = $conditions = [];
            foreach ($filterGroup->getFilters() as $filter) {
                $fields[] = $filter->getField();
                $conditions[] = [$filter->getConditionType() => $filter->getValue()];
            }
            $collection->addFieldToFilter($fields, $conditions);
        }
    }


    /**
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @param \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     *
     * @return void
     */
    public function addSortOrdersToCollection(
        SearchCriteriaInterface $searchCriteria,
        AbstractCollection $collection
    ) : void {
        foreach ((array) $searchCriteria->getSortOrders() as $sortOrder) {
            $direction = $sortOrder->getDirection() == SortOrder::SORT_ASC ? SortOrder::SORT_ASC : SortOrder::SORT_DESC;
            $collection->addOrder($sortOrder->getField(), $direction);
        }
    }


    /**
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @param \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     *
     * @return void
     */
    public function addPagingToCollection(
        SearchCriteriaInterface $searchCriteria,
        AbstractCollection $collection
    ) : void {
        $collection->setPageSize($searchCriteria->getPageSize());
        $collection->setCurPage($searchCriteria->getCurrentPage());
    }
}
