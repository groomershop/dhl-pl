<?php
declare(strict_types=1);

namespace DHL\Dhl24pl\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Class UpgradeSchema
 * @package DHL\Dhl24pl\Setup
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        if (version_compare($context->getVersion(), '1.0.1') < 0) {
            $this->createPackageTable($installer);
        }

        if (version_compare($context->getVersion(), '1.0.2') < 0) {
            $this->createSenderTable($installer);
        }

        if (version_compare($context->getVersion(), '1.0.3') < 0) {
            $this->addSenderColumns($installer);
        }

        if (version_compare($context->getVersion(), '1.0.4') < 0) {
            $this->addPredeliveryColumn($installer);
        }

        $installer->endSetup();
    }

    /**
     * @param $installer
     */
    protected function createPackageTable($installer)
    {
        $table = $installer->getConnection()->newTable(
            $installer->getTable('dhl_packages')
        )->addColumn(
            'id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Id'
        )->addColumn(
            'name',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Name'
        )->addColumn(
            'sender',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Sender'
        )->addColumn(
            'is_courier',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            [],
            'Get courier'
        )->addColumn(
            'product_type',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Product type'
        )->addColumn(
            'payer',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Payer'
        )->addColumn(
            'is_default',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            [],
            'Is default'
        )->addColumn(
            'package_type',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Packaget type'
        )->addColumn(
            'weight',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'weight'
        )->addColumn(
            'height',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'height'
        )->addColumn(
            'width',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'width'
        )->addColumn(
            'lenght',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Lenght'
        )->addColumn(
            'quantity',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'quantity'
        )->addColumn(
            'non_standard',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            [],
            'Non standard'
        )->addColumn(
            'insurance',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            [],
            'insurance'
        )->addColumn(
            'insurance_price',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'insurance_price'
        )->addColumn(
            'cod',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            [],
            'cod'
        )->addColumn(
            'cod_price',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'cod_price'
        )->addColumn(
            'proof_of_delivery',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            [],
            'proofOfDelivery'
        )->addColumn(
            'delivery_to_lm',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            [],
            'delivery_to_lm'
        )->addColumn(
            'delivery_evening',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            [],
            'delivery_evening'
        )->addColumn(
            'delivery_on_saturday',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            [],
            'delivery_on_saturday'
        )->addColumn(
            'pickup_on_saturday',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            [],
            'pickup_on_saturday'
        )->addColumn(
            'self_collect',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            [],
            'self_collect'
        )->addColumn(
            'content',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'content'
        )->addColumn(
            'costs_center',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'costs_center'
        )->addColumn(
            'comment',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'comment'
        )->addColumn(
            'created_at',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
            'Created At'
        )->addColumn(
            'updated_at',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
            'Updated At');
        $installer->getConnection()->createTable($table);
    }

    /**
     * @param $installer
     */
    protected function createSenderTable($installer)
    {
        $table = $installer->getConnection()->newTable(
            $installer->getTable('dhl_senders')
        )->addColumn(
            'id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Id'
        )->addColumn(
            'name',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            'Name'
        )->addColumn(
            'sap',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            'Sap'
        )->addColumn(
            'city',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            'City'
        )->addColumn(
            'postcode',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            'Post Code'
        )->addColumn(
            'street',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            'Street'
        )->addColumn(
            'street_number',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            'Street Number'
        )->addColumn(
            'house_number',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            'House Number'
        )->addColumn(
            'email',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            'Email'
        )->addColumn(
            'phone',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            'Phone'
        )->addColumn(
            'contact_person',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            'Contact Person'
        )->addColumn(
            'created_at',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
            'Created At'
        )->addColumn(
            'updated_at',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
            'Updated At');
        $installer->getConnection()->createTable($table);
    }

    /**
     * @param $installer
     */
    protected function addSenderColumns($installer)
    {
        $installer->getConnection()->addColumn(
            $installer->getTable('dhl_packages'),
            'return_on_delivery',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                'nullable' => true,
                'comment' => 'return_on_delivery'
            ]
        );

        $installer->getConnection()->addColumn(
            $installer->getTable('dhl_packages'),
            'delivery_to_neighbour',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                'nullable' => true,
                'comment' => 'delivery_to_neighbour'
            ]
        );
    }

    /**
     * @param $installer
     */
    protected function addPredeliveryColumn($installer)
    {
        $installer->getConnection()->addColumn(
            $installer->getTable('dhl_packages'),
            'predelivery_information',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                'nullable' => true,
                'comment' => 'predelivery_information'
            ]
        );
    }
}
