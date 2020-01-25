<?php

/**
 * Class Cminds_MultiUserAccounts_Model_Resource_Sales_Order_Collection
 */
class Cminds_Marketplaceamount_Model_Mysql4_Sales_Order_Collection
    extends Mage_Sales_Model_Resource_Order_Collection
{

    public function getDaySupplierAmount($creatorId)
    {
        $eavAttribute = new Mage_Eav_Model_Mysql4_Entity_Attribute();
        $code = $eavAttribute->getIdByCode('catalog_product', 'creator_id');
        $resource  = Mage::getSingleton('core/resource');
        $productIntTable = $resource->getTableName('catalog_product_entity_int');
        $orderItemTable = $resource->getTableName('sales/order_item');

        $now = Mage::getModel('core/date')->timestamp(time());
        $dateStart = date('Y-m-d' . ' 00:00:00', $now);
        $dateEnd = date('Y-m-d' . ' 23:59:59', $now);

        $this->addFieldToFilter('main_table.created_at', array('from' => $dateStart, 'to' => $dateEnd));

        $this->getSelect()
            ->joinInner(array('i' => $orderItemTable), 'i.order_id = main_table.entity_id', array())
            ->joinInner(
                array('e' => $productIntTable),
                'e.entity_id = i.product_id AND e.attribute_id = ' . $code,
                array()
            )
            ->where('i.parent_item_id is null')
            ->where('e.value = ?', $creatorId);

        $this->addExpressionFieldToSelect(
            'sale_amount',
            'SUM(i.base_row_total - i.base_discount_amount)',
            'i.base_row_total - i.base_discount_amount'
        );
        $orderAmount = $this->getFirstItem()->getData('sale_amount');

        return $orderAmount;
    }

    public function getDaySupplierQty($creatorId)
    {
        $eavAttribute = new Mage_Eav_Model_Mysql4_Entity_Attribute();
        $code = $eavAttribute->getIdByCode('catalog_product', 'creator_id');
        $resource = Mage::getSingleton('core/resource');
        $productIntTable = $resource->getTableName('catalog_product_entity_int');
        $orderItemTable = $resource->getTableName('sales/order_item');

        $now = Mage::getModel('core/date')->timestamp(time());
        $dateStart = date('Y-m-d' . ' 00:00:00', $now);
        $dateEnd = date('Y-m-d' . ' 23:59:59', $now);

        $this->addFieldToFilter('main_table.created_at', array('from' => $dateStart, 'to' => $dateEnd));

        $this->getSelect()
            ->joinInner(array('i' => $orderItemTable), 'i.order_id = main_table.entity_id', array())
            ->joinInner(
                array('e' => $productIntTable),
                'e.entity_id = i.product_id AND e.attribute_id = ' . $code,
                array()
            )
            ->where('i.parent_item_id is null')
            ->where('e.value = ?', $creatorId);

        $this->addExpressionFieldToSelect('qty_sum', 'SUM(total_qty_ordered)');
        $qtyAmount = $this->getFirstItem()->getData('qty_sum');

        return $qtyAmount;
    }
}
