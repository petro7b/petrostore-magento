<?php
$installer = $this;
$installer->startSetup();

$entity = $this->getEntityTypeId('customer');

$this->addAttribute($entity, 'supplier_logo_new', array(
    'type' => 'text',
    'label' => 'Supplier Logo file After Change',
    'input' => 'text',
    'visible' => TRUE,
    'required' => FALSE,
    'default_value' => '',
));

$installer->endSetup();