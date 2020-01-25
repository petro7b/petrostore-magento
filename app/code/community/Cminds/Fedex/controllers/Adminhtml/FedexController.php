<?php

class Cminds_Fedex_Adminhtml_FedexController extends Cminds_Marketplace_Controller_Action
{
    public function createLabelAction()
    {
        $order_id = $this->getRequest()->getParam("order_id", null);
        $order = Mage::getModel('sales/order')->load($order_id);
        $vendor_ids = array();
        foreach($order->getAllItems() AS $item) {
            $vendor_ids[] = Mage::helper('marketplace')->getProductSupplierId($item->getProduct());
        }

        $i = 0;

        foreach(array_unique($vendor_ids) AS $vendor_id) {
            $c = Mage::getModel('customer/customer')->load($vendor_id);
            try {

                $this->_prepareShipment($order, $c);
                $i++;
            } catch(Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        if($i > 0) {
            Mage::getSingleton('adminhtml/session')->addSuccess($i . " shipments has been created");
        }

        $this->_redirect('*/sales_order/view', array('order_id' => $order_id));
    }

    protected function _prepareShipment($order, $customer)
    {

        $trackingModel = Mage::getModel('cminds_fedex/tracking_fedex_vendor');
        $trackingModel->setOrder($order);
        $trackingModel->setVendor($customer);
        $trackingModel->request();

        $transaction = Mage::getModel('core/resource_transaction');
        $items = array();

        foreach($trackingModel->getItems() AS $item) {
            $items[$item->getId()] = $item->getQtyOrdered();
        }
        if($order->getState() == 'canceled') {
            throw new Exception('You cannot create shipment for canceled order');
        }
        $shipment = $order->prepareShipment($items);

        $shipment->register();

        foreach($shipment->getAllItems() AS $item) {
            $orderItem = Mage::getModel('sales/order_item')->load($item->getOrderItemId());
            $orderItem->setQtyShipped($item->getQty() + $orderItem->getQtyShipped());
            $transaction->addObject($orderItem);
        }

        $transaction->addObject($shipment);
        $sh = Mage::getModel('sales/order_shipment_track')
            ->setShipment($shipment)
            ->setData('title', 'Fedex')
            ->setData('number', $trackingModel->getTrackingNumber())
            ->setData('carrier_code', 'fedex')
            ->setData('order_id', $shipment->getData('order_id'));

        $transaction->addObject($sh);

        $fullyShipped = true;

        foreach ($order->getAllItems() as $item) {
            if ($item->getQtyToShip()>0 && !$item->getIsVirtual()
                && !$item->getLockedDoShip())
            {
                $fullyShipped = false;
            }
        }

        if($fullyShipped) {
            if($order->getState() != Mage_Sales_Model_Order::STATE_PROCESSING) {
                $state = Mage_Sales_Model_Order::STATE_PROCESSING;
            } elseif($order->getState() == Mage_Sales_Model_Order::STATE_PROCESSING) {
                $state = Mage_Sales_Model_Order::STATE_COMPLETE;
            }

            if($state) {
                $order->setData('state', $state);

                $status = $order->getConfig()->getStateDefaultStatus($state);
                $order->setStatus($status);
                $order->addStatusHistoryComment("Shipment was created", false);
            }
        }

        $transaction->addObject($order);

        $transaction->save();
        $shipment->sendEmail((isset($post['notify_customer']) && $post['notify_customer'] == '1'))
            ->setEmailSent(false);
    }
}