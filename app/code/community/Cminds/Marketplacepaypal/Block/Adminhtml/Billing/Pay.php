<?php

class Cminds_Marketplacepaypal_Block_Adminhtml_Billing_Pay extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();

        $this->_objectId = 'id';
        $this->_blockGroup = 'marketplacepaypal';
        $this->_controller = 'adminhtml_billing';
        $this->_mode = 'pay';
    }

    public function getHeaderText()
    {
        return Mage::helper('marketplace')->__('Pay Supplier "%s %s" for Order "%s"', $this->escapeHtml(Mage::registry('supplier_data')->getFirstname()), $this->escapeHtml(Mage::registry('supplier_data')->getLastname()), $this->escapeHtml(Mage::registry('payment_data')->getOrderId()));
    }

}