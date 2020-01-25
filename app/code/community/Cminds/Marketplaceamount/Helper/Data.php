<?php

class Cminds_Marketplaceamount_Helper_Data extends Mage_Core_Helper_Abstract
{

    public function isEnabled()
    {
        $config = Mage::getStoreConfig(
            'marketplaceamount_configuration/general/enabled'
        );

        return $config;
    }

    public function assignCustomersEnabled()
    {
        $enabled = Mage::helper('core')->isModuleEnabled('Cminds_Assignsupplierstocustomers');

        return $enabled;
    }
}
