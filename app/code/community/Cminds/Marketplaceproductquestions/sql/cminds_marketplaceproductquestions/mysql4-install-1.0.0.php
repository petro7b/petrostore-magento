<?php
$installer = $this;
$installer->startSetup();

$table = $installer->getConnection()
    ->newTable($installer->getTable('marketplaceproductquestions/questions'))
    ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable' => false,
        'identity' => true,
        'unsigned'  => true,
        'primary'   => true,
        'auto_increment' => true
    ), 'Id')
    ->addColumn('customer_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable' => false,
    ), 'CUSTOMER ID')
    ->addColumn('product_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable' => false,
    ), 'PRODUCT ID')
    ->addColumn('author_name', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'nullable' => false,
    ))
    ->addColumn('question_body', Varien_Db_Ddl_Table::TYPE_TEXT, null, array(
        'nullable' => false,
    ))
    ->addColumn('visibility', Varien_Db_Ddl_Table::TYPE_INTEGER, null,  array(
        'nullable' => false,
        'default' => '0',
    ))
    ->addColumn('supplier_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null,  array(
        'nullable' => true,
        'default' => null,
    ))
    ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_DATETIME, null, array(
        'nullable'  => false,
    ))
    ->addColumn('updated_at', Varien_Db_Ddl_Table::TYPE_DATETIME, null, array(
        'nullable'  => false,
    ));
$table->setOption('type', 'InnoDB');
$table->setOption('charset', 'utf8');

$installer->getConnection()->createTable($table);

$table = $installer->getConnection()
    ->newTable($installer->getTable('marketplaceproductquestions/answers'))
    ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable' => false,
        'identity'  => true,
        'unsigned'  => true,
        'primary'   => true,
        'auto_increment' => true
    ), 'Id')
    ->addColumn('question_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable' => false,
    ), 'QUESTION ID')
    ->addColumn('admin_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable' => true,
    ))
    ->addColumn('customer_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable' => true,
    ))
    ->addColumn('author_name', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'nullable' => false,
    ))
    ->addColumn('answer_body', Varien_Db_Ddl_Table::TYPE_TEXT, null, array(
        'nullable' => false,
    ))
    ->addColumn('is_customer_notified', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable' => false,
        'default' => '0',
    ))
    ->addColumn('admin_approval', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable' => false,
        'default' => '0',
    ))
    ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_DATETIME, null, array(
        'nullable'  => false,
    ))
    ->addColumn('updated_at', Varien_Db_Ddl_Table::TYPE_DATETIME, null, array(
        'nullable'  => false,
    ));
$table->setOption('type', 'InnoDB');
$table->setOption('charset', 'utf8');

$installer->getConnection()->createTable($table);


$installer->endSetup();