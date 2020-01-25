<?php
class Cminds_Marketplace_Helper_Profits extends Mage_Core_Helper_Abstract {
    private $_fee;

    /**
     * Calculating store's income.
     *
     * @param $netIncome
     * @param $amount
     *
     * @return int
     */
    public function calcuateStoreIncome($netIncome, $amount)
    {
        return $amount - $netIncome;
    }

    /**
     * Calculating supplier's income.
     *
     * @param $supplierIncome
     * @param $quantity
     *
     * @return int
     */
    public function calculateNetIncome($supplierIncome, $quantity)
    {
        return $supplierIncome * $quantity;
    }
    
    public function getVendorIncome($product, $price) {
        $storeProfit = $this->getStoreProfitByProduct($product);
        $profitType = $this->getFeeType($product);

        if($profitType == Cminds_Marketplace_Model_Config_Source_Fee_Type::FIXED) {
            $vendorIncome =  min($price, ($price - $storeProfit));
            $realPercentValue = (100-(100*$vendorIncome)/$price);
        } else {
            $vendorIncome = min($price, (($price * (100 - $storeProfit))) / 100);
            $realPercentValue = $storeProfit;
        }
        
        return array('income' => $vendorIncome, 'percentage' => $realPercentValue);
    }
    
    public function getStoreProfit($supplier) {
        if(!$this->_fee) {
            $customerObj = Mage::getModel('customer/customer')->load($supplier);
            $this->_fee = $customerObj->getData('percentage_fee');
         
            if($this->_fee == null || trim($this->_fee) == "") {
                $this->_fee = Mage::getStoreConfig('marketplace_configuration/general/default_percentage_fee');
            }
        }
        return $this->_fee;
    }

    public function getStoreProfitByProduct($product) {
        $_fee = null;
        
        if(is_object($product)) {
            $p = $product;
        } else {
            $p = Mage::getModel('catalog/product')->load($product);
        }
        
        if($p->getData(Cminds_Marketplace_Model_Config_Source_Fee_Type::PRODUCT_ATTRIBUTE_FEE)){
            $_fee = $p->getData(Cminds_Marketplace_Model_Config_Source_Fee_Type::PRODUCT_ATTRIBUTE_FEE);
            return $_fee;
        }
        
        if(!$_fee) {
            $categories = $product->getCategoryIds();
            $categories[] = 9;

            $categories = Mage::getModel('catalog/category')
                ->getCollection()
                ->addFieldToFilter('entity_id', array('in' => $categories))
                ->addFieldToFilter('is_active', array('eq' => '1'))
                ->addAttributeToSelect(Cminds_Marketplace_Model_Config_Source_Fee_Type::CATEGORY_ATTRIBUTE_FEE)
                ->setOrder(Cminds_Marketplace_Model_Config_Source_Fee_Type::CATEGORY_ATTRIBUTE_FEE, 'DESC');

            $_fee = $categories->getFirstItem()->getData(Cminds_Marketplace_Model_Config_Source_Fee_Type::CATEGORY_ATTRIBUTE_FEE);
        }
        
        if($_fee == null || trim($_fee) == "" ) {
            $customerObj = Mage::getModel('customer/customer')->load($p->getCreatorId());
            $_fee = $customerObj->getData(Cminds_Marketplace_Model_Config_Source_Fee_Type::VENDOR_ATTRIBUTE_FEE);
            
            if($_fee != null && trim($_fee) != "") {
                return $_fee;
            } else {
                $_fee = null;
            }
            
        }
        
        if(!$_fee || trim($_fee) == "") {
            $_fee = Mage::getStoreConfig('marketplace_configuration/general/default_percentage_fee');
        }
        
        return $_fee;
    }
    
    public function getFeeType($product) {
        $_feeType = null;
        
        if(is_object($product)) {
            $p = $product;
        } else {
            $p = Mage::getModel('catalog/product')->load($product);
        }
        
        if($p->getData(Cminds_Marketplace_Model_Config_Source_Fee_Type::PRODUCT_ATTRIBUTE_FEE_TYPE) &&
                in_array($p->getData(Cminds_Marketplace_Model_Config_Source_Fee_Type::PRODUCT_ATTRIBUTE_FEE_TYPE), Cminds_Marketplace_Model_Config_Source_Fee_Type::toValidate())){
            $_feeType = $p->getData(Cminds_Marketplace_Model_Config_Source_Fee_Type::PRODUCT_ATTRIBUTE_FEE_TYPE);
            return $_feeType;
        }
        
        if(($_feeType == null || trim($_feeType) == "" )) {
            $categories = $product->getCategoryIds();
            $categories[] = 9;

            $categories = Mage::getModel('catalog/category')
                ->getCollection()
                ->addFieldToFilter('entity_id', array('in' => $categories))
                ->addFieldToFilter('is_active', array('eq' => '1'))
                ->addAttributeToSelect(Cminds_Marketplace_Model_Config_Source_Fee_Type::CATEGORY_ATTRIBUTE_FEE, Cminds_Marketplace_Model_Config_Source_Fee_Type::CATEGORY_ATTRIBUTE_FEE_TYPE)
                ->setOrder(Cminds_Marketplace_Model_Config_Source_Fee_Type::CATEGORY_ATTRIBUTE_FEE_TYPE, 'DESC');

            $_feeType = $categories->getFirstItem()->getData(Cminds_Marketplace_Model_Config_Source_Fee_Type::CATEGORY_ATTRIBUTE_FEE_TYPE);
        }
        
        if(($_feeType == null || trim($_feeType) == "" )) {
            $customerObj = Mage::getModel('customer/customer')->load($p->getCreatorId());
            $_feeType = $customerObj->getData(Cminds_Marketplace_Model_Config_Source_Fee_Type::VENDOR_ATTRIBUTE_FEE_TYPE);

            if(in_array($customerObj->getData(Cminds_Marketplace_Model_Config_Source_Fee_Type::VENDOR_ATTRIBUTE_FEE_TYPE), Cminds_Marketplace_Model_Config_Source_Fee_Type::toValidate())) {
                return $_feeType;
            } else {
                $_feeType = null;
            }
        }
        
        if(!$_feeType || trim($_feeType) == "") {
            $_feeType = Mage::getStoreConfig('marketplace_configuration/general/fee_type');
        }
        
        return $_feeType;
    }
}
