<?php
abstract class Cminds_MarketplacePaypal_Model_Transfer_Abstract extends Mage_Core_Model_Abstract {
    protected $_api = null;
    private $_suppliers = false;
    abstract function transfer();
    abstract function canTransfer();

    protected function _getApi() {
        if (null === $this->_api) {

            $paypalConfig = new Mage_Paypal_Model_Config();
            $paypalConfig->setMethod(Mage_Paypal_Model_Config::METHOD_WPP_DIRECT);
            $paypalConfig->setStoreId(0);

            $this->_api = Mage::getModel($this->_apiType)->setConfigObject($paypalConfig);
        }
        return $this->_api;
    }


    public function _prepareVendorList() {
        if(!$this->_suppliers) {
            $this->_validateOrder();

            $this->_suppliers = array();

            $p = $this->_prepareProductList();

            $vendors = array();
            foreach ($p AS $product_id => $options) {
                if (!$this->_validateProduct($product_id)) continue;
                if ($vendor_id = Mage::helper('marketplace')->getSupplierIdByProductId($product_id)) {
                    if (!$this->_validateVendor($vendor_id)) continue;

                    $amount = $this->_calculateIndividualAmount($vendor_id, $options['price']);

                    if (isset($vendors[$vendor_id])) {
                        $this->_suppliers[$vendor_id] = $vendors[$vendor_id] + $amount;
                    } else {
                        $costModel = Mage::getModel('marketplace/methods')
                            ->load($vendor_id, 'supplier_id');

                        $shippingPrice = $this->_getPrice($costModel);

                        $this->_suppliers[$vendor_id] = $amount + $shippingPrice;
                    }
                }
            }
        }
        return $this->_suppliers;
    }

    protected function _prepareProductList() {
        $productArray = array();

        foreach($this->getOrder()->getAllItems() AS $item) {
            $productArray[$item->getProductId()] = array('qty' => $item->getQtyOrdered(), 'price' => $item->getRowTotal(), 'vendor_profit' => $item->getVendorFee());
        }

        return $productArray;
    }

    protected function _validateOrder() {
        if(!$this->getOrder()) {
            throw new Exception(Mage::helper('marketplace')->__("Order not found"));
        }
    }

    protected function _calculateIndividualAmount($vendor_id, $supplier_id) {
        return Mage::helper('marketplace/profits')->calculateNetIncome($vendor_id, $supplier_id);
    }

    protected function _getVendorPaypalData($vendor_id) {
        $vendor = Mage::getModel('customer/customer')->load($vendor_id);

        if(!$vendor) return false;


        $email = $vendor->getPaypalEmail();

        if($email) {
            if(Zend_Validate::is($email, 'NotEmpty') &&
                Zend_Validate::is($email, 'EmailAddress')
            ) {
                return $email;
            }
        }

        return false;
    }

    protected function _validateVendor($vendor_id) {
        return true;
    }

    protected function _validateProduct($product_id) {
        return true;
    }

    public function _getPrice($supplierPriceModel) {
        if($this->getOrder()->getShippingMethod() != 'marketplace_shipping_marketplace_shipping') {
            return 0.0;
        }

        if($supplierPriceModel->getFreeShipping()) {
            return 0.0;
        }

        if($supplierPriceModel->getFlatRateAvailable()) {
            return $supplierPriceModel->getFlatRateFee();
        }

        if($supplierPriceModel->getTableRateAvailable()) {
            $supplerRates = Mage::getModel("marketplace/rates")
                ->load($supplierPriceModel->getSupplierId(), 'supplier_id');

            if(!$supplerRates->getId()) return $supplierPriceModel->getTableRateFee();

            $calculatedRate = false;

            if($calculatedRate === false) {
                $calculatedRate = $supplierPriceModel->getTableRateFee();
            }

            return $calculatedRate;
        }

        return 0.0;
    }

    private function _calculateTableFee($items, $model, $type) {
        $country = $this->getShippingAddress()->getCountry();
        $region = $this->getShippingAddress()->getRegion();
        $postcode = $this->getShippingAddress()->getPostcode();

        $total = 0;

        foreach($items AS $item) {
            if(isset($item[$type-1])) {
                $total += $item[$type-1];
            }
        }

        return $model->getRate($country, $region, $postcode, $total);
    }
}
