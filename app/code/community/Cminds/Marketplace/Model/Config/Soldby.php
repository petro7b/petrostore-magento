<?php

class Cminds_Marketplace_Model_Config_Soldby {
    public function toOptionArray() {

        $show = array('Cart' => 'checkout_cart', 'Mini Cart' => 'checkout_minicart', 'Checkout' => 'checkout', 'Customer Order View' => 'order_view', 'Order emails' => 'emails');
        $result = array();
        foreach ($show as $key => $value) {
            $result[] = array(
                'value' => $value,
                'label' => $key,
            );
        }
        return $result;
    }
}