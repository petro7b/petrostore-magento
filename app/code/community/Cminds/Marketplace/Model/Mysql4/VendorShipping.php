<?php
class Cminds_Marketplace_Model_Mysql4_VendorShipping extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('marketplace/vendor_shipping_method', 'id');
    }
}
