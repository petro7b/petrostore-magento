<?php

class Cminds_Marketplaceproductquestions_Block_Adminhtml_Product_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();

        $this->_objectId = 'id';
        $this->_blockGroup = 'marketplaceproductquestions';
        $this->_controller = 'adminhtml_product_questions';
        $this->_mode = 'edit';
    }

    public function getHeaderText()
    {
        return Mage::helper('marketplaceproductquestions')->__('Manage Your Answer');
    }
}