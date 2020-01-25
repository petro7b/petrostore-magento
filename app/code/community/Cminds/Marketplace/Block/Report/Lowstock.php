<?php
class Cminds_Marketplace_Block_Report_Lowstock extends Cminds_Marketplace_Block_Report_Abstract {
    protected $_resourceModel = 'reports/product_lowstock_collection';
    protected $_columns = array('Product Name', 'Product SKU', 'Stock Qty');
    protected $_removeIndexes = array('entity_id');
    protected $_availableIndexes = array('name', 'sku', 'qty');
    public $title = 'Low Stock';

    protected function _prepareCollection() {
        $collection = Mage::getResourceModel($this->_resourceModel)
            ->addAttributeToSelect('*')
            ->setStoreId(1)
            ->filterByIsQtyProductTypes()
            ->joinInventoryItem('qty')
            ->useManageStockFilter(1)
            ->useNotifyStockQtyFilter(1)
            ->setOrder('qty', Varien_Data_Collection::SORT_ORDER_ASC);

        $collection->getSelect()->joinInner(array('eav' => $this->_getEntityIntTable()), 'eav.entity_id = product_id AND eav.attribute_id = ' . $this->_getAttributeId(), array());
        $collection->getSelect()->where('eav.value = ?', $this->_getSupplierId());
        $collection->getSelect()->group('eav.entity_id');

        return $collection->load();
    }

    protected function _getAttributeId() {
        $eavAttribute   = new Mage_Eav_Model_Mysql4_Entity_Attribute();
        return $eavAttribute->getIdByCode('catalog_product', 'creator_id');
    }

}