<?php
class Cminds_MarketplaceDeliveryDate_Block_Settings_Settings extends Mage_Core_Block_Template {

    public function _construct()
    {
        $this->setTemplate('marketplace_delivery_date/settings/settings.phtml');
    }

    /**
     * Get logged customer object.
     *
     * @return Mage_Customer_Model_Customer
     */
    public function getCustomer()
    {
        return Mage::getSingleton('customer/session')->getCustomer();
    }

    /**
     * Check allow for set order lead time.
     *
     * @return bool
     */
    public function checkOrderLeadTime()
    {
        return (bool) Mage::helper('marketplace_delivery_date')->getOrderLeadTimeConfig();
    }

    /**
     * Get excluded dates for logged supplier.
     *
     * @return Cminds_MarketplaceDeliveryDate_Model_Mysql4_Excluded_Collection
     */
    public function getCurrentVendorExcludedDates()
    {
        $excluded = Mage::getModel('marketplace_delivery_date/excluded')->getCollection()
            ->addFieldtoFilter('supplier_id', $this->getCustomer()->getId());

        return $excluded;
    }
}