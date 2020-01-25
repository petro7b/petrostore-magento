<?php

$installer = $this;
$connection = $installer->getConnection();

$installer->startSetup();

$installer->getConnection()
    ->addColumn($installer->getTable('sales/order_item'),
        'delivery_date',
        array(
            'type' => Varien_Db_Ddl_Table::TYPE_DATE,
            'nullable' => false,

            'comment' => 'Delivery date'
        )
    );

$installer->endSetup();
