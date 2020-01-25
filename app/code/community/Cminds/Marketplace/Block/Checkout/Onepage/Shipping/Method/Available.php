<?php
class Cminds_Marketplace_Block_Checkout_Onepage_Shipping_Method_Available extends Mage_Checkout_Block_Onepage_Shipping_Method_Available {

    protected $_rates = array();

    /**
     * @return array
     */
    public function getProductsByVendors() {
        $_rates = $this->getShippingRates();
        foreach($_rates AS $rates) {
            foreach($rates AS $_rate) {
                if(!$_rate->getMethodDescription()) continue;
                $methods = unserialize($_rate->getMethodDescription());
                if(count($methods) == 0) continue;
                return $methods;
            }
        }
    }

    public function canShowMethod($method)
    {
        $data = array('method' => $method);

       Mage::dispatchEvent(
            'marketplace_validate_method',
            $data
        );

        $validate = Mage::registry("can_show_method");

        if ($validate === false) {
            return false;
        }

        return true;
    }
}