<?php

class Cminds_Marketplaceamount_Model_Source_MinimumAmount
    extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    const NONE = 0;
    const ORDER = 1;
    const DAY = 2;

    public function getAllOptions() {
        $helper = Mage::helper('marketplaceamount');

        $options = array(
            array('value' => self::NONE, 'label' => $helper->__('None')),
            array('value' => self::ORDER, 'label' => $helper->__('Order')),
            array('value' => self::DAY, 'label' => $helper->__('Day'))
        );

        foreach ($options as $option) {
            $this->_options[] = array(
                'label'=> $option['label'],
                'value' => $option['value']
            );
        }

        return $this->_options;
    }

    public function toOptionArray() {
        return $this->getAllOptions();
    }

}