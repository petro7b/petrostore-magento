<?php
$installer = $this;
$installer->startSetup();

$db = Mage::getSingleton('core/resource')->getConnection('core_write');
$table_prefix = Mage::getConfig()->getTablePrefix();

$this->addAttribute('customer', 'origin_zipcode', array(
    'type' => 'text',
    'label' => 'Zip Code used to Origin',
    'input' => 'text',
    'visible' => TRUE,
    'required' => FALSE,
    'default_value' => '',
    'adminhtml_only' => '1'
));


$vendorColumn = false;
$result = $db->query("EXPLAIN {$table_prefix}sales_flat_order_item");

while ($resultset = $result->fetch(PDO::FETCH_ASSOC)) {
    if ($resultset['Field'] == 'shipping_method')
        $vendorColumn = true;
}

if(!$vendorColumn) {
    $installer->getConnection()->addColumn($installer->getTable('sales/order_item'), 'shipping_method', "VARCHAR(255)");
}


$installer->getConnection()
          ->addColumn(
              $installer->getTable('marketplace/methods'),
              'fedex',
              array(
                  'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                  'length'    => 2,
                  'unsigned'  => true,
                  'nullable'  => true,
                  'COMMENT'   => 'Fedex Available'
              )
          );

$installer->endSetup();