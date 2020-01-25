<?php
$installer = $this;
$installer->startSetup();
$db = Mage::getSingleton('core/resource')->getConnection('core_write');
$table_prefix = Mage::getConfig()->getTablePrefix();

$isGroundColumn = false;
$result = $db->query("EXPLAIN ".$installer->getTable('marketplace/methods'));

while ($resultset = $result->fetch(PDO::FETCH_ASSOC)) {
    if ($resultset['Field'] == 'is_ground')
        $isGroundColumn = true;
}

if(!$isGroundColumn) {
    $installer->getConnection()
        ->addColumn(
            $installer->getTable('marketplace/methods'),
            'is_ground',
            array(
                'type' => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'nullable' => true,
                'length'     => 1,
                'COMMENT' => 'Is Ground Method'
            )
        );
}

$installer->endSetup();