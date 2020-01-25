<?php

class Cminds_Fedex_Model_Observer {
    public function onCartChanged()
    {
        Mage::getSingleton("checkout/session")->setData("selected_rate", false);
    }

    public function onOrderSaved($observer)
    {
        $estimatedData = Mage::getSingleton("checkout/session")->getData("estimate_data");

        if (!$estimatedData) {
            $order = $observer->getOrder();

            foreach ($order->getAllItems() as $item) {
                $vendor_id = Mage::helper('marketplace')->getProductSupplierId($item->getProduct());

                if (isset($estimatedData[$vendor_id])) {
                    $item->setShippingMethod($estimatedData[ $vendor_id ]);
                    $item->save();
                }
            }
        }
    }

    public function onNavLoad($observer)
    {
        $items = $observer->getEvent()->getItems();
        $items['FEDEX'] = array(
            'label'  => 'Fedex Account',
            'url'    => 'marketplace/fedex/view',
            'parent' => 'SETTINGS',
            'sort'   => 3
        );
        $observer->getEvent()->setItems($items);
    }

    public function adminhtmlWidgetContainerHtmlBefore($event)
    {
        $block = $event->getBlock();
        if ($block instanceof Mage_Adminhtml_Block_Sales_Order_View) {
            $message = Mage::helper('cminds_fedex')->__('Are you sure you want to do this?');
            $block->addButton('create_label', array(
                'label'   => Mage::helper('cminds_fedex')->__('Create label'),
                'onclick' => "confirmSetLocation('{$message}', '{$block->getUrl('*/fedex/createLabel')}')",
                'class'   => 'go'
            ));
        }
    }

    public function saveCustomerCustomizationData($observer)
    {
        if (empty($observer['customer'])) {
            return;
        }
        $customerData = $observer['customer']->getData();
        $customer     = Mage::getModel('customer/customer')->load($customerData['entity_id']);
        $customer
            ->setFedexKey(Mage::app()->getRequest()->getPost('fed-key'))
            ->setMeterNumber(Mage::app()->getRequest()->getPost('meter_number'))
            ->setFedexPassword(Mage::app()->getRequest()->getPost('fed-pass'))
            ->setAccountNumber(Mage::app()->getRequest()->getPost('account_number'))
            ->save();
    }

    public function onSaveShippingMethod($observer)
    {
        $request  = $observer->getControllerAction()->getRequest();
        $postData = $request->getPost();

        if (empty($postData['shipping_method'])) {
            return;
        }

        if (empty($postData['estimatetime_name'])) {
            return;
        }

        if ($postData['shipping_method'] !== 'marketplace_shipping_marketplace_shipping') {
            return;
        }

        Mage::getSingleton('checkout/session')->setEstimateNameData($postData['estimatetime_name']);
    }

    public function onSaveShippingMethodVendor($observer)
    {
        $request  = $observer->getControllerAction()->getRequest();
        $postData = $request->getPost();

        if (empty($postData['shipping_method'])) {
            return;
        }
        if (array_shift(array_values($postData['shipping_method'][0])) == 'fedex')
        {
            $controller = $observer->getControllerAction();
            $controller->getRequest()->setDispatched(true);
            $controller->setFlag('',
                Mage_Core_Controller_Front_Action::FLAG_NO_DISPATCH, true);

            foreach ($postData['id'] AS $i => $k) {
                $shipping = Mage::getModel('marketplace/methods')->load($k);
                $shipping->setSupplierId(Mage::helper('marketplace')->getSupplierId());
                $shipping->setName($postData['shipping_name'][ $i ]);
                $shipping->setFlatRateFee(0);
                $shipping->setFlatRateAvailable(0);
                $shipping->setTableRateAvailable(0);
                $shipping->setTableRateCondition(0);
                $shipping->setTableRateFee(0);
                $shipping->setFreeShipping(0);
                $shipping->setFedex(1);
                $transaction = Mage::getModel('core/resource_transaction');
                $transaction->addObject($shipping);
                $transaction->save();
                Mage::app()->getResponse()->setRedirect(Mage::getUrl("marketplace/settings/shipping"));
            }
        }
    }

    public function onOrderPlaceMethod($observer)
    {
        if (!is_array(Mage::getSingleton('checkout/session')->getEstimateNameData())) {
            return $this;
        }

        foreach (Mage::getSingleton('checkout/session')->getEstimateNameData() as $quote_item_id => $data) {
            $i = Mage::getModel('sales/order_item')->load(
                $quote_item_id,
                'quote_item_id'
            );

            $i->setShippingMethod($data);
            $i->save();
        }
        Mage::getSingleton('checkout/session')->unsEstimateNameData();
    }

    public function canShowMethod($observer)
    {
        $shippingMethod = $observer->getMethod();

        Mage::unregister("can_show_method");

        if ($shippingMethod->getFedex()) {
            Mage::register("can_show_method", false);

            return false;
        }

        Mage::register("can_show_method", true);

        return true;
    }

    public function beforeShippingMethodSave($observer)
    {
        $shippingMethod = $observer->getMethod();
        $postData = $observer->getPost();
    }
}