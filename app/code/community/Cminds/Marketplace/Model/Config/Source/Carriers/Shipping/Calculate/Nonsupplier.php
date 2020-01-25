<?php
class Cminds_Marketplace_Model_Config_Source_Carriers_Shipping_Calculate_Nonsupplier {

    const FIXED = 0;
    const TABLE_RATE = 1;

    public function toOptionArray() {

        $options = array(
            array('value' => self::FIXED, 'label' => Mage::helper('marketplace')->__('Fixed')),
            array('value' => self::TABLE_RATE, 'label' => Mage::helper('marketplace')->__('Based on Store Table Rates'))
        );

        return $options;
    }
}