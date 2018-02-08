<?php

declare(strict_types = 1);

/**
 * File: InitForSave.php
 *
 * @author Bartosz Kubicki bartosz.kubicki@lizardmedia.pl>
 * @copyright Copyright (C) 2018 Lizard Media (http://lizardmedia.pl)
 */

namespace LizardMedia\ProductAttachment\Plugin;

use \LizardMedia\ProductAttachment\Api\Data\AttachmentFactoryInterface;
use \LizardMedia\ProductAttachment\Model\Attachment\Builder as AttachmentBuilder;
use \Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper;
use \Magento\Catalog\Model\Product;
use \Magento\Framework\App\RequestInterface;

/**
 * Class InitForSave
 * @package LizardMedia\ProductAttachment\Plugin
 */
class InitForSave
{
    /**
     * @var \LizardMedia\ProductAttachment\Api\Data\AttachmentFactoryInterface
     */
    private $attachmentFactory;


    /**
     * @var \LizardMedia\ProductAttachment\Model\Attachment\Builder
     */
    private $attachmentBuilder;


    /**
     * @var RequestInterface
     */
    private $request;


    /**
     * @param \LizardMedia\ProductAttachment\Api\Data\AttachmentFactoryInterface $attachmentFactory
     * @param \LizardMedia\ProductAttachment\Model\Attachment\Builder $attachmentBuilder
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(
        AttachmentFactoryInterface $attachmentFactory,
        AttachmentBuilder $attachmentBuilder,
        RequestInterface $request
    ) {
        $this->attachmentFactory = $attachmentFactory;
        $this->attachmentBuilder = $attachmentBuilder;
        $this->request = $request;
    }


    /**
     * @param \Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper $subject
     * @param \Magento\Catalog\Model\Product $product
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     *
     * @return \Magento\Catalog\Model\Product
     */
    public function afterInitialize(Helper $subject, Product $product)
    {
        if ($downloadable = $this->request->getPost('downloadable')) {
            if (isset($downloadable['attachment']) && is_array($downloadable['attachment'])) {
                $product->setDownloadableData($downloadable);
                $extension = $product->getExtensionAttributes();

                $attachments = [];
                foreach ($downloadable['attachment'] as $attachmentData) {
                    if (!$attachmentData ||
                        (isset($attachmentData['is_delete']) && (bool) $attachmentData['is_delete'])) {
                        continue;
                    } else {
                        $attachments[] = $this->attachmentBuilder->setData(
                            $attachmentData
                        )->build(
                            $this->attachmentFactory->create()
                        );
                    }
                }

                $extension->setProductAttachments($attachments);
                $product->setExtensionAttributes($extension);
            }
        }

        return $product;
    }
}
