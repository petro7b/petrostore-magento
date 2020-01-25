<?php
class Cminds_Marketplace_Model_Layer extends Mage_Catalog_Model_Layer
{
    public function getProductCollection()
    {

        if (isset($this->_productCollections[$this->getCurrentCategory()->getId()])) {
            $collection = $this->_productCollections[$this->getCurrentCategory()->getId()];
        } else {
            $collection = Mage::helper('marketplace')->getSupplierProducts(
                Mage::app()->getRequest()->getParam('id')
            );

            $this->prepareProductCollection($collection);
            $this->_productCollections[$this->getCurrentCategory()->getId()] = $collection;
        }
        return $collection;
    }
}