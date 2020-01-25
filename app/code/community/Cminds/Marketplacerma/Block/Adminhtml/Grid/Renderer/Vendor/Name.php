<?php

class Cminds_Marketplacerma_Block_Adminhtml_Grid_Renderer_Vendor_Name
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $value = $row->getData($this->getColumn()->getIndex());
        $_product = Mage::getModel("catalog/product")->load($row->getProductId());
        $customer = Mage::getModel('customer/customer')->load($_product->getCreatorId());
        if($customer->getId()) {
            $ret = "<a href='".Mage::helper("adminhtml")->getUrl("adminhtml/customer/edit/",
                    array("id"=>$_product->getCreatorId()))."'>" . $customer->getName() . "</a>";
        } else {
            $ret = $value;
        }

        return $ret;
    }
}

?>