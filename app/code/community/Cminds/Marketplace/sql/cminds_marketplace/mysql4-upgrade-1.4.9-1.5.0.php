<?php
$installer = $this;
$installer->startSetup();
$db = Mage::getSingleton('core/resource')->getConnection('core_write');
$table_prefix = Mage::getConfig()->getTablePrefix();

$installer->getConnection()
    ->addColumn(
        $installer->getTable('marketplace/methods'),
        'name',
        array(
            'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
            'length'    => 256,
            'unsigned'  => true,
            'nullable'  => true,
            'COMMENT'   => 'Method Name'
        )
    );


$installer->addAttribute(
    $this->getEntityTypeId('catalog_product'),
    Cminds_Marketplace_Model_Config_Source_Fee_Type::PRODUCT_ATTRIBUTE_FEE,
    array(
        'label'                      => Mage::helper('marketplace')->__('Fee'),
        'group'                      => 'General',
        'type'                       => 'int',
        'input'                      => 'text',
        'global'                     => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
        'user_defined'               => false,
        'required'                   => false,
        'visible'                    => true,
        'backend'                    => null,
        'searchable'                 => false,
        'visible_in_advanced_search' => false,
        'visible_on_front'           => false,
        'is_configurable'            => false,
        'is_html_allowed_on_front'   => false,
    )
);

$installer->addAttribute(
    $this->getEntityTypeId('catalog_product'),
    Cminds_Marketplace_Model_Config_Source_Fee_Type::PRODUCT_ATTRIBUTE_FEE_TYPE,
    array(
        'label'                      => Mage::helper('marketplace')->__('Fee Type'),
        'group'                      => 'General',
        'type'                       => 'int',
        'input'                      => 'select',
        'source'                     => 'marketplace/config_source_fee_type',
        'global'                     => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
        'user_defined'               => false,
        'required'                   => false,
        'visible'                    => true,
        'backend'                    => null,
        'searchable'                 => false,
        'visible_in_advanced_search' => false,
        'visible_on_front'           => false,
        'is_configurable'            => false,
        'is_html_allowed_on_front'   => false,
    )
);

$this->addAttribute($this->getEntityTypeId('catalog_category'),
    Cminds_Marketplace_Model_Config_Source_Fee_Type::CATEGORY_ATTRIBUTE_FEE,
    array(
        'type' => 'int',
        'label' => Mage::helper('marketplace')->__('Sales Fee'),
        'input' => 'text',
        'visible' => TRUE,
        'required' => FALSE,
        'default_value' => 1,
        'adminhtml_only' => 1,
    )
);

$this->addAttribute($this->getEntityTypeId('catalog_category'),
    Cminds_Marketplace_Model_Config_Source_Fee_Type::CATEGORY_ATTRIBUTE_FEE_TYPE,
    array(
        'type' => 'int',
        'label' => Mage::helper('marketplace')->__('Sales Fee Type'),
        'input'                      => 'select',
        'source'                     => 'marketplace/config_source_fee_type',
        'visible' => TRUE,
        'required' => FALSE,
        'default_value' => 1,
        'adminhtml_only' => 1,
    )
);

$this->addAttribute($this->getEntityTypeId('customer'),
    Cminds_Marketplace_Model_Config_Source_Fee_Type::VENDOR_ATTRIBUTE_FEE_TYPE,
    array(
        'type' => 'text',
        'label' => 'Sales Fee Type',
        'input'                      => 'select',
        'source'                     => 'marketplace/config_source_fee_type',
        'visible' => TRUE,
        'required' => FALSE,
        'default_value' => 1,
        'adminhtml_only' => '1'
    ));

Mage::getSingleton('eav/config')
    ->getAttribute('customer', Cminds_Marketplace_Model_Config_Source_Fee_Type::VENDOR_ATTRIBUTE_FEE_TYPE)
    ->setData('used_in_forms', array('adminhtml_customer','customer_account_create','customer_account_edit','checkout_register'))
    ->save();

$vendorIncomeColumn = false;
$result = $db->query("EXPLAIN {$table_prefix}sales_flat_order_item");

while ($resultset = $result->fetch(PDO::FETCH_ASSOC)) {
    if ($resultset['Field'] == 'vendor_income')
        $vendorIncomeColumn = true;
}

if(!$vendorIncomeColumn) {
    $installer->getConnection()->addColumn($installer->getTable('sales/order_item'), 'vendor_income', "DECIMAL(12,4)");
}
$items = Mage::getModel('sales/order_item')->getCollection()->addFilter('vendor_fee', array('notnull' => true));
foreach($items AS $item) {
    if($item->getVendorFee() != NULL && $item->getVendorIncome()) {
        $vendorIncome = $item->getVendorFee() * $item->getPrice();
        $item->setVendorIncome($vendorIncome);
        $item->save();
    }
}


$installer->endSetup();