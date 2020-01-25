<?php
$installer = $this;
$installer->startSetup();
$entity = $this->getEntityTypeId('customer');

if (!$installer->tableExists('marketplace/vendor_shipping_method')) {
    $table = $installer->getConnection()
        ->newTable($installer->getTable('marketplace/vendor_shipping_method'))
        ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'identity'  => true,
            'unsigned'  => true,
            'nullable'  => false,
            'primary'   => true,
        ), 'Id')
        ->addColumn('order_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'nullable'  => false,
        ), 'SUPPLIER ID')
        ->addColumn('vendor_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'nullable'  => false,
        ), 'vendor_id')
        ->addColumn('shipping_method_name', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
            'nullable'  => false,
        ), 'Shipping Method Name')
        ->addColumn('shipping_method_price', Varien_Db_Ddl_Table::TYPE_VARCHAR, 15, array(
            'nullable'  => false,
        ), 'Shipping Method Name')
        ->addIndex(
            $installer->getIdxName('marketplace/vendor_shipping_method', array('order_id')),
            array('order_id')
        );
    $installer->getConnection()->createTable($table);

    $installer->getConnection()
        ->addForeignKey(
            $installer->getFkName('sales/order', 'entity_id', 'marketplace/vendor_shipping_method', 'order_id'),
            $installer->getTable("marketplace/vendor_shipping_method"),
            'order_id',
            $installer->getTable('sales/order'),
            'entity_id',
            Varien_Db_Ddl_Table::ACTION_CASCADE,
            Varien_Db_Ddl_Table::ACTION_CASCADE
        );
}

$installer->endSetup();