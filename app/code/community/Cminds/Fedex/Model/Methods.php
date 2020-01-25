<?php

class Cminds_Fedex_Model_Methods extends Cminds_Marketplace_Model_Methods
{
    public function isFedex()
    {
        return (bool)$this->getFedex();
    }

    public function requestFedexRates($items)
    {
        $fedexModel = Mage::getModel("cminds_fedex/shipping_fedex");
        $requestModel = Mage::getModel('shipping/rate_request');
        $_items = array();
        $vendor_id = false;
        foreach ($items as $item) {
            $_item = Mage::getModel("catalog/product")->load($item->getProductId());
            $vendor_id = Mage::helper('marketplace')->getProductSupplierId($_item);
            $_items[] = $_item;
        }
        $requestModel->setAllItems($_items);
        $requestModel->setVendorId($vendor_id);

        $result = $fedexModel->collectRates($requestModel);

        return $result;
    }
}