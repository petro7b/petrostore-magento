<?php
class Cminds_Marketplace_Model_Api2_Supplier extends Mage_Api2_Model_Resource
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
            ->getSupplier($id);

        Mage::dispatchEvent(
            'marketplace_api_supplier_data_response',
            array(
                'data' => $supplierData
            )
        );
        $response = array("success" => true);
        $response['data'] = json_decode($supplierData);
        $this
            ->getResponse()
            ->clearHeaders()
            ->setHeader('Content-type','application/json',true)
            ->setBody(json_encode($response));
        return $response;
    }
}