<?php
class Cminds_Marketplace_Model_Supplier_Logo_Renderer_Image
{
    public function render()
    {
        $customerId = Mage::app()->getRequest()->getParam('id');
        if ($customerId) {
            $customer = Mage::getModel('customer/customer')->load($customerId);
            $supplierLogoNew = $customer->getSupplierLogoNew();
            if ($supplierLogoNew) {
                if (Mage::helper('marketplace')->getLogosPath() . $supplierLogoNew) {
                    return Mage::helper('marketplace')->getLogosUrl() . $supplierLogoNew;
                }
            }
        }

        return '';
    }
}