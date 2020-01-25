<?php
class Cminds_MarketplacePaypal_Model_Transfer extends Mage_Core_Model_Abstract {
    private $_method = null;
    private $_order = null;

    public function transfer() {
        try {
            $this->_prepareMethod();

            $this->getMethod()->setOrder($this->_order);
//            if($this->getMethod()->canTransfer()) {
                $this->getMethod()->transfer();
//            }
        } catch(Exception $e) {
            Mage::logException($e);
        }
    }

    public function canTransfer() {
        $this->getMethod()->canTransfer();
    }

    public function setOrder($order) {
        $this->_order = $order;
    }

    protected function getMethod() {
        return $this->_method;
    }

    private function _prepareMethod() {
        $tranferType = Mage::helper('marketplacepaypal')->getTransferType();
        if($tranferType == 1) {
            $this->_method = Mage::getModel('marketplacepaypal/transfer_paypal');
        } elseif($tranferType == 2) {
            $this->_method = Mage::getModel('marketplacepaypal/transfer_adaptive');
        } else {
            throw new Exception("Please set valid transfer type.");
        }
    }
}
