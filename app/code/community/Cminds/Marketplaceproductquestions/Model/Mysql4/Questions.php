<?php
class Cminds_Marketplaceproductquestions_Model_Mysql4_Questions extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('marketplaceproductquestions/questions', 'id');
    }
}