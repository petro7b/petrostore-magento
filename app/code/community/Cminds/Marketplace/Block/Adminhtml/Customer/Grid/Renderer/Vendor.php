<?php

class Cminds_Marketplace_Block_Adminhtml_Customer_Grid_Renderer_Vendor
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $customerGroup = $row->getData('group_id');

        $supplierHelper = Mage::helper("supplierfrontendproductuploader");
        $allowedGroups = $supplierHelper->getAllowedGroups();

        return in_array($customerGroup, $allowedGroups) ? $this->__('Yes') : $this->__('No');
    }
}
