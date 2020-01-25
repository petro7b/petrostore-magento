<?php
class Cminds_Marketplace_Model_Api2_SupplierProduct extends Mage_Api2_Model_Resource
{
    public function _retrieve() {
        $id = $this->getRequest()->getParam("id");

        if(!is_int($id)) {
            $vendor = Mage::getModel("customer/customer")->load("supplier_name", $id);

            if(!$vendor->getId()){
                return false;
            }

            $id = $vendor->getId();
        }

        $supplierData = Mage::getModel("marketplace/core_api")
                            ->getProductById($id);

        Mage::dispatchEvent(
            'marketplace_api_get_supplier_product_response',
            array(
                'data' => $supplierData
            )
        );

        return json_decode($supplierData);
    }
}