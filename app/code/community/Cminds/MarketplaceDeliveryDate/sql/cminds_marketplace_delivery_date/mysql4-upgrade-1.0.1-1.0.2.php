<?php
$installer = $this;
$installer->startSetup();

$table_prefix = Mage::getConfig()->getTablePrefix();
$entity = $this->getEntityTypeId('customer');

$this->addAttribute($entity, 'cutoff_time_finish', array(
    'frontend'      => '',
    'label'         => 'Cut-off time finish',
    'type'          => 'varchar',
    'input'         => 'text',
    'global'        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE,
    'visible'       => true,
    'required'      => false,
    'visible_on_front' => false,
));

$this->addAttribute($entity, 'order_lead_time', array(
    'frontend'      => '',
    'label'         => 'Order lead time ',
    'type'          => 'varchar',
    'input'         => 'text',
    'global'        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE,
    'visible'       => true,
    'required'      => false,
    'visible_on_front' => false,
));

Mage::getSingleton('eav/config')
    ->getAttribute('customer', 'order_lead_time')
    ->setData('used_in_forms', array('adminhtml_customer','customer_account_create','customer_account_edit','checkout_register'))
    ->save();

$table = $installer->getConnection()
    ->newTable($installer->getTable('marketplace_delivery_date/deliverydate_supplier_time_excluded_days'))
    ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
    ), 'Id')
    ->addColumn('supplier_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable'  => false,
    ), 'VENDOR ID')
    ->addColumn('date', Varien_Db_Ddl_Table::TYPE_DATE, null, array(
        'nullable'  => false,
    ), 'Excluded date');
$installer->getConnection()->createTable($table);

$installer->endSetup();
