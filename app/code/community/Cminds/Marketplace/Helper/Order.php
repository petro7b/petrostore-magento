<?php

class Cminds_Marketplace_Helper_Order extends Mage_Core_Helper_Abstract
{
    public function isSingleVendor($order, $vendor_id)
    {
        $orderItems = $order->getAllItems();
        $dataHelper = Mage::helper("marketplace");

        foreach ($orderItems as $item) {
            if ((int) $dataHelper->getProductSupplierId($item->getProduct()) !== (int) $vendor_id) {
                return false;
            }
        }

        return true;
    }

    public function isVendorOrder($order, $vendor_id)
    {
        $orderItems = $order->getAllItems();
        $dataHelper = Mage::helper("marketplace");

        foreach ($orderItems as $item) {
            if ((int) $dataHelper->getProductSupplierId($item->getProduct()) === (int) $vendor_id) {
                return true;
            }
        }

        return false;
    }

    /**
     * Calculating supplier's income by products from one order.
     *
     * @param $order
     *
     * @return int
     */
    public function calculateIncome($order)
    {
        $income = 0;
        foreach ($order->getAllItems() as $item) {
            if (Mage::helper('marketplace')->isOwner($item->getProductId())) {
                $income += $item->getVendorIncome() * $item->getQtyOrdered();
            }
        }

        return $income;
    }

    /**
     * Calculating supplier's income by all products.
     *
     * @param $supplier
     *
     * @return int
     */
    public function calculateNetIncomeAllProducts($supplier)
    {
        $productCollection = Mage::getModel('catalog/product')->getCollection();
        $income = 0;
        foreach ($productCollection as $item) {
            if (Mage::helper('marketplace')->isOwner($item->getId(), $supplier)) {
                $orders = Mage::getModel('sales/order_item')
                    ->getCollection()
                    ->addFieldToFilter('product_id', $item->getId());
                foreach ($orders as $order) {
                    $income += $order->getVendorIncome() * $order->getQtyOrdered();
                }
            }
        }

        return $income;
    }
}
