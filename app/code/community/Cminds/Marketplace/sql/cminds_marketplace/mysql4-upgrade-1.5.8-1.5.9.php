<?php
$installer = $this;
$installer->startSetup();
$db = Mage::getSingleton('core/resource')->getConnection('core_write');
$table_prefix = Mage::getConfig()->getTablePrefix();

$methodIdColumnExists = false;
$result = $db->query("EXPLAIN " . $installer->getTable('marketplace/rates'));

while ($resultSet = $result->fetch(PDO::FETCH_ASSOC)) {
    if ($resultSet['Field'] == 'method_id')
        $methodIdColumnExists = true;
}

if(!$methodIdColumnExists) {
    $installer->getConnection()
        ->addColumn(
            $installer->getTable('marketplace/rates'),
            'method_id',
            array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'length'    => 256,
                'unsigned'  => true,
                'nullable'  => true,
                'COMMENT'   => 'Method ID'
            )
        );
}