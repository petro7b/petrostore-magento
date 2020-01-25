<?php
class Cminds_Marketplace_Model_Mysql4_Methods_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('marketplace/methods');
    }

    public function addItemFilter($item) {
        return $this->addProductFilter($item->getProduct());
    }

    public function addProductFilter($_product) {
        $this->addFieldToFilter('supplier_id', Mage::helper('marketplace')->getProductSupplierId($_product));
        return $this;
    }


}
