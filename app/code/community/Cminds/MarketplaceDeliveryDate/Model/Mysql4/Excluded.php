<?php
class Cminds_MarketplaceDeliveryDate_Model_Mysql4_Excluded extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('marketplace_delivery_date/deliverydate_supplier_time_excluded_days', 'id');
    }
}