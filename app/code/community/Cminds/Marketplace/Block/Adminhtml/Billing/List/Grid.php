<?php

class Cminds_Marketplace_Block_Adminhtml_Billing_List_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    protected $_countTotals = true;

    public function __construct()
    {
        parent::__construct();

        $this->setDefaultSort('id');
        $this->setId('billing_list_grid');
        $this->setDefaultDir('asc');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        /**
         * @var Mage_Core_Model_Resource $coreResourceModel
        */
        $coreResourceModel = Mage::getSingleton("core/resource");
        $code = $this->getEavAttrCode();

        $isDiscountEff = Mage::getStoreConfig('marketplace_configuration/general/is_discount_effective');

        $tableName = $coreResourceModel->getTableName("catalog_product_entity_int");
        $orderTable = $coreResourceModel->getTableName('sales/order');
        $supplierPayment = $coreResourceModel->getTableName('marketplace/payments');

        $collection = Mage::getResourceModel('marketplace/report_billing');
        $helper = Mage::helper('marketplace');

        if ($helper->isBillingReportInclTax() == Cminds_Marketplace_Model_Config_Source_Billing_Calculation::INCL_TAX) {
            $total = 'row_total_incl_tax';
        } else {
            $total = 'row_total';
        }

        if ($isDiscountEff) {
            $collection->addExpressionFieldToSelect(
                'vendor_amount_with_discount',
                '(' . $total . '-main_table.discount_amount)-((' . $total . '-main_table.discount_amount)*(vendor_fee/100))',
                'vendor_fee'
            );
        }

        $collection->addExpressionFieldToSelect(
            'vendor_amount',
            $total . '-(' . $total . ' *(vendor_fee/100))',
            'vendor_fee'
        );
        

        $collection->getSelect()
            ->joinInner(
                array('o' => $orderTable),
                'o.entity_id = main_table.order_id',
                array('status', 'state', 'subtotal', 'increment_id')
            )
            ->joinInner(
                array('e' => $tableName),
                'e.entity_id = main_table.product_id AND e.attribute_id = ' . $code,
                array('value as supplier_id')
            )
            ->joinLeft(
                array('p' => $supplierPayment),
                'p.order_id = main_table.order_id AND p.supplier_id = supplier_id',
                array('amount AS payment_amount', 'payment_date', 'id AS payment_id')
            )
            ->where('main_table.parent_item_id is null')
            ->where('e.value IS NOT NULL')
            ->where('o.state != "canceled"')
            ->group('o.entity_id')
            ->group('e.value');


        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $isDiscountEff = Mage::getStoreConfig('marketplace_configuration/general/is_discount_effective');
        $currencyCode = (string)Mage::getStoreConfig(Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE);
        $this->addColumn('supplier_id', array(
            'header' => Mage::helper('marketplace')->__('Vendor ID'),
            'width' => '50px',
            'index' => 'supplier_id',
            'filter_index' => 'e.value'
        ));
        $this->addColumn('order_id', array(
            'header' => Mage::helper('marketplace')->__('Order'),
            'index' => 'increment_id',
            'type' => 'number',
        ));
        $this->addColumn('created_at', array(
            'header' => Mage::helper('marketplace')->__('Date'),
            'index' => 'created_at',
            'type' => 'datetime',
            'filter_index' => 'o.created_at',
            'gmtoffset' => true
        ));
        $this->addColumn('order_status', array(
            'header' => Mage::helper('marketplace')->__('Order Status'),
            'width' => '150',
            'index' => 'status',
            'type' => 'options',
            'options' => Mage::getSingleton('sales/order_config')->getStatuses(),
        ));

        $this->addColumn('subtotal', array(
            'header' => Mage::helper('marketplace')->__('Products Price'),
            'width' => '100',
            'index' => 'row_total',
            'type' => 'price',
            'currency_code' => $currencyCode,
        ));

        $this->addColumn('row_total_incl_tax', array(
            'header' => Mage::helper('marketplace')->__('Total Price'),
            'width' => '100',
            'index' => 'row_total_incl_tax',
            'type' => 'price',
            'currency_code' => $currencyCode,
        ));

        $this->addColumn('vendor_amount', array(
            'header' => Mage::helper('marketplace')->__('Net Income'),
            'width' => '100',
            'index' => 'vendor_amount',
            'type' => 'price',
            'currency_code' => $currencyCode,
        ));

        if ($isDiscountEff) {
            $this->addColumn('discount_amount', array(
                'header' => Mage::helper('marketplace')->__('Discount'),
                'width' => '100',
                'index' => 'discount_amount',
                'type' => 'price',
                'currency_code' => $currencyCode,
            ));


            $this->addColumn('vendor_amount_with_discount', array(
                'header' => Mage::helper('marketplace')->__('With discount'),
                'width' => '100',
                'index' => 'vendor_amount_with_discount',
                'type' => 'price',
                'currency_code' => $currencyCode,
            ));
        }

        $this->addColumn('payed_amount', array(
            'header' => Mage::helper('marketplace')->__('Payed Amount'),
            'width' => '90',
            'index' => 'payment_amount',
            'type' => 'price',
            'currency_code' => $currencyCode,
        ));

        $this->addColumn('payed_date', array(
            'header' => Mage::helper('marketplace')->__('Payed Date'),
            'index' => 'payment_date',
            'type' => 'datetime',
            'gmtoffset' => true
        ));

        $this->addColumn('owning', array(
            'header' => Mage::helper('marketplace')->__('Owing'),
            'index' => 'owning',
            'totals_label' => '',
            'filter' => false,
            'align' => 'center',
            'type' => 'price',
            'renderer' => 'Cminds_Marketplace_Block_Adminhtml_Billing_List_Grid_Renderer_Owning'
        ));

        if ($isDiscountEff) {
            $this->addColumn('owning_with_discount', array(
                'header' => Mage::helper('marketplace')->__('With Discount'),
                'index' => 'owning_with_discount',
                'totals_label' => '',
                'filter' => false,
                'align' => 'center',
                'type' => 'price',
                'renderer' => 'Cminds_Marketplace_Block_Adminhtml_Billing_List_Grid_Renderer_Owningdiscount'
            ));
        }

        $this->addColumn('action',
            array(
                'header' => Mage::helper('marketplace')->__('Action'),
                'width' => '100',
                'type' => 'action',
                'renderer' => 'Cminds_Marketplace_Block_Adminhtml_Billing_List_Grid_Renderer_Action',
                'totals_label' => '',
                'filter' => false,
                'sortable' => false,
                'is_system' => true,
            ));

        $this->addExportType('*/*/exportCsv', Mage::helper('marketplace')->__('CSV'));
        $this->addExportType('*/*/exportXml', Mage::helper('marketplace')->__('Excel XML'));

        return parent::_prepareColumns();
    }

    public function getTotals()
    {
        $totals = new Varien_Object();
        $fields = array(
            'payment_amount' => 0,
            'vendor_amount' => 0,
            'subtotal' => 0,
            'owning' => 0,
        );
        foreach ($this->getCollection() as $item) {
            foreach ($fields as $field => $value) {
                if ($field == 'owning') {
                } else {
                    $fields[$field] += $item->getData($field);
                }
            }
        }
        $fields['supplier_id'] = 'Totals';
        $totals->setData($fields);
        return $totals;
    }

    private function getEavAttrCode()
    {
        $eavAttribute = new Mage_Eav_Model_Mysql4_Entity_Attribute();
        return $eavAttribute->getIdByCode('catalog_product', 'creator_id');
    }

    public function getRowUrl($row)
    {
        return $this->getUrl(
            '*/*/edit',
            array(
                'order_id' => $row->getOrderId(),
                'supplier_id' => $row->getSupplierId()
            )
        );
    }
}