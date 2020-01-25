<?php

class Cminds_Marketplaceproductquestions_Block_Questions_List
    extends Mage_Core_Block_Template
{
    public function getItems() {
        $supplier_id = Mage::helper('supplierfrontendproductuploader')->getSupplierId();

        $collection = Mage::getResourceModel('marketplaceproductquestions/questions_collection')
        ->addFieldToFilter('supplier_id', $supplier_id)
        ->setOrder('id');

        $page = Mage::app()->getRequest()->getParam('p', 1);
        $collection->setPageSize(10)->setCurPage($page);

        return $collection;
    }

    public function getAnswer($questionId) {
        $answer = Mage::getModel('marketplaceproductquestions/answers')->load($questionId, 'question_id');

        return $answer;
    }

    public function getProductUrl($id) {
        $product = Mage::getModel('catalog/product')->load($id);
        return $product->getProductUrl();
    }
}