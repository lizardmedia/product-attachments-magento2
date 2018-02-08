<?php

declare(strict_types = 1);

/**
 * File: InstallSchema.php
 *
 * @author Bartosz Kubicki bartosz.kubicki@lizardmedia.pl>
 * @copyright Copyright (C) 2018 Lizard Media (http://lizardmedia.pl)
 */

namespace LizardMedia\ProductAttachment\Setup;

use \LizardMedia\ProductAttachment\Model\Attachment;
use \Magento\Framework\Setup\InstallSchemaInterface;
use \Magento\Framework\Setup\ModuleContextInterface;
use \Magento\Framework\Setup\SchemaSetupInterface;
use \Magento\Framework\DB\Ddl\Table;

/**
 * Class InstallSchema
 * @package LizardMedia\ProductAttachment\Setup
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * @param \Magento\Framework\Setup\SchemaSetupInterface $setup
     * @param \Magento\Framework\Setup\ModuleContextInterface $context
     *
     * @throws \Zend_Db_Exception
     *
     * @return void
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $lizardmedia_product_attachments = $setup->getConnection()->newTable(
            $setup->getTable(Attachment::MAIN_TABLE)
        )->addColumn(
            Attachment::ID,
            Table::TYPE_INTEGER,
            11,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary'  => true],
            'Id'
        )->addColumn(
            Attachment::PRODUCT_ID,
            Table::TYPE_INTEGER,
            11,
            ['unsigned' => true, 'nullable' => false],
            'Product id'
        )->addColumn(
            Attachment::SORT_ORDER,
            Table::TYPE_INTEGER,
            11,
            ['unsigned' => true, 'nullable' => false],
            'Sort order'
        )->addColumn(
            Attachment::ATTACHMENT_TYPE,
            Table::TYPE_TEXT,
            21,
            ['nullable' => false],
            'Type'
        )->addColumn(
            Attachment::ATTACHMENT_FILE,
            Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            'File'
        )->addColumn(
            Attachment::ATTACHMENT_URL,
            Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            'File'
        )->addForeignKey(
            $setup->getFkName(
                Attachment::MAIN_TABLE,
                Attachment::PRODUCT_ID,
                'catalog_product_entity',
                'entity_id'
            ),
            Attachment::PRODUCT_ID,
            $setup->getTable('catalog_product_entity'),
            'entity_id',
            Table::ACTION_CASCADE
        )->setComment(
            'Product attachments'
        );

        $setup->getConnection()->createTable($lizardmedia_product_attachments);



        $lizardmedia_product_attachments_title = $setup->getConnection()->newTable(
            $setup->getTable(Attachment::TITLE_TABLE)
        )->addColumn(
            Attachment::TITLE_ID,
            Table::TYPE_INTEGER,
            11,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary'  => true],
            'Id'
        )->addColumn(
            Attachment::ATTACHMENT_ID,
            Table::TYPE_INTEGER,
            11,
            ['unsigned' => true, 'nullable' => false],
            'Attachment id'
        )->addColumn(
            Attachment::STORE_ID,
            Table::TYPE_INTEGER,
            11,
            ['unsigned' => true, 'nullable' => false],
            'Store id'
        )->addColumn(
            Attachment::TITLE,
            Table::TYPE_TEXT,
            120,
            ['nullable' => false],
            'Title'
        )->addForeignKey(
            $setup->getFkName(
                Attachment::TITLE_TABLE,
                Attachment::ATTACHMENT_ID,
                Attachment::MAIN_TABLE,
                Attachment::ID
            ),
            Attachment::ATTACHMENT_ID,
            Attachment::MAIN_TABLE,
            Attachment::ID,
            Table::ACTION_CASCADE
        )->setComment(
            'Product attachments titles'
        );

        $setup->getConnection()->createTable($lizardmedia_product_attachments_title);

        $setup->endSetup();
    }
}
