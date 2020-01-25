<?php
class Cminds_Marketplace_Model_Api2_SearchStore extends Mage_Api2_Model_Resource
{
    public function _retrieveCollection() {
        $id = $this->getRequest()->getParam("id");
        $category = $this->getRequest()->getParam("category_id");

        $productCollection = Mage::getModel('catalog/category')
                                 ->load($category)
                                 ->getProductCollection()
                                 ->addAttributeToSelect('*')
                                 ->addAttributeToFilter('status', 1)
                                 ->addAttributeToFilter('visibility', 4)
                                 ->setOrder('price', 'ASC');

        $suppliers = array();

        foreach($productCollection AS $product) {
            $suppliers[] = $product->getCreatorId();
        }

        if(!$suppliers) {
            return array();
        }

        $customerCollection = Mage::getModel("customer/customer")
                                  ->getCollection()
                                  ->addFilter("entity_id", array("in" => $suppliers));

        $response = array();
        foreach($customerCollection AS $c) {
            $supplierLogo = Mage::helper('marketplace')->getSupplierLogo($c->getId());

            $response[] = array(
                'supplierId' => $c->getId(),
                'supplier_name' => $c->getSupplierName(),
                'supplier_logo' => $supplierLogo ? $supplierLogo : NULL,
                'supplier_address' => ($c->getPrimaryBillingAddress()) ? join(' ', $c->getPrimaryBillingAddress()->getStreet()) : '',
                'supplier_city' => ($c->getPrimaryBillingAddress()) ? $c->getPrimaryBillingAddress()->getCity() : '',
                'supplier_phone' => ($c->getPrimaryBillingAddress()) ? $c->getPrimaryBillingAddress()->getTelephone() : '',
                'dateofsubscription' => $c->getPlanFromDate(),
            );
        }

        Mage::dispatchEvent(
            'marketplace_api_search_by_product_category_response',
            array(
                'data' => $response
            )
        );

        return $response;
    }
}