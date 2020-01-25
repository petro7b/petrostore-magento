<?php

class Cminds_Fedex_Block_Adminhtml_Customer_Edit_Tab_Action extends Mage_Adminhtml_Block_Template implements Mage_Adminhtml_Block_Widget_Tab_Interface{

    public function _construct()
    {
        parent::_construct();

        $this->setTemplate('cminds/fedex/action.phtml');
    }

    public function getCustomtabInfo()
    {
        return $this->__("Fedex Account Configuration");
    }

    public function getTabLabel()
    {
        return $this->__('Fedex Account Configuration');
    }

    public function getTabTitle()
    {
        return $this->__('Action Tab');
    }

    public function canShowTab()
    {
        $customer = Mage::registry('current_customer');

        return (bool)$customer->getId();
    }

    public function isHidden()
    {
        return false;
    }

    public function getAfter()
    {
        return 'tags';
    }
}
