<?php

class Cminds_Marketplaceproductquestions_Block_Product_View extends Mage_Catalog_Block_Product_View
{
    public function getQuestions($productId)
    {
        $questions = Mage::getModel('marketplaceproductquestions/questions')
            ->getCollection()
            ->addFieldToFilter('product_id', $productId)
            ->addFieldToFilter('visibility', 1);
        return $questions;
    }

    protected function _toHtml()
    {
        $isEnabled = Mage::getStoreConfig('marketplace_productquestions/general/module_enabled');

        if ($isEnabled) {
            return parent::_toHtml();
        }
    }
}