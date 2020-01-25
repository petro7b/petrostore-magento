<?php
class Cminds_Marketplaceproductquestions_Model_Questions extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('marketplaceproductquestions/questions', 'id');
    }

    public function isApprovedByAdmin() {
        if(!Mage::helper('marketplaceproductquestions')->adminApprovalRequired()) return true;
        return Mage::getModel('marketplaceproductquestions/answers')->load($this->getId(), 'question_id')->getAdminApproval();
    }
}