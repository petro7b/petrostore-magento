<?php

/**
 * Class Cminds_Assignsupplierstocustomers_Block_Adminhtml_Customer_Edit_Tab_Assignedvendors
 */
class Cminds_MarketplaceDeliveryDate_Block_Adminhtml_Customer_Edit_Tab_Grid_ExcludedDates
    extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * @return mixed
     */
    public function getCustomer()
    {
        return Mage::registry('current_customer');
    }

    /**
     * Cminds_Assignsupplierstocustomers_Block_Adminhtml_Customer_Edit_Tab_Assignedvendors constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->setId('customer_edit_tab_excludeddates');
        $this->setUseAjax(true);
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    /**
     * Prepare collection
     *
     * @return Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Super_Config_Grid
     */
    protected function _prepareCollection()
    {
        $customer = $this->getCustomer();

        $collection = Mage::getModel('marketplace_delivery_date/excluded')->getCollection();
        $collection
            ->addFieldToFilter('supplier_id', $customer->getId());

        $this->setCollection($collection);

        parent::_prepareCollection();

        return $this;
    }


    /**
     * @return $this
     * @throws Exception
     */
    protected function _prepareColumns()
    {
        $helper = Mage::helper('marketplace_delivery_date');

        $this->addColumn('id', array(
            'header'    => $helper->__('Id'),
            'type'      => 'int',
            'name'      => 'id',
            'align'     => 'left',
            'index'     => 'id',
            'width'     => '20px'
        ));

        $this->addColumn('first_name', array(
            'header'    => $helper->__('Date'),
            'type'      => 'date',
            'name'      => 'date',
            'align'     => 'left',
            'index'     => 'date',
            'width'     => '200px'

        ));

        $this->addColumn('action', array(
                'header'    => 'Action',
                'width'     => '50px',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption' => $helper->__('Delete'),
                        'url'       => array('base'=> '*/excludedDates/delete'),
                        'field'     => 'id'
                    )
                )
        ));

        return parent::_prepareColumns();
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/excludedDates/grid', array('_current' => true));
    }

    /**
     * @param $row
     * @return bool
     */
    public function getRowUrl($row)
    {
        return false;
    }

}
