<?php
class Cminds_MarketplacePaypal_Model_Config_Type {
    const UPON_CHECKOUT = 1;
    const ORDER_COMPLETE = 2;
    const MANUALLY = 3;

    public function toOptionArray() {
        $options = array(
            array('value' => self::MANUALLY, 'label' => Mage::helper('marketplacepaypal')->__('Manually')),
            array('value' => self::UPON_CHECKOUT, 'label' => Mage::helper('marketplacepaypal')->__('Upon Checkout')),
            array('value' => self::ORDER_COMPLETE, 'label' => Mage::helper('marketplacepaypal')->__('When order status is changed to complete')),
        );
        return $options;
    }
}
