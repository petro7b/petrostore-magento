<?php
abstract class Cminds_Marketplacerma_Block_Supplier_Rma_Abstract extends Mage_Core_Block_Template {
    protected function getLoggedCustomer() {
        return Mage::getSingleton('customer/session')->getCustomer();
    }

    protected function getRmaId() {
        return Mage::registry('marketplace_rma');
    }
}