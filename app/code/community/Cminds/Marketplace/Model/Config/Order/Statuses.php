<?php

class Cminds_Marketplace_Model_Config_Order_Statuses {
    public function toOptionArray() {

        $statuses = Mage::getModel('sales/order_status')->getResourceCollection()->getData();
        $canSee = array();
        foreach ($statuses as $status) {
            $canSee[] = array(
                'value' => $status['status'],
                'label' => $status['label'],
            );
        }
        return $canSee;
    }
}