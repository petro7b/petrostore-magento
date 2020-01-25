<?php
$installer = $this;
$installer->startSetup();
$db = Mage::getSingleton('core/resource')->getConnection('core_write');
$table_prefix = Mage::getConfig()->getTablePrefix();

$vendorColumn = false;
$result = $db->query("EXPLAIN {$table_prefix}sales_flat_order_item");

while ($resultset = $result->fetch(PDO::FETCH_ASSOC)) {
    if ($resultset['Field'] == 'shipping_method')
        $vendorColumn = true;
}

if(!$vendorColumn) {
    $installer->getConnection()->addColumn($installer->getTable('sales/order_item'), 'shipping_method', "VARCHAR(255)");
}

$installer->endSetup();