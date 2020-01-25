<?php
class Cminds_Marketplacepaypal_Block_Adminhtml_Billing_List_Grid extends Cminds_Marketplace_Block_Adminhtml_Billing_List_Grid
{
    protected function _prepareColumns()
    {
        parent::_prepareColumns();

        $this->addColumn('action',
            array(
                'header'    =>  Mage::helper('marketplace')->__('Action'),
                'width'     => '100',
                'type'      => 'action',
                'renderer'  => 'Cminds_Marketplacepaypal_Block_Adminhtml_Billing_List_Grid_Renderer_Action',
                'totals_label' => '',
                'filter'    => false,
                'sortable'  => false,
                'is_system' => true,
            )
        );

    }
}