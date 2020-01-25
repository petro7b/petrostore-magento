<?php

class Cminds_Marketplaceproductquestions_Block_Product_Answers extends Mage_Catalog_Block_Product_View
{

    public function getAnswer($questionId)
    {
        $answers = Mage::getModel('marketplaceproductquestions/answers')
            ->load($questionId, 'question_id');
        return $answers;
    }

    protected function _toHtml()
    {
        if (Mage::helper('marketplaceproductquestions')->isEnabled()) {
            if (!$this->getTemplate()) {
                return '';
            }
            $html = $this->renderView();
            return $html;
        }
    }

}