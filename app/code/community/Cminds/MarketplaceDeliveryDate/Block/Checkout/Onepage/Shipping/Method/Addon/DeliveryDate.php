<?php
class Cminds_MarketplaceDeliveryDate_Block_Checkout_Onepage_Shipping_Method_Addon_DeliveryDate
    extends Mage_Core_Block_Template
{

    /**
     * Get supplier by id.
     *
     * @param $supplierId
     *
     * @return Mage_Core_Model_Abstract
     */
    public function getSupplierById($supplierId)
    {
        $supplier = Mage::getModel('customer/customer')->load($supplierId);

        return $supplier;
    }

    /**
     * Get excluded dates for supplier.
     *
     * @return string JSON
     */
    public function getExcludedDatesForSupplier($supplierId)
    {
        $excluded = Mage::getModel('marketplace_delivery_date/excluded')->getCollection()
            ->addFieldtoFilter('supplier_id', $supplierId);

        $json = $excluded->toJson();

        return $json;
    }

}
