<?php
class Cminds_Marketplace_Model_Mysql4_Report_Bestsellers_Collection extends Mage_Sales_Model_Resource_Report_Bestsellers_Collection {
    protected function _applyStoresFilterToSelect(Zend_Db_Select $select)
    {
        $nullCheck = false;
        $storeIds  = $this->_storesIds;

        if (!is_array($storeIds)) {
            $storeIds = array($storeIds);
        }

        $storeIds = array_unique($storeIds);

        if ($index = array_search(null, $storeIds)) {
            unset($storeIds[$index]);
            $nullCheck = true;
        }

        $storeIds[0] = ($storeIds[0] == '') ? 0 : $storeIds[0];
        $selectParams = $select->getPart(Zend_Db_Select::FROM);
        $tableNames = array_keys($selectParams);

        if ($nullCheck) {
            $select->where($tableNames[0] . '.store_id IN(?) OR e.store_id IS NULL', $storeIds);
        } else {
            $select->where($tableNames[0] . '.store_id IN(?)', $storeIds);
        }

        return $this;
    }

    protected function _makeBoundarySelect($from, $to)
    {
        $adapter = $this->getConnection();
        $cols    = $this->_getSelectedColumns();
        $cols['qty_ordered'] = 'SUM(qty_ordered)';
        $sel     = $adapter->select()
            ->from($this->getResource()->getMainTable(), $cols)
            ->joinInner(array('eav' => $this->_getEntityIntTable()), 'eav.entity_id = product_id AND eav.attribute_id = ' . $this->_getAttributeId(), array() )
        ->where('eav.value = ?', Mage::helper('marketplace')->getSupplierId())
        ->where('period >= ?', $from)
        ->where('period <= ?', $to)
        ->group('product_id')
        ->order('qty_ordered DESC')
        ->limit($this->_ratingLimit);

        $this->_applyStoresFilterToSelect($sel);

        return $sel;
    }

    protected function _getEntityIntTable() {
        return Mage::getSingleton("core/resource")->getTableName('catalog_product_entity_int');
    }

    protected function _getAttributeId() {
        $eavAttribute   = new Mage_Eav_Model_Mysql4_Entity_Attribute();
        return $eavAttribute->getIdByCode('catalog_product', 'creator_id');
    }
}
