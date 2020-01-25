<?php
$installer = $this;
$installer->startSetup();

$table_prefix = Mage::getConfig()->getTablePrefix();
$entity = $this->getEntityTypeId('customer');

$installer->addAttribute($entity, 'supplier_min_order_amount', array(
    'frontend'      => '',
    'label'         => 'Supplier Min Order Amount',
    'type'          => 'decimal',
    'input'         => 'text',
    'global'        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE,
    'visible'       => false,
    'required'      => false,
    'visible_on_front' => false,
));

$installer->addAttribute($entity, 'supplier_min_order_qty', array(
    'frontend'      => '',
    'label'         => 'Supplier Min Order Qty',
    'type'          => 'decimal',
    'input'         => 'text',
    'global'        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE,
    'visible'       => false,
    'required'      => false,
    'visible_on_front' => false,
));

$this->addAttribute($entity, 'supplier_min_order_amount_per', array(
    'type' => 'int',
    'source' => 'marketplaceamount/source_minimumAmount',
    'label' => 'Supplier Minimum Order Amount Per',
    'input' => 'select',
    'visible' => false,
    'required' => false,
    'default' => 0,
));

$installer->endSetup();