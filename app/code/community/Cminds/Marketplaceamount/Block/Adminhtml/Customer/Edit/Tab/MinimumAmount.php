<?php

class Cminds_Marketplaceamount_Block_Adminhtml_Customer_Edit_Tab_MinimumAmount
    extends Mage_Adminhtml_Block_Template
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{

    public function __construct()
    {
        parent::_construct();

        $this->setTemplate('cminds/marketplaceamount/customer/tab/view/minimumAmount.phtml');
    }

    /**
     * Return Tab label
     *
     * @return string
     */
    public function getTabLabel()
    {
        return $this->__('Minimum Order Amount');
    }

    /**
     * Return Tab title
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->__('Minimum Order Amount');
    }

    /**
     * Can show tab in tabs
     *
     * @return boolean
     */
    public function canShowTab()
    {
        $isSupplier = Mage::helper('supplierfrontendproductuploader')->isSupplier(
            Mage::registry('current_customer')->getId()
        );

        $minAmountEnabled = Mage::getStoreConfig(
            'marketplaceamount_configuration/general/enabled'
        );

        if (!$isSupplier || !$minAmountEnabled) {
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
        $minOrderType = (int)$customer->getData('supplier_min_order_amount_per');
        if ($minOrderType !== $value) {
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