<?php

declare(strict_types = 1);

/**
 * File: AttachmentPanel.php
 *
 * @author Bartosz Kubicki bartosz.kubicki@lizardmedia.pl>
 * @copyright Copyright (C) 2018 Lizard Media (http://lizardmedia.pl)
 */

namespace LizardMedia\ProductAttachment\Ui\DataProvider\Product\Form\Modifier;

use \Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use \Magento\Catalog\Model\Locator\LocatorInterface;
use \Magento\Framework\Stdlib\ArrayManager;
use \Magento\Ui\Component\Form;

/**
 * Class AttachmentPanel
 * @package LizardMedia\ProductAttachment\Ui\DataProvider\Product\Form\Modifier
 */
class AttachmentPanel extends AbstractModifier
{
    /**
     * @var \Magento\Catalog\Model\Locator\LocatorInterface
     */
    private $locator;


    /**
     * @var \Magento\Framework\Stdlib\ArrayManager
     */
    private $arrayManager;


    /**
     * @var array
     */
    private $meta = [];


    /**
     * @param \Magento\Catalog\Model\Locator\LocatorInterface $locator
     * @param \Magento\Framework\Stdlib\ArrayManager $arrayManager
     */
    public function __construct(LocatorInterface $locator, ArrayManager $arrayManager)
    {
        $this->locator = $locator;
        $this->arrayManager = $arrayManager;
    }


    /**
     * @param array $data
     *
     * @return array $data
     */
    public function modifyData(array $data) : array
    {
        return $data;
    }


    /**
     * @param array $meta
     *
     * @return array $meta
     */
    public function modifyMeta(array $meta) : array
    {
        $this->meta = $meta;

        $panelConfig['arguments']['data']['config'] = [
            'componentType' => Form\Fieldset::NAME,
            'label' => __('Product attachments'),
            'collapsible' => true,
            'opened' => true,
            'sortOrder' => '900',
            'dataScope' => 'data'
        ];

        $this->meta = $this->arrayManager->set('product_attachment', $this->meta, $panelConfig);

        return $this->meta;
    }
}
