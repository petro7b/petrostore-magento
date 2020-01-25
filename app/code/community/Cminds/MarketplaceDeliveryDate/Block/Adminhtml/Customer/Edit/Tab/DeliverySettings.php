<?php
class Cminds_MarketplaceDeliveryDate_Block_Adminhtml_Customer_Edit_Tab_DeliverySettings
    extends Mage_Adminhtml_Block_Template
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{

    public function __construct()
    {
        parent::_construct();

        $this->setTemplate('marketplace_delivery_date/customer/tab/view/deliverySettings.phtml');
    }

    /**
     * Return Tab label
     *
     * @return string
     */
    public function getTabLabel()
    {
        return $this->__('Delivery Settings');
    }

    /**
     * Return Tab title
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->__('Delivery Settings');
    }

    /**
     * Can show tab in tabs
     *
     * @return boolean
     */
    public function canShowTab()
    {
        $customerId = Mage::registry('current_customer')->getId();

        if (!$customerId) {
            return false;
        }

        $isSupplier = Mage::helper('supplierfrontendproductuploader')->isSupplier(
            Mage::registry('current_customer')->getId()
        );

        if (!$isSupplier) {
            return false;
        }

        return true;
    }

    /**
     * Tab is hidden
     *
     * @return boolean
     */
    public function isHidden()
    {
        return false;
    }

    public function getHeaderHtml()
    {
        return null;
    }

    public function isSelected($value)
    {
        $customer = Mage::registry('current_customer');

        if ($customer->getData('supplier_min_order_amount_per') != $value) {
            return false;
        }

        return true;
    }

    public function getComment()
    {
        $comment = Mage::helper('marketplaceamount')->__(
            'In Base store currency -> %s',
            Mage::app()->getStore()->getBaseCurrencyCode()
        );

        return $comment;
    }
}