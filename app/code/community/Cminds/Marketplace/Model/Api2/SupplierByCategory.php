<?php
class Cminds_Marketplace_Model_Api2_SupplierByCategory extends Mage_Api2_Model_Resource
{
    public function _retrieveCollection() {
        $id = $this->getRequest()->getParam("id");
        try {
            $collection = Mage::getModel("marketplace/core_api")
                              ->getSuppliersByCategory($id);

            $response = array("success" => true);
            $response["data"] = $collection;

            $this
                ->getResponse()
                ->clearHeaders()
                ->setHeader('Content-type','application/json',true)
                ->setBody(json_encode($response));
            return $response;
        }catch(Exception $e) {
            Mage::logException($e);
        }
    }
}