<?php
class Cminds_Marketplace_Block_Adminhtml_Billing_List extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_blockGroup = 'marketplace';
        $this->_controller = 'adminhtml_billing_list';
        $this->_headerText = $this->__('Suppliers Billing Report');

        parent::__construct();

        $this->_removeButton('add');
    }
}