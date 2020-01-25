<?php

class Cminds_Marketplace_Helper_Shipping extends Mage_Core_Helper_Abstract
{

    public function useStoreTableRate()
    {
        if (Mage::getStoreConfig('carriers/marketplace_shipping/shipping_fee_nonsupplier')
            == Cminds_Marketplace_Model_Config_Source_Carriers_Shipping_Calculate_Nonsupplier::TABLE_RATE
        ) {
            return true;
        }

        return false;
    }

    public function hideTableRate()
    {
        if (!$this->useStoreTableRate()) {
            return false;
        }

        if(!Mage::getStoreConfig('carriers/marketplace_shipping/shipping_hide_tablerate')) {
            return false;
        }

        if (!Mage::getStoreConfig('carriers/marketplace_shipping/active')
            || !Mage::getStoreConfig('carriers/marketplace_estimated_time/active')
        ) {
            return false;
        }

        return true;
    }
}