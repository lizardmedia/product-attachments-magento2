<?php

declare(strict_types = 1);

/**
 * File: PurchasedItemsAttachmentProviderInterface.php
 *
 * @author Bartosz Kubicki bartosz.kubicki@lizardmedia.pl>
 * @copyright Copyright (C) 2018 Lizard Media (http://lizardmedia.pl)
 */

namespace LizardMedia\ProductAttachment\Api;

use \Magento\Customer\Api\Data\CustomerInterface;
use \LizardMedia\ProductAttachment\Model\ResourceModel\Attachment\Collection;

/**
 * Interface PurchasedItemsAttachmentProviderInterface
 * @package LizardMedia\ProductAttachment\Api
 */
interface PurchasedItemsAttachmentProviderInterface
{
    /**
     * Return type: Collection used instead repository,
     * as in block later collection can be used to init pager
     *
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     *
     * @return \LizardMedia\ProductAttachment\Model\ResourceModel\Attachment\Collection
     */
    public function get(CustomerInterface $customer) : Collection;
}
