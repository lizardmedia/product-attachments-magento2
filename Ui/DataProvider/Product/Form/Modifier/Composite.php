<?php

declare(strict_types = 1);

/**
 * File: Composite.php
 *
 * @author Bartosz Kubicki bartosz.kubicki@lizardmedia.pl>
 * @copyright Copyright (C) 2018 Lizard Media (http://lizardmedia.pl)
 */

namespace LizardMedia\ProductAttachment\Ui\DataProvider\Product\Form\Modifier;

use \Magento\Catalog\Model\Locator\LocatorInterface;
use \Magento\Catalog\Model\Product\Type as CatalogType;
use \Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use \Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableType;
use \Magento\Downloadable\Model\Product\Type as DownloadableType;
use \Magento\GroupedProduct\Model\Product\Type\Grouped as GroupedType;
use \Magento\Ui\DataProvider\Modifier\ModifierFactory;

/**
 * Class Composite
 * @package LizardMedia\ProductAttachment\Ui\DataProvider\Product\Form\Modifier
 */
class Composite extends AbstractModifier
{
    /**
     * Path elements.
     *
     * @var string
     */
    const CHILDREN_PATH = 'product_attachment/children';
    const CONTAINER_ATTACHMENTS = 'container_attachments';


    /**
     * @var array
     */
    private $modifiers = [];


    /**
     * @var \Magento\Catalog\Model\Locator\LocatorInterface
     */
    private $locator;


    /**
     * @var \Magento\Ui\DataProvider\Modifier\ModifierFactory
     */
    private $modifierFactory;


    /**
     * @var \Magento\Ui\DataProvider\Modifier\ModifierInterface[]
     */
    private $modifiersInstances = [];


    /**
     * @param \Magento\Catalog\Model\Locator\LocatorInterface $locator
     * @param \Magento\Ui\DataProvider\Modifier\ModifierFactory $modifierFactory
     * @param array $modifiers
     */
    public function __construct(
        LocatorInterface $locator,
        ModifierFactory $modifierFactory,
        array $modifiers = []
    ) {
        $this->locator = $locator;
        $this->modifierFactory = $modifierFactory;
        $this->modifiers = $modifiers;
    }


    /**
     * @param array $data
     *
     * @return array $data
     */
    public function modifyData(array $data) : array
    {
        if ($this->canShowAttachmentPanel()) {
            foreach ($this->getModifiers() as $modifier) {
                $data = $modifier->modifyData($data);
            }
        }

        return $data;
    }


    /**
     * @param array $meta
     *
     * @return array $meta
     */
    public function modifyMeta(array $meta) : array
    {
        if ($this->canShowAttachmentPanel()) {
            foreach ($this->getModifiers() as $modifier) {
                $meta = $modifier->modifyMeta($meta);
            }
        }

        return $meta;
    }


    /**
     * Get modifiers list
     *
     * @return \Magento\Ui\DataProvider\Modifier\ModifierInterface[]
     */
    private function getModifiers() : array
    {
        if (empty($this->modifiersInstances)) {
            foreach ($this->modifiers as $modifierClass) {
                $this->modifiersInstances[$modifierClass] = $this->modifierFactory->create($modifierClass);
            }
        }

        return $this->modifiersInstances;
    }


    /**
     * @return bool
     */
    private function canShowAttachmentPanel() : bool
    {
        $productTypes = [
            CatalogType::TYPE_SIMPLE,
            CatalogType::TYPE_VIRTUAL,
            CatalogType::TYPE_BUNDLE,
            ConfigurableType::TYPE_CODE,
            DownloadableType::TYPE_DOWNLOADABLE,
            GroupedType::TYPE_CODE
        ];

        return in_array($this->locator->getProduct()->getTypeId(), $productTypes);
    }
}
