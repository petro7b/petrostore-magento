<?php
$installer = $this;
$installer->startSetup();

$entity = $this->getEntityTypeId('customer');

$this->addAttribute($entity, 'supplier_background', array(
    'type' => 'text',
    'label' => 'Supplier Background file',
    'input' => 'text',
    'visible' => TRUE,
    'required' => FALSE,
    'default_value' => '',
    'adminhtml_only' => '1'
));

$installer->endSetup();