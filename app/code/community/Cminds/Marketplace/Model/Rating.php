<?php
class Cminds_Marketplace_Model_Rating extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('marketplace/rating', 'id');
    }
    
    public function getSupplierAvgRating($supplierId) {
        $collection = $this->getCollection();

        $collection->addExpressionFieldToSelect('rating_avg', 'AVG(main_table.rate)', 'main_table.rate');
        $collection->getSelect()->where('main_table.supplier_id = ?', $supplierId);

        $ratingAvg = $collection->getFirstItem()->getData('rating_avg');

        return $ratingAvg;
    }
}
