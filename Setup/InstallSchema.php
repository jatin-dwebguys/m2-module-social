<?php

namespace LoganStellway\Social\Setup;

/**
 * Dependencies
 */
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\DB\Ddl\Table;

/**
 * Install Schema
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * Install Tables
     * @return void
     */
    public function install(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $setup->startSetup();

        if (! $setup->tableExists('loganstellway_social_customer')) {
            $table = $setup->getConnection()->newTable(
                $setup->getTable('loganstellway_social_customer')
            )->addColumn(
                'entity_id', Table::TYPE_INTEGER, 11, [
                    'identity' => true,
                    'nullable' => false,
                    'primary'  => true,
                    'unsigned' => true,
                ],
                'Entity ID'
            )->addColumn(
                'customer_id', Table::TYPE_INTEGER, 10, ['unsigned' => true, 'nullable' => false], 'Customer Id'
            )->addColumn(
                'user_id', Table::TYPE_TEXT, 255, ['unsigned' => true, 'nullable' => false], 'Social User Id'
            )->addColumn(
                'type', Table::TYPE_TEXT, 255, ['default' => ''], 'Type'
            )->addForeignKey(
                $setup->getFkName('loganstellway_social_customer', 'customer_id', 'customer_entity', 'entity_id'),
                'customer_id',
                $setup->getTable('customer_entity'),
                'entity_id',
                Table::ACTION_CASCADE
            )->setComment('Customers created with social accounts');

            $setup->getConnection()->createTable($table);
        }

        $setup->endSetup();
    }
}
