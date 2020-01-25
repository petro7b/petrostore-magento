<?php
$installer = $this;
$installer->startSetup();

$table_prefix = Mage::getConfig()->getTablePrefix();
$entity = $this->getEntityTypeId('customer');

$this->addAttribute($entity, 'allowed_week_days', array(
    'frontend'      => '',
    'label'         => 'Allowed week days',
    'type'          => 'varchar',
    'input'         => 'text',
    'global'        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE,
    'visible'       => true,
    'required'      => false,
    'visible_on_front' => false,
));

$installer->endSetup();
