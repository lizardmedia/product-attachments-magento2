<?php

declare(strict_types = 1);

/**
 * File: AttachmentRepositoryInterface.php
 *
 * @author Bartosz Kubicki bartosz.kubicki@lizardmedia.pl>
 * @copyright Copyright (C) 2018 Lizard Media (http://lizardmedia.pl)
 */

namespace LizardMedia\ProductAttachment\Api;

use \LizardMedia\ProductAttachment\Api\Data\AttachmentInterface;
use \Magento\Catalog\Api\Data\ProductInterface;
use \Magento\Framework\Api\SearchCriteriaInterface;
use \Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface AttachmentRepositoryInterface
 * @package LizardMedia\ProductAttachment\Api
 */
interface AttachmentRepositoryInterface
{
    /**
     * @param int $id
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     *
     * @return \LizardMedia\ProductAttachment\Api\Data\AttachmentInterface
     */
    public function getById($id) : AttachmentInterface;


    /**
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @param int $storeId
     *
     * @return \Magento\Framework\Api\SearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria, int $storeId = 0) : SearchResultsInterface;


    /**
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     *
     * @return \LizardMedia\ProductAttachment\Api\Data\AttachmentInterface[]
     */
    public function getAttachmentsByProduct(ProductInterface $product) : array;


    /**
     * @param string $sku
     * @param \LizardMedia\ProductAttachment\Api\Data\AttachmentInterface $attachment
     * @param bool $isGlobalScopeContent
     *
     * @return int
     */
    public function save(string $sku, AttachmentInterface $attachment, bool $isGlobalScopeContent = true) : int;


    /**
     * @param int $id
     *
     * @return bool
     */
    public function delete(int $id) : bool;
}
