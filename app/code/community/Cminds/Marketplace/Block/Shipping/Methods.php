<?php
class Cminds_Marketplace_Block_Shipping_Methods extends Mage_Core_Block_Template {
    public function _construct() {
        $this->setTemplate('marketplace/customer/register/shipping/methods.phtml');
    }
    public function getSavedMethods() {
        return Mage::getModel('marketplace/methods')->getCollection()->addFieldToFilter('supplier_id', Mage::helper('marketplace')->getSupplierId());
    }
}