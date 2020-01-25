<?php

/**
 * Cminds OAPM adminhtml sales order block.
 *
 * @category    Cminds
 * @package     Cminds_Oapm
 * @author      Piotr Pierzak <piotrek.pierzak@gmail.com>
 */
class Cminds_Oapm_Block_Adminhtml_Sales_Order extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        parent::__construct();

        $this->_blockGroup = 'cminds_oapm';
        $this->_controller = 'adminhtml_sales_order';
        $this->_headerText = Mage::helper('cminds_oapm')->__('Pending Approval Orders');

        $this->_removeButton('add');
    }
}
