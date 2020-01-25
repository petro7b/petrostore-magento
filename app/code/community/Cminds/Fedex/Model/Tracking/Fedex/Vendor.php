<?php

class Cminds_Fedex_Model_Tracking_Fedex_Vendor extends Cminds_Fedex_Model_Tracking_Fedex
{
    protected function applyFilters($items) {
        foreach($items AS $k => $item) {
            if(!Mage::helper("marketplace")->isOwner($item->getProduct(), $this->getVendor()->getId())) {
                unset($items[$k]);
            }
        }
    }
}