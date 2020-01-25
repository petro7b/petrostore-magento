<?php
$installer = $this;
$installer->startSetup();

$installer->getConnection()
    ->addColumn(
        $installer->getTable('marketplace/methods'),
        'fedex',
        array(
            'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
            'length'    => 2,
            'unsigned'  => true,
            'nullable'  => true,
            'COMMENT'   => 'Fedex Available'
        )
    );

$installer->endSetup();