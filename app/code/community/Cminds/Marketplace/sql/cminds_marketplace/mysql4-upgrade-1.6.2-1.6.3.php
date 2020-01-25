<?php

$installer = $this;
$installer->startSetup();
$db = Mage::getSingleton('core/resource')->getConnection('core_write');

$methodIdColumnExists = false;

$result = $db->query('
    SELECT *
    FROM information_schema.COLUMNS
    WHERE
    TABLE_SCHEMA = "'.Mage::getConfig()->getResourceConnectionConfig('default_setup')->dbname.'"
    AND TABLE_NAME = "'.$installer->getTable('marketplace/fields').'"
    AND COLUMN_NAME = "show_in_registration"
');

$resultSet = $result->fetch(PDO::FETCH_ASSOC);

if ($resultSet) {
    $methodIdColumnExists = true;
}

if(!$methodIdColumnExists) {
    $installer->getConnection()
        ->addColumn(
            $installer->getTable('marketplace/fields'),
            'show_in_registration',
            array(
                'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
                'unsigned'  => true,
                'nullable'  => true,
                'COMMENT'   => 'Show in registration form'
            )
        );
}