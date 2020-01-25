<?php
$installer = $this;
$installer->startSetup();

$table_prefix = Mage::getConfig()->getTablePrefix();
$entity = $this->getEntityTypeId('customer');
$this->addAttribute($entity, 'paypal_email', array(
    'type' => 'text',
    'label' => 'Paypal Email',
    'input' => 'text',
    'visible' => TRUE,
    'required' => FALSE,
    'default_value' => '',
    'adminhtml_only' => '1'
));
Mage::getSingleton('eav/config')
    ->getAttribute('customer', 'paypal_email')
    ->setData('used_in_forms', array('adminhtml_customer','customer_account_create','customer_account_edit','checkout_register'))
    ->save();

$installer->endSetup();