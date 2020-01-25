<?php
class Cminds_Marketplace_Model_Config_Source_Billing_Calculation extends Mage_Eav_Model_Entity_Attribute_Source_Abstract {
    const INCL_TAX = 1;
    const EXCL_TAX = 0;

    public function toOptionArray() {
        $options = array(
            array('value' => self::INCL_TAX, 'label' => Mage::helper('marketplace')->__('Including Tax')),
            array('value' => self::EXCL_TAX, 'label' => Mage::helper('marketplace')->__('Excluding Tax'))
        );
        return $options;
    }

    public function getAllOptions() {
        $options = array(
            array('value' => self::INCL_TAX, 'label' => Mage::helper('marketplace')->__('Including Tax')),
            array('value' => self::EXCL_TAX, 'label' => Mage::helper('marketplace')->__('Excluding Tax'))
        );
        return $options;
    }


}
