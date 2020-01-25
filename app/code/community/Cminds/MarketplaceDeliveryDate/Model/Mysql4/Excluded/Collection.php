<?php
class Cminds_MarketplaceDeliveryDate_Model_Mysql4_Excluded_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('marketplace_delivery_date/excluded');
    }

    public function toJson()
    {
        $arrayOfItems = array();

        foreach ($this->getItems() as $item) {
            $arrayOfItems[] = $item->getData();
        }

        return json_encode($arrayOfItems);
    }
}
