<?php

class Cminds_Marketplaceproductquestions_Block_Adminhtml_Product_Questions extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function _construct()
    {
        $this->_blockGroup = 'marketplaceproductquestions';
        $this->_controller = 'adminhtml_product_questions';
        $this->_headerText = $this->__('Questions');

        parent::_construct();
    }

    protected function _prepareLayout()
    {
        $this->_removeButton('add');
        return parent::_prepareLayout();
    }

}