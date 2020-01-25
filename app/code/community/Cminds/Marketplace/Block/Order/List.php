<?php
class Cminds_Marketplace_Block_Order_List extends Mage_Core_Block_Template {
    protected $collection;
    public function _construct() {
        $this->setTemplate('marketplace/order/list.phtml');
    }

    public function getFlatCollection() {
        if(!$this->collection) {
            $collection = Mage::getModel( 'sales/order' )->getCollection();
            $page       = $this->getRequest()->getParam( "p", 1 );
            if ( $this->getFilter( 'autoincrement_id' ) ) {
                $collection->getSelect()->where( 'main_table.increment_id LIKE ?',
                    "%" . $this->getFilter( 'autoincrement_id' ) . "%" );
            }
            if ( $this->getFilter( 'status' ) ) {
                $collection->getSelect()->where( 'main_table.status = ?',
                    $this->getFilter( 'status' ) );
            }

            if ($this->getFilter('from') && strtotime($this->getFilter('from'))) {
                $datetime = Mage::helper('marketplace')
                    ->getTimeFilterFrom($this->getFilter('from'));

                $collection
                    ->getSelect()
                    ->where(
                        'main_table.created_at >= ?',
                        $datetime->format('Y-m-d H:i:s')
                    );
            }

            if ($this->getFilter('to') && strtotime($this->getFilter('to'))) {
                $datetime = Mage::helper('marketplace')
                    ->getTimeFilterTo($this->getFilter('to'));

                $collection
                    ->getSelect()
                    ->where(
                        'main_table.created_at <= ?',
                        $datetime->format('Y-m-d H:i:s')
                    );
            }

            $collection->addFieldToFilter( "entity_id",
                array( "in" => $this->getRestrictedOrderIds() ) );
            $collection->addFieldToFilter( "status",
                array( "in" => array( Mage::helper( 'marketplace' )->getStatusesCanSee() ) ) );

            $collection->setPageSize( 20 );
            $collection->addOrder( 'entity_id', "desc" );


            $collection->setCurPage( $page );
            $this->collection = $collection;
        }
        return $this->collection;
    }

    protected function getFilter($key) {
        return $this->getRequest()->getPost($key);
    }

    public function isFullyShipped($order) {
        $orderItems = $order->getItemsCollection();
        $shipments = $order->getShipmentsCollection();
        $allOrderItemIds = array();
        $shippedItemIds = array();

        foreach($orderItems As $item) {
            if(Mage::helper('marketplace')->isOwner($item->getProductId())) {
                $allOrderItemIds[$item->getItemId()] = $item->getQtyOrdered();
            }
        }

        foreach ($shipments as $shipment) {
            $shippedItems = $shipment->getItemsCollection();
            foreach ($shippedItems as $item) {
                if(Mage::helper('marketplace')->isOwner($item->getOrderItem()->getProductId())) {
                    if(!isset($shippedItemIds[$item->getOrderItemId()])) {
                        $shippedItemIds[$item->getOrderItemId()] = 0;
                    }
                    $shippedItemIds[$item->getOrderItemId()] = $shippedItemIds[$item->getOrderItemId()] + $item->getQty();
                }
            }
        }
        return (count($shippedItemIds) == count($allOrderItemIds) && array_sum($allOrderItemIds) == array_sum($shippedItemIds));
    }

    public function getSupplierProductIds() {
        $supplier_id = Mage::helper('supplierfrontendproductuploader')->getSupplierId();

        $collection = Mage::getModel('catalog/product')
                          ->getCollection()
                          ->addAttributeToSelect('creator_id')
                          ->addAttributeToFilter(array(array('attribute' => 'creator_id', 'eq' => $supplier_id)))
                          ->setOrder('entity_id');
        $productList = array();

        foreach($collection as $product) {
            $productList[] = $product->getId();
        }

        return array_unique($productList);
    }

    public function getRestrictedOrderIds() {
        $collection = Mage::getModel('sales/order_item')
                          ->getCollection()
                          ->addAttributeToFilter('product_id',
                              array("in" => $this->getSupplierProductIds()));

        $orderList = array();

        foreach($collection as $item) {
            $orderList[] = $item->getOrderId();
        }

        return array_unique($orderList);
    }

    public function calculateSubtotal($order) {
        $subtotal = 0;
        foreach($order->getAllItems() AS $item) {
            if(Mage::helper('marketplace')->isOwner($item->getProductId())) {
                $subtotal += $item->getPrice() * $item->getQtyOrdered();
            }
        }
        return $subtotal;
    }

    public function calculateDiscount($order) {
        $discount = 0;
        foreach($order->getAllItems() AS $item) {
            if(Mage::helper('marketplace')->isOwner($item->getProductId())) {
                $discount += $item->getDiscountAmount();
            }
        }
        return $discount;
    }

    public function canShowShipButton($order) {
        if($order->getIsVirtual()) return false;
        if($this->isFullyShipped($order)) return false;

        return true;
    }

    public function getPagerHtml() {

        $pager = $this->getLayout()->createBlock('page/html_pager', 'custom.pager');
        $pager->setAvailableLimit(array(20=>20));
        $pager->setCollection($this->getFlatCollection());

        return $pager->toHtml();
    }
}