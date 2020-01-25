<?php

class Cminds_Marketplace_Block_Adminhtml_Customer_Edit_Tab_Grid_Net
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    /**
     * Rendering supplier's income by products.
     *
     * @param Varien_Object $row
     *
     * @return int
     */
    public function render(Varien_Object $row)
    {
        $supplierIncome = $row->getData(
            $this
                ->getColumn()
                ->getIndex()
        );

        $qtyProducts = $row->getData('qty_ordered');
        $renderValue = Mage::helper('core')
            ->currency(
                Mage::helper('marketplace/profits')->calculateNetIncome($supplierIncome, $qtyProducts),
                true,
                false
            );

        return $renderValue;
    }

}
