<?php
$installer = $this;
$installer->startSetup();

$entity = $this->getEntityTypeId('customer');

$this->addAttribute($entity, 'supplier_logo', array(
    'type' => 'text',
    'label' => 'Supplier Logo file',
    'input' => 'text',
    'visible' => TRUE,
    'required' => FALSE,
    'default_value' => '',
    'adminhtml_only' => '1'
));

$installer->endSetup();