<?php

declare(strict_types = 1);

/**
 * File: Builder.php
 *
 * @author Bartosz Kubicki bartosz.kubicki@lizardmedia.pl>
 * @copyright Copyright (C) 2018 Lizard Media (http://lizardmedia.pl)
 */

namespace LizardMedia\ProductAttachment\Model\Attachment;

use \LizardMedia\ProductAttachment\Api\Data\AttachmentFactoryInterface;
use \LizardMedia\ProductAttachment\Api\Data\AttachmentInterface;
use LizardMedia\ProductAttachment\Model\Attachment;
use \Magento\Downloadable\Helper\Download;
use \Magento\Downloadable\Helper\File;
use \Magento\Framework\Api\DataObjectHelper;
use \Magento\Framework\Exception\LocalizedException;
use \Magento\Framework\DataObject\Copy;

/**
 * Class Builder
 * @package LizardMedia\ProductAttachment\Model\Attachment
 */
class Builder
{
    /**
     * @var \LizardMedia\ProductAttachment\Api\Data\AttachmentFactoryInterface
     */
    private $componentFactory;


    /**
     * @var \LizardMedia\ProductAttachment\Api\Data\AttachmentInterface
     */
    private $component;


    /**
     * @var \Magento\Downloadable\Helper\File
     */
    private $downloadableFile;


    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    private $dataObjectHelper;


    /**
     * @var \Magento\Framework\DataObject\Copy
     */
    private $objectCopyService;


    /**
     * @var array
     */
    private $data = [];


    /**
     * @param \LizardMedia\ProductAttachment\Api\Data\AttachmentFactoryInterface $componentFactory
     * @param \Magento\Downloadable\Helper\File $downloadableFile
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     * @param \Magento\Framework\DataObject\Copy $objectCopyService
     */
    public function __construct(
        AttachmentFactoryInterface $componentFactory,
        File $downloadableFile,
        DataObjectHelper $dataObjectHelper,
        Copy $objectCopyService
    ) {
        $this->componentFactory = $componentFactory;
        $this->downloadableFile = $downloadableFile;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->objectCopyService = $objectCopyService;
    }


    /**
     * @param array $data
     *
     * @return $this
     */
    public function setData(array $data)
    {
        $this->data = $data;
        return $this;
    }


    /**
     * @param \LizardMedia\ProductAttachment\Api\Data\AttachmentInterface $attachment
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     *
     * @return \LizardMedia\ProductAttachment\Api\Data\AttachmentInterface $attachment
     */
    public function build(AttachmentInterface $attachment)
    {
        $downloadableData = $this->objectCopyService->getDataFromFieldset(
            'downloadable_data',
            'to_attachment',
            $this->data
        );


        $this->dataObjectHelper->populateWithArray(
            $attachment,
            array_merge(
                $this->data,
                $downloadableData
            ),
            AttachmentInterface::class
        );


        if ($attachment->getAttachmentType() === Download::LINK_TYPE_FILE) {
            if (!isset($this->data['file'])) {
                throw new LocalizedException(__('Attachment file not provided'));
            }


            $fileName = $this->downloadableFile->moveFileFromTmp(
                $this->getComponent()->getBaseTmpPath(),
                $this->getComponent()->getBasePath(),
                $this->data['file']
            );

            $attachment->setAttachmentFile($fileName);
        }

        if (!$attachment->getSortOrder()) {
            $attachment->setSortOrder(1);
        }

        $this->resetData();

        return $attachment;
    }


    /**
     * @return void
     */
    private function resetData() : void
    {
        $this->data = [];
    }


    /**
     * @return \LizardMedia\ProductAttachment\Api\Data\AttachmentInterface
     */
    private function getComponent()
    {
        if (!$this->component) {
            $this->component = $this->componentFactory->create();
        }

        return $this->component;
    }
}
