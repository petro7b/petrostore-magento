<?php
class Cminds_Marketplaceproductquestions_Model_Mysql4_Answers extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('marketplaceproductquestions/answers', 'id');
    }
}