<?php
$installer = $this;
$installer->startSetup();

$db = Mage::getSingleton('core/resource')->getConnection('core_write');
$table_prefix = Mage::getConfig()->getTablePrefix();

$entity = $this->getEntityTypeId('customer');

$this->addAttribute($entity, 'overnight_additional_charge', array(
    'type' => 'text',
    'label' => __('Overnight Additional Charge'),
    'input' => 'text',
    'visible' => TRUE,
    'required' => FALSE,
    'default_value' => 1,
    'adminhtml_only' => '1'
));

Mage::getSingleton('eav/config')
    ->getAttribute('customer', 'overnight_additional_charge')
    ->setData('used_in_forms', array('adminhtml_customer'))
    ->save();
$this->addAttribute($entity, 'two_days_additional_charge', array(
    'type' => 'text',
    'label' => __('Two Days Additional Charge'),
    'input' => 'text',
    'visible' => TRUE,
    'required' => FALSE,
    'default_value' => 1,
    'adminhtml_only' => '1'
));

Mage::getSingleton('eav/config')
    ->getAttribute('customer', 'two_days_additional_charge')
    ->setData('used_in_forms', array('adminhtml_customer'))
    ->save();

$installer->endSetup();
