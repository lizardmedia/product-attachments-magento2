<?php

declare(strict_types = 1);

/**
 * File: InstallData.php
 *
 * @author Bartosz Kubicki bartosz.kubicki@lizardmedia.pl>
 * @copyright Copyright (C) 2018 Lizard Media (http://lizardmedia.pl)
 */

namespace LizardMedia\ProductAttachment\Setup;

use \Magento\Catalog\Api\Data\ProductAttributeInterface;
use \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use \Magento\Eav\Setup\EavSetupFactory;
use \Magento\Framework\Setup\InstallDataInterface;
use \Magento\Framework\Setup\ModuleContextInterface;
use \Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * Class InstallData
 * @package LizardMedia\ProductAttachment\Setup
 */
class InstallData implements InstallDataInterface
{
    /**
     * @var \Magento\Eav\Setup\EavSetupFactory
     */
    private $eavSetupFactory;


    /**
     * @param \Magento\Eav\Setup\EavSetupFactory $eavSetupFactory
     */
    public function __construct(EavSetupFactory $eavSetupFactory)
    {
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * @param \Magento\Framework\Setup\ModuleDataSetupInterface $setup
     * @param \Magento\Framework\Setup\ModuleContextInterface $context
     *
     * @return void
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        /** @var \Magento\Eav\Setup\EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

        $eavSetup->addAttribute(
            ProductAttributeInterface::ENTITY_TYPE_CODE,
            'attachments_title',
            [
                'type' => 'varchar',
                'input' => 'text',
                'frontend_class' => 'validate-no-html-tags validate-length maximum-length-255',
                'label' => 'Attachments title',

                'group' => 'product-details',
                'sort_order' => 115,


                'backend' => '',
                'frontend' => '',
                'source' => '',

                'default' => null,

                'wysiwyg_enabled' => false,
                'is_html_allowed_on_front' => false,

                'used_for_sort_by' => false,

                'global' => ScopedAttributeInterface::SCOPE_STORE,
                'visible' => false,
                'required' => false,
                'user_defined' => false,

                'searchable' => false,
                'visible_in_advanced_search' => false,
                'search_weight' => '',
                'filterable' => false,
                'filterable_in_search' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => false,
                'unique' => false,
                'apply_to' => '',
                'used_for_promo_rules' => false,

                'is_used_in_grid' => false,
                'is_visible_in_grid' => false,
                'is_filterable_in_grid' => false,

                'is_required_in_admin_store' => '',

                'system' => 0
            ]
        );
    }
}
