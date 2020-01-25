<?php
$installer = $this;
$installer->startSetup();
$db = Mage::getSingleton('core/resource')->getConnection('core_write');
$table_prefix = Mage::getConfig()->getTablePrefix();

$flat2Days = false;
$flatOvernight = false;
$twoDaysEnabled = false;
$overnightEnabled = false;
$result = $db->query("EXPLAIN ".$installer->getTable('marketplace/methods'));

while ($resultset = $result->fetch(PDO::FETCH_ASSOC)) {
    if ($resultset['Field'] == 'flat_two_days')
        $flat2Days = true;

    if ($resultset['Field'] == 'flat_overnight')
        $flatOvernight = true;

    if ($resultset['Field'] == 'flat_two_days_enabled')
        $twoDaysEnabled = true;

    if ($resultset['Field'] == 'flat_overnight_enabled')
        $overnightEnabled = true;
}

if(!$twoDaysEnabled) {
    $installer->getConnection()
        ->addColumn(
            $installer->getTable('marketplace/methods'),
            'flat_two_days_enabled',
            array(
                'type' => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'nullable' => true,
                'length'     => 1,
                'COMMENT' => 'Overnight Additional Price'
            )
        );
}

if(!$flat2Days) {
    $installer->getConnection()
        ->addColumn(
            $installer->getTable('marketplace/methods'),
            'flat_two_days',
            array(
                'type' => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 2,
                'precision' => 5,
                'nullable' => true,
                'COMMENT' => '2 Days Enabled'
            )
        );
}

if(!$overnightEnabled) {
    $installer->getConnection()
        ->addColumn(
            $installer->getTable('marketplace/methods'),
            'flat_overnight_enabled',
            array(
                'type' => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'nullable' => true,
                'length' => 1,
                'COMMENT' => 'Overnight Enabled'
            )
        );
}

if(!$flatOvernight) {
    $installer->getConnection()
        ->addColumn(
            $installer->getTable('marketplace/methods'),
            'flat_overnight',
            array(
                'type' => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'nullable' => true,
                'scale'     => 2,
                'precision' => 5,

                'COMMENT' => 'Overnight Additional Price'
            )
        );
}

$installer->endSetup();