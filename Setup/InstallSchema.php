<?php

namespace DHL\Dhl24pl\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        $installer->getConnection()->addColumn(
            $installer->getTable('sales_order_grid'),
            'dhlpl_settings',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length'    => 1024,
                'unsigned' => true,
                'nullable' => false,
                'comment' => 'dhlpl_settings'
            ]
        );

        $installer->getConnection()->addColumn(
            $installer->getTable('sales_order'),
            'dhlpl_settings',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length'    => 1024,
                'unsigned' => true,
                'nullable' => false,
                'comment' => 'dhlpl_settings'
            ]
        );

        $installer->getConnection()->addColumn(
            $installer->getTable('quote'),
            'dhlpl_parcelshop',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length'    => 1024,
                'unsigned' => true,
                'nullable' => true,
                'default' => null,
                'comment' => 'dhlpl_parcelshop'
            ]
        );

        $installer->getConnection()->addColumn(
            $installer->getTable('sales_order'),
            'dhlpl_parcelshop',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length'    => 1024,
                'unsigned' => true,
                'nullable' => true,
                'default' => null,
                'comment' => 'dhlpl_parcelshop'
            ]
        );

        $installer->getConnection()->addColumn(
            $installer->getTable('quote'),
            'dhlpl_neighbor',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length'    => 1024,
                'unsigned' => true,
                'nullable' => true,
                'default' => null,
                'comment' => 'dhlpl_neighbor'
            ]
        );
        $installer->getConnection()->addColumn(
            $installer->getTable('sales_order'),
            'dhlpl_neighbor',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length'    => 1024,
                'unsigned' => true,
                'nullable' => true,
                'default' => null,
                'comment' => 'dhlpl_neighbor'
            ]
        );

        $setup->endSetup();
    }
}
