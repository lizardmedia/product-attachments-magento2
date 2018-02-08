<?php

declare(strict_types = 1);

/**
 * File: AttachmentFactory.php
 *
 * @author Bartosz Kubicki bartosz.kubicki@lizardmedia.pl>
 * @copyright Copyright (C) 2018 Lizard Media (http://lizardmedia.pl)
 */

namespace LizardMedia\ProductAttachment\Model;

use \LizardMedia\ProductAttachment\Api\Data\AttachmentFactoryInterface;
use \LizardMedia\ProductAttachment\Api\Data\AttachmentInterface;
use \Magento\Framework\ObjectManagerInterface;

/**
 * Class AttachmentFactory
 * @package LizardMedia\ProductAttachment\Model
 */
class AttachmentFactory implements AttachmentFactoryInterface
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;


    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }


    /**
     * @param array $data
     *
     * @return \LizardMedia\ProductAttachment\Api\Data\AttachmentInterface
     */
    public function create(array $data = [])
    {
        return $this->objectManager->create(AttachmentInterface::class, $data);
    }
}
