<?php
$installer = $this;

$installer->startSetup();

$setup = new Mage_Eav_Model_Entity_Setup('core_setup');

$groupName        = 'Fedex Account Conformation';
$entityTypeId     = $setup->getEntityTypeId('customer');
$attributeSetId   = $setup->getDefaultAttributeSetId($entityTypeId);
$attributeGroupId = $setup->getAttributeGroupId($entityTypeId, $attributeSetId, $groupName);

$installer->addAttribute("customer", "fedex_key",  array(
    "type"     => "varchar",
    "backend"  => "",
    "label"    => "Fedex key",
    "input"    => "text",
    "source"   => "",
    "visible"  => true,
    "required" => false,
    "default" => "",
    "frontend" => "",
    "unique"     => false,
    "note"       => ""
));

$installer->addAttribute("customer", "fedex_password",  array(
    "type"     => "varchar",
    "backend"  => "",
    "label"    => "Fedex Password",
    "input"    => "text",
    "source"   => "",
    "visible"  => true,
    "required" => false,
    "default" => "",
    "frontend" => "",
    "unique"     => false,
    "note"       => ""
));

$installer->addAttribute("customer", "account_number",  array(
    "type"     => "varchar",
    "backend"  => "",
    "label"    => "Fedex Account Number",
    "input"    => "text",
    "source"   => "",
    "visible"  => true,
    "required" => false,
    "default" => "",
    "frontend" => "",
    "unique"     => false,
    "note"       => ""
));

$installer->addAttribute("customer", "meter_number",  array(
    "type"     => "varchar",
    "backend"  => "",
    "label"    => "Fedex Meter Number",
    "input"    => "text",
    "source"   => "",
    "visible"  => true,
    "required" => false,
    "default" => "",
    "frontend" => "",
    "unique"     => false
));

$attribute   = Mage::getSingleton("eav/config")->getAttribute("customer", "meter_number");
attributeSave($attribute);
$attribute   = Mage::getSingleton("eav/config")->getAttribute("customer", "account_number");
attributeSave($attribute);
$attribute   = Mage::getSingleton("eav/config")->getAttribute("customer", "fedex_password");
attributeSave($attribute);
$attribute   = Mage::getSingleton("eav/config")->getAttribute("customer", "fedex_key");
attributeSave($attribute);


function attributeSave($attribute)
{
    $used_in_forms = array();

    $used_in_forms[]="adminhtml_customer";

    $attribute
        ->setData("is_used_for_customer_segment", true)
        ->setData("is_system", 0)
        ->setData("is_user_defined", 1)
        ->setData("is_visible", 1)
        ->setData("sort_order", 100);
    $attribute->save();
}

$installer->endSetup();