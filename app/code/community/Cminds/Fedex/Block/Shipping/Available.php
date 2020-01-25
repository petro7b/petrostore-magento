<?php

class Cminds_Fedex_Block_Shipping_Available extends Cminds_Marketplace_Block_Checkout_Onepage_Shipping_Method_Available
{
    protected $vendorProducts = array();
    protected $raisedVendors = array();
    public function getSupplierMethods()
    {
        $vendors = $this->getCalculatedVendors();
        if (in_array($this->raisedVendors, $this->getSupplierId())) {
            return array();
        }

        $supplierMethods = Mage::getModel("marketplace/methods")->getCollection()
            ->addFilter("fedex", 1)
            ->addFilter("supplier_id", $this->getSupplierId());

        $vendors[] = $this->getSupplierId();

        $this->setCalculatedVendors($vendors);

        return $supplierMethods;
    }

    protected function getCalculatedVendors()
    {
        $requestedVendors = Mage::registry("fedex_requested_vendors");

        if (!$requestedVendors) {
            return array();
        }

        return $requestedVendors;
    }

    protected function setCalculatedVendors($vendors)
    {
        if ($this->getCalculatedVendors()) {
            Mage::unregister("fedex_requested_vendors");
        }

        Mage::register("fedex_requested_vendors", $vendors);
    }

    public function getAllVendorItems()
    {
        if (!$this->vendorProducts) {
            /**
             * @var Cminds_Marketplace_Helper_Data $dataHelper
            */
            $dataHelper = Mage::helper("marketplace");
            $quote = $this->getQuote();
            $items = $quote->getAllVisibleItems();

            foreach ($items as $item) {
                $vendor_id = $dataHelper->getSupplierIdByProductId(
                    $item->getProductId()
                );

                $this->vendorProducts[$vendor_id][] = $item;
            }
        }

        return $this->vendorProducts;
    }

    public function getVendorItems()
    {
        $items = $this->getAllVendorItems();

        if (isset($items[$this->getSupplierId()])) {
            return $items[$this->getSupplierId()];
        }

        return array();
    }

    protected function _toHtml()
    {
        $vendor_id = $this->getParentBlock()->getSupplierId();
        var_dump($vendor_id);

        return false;
//        if()
    }
}
