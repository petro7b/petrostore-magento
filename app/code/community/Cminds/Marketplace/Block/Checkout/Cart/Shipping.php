<?php

class Cminds_Marketplace_Block_Checkout_Cart_Shipping extends Mage_Checkout_Block_Cart_Shipping
{
    public function getProductsByVendors() {
        $_rates = $this->getEstimateRates();
        foreach($_rates AS $rates) {
            foreach($rates AS $_rate) {
                if(!$_rate->getMethodDescription()) continue;
                $methods = unserialize($_rate->getMethodDescription());
                if(count($methods) == 0) continue;
                return $methods;
            }
        }
    }
}