<?php

declare(strict_types = 1);

/**
 * File: AttachmentList.php
 *
 * @author Bartosz Kubicki bartosz.kubicki@lizardmedia.pl>
 * @copyright Copyright (C) 2018 Lizard Media (http://lizardmedia.pl)
 */

namespace LizardMedia\ProductAttachment\Block\Customer\Account;

use \LizardMedia\ProductAttachment\Api\Data\AttachmentInterface;
use \LizardMedia\ProductAttachment\Api\PurchasedItemsAttachmentProviderInterface;
use \LizardMedia\ProductAttachment\Api\SettingsInterface;
use \LizardMedia\ProductAttachment\Model\ResourceModel\Attachment\Collection;
use \Magento\Customer\Helper\Session\CurrentCustomer;
use \Magento\Framework\Message\ManagerInterface;
use \Magento\Framework\View\Element\Template;
use \Magento\Framework\View\Element\Template\Context;

/**
 * Class Attachment
 * @package LizardMedia\ProductAttachment\Block\Customer\Account
 */
class AttachmentList extends Template
{
    /**
     * @var bool
     */
    private $isCollectionLoaded = false;


    /**
     * @var \LizardMedia\ProductAttachment\Api\PurchasedItemsAttachmentProviderInterface
     */
    private $purchasedItemsAttachmentProvider;


    /**
     * @var \LizardMedia\ProductAttachment\Api\SettingsInterface
     */
    private $settings;


    /**
     * @var \LizardMedia\ProductAttachment\Model\ResourceModel\Attachment\Collection
     */
    private $collection;


    /**
     * @var \Magento\Customer\Helper\Session\CurrentCustomer
     */
    private $currentCustomer;


    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $messageManager;


    /**
     * @param \LizardMedia\ProductAttachment\Api\PurchasedItemsAttachmentProviderInterface $purchasedItemsAttachmentProvider
     * @param \LizardMedia\ProductAttachment\Api\SettingsInterface $settings
     * @param \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param array $data
     */
    public function __construct(
        PurchasedItemsAttachmentProviderInterface $purchasedItemsAttachmentProvider,
        SettingsInterface $settings,
        CurrentCustomer $currentCustomer,
        Context $context,
        ManagerInterface $messageManager,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->purchasedItemsAttachmentProvider = $purchasedItemsAttachmentProvider;
        $this->settings = $settings;
        $this->currentCustomer = $currentCustomer;
        $this->messageManager = $messageManager;
    }


    /**
     * @throws \Exception
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        if ($attachments = $this->getAttachments()) {
            $this->getChildBlock('lizard_attachment_list_pager')
                ->setCollection($attachments)
                ->setPath('downloadable/customer/attachment_index');
        }
        return $this;
    }


    /**
     * @return \LizardMedia\ProductAttachment\Model\ResourceModel\Attachment\Collection
     */
    public function getAttachments() : Collection
    {
        $customer = $this->currentCustomer->getCustomer();

        if (!$this->collection && $this->isCollectionLoaded === false) {
            try {
                $this->collection = $this->purchasedItemsAttachmentProvider->get($customer);
                $this->isCollectionLoaded = true;
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(
                    __('Something went wrong, please contact the store support')
                );
            }
        }

        return $this->collection;
    }


    /**
     * @return string
     */
    public function getBackUrl() : string
    {
        if ($this->getRefererUrl()) {
            return $this->getRefererUrl();
        }

        return $this->getUrl('customer/account/');
    }


    /**
     * @param \LizardMedia\ProductAttachment\Api\Data\AttachmentInterface $attachment
     *
     * @return string
     */
    public function getDownloadUrl(AttachmentInterface $attachment) : string
    {
        return $this->getUrl('downloadable/download/attachment', ['id' => $attachment->getId(), '_secure' => true]);
    }


    /**
     * @return bool
     */
    public function getIsOpenInNewWindow() : bool
    {
        return $this->settings->areLinksOpenedInNewWindow();
    }
}
