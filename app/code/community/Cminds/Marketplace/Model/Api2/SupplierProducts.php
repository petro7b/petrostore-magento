<?php
class Cminds_Marketplace_Model_Api2_SupplierProducts extends Mage_Api2_Model_Resource
{
    public function _retrieveCollection() {
        $id = $this->getRequest()->getParam("id");
        if(!is_numeric($id)) {
            $vendor = Mage::getModel("customer/customer")->load("supplier_name", $id);

            if(!$vendor->getId()){
                return false;
            }

            $id = $vendor->getId();
        }

        $supplierData = Mage::getModel("marketplace/core_api")
            ->getProductsBySupplierId($id);
        $response = array("success" => true);
        $response['data'] = json_decode($supplierData);
        Mage::log(json_encode($response), null, '123232.log');
        return json_decode($response);
    }
}