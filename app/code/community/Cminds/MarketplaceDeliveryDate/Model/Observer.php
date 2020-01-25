<?php

/**
 * Class Cminds_MarketplaceDeliveryDate_Model_Observer
 */
class Cminds_MarketplaceDeliveryDate_Model_Observer extends Mage_Core_Model_Abstract
{

    /**
     * Save delivery date to items for onepage checkout.
     *
     * @param $observer
     */
    public function onOrderPlaced($observer)
    {
        $helper = Mage::helper("marketplace_delivery_date");
        $deliveryDate = $helper->getDeliveryDateConfig();
        $order = $observer->getEvent()->getOrder();
        $items = $order->getAllItems();

        if ($deliveryDate == 1) {
            $session = Mage::getSingleton('checkout/session');
            $session = $session->getDeliveryDate();
            if (count($session)>0) {
                foreach ($items as $item) {
                    $product = Mage::getModel('catalog/product')->load($item->getProductId());
                    $creatorId = $product->getCreatorId();

                    if (isset($session[$creatorId])) {
                        $item->setDeliveryDate($session[$creatorId]);
                        $item->save();
                    }
                }
            }
        }
    }

    /**
     * Save delivery date to items for multishipping checkout.
     *
     * @param $observer
     */
    public function multishippingSuccessAction($observer)
    {
        $orders_ids = $observer->getEvent()->getOrderIds();

        $deliveryDate = Mage::helper("marketplace_delivery_date")->getDeliveryDateConfig();
        $deliveryDates = Mage::getSingleton('checkout/session')->getDeliveryDate();

        if ($deliveryDate == 1 and count($deliveryDates) > 0) {
            foreach ($orders_ids as $id) {
                foreach ($deliveryDates as $index => $date) {
                    $arr = explode('_', $index);
                    $items = Mage::getModel('sales/order_item')->getCollection()
                        ->addAttributeToFilter('order_id', $id)
                        ->addAttributeToFilter('product_id', $arr[1]);

                    foreach ($items as $item) {
                        $i = Mage::getModel('sales/order_item')->load($item->getId());
                        $i->setDeliveryDate($date);
                        $i->save();
                    }
                }
            }
        }
    }

    /**
     * Save delivery date to session.
     *
     * @param $observer
     */
    public function onSaveShippingMethod($observer)
    {
        $deliveryDate = Mage::helper("marketplace_delivery_date")->getDeliveryDateConfig();
        if ($deliveryDate == 1) {
            $request = $observer->getControllerAction()->getRequest();
            $postData = $request->getPost();
            if (isset($postData['delivery_date'])) {
                Mage::getSingleton('checkout/session')->setDeliveryDate($postData['delivery_date']);
                Mage::getSingleton('checkout/session')->setOnepageDeliveryDate($postData['delivery_date']);
            }
        }
    }

    /**
     * Method add new tab to supplier vendor portal menu.
     *
     * @param $observer
     */
    public function navLoad($observer)
    {
        $event = $observer->getEvent();
        $items = $event->getItems();

        if (Mage::helper('marketplace_delivery_date')->getOrderLeadTimeConfig()) {
            $items['DELIVERYDATE_SETTINGS'] = [
                'label' => 'Delivery Date Settings',
                'url' => 'deliverydate/settings/settings',
                'parent' => 'SETTINGS',
                'sort' => 1
            ];
            $items['SETTINGS']['action_names'] =
                array_merge($items['SETTINGS']['action_names'], ['cminds_marketplace_settings_deliverydate_settings']);
        }

        $observer->getEvent()->setItems($items);
    }
}