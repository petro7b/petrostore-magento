<?php

class Cminds_MarketplaceDeliveryDate_Block_Customer_Delivery_Report
    extends Mage_Core_Block_Template
{
    public function __construct()
    {
        $orders = Mage::getResourceModel('sales/order_item_collection')
            ->addAttributeToSelect('*');
        $orders
            ->getSelect()
            ->join(
                array('orders' => 'sales_flat_order'),
                'orders.entity_id = main_table.order_id',
                array(
                    'orders.increment_id as increment_id',
                    'orders.customer_lastname as last_name',
                    'orders.customer_firstname as first_name',
                    'orders.customer_id as customer_id'
                ));
        $this
            ->joinEav(
                'creator_id',
                'catalog_product',
                'main_table.product_id',
                $orders
            );

        $orders
            ->addFieldToFilter('customer_id', Mage::getSingleton('customer/session')->getCustomer()->getId())
            ->addFieldToFilter('delivery_date', array('gt' => date('Y-m-d')))
            ->addFieldToFilter('delivery_date', array('lt' => date('Y-m-d', strtotime('+1 week'))))
            ->setOrder('delivery_date', 'ASC')
            ->setOrder('catalog_product_creator_id', 'ASC');

        $this->setOrders($orders);
    }

    public function joinEav($attrCode, $entityType, $joinField, $collection)
    {
        $attr = Mage::getModel("eav/config")->getAttribute($entityType, $attrCode);
        $attributeId = $attr->getAttributeId();
        $attrTable = $attr->getBackendTable();
        if ($attr->getBackendType() === 'static') {
            $joinSql = "{$attrTable}.entity_id={$joinField}";
            $alias = $attrTable;
            $fields = '*';
            $fieldAlias = $entityType . '_' . $attrCode;
        } else {
            $alias = $entityType . '_' . $attrCode;
            $dbRow = 'value';
            $joinSql = "{$alias}.entity_id={$joinField} AND {$alias}.attribute_id={$attributeId}";
            $fields = array($alias => "{$alias}.{$dbRow}");
        }

        if ($attr->getBackendType() === 'static') {
            $collection->addExpressionFieldToSelect($fieldAlias, "{$attrTable}.{$attrCode}");
        }

        if (stristr($collection->getSelectSql(), "`{$alias}`")) {
            return $this;
        }

        $collection
            ->getSelect()
            ->joinLeft(
                array($alias => $attrTable),
                $joinSql,
                $fields
            );

        return $this;
    }

    /**
     *Get name of bundle item creator.
     *
     * @param $orderItem
     * @param $orders
     * @return mixed
     */
    public function getVendorName($orderItem, $orders)
    {
        $creatorId = $orderItem->getCatalogProductCreatorId();
        $vendorName = '';

        if ($orderItem->getProductType() === 'bundle') {
            $creatorId = Mage::helper('supplierfrontendproductuploader/bundle')
                ->findCreatorId($creatorId, $orderItem, $orders);
        }

        $vendor = Mage::getModel('customer/customer')->load($creatorId);
        if ($vendor && $vendor->getId()) {
            $vendorName = $vendor->getName();
        }

        return $vendorName;
    }

    /**
     * Get sales order view url.
     *
     * @param $orderId
     * @return string
     */
    public function getOrderViewUrl($orderId)
    {
        return Mage::getUrl('sales/order/view', array('order_id' => $orderId));
    }

}
