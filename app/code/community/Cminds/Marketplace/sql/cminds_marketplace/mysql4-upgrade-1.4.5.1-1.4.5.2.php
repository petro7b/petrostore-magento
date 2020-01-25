<?php
$installer = $this;
$installer->startSetup();

$installer->updateAttribute(Mage_Catalog_Model_Product::ENTITY, Cminds_Marketplace_Model_Config_Source_Fee_Type::PRODUCT_ATTRIBUTE_FEE, 'note', 'If you put a value here, it will override commissions added for categories');
$installer->updateAttribute(Mage_Catalog_Model_Category::ENTITY, Cminds_Marketplace_Model_Config_Source_Fee_Type::CATEGORY_ATTRIBUTE_FEE, 'note', 'If you put a value here, it will override commissions added for vendors');

$installer->endSetup();