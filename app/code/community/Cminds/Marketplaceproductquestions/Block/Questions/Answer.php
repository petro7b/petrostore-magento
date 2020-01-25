<?php

class Cminds_Marketplaceproductquestions_Block_Questions_Answer
    extends Mage_Core_Block_Template
{
    public function getQuestionId() {
        return Mage::registry('question_id');
    }

    public function getQuestion() {
        return Mage::getModel('marketplaceproductquestions/questions')->load($this->getQuestionId());
    }

    public function getAnswer() {
        return Mage::getModel('marketplaceproductquestions/answers')->load($this->getQuestionId(), 'question_id');
    }
}