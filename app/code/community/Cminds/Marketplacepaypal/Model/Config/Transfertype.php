<?php
class Cminds_MarketplacePaypal_Model_Config_Transfertype {
    const REGULAR = 1;
    const ADAPTIVE = 2;

    public function toOptionArray() {
        $options = array(
            array('value' => self::REGULAR, 'label' => Mage::helper('marketplacepaypal')->__('Regular')),
            array('value' => self::ADAPTIVE, 'label' => Mage::helper('marketplacepaypal')->__('Adaptive Payment')),
        );
        return $options;
    }
}
