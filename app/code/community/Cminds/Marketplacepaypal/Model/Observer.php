<?php 
class Cminds_MarketplacePaypal_Model_Observer {
    public function onOrderPlacedStartPayment($observer) {
//        if(!Mage::helper('supplierfrontendproductuploader')->isEnabled() ||
//            !Mage::helper('marketplacepaypal')->isAfterPlacingOrderEnabled()) {
//
//            return;
//        }

        $orderIds = $observer->getEvent()->getOrderIds();

        if(count($orderIds) < 1) {
            $orderIds[] = $observer->getEvent()->getOrder()->getId();
        }

        $this->runPayment($orderIds);
    }
    
    public function OnOrderCompleted($observer) {
        if(!Mage::helper('supplierfrontendproductuploader')->isEnabled() ||
            !Mage::helper('marketplacepaypal')->isAfterOrderCompleteEnabled()) {

            return;
        }
        
        $order = $observer->getOrder();

        if($order->getState() == Mage_Sales_Model_Order::STATE_COMPLETE){
            $this->runPayment(array($order->getId()));
        }  
    }
    
    private function runPayment($orderIds) {
        $order = Mage::getModel('sales/order')->load($orderIds[0]);

        $paypalMoneyTransfer = Mage::getModel('marketplacepaypal/transfer');
        $paypalMoneyTransfer->setOrder($order);
        $paypalMoneyTransfer->transfer();
    }

    public function navLoad($observer) {
        $event = $observer->getEvent();
        $items = $event->getItems();

        if(Mage::helper('marketplacepaypal')->isEnabled()) {
            $items['PAPYPAL_EMAIL'] = [
                'label' => 'Paypal Settings',
                'url' => 'supplier/manage/settings',
                'parent' => 'SETTINGS',
                'sort' => 6
            ];
        }

        $observer->getEvent()->setItems($items);
    }
}