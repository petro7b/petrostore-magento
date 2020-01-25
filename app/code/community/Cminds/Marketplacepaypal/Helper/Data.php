<?php
class Cminds_MarketplacePaypal_Helper_Data extends Mage_Core_Helper_Abstract {
    
    public function isEnabled() {
        return Mage::getStoreConfig('marketplace_configuration/paypal_transfer/enabled');
    }
    
    public function isAfterPlacingOrderEnabled() {
        return $this->isEnabled() && Mage::getStoreConfig('marketplace_configuration/paypal_transfer/type') == Cminds_Marketplacepaypal_Model_Config_Type::UPON_CHECKOUT;
    }
    
    public function isAfterOrderCompleteEnabled() {
        return $this->isEnabled() && Mage::getStoreConfig('marketplace_configuration/paypal_transfer/type') == Cminds_Marketplacepaypal_Model_Config_Type::ORDER_COMPLETE;
    }
    
    public function isManuallyEnabled() {
        return $this->isEnabled() && Mage::getStoreConfig('marketplace_configuration/paypal_transfer/type') == Cminds_Marketplacepaypal_Model_Config_Type::MANUALLY;
    }

    public function getTransferType() {
        return Mage::getStoreConfig('marketplace_configuration/paypal_transfer/transfer_type');
    }
}