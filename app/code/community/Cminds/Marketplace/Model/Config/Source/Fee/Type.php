<?php
class Cminds_Marketplace_Model_Config_Source_Fee_Type extends Mage_Eav_Model_Entity_Attribute_Source_Abstract {
    const PERCENTAGE = 1;
    const FIXED = 2;
    
    const PRODUCT_ATTRIBUTE_FEE = 'marketplace_fee';
    const PRODUCT_ATTRIBUTE_FEE_TYPE = 'marketplace_fee_type';
    const CATEGORY_ATTRIBUTE_FEE = 'marketplace_fee';
    const CATEGORY_ATTRIBUTE_FEE_TYPE = 'marketplace_fee_type';
    const VENDOR_ATTRIBUTE_FEE = 'percentage_fee';
    const VENDOR_ATTRIBUTE_FEE_TYPE = 'fee_type';

    public function toOptionArray() {
        $options = array(
            array('value' => self::PERCENTAGE, 'label' => Mage::helper('marketplace')->__('Percentage')),
            array('value' => self::FIXED, 'label' => Mage::helper('marketplace')->__('Fixed'))
        );
        return $options;
    }

    public function getAllOptions() {
        $options = array(
            array('value' => self::PERCENTAGE, 'label' => Mage::helper('marketplace')->__('Percentage')),
            array('value' => self::FIXED, 'label' => Mage::helper('marketplace')->__('Fixed'))
        );
        return $options;
    }

    public static function toValidate() {
        $validate = array(
            self::PERCENTAGE,
            self::FIXED
        );

        return $validate;
    }
}
