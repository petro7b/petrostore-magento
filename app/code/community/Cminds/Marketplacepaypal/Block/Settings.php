<?php
class Cminds_Marketplacepaypal_Block_Settings extends Mage_Core_Block_Template
{

    public function getSupplier()
    {
        $supplier = Mage::getModel('customer/customer')->load(
            Mage::helper('supplierfrontendproductuploader')->getSupplierId()
        );

        return $supplier;
    }
}