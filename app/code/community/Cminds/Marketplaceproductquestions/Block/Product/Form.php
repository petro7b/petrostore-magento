<?php

class Cminds_Marketplaceproductquestions_Block_Product_Form extends Mage_Catalog_Block_Product_View
{
    protected function _toHtml()
    {
        $productId = $this->getProduct()->getId();
        $questionsLimit = Mage::getStoreConfig('marketplace_productquestions/general/default_max_questions');

        $questions = Mage::getModel('marketplaceproductquestions/questions')
            ->getCollection()
            ->addFieldToFilter('product_id', $productId);
        $numberOfQuestions = $questions->count();
        if (Mage::helper('marketplaceproductquestions')->isEnabled() && ($numberOfQuestions <= $questionsLimit || $questionsLimit == 0)) {
            return parent::_toHtml();
        }
    }
}
