<?php
$installer = $this;
$installer->startSetup();

$this->addAttribute('customer', 'schedule_delay', array(
    'type' => 'text',
    'label' => 'schedule delay for shipping',
    'input' => 'text',
    'visible' => TRUE,
    'required' => FALSE,
    'default_value' => '',
    'adminhtml_only' => '1'
));

$installer->endSetup();
