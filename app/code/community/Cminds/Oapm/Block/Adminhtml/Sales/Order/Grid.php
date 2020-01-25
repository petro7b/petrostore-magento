<?php

/**
 * Cminds OAPM adminhtml sales order grid block.
 *
 * @category    Cminds
 * @package     Cminds_Oapm
 * @author      Piotr Pierzak <piotrek.pierzak@gmail.com>
 */
class Cminds_Oapm_Block_Adminhtml_Sales_Order_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Cminds_Oapm_Block_Adminhtml_Sales_Order_Grid constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('sales_order_grid');
        $this->setUseAjax(true);
        $this->setDefaultSort('created_at');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    /**
     * Retrieve collection class.
     *
     * @return string
     */
    protected function _getCollectionClass()
    {
        return 'sales/order_grid_collection';
    }

    /**
     * Prepare grid collection object.
     *
     * @return Cminds_Oapm_Block_Adminhtml_Sales_Order_Grid
     */
    protected function _prepareCollection()
    {
        /** @var Mage_Sales_Model_Resource_Order_Grid_Collection $collection */
        $collection = Mage::getResourceModel($this->_getCollectionClass());
        $collection
            ->getSelect()
            ->join(
                array('cho' => 'cminds_oapm_order'),
                'cho.order_id = main_table.entity_id',
                array('oapm_status' => 'cho.status')
            );

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * Prepare columns.
     *
     * @return Cminds_Oapm_Block_Adminhtml_Sales_Order_Grid
     * @throws Exception
     */
    protected function _prepareColumns()
    {
        /** @var Cminds_Oapm_Helper_Data $helper */
        $helper = Mage::helper('cminds_oapm');

        $this->addColumn('real_order_id', array(
            'header' => $helper->__('Order #'),
            'width' => '80px',
            'type' => 'text',
            'index' => 'increment_id',
        ));

        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('store_id', array(
                'header' => $helper->__('Purchased From (Store)'),
                'index' => 'store_id',
                'type' => 'store',
                'store_view' => true,
                'display_deleted' => true,
            ));
        }

        $this->addColumn('created_at', array(
            'header' => $helper->__('Purchased On'),
            'index' => 'created_at',
            'type' => 'datetime',
            'width' => '100px',
        ));

        $this->addColumn('billing_name', array(
            'header' => $helper->__('Creator Name'),
            'index' => 'billing_name',
        ));

        $this->addColumn('payer_name', array(
            'header' => $helper->__('Payer Name'),
            'index' => 'payer_name',
            'renderer' => 'Cminds_Oapm_Block_Adminhtml_Sales_Order_Grid_Renderer_Column_Payer_Name',
        ));

        $this->addColumn('base_grand_total', array(
            'header' => $helper->__('G.T. (Base)'),
            'index' => 'base_grand_total',
            'type' => 'currency',
            'currency' => 'base_currency_code',
        ));

        $this->addColumn('grand_total', array(
            'header' => $helper->__('G.T. (Purchased)'),
            'index' => 'grand_total',
            'type' => 'currency',
            'currency' => 'order_currency_code',
        ));

        $this->addColumn('status', array(
            'header' => $helper->__('Status'),
            'index' => 'status',
            'type' => 'options',
            'width' => '70px',
            'options' => Mage::getSingleton('sales/order_config')->getStatuses(),
        ));

        $this->addColumn('oapm_status', array(
            'header' => $helper->__('OAPM Status'),
            'index' => 'oapm_status',
            'type' => 'options',
            'width' => '70px',
            'options' => Mage::getSingleton('cminds_oapm/order')->getStatuses(),
        ));

        if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/view')) {
            $this->addColumn('action',
                array(
                    'header' => $helper->__('Action'),
                    'width' => '50px',
                    'type' => 'action',
                    'getter' => 'getId',
                    'actions' => array(
                        array(
                            'caption' => $helper->__('View'),
                            'url' => array('base' => '*/sales_order/view'),
                            'field' => 'order_id'
                        ),
                        array(
                            'caption' => $helper->__('Approve'),
                            'url' => array('base' => '*/adminhtml_sales_order_oapm/approve'),
                            'field' => 'order_id',
                            'confirm' => Mage::helper('catalog')->__('Are you sure?')
                        )
                    ),
                    'filter' => false,
                    'sortable' => false,
                    'index' => 'stores',
                    'is_system' => true,
                ));
        }

        $this->addExportType('*/*/exportCsv', $helper->__('CSV'));
        $this->addExportType('*/*/exportExcel', $helper->__('Excel XML'));

        return parent::_prepareColumns();
    }

    /**
     * Return row url.
     *
     * @param   $row
     * @return  bool|string
     */
    public function getRowUrl($row)
    {
        if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/view')) {
            return $this->getUrl('*/sales_order/view', array('order_id' => $row->getId()));
        }
        return false;
    }

    /**
     * Return grid url.
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current' => true));
    }
}
