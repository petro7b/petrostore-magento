<?php

class Cminds_Marketplace_Block_Adminhtml_Customer_Edit_Tab_Shippingfees extends Mage_Adminhtml_Block_Widget_Form_Container
{

    public function __construct()
    {
        parent::__construct();

        $this->_objectId = 'id';
        $this->_controller = 'adminhtml_customer_edit_tab_shippingfees';
        $this->_blockGroup = 'marketplace';
        $this->_removeButton('save');
        $this->_removeButton('delete');
        $this->_removeButton('back');
        $this->_removeButton('reset');

        $data = array(
            'label' =>  Mage::helper('marketplace')->__('Add New Method'),
            'onclick'   => 'addShippingMethod()',
            'class'     =>  'add'
        );
        $this->addButton ('add_new_method', $data, 0, 100,  'header');
    }

    public function getHeaderHtml()
    {
        return '';
    }
    public function getHeaderCssClass()
    {
        return 'icon-head head-cms-page';
    }
}
