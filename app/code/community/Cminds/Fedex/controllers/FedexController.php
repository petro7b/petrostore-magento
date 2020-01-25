<?php

class Cminds_Fedex_FedexController extends Cminds_Marketplace_Controller_Action
{
    public function viewAction()
    {
        $this->_renderBlocks(true);
    }

    public function postAction()
    {
        if (Mage::getSingleton('customer/session')->isLoggedIn()) {
            $customer = Mage::getSingleton('customer/session')->getCustomer();

            $customer
                ->setFedexKey($this->getRequest()->getParam('key'))
                ->setMeterNumber($this->getRequest()->getParam('meter_number'))
                ->setFedexPassword($this->getRequest()->getParam('password'))
                ->setAccountNumber($this->getRequest()->getParam('account_number'))
                ->setAccountNumber($this->getRequest()->getParam('account_number'))
                ->setOriginZipcode($this->getRequest()->getParam('origin_zipcode'))
                ->save();

            $this->_redirect('marketplace/fedex/view');
        }
    }

    public function createLabelAction()
    {
        if (!Mage::getSingleton("customer/session")->isLoggedIn()) {
            exit;
        }

        try {
            $order_id = $this->getRequest()->getParam("id", null);
            $order = Mage::getModel('sales/order')->load($order_id);

            $customer = Mage::getSingleton("customer/session")->getCustomer();


            $trackingModel = Mage::getModel('cminds_fedex/tracking_fedex/vendor');
            $trackingModel->setOrder($order);
            $trackingModel->setVendor($customer);
            $trackingModel->request();

            $transaction = Mage::getModel('core/resource_transaction');
            $items = array();

            foreach ($trackingModel->getItems() AS $item) {
                $items[$item->getId()] = $item->getQtyOrdered();
            }
            if ($order->getState() == 'canceled') {
                throw new Exception('You cannot create shipment for canceled order');
            }
            $shipment = $order->prepareShipment($items);

            $shipment->register();


            foreach ($shipment->getAllItems() AS $item) {
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


            $loggedUser = Mage::getSingleton('customer/session', array('name' => 'frontend'));
            $customer = $loggedUser->getCustomer();

            $comment = $customer->getFirstname() . ' ' . $customer->getLastname() . ' (#' . $customer->getId() . ') created shipment for ' . count($post['product']) . ' item(s)';

            $fullyShipped = true;

            foreach ($order->getAllItems() as $item) {
                if ($item->getQtyToShip() > 0 && !$item->getIsVirtual()
                    && !$item->getLockedDoShip()
                ) {
                    $fullyShipped = false;
                }
            }

            if ($fullyShipped) {
                if ($order->getState() != Mage_Sales_Model_Order::STATE_PROCESSING) {
                    $state = Mage_Sales_Model_Order::STATE_PROCESSING;
                } elseif ($order->getState() == Mage_Sales_Model_Order::STATE_PROCESSING) {
                    $state = Mage_Sales_Model_Order::STATE_COMPLETE;
                }

                if ($state) {
                    $order->setData('state', $state);

                    $status = $order->getConfig()->getStateDefaultStatus($state);
                    $order->setStatus($status);
                    $order->addStatusHistoryComment($comment, false);
                }
            }

            $transaction->addObject($order);

            $transaction->save();
            $shipment->sendEmail((isset($post['notify_customer']) && $post['notify_customer'] == '1'))
                ->setEmailSent(false);

            Mage::getSingleton('core/session')->addSuccess("Label has been created");
        } catch (Exception $e) {
            Mage::getSingleton('core/session')->addError($e->getMessage());
        }

        Mage::app()->getFrontController()->getResponse()->setRedirect(Mage::getBaseUrl() . 'marketplace/order/view/id/' . $order_id);
    }
}