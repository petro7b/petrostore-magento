<?php

class Cminds_Marketplace_Model_Observer extends Mage_Core_Model_Abstract
{
    public function onOrderPlaced($observer)
    {
        if (!Mage::helper('supplierfrontendproductuploader')->isEnabled()) {
            return $this;
        }

        $orderId = $observer->getEvent()->getOrder()->getId();
        $order = Mage::getModel('sales/order')->load($orderId);
        $items = $order->getAllItems();

        $estimateData = Mage::getSingleton('checkout/session')->getEstimateData();
        $estimateName = Mage::getSingleton('checkout/session')->getEstimateMethodName();

        foreach ($items as $item) {
            $product = Mage::getModel('catalog/product')->load($item->getProductId());
            $productCreatorId = $product->getCreatorId();
            $price = 0;
            $name = '';

            if ($productCreatorId === null) {
                continue;
            }

            $orderItem = Mage::getModel('sales/order_item')->load($item->getId());
            if (!$orderItem) {
                continue;
            }

            $vendorIncomes = Mage::helper('marketplace/profits')->getVendorIncome($product, $item->getPrice());
            if (!$vendorIncomes) {
                continue;
            }

            $orderItem
                ->setVendorFee($vendorIncomes['percentage'])
                ->setVendorIncome($vendorIncomes['income'])
                ->save();



            if (isset($estimateData[$item->getQuoteItemId()])) {
                $price = $estimateData[$item->getQuoteItemId()];
            }
            if (isset($estimateName[$item->getQuoteItemId()])) {
                $name = $estimateName[$item->getQuoteItemId()];
            }

            if ($price > 0) {
                Mage::getModel("marketplace/vendorShipping")
                    ->setOrderId($orderId)
                    ->setVendorId($productCreatorId)
                    ->setShippingMethodName($name)
                    ->setShippingMethodPrice($price)
                    ->save();
            }
        }

        return $this;
    }

    public function onOrderSave($observer)
    {

        if (!Mage::helper('supplierfrontendproductuploader')->isEnabled()) {
            return $this;
        }
        $order = $observer->getOrder();
        if ($order->getState() === Mage_Sales_Model_Order::STATE_COMPLETE
            || $order->getStatus() === 'complete'
        ) {
            $orderId = $order->getId();
            $order = Mage::getModel('sales/order')->load($orderId);
            $items = $order->getAllItems();

            if (!$order->getCustomerId()) {
                return $this;
            }

            foreach ($items as $item) {
                $product = Mage::getModel('catalog/product')->load($item->getProductId());

                if ($product->getData('creator_id') !== null) {
                    if ($product->getData('creator_id') === $order->getCustomerId()) {
                        continue;
                    }

                    $rateCollection = Mage::getModel('marketplace/torate')->getCollection()
                        ->addFieldToFilter('supplier_id', $product->getData('creator_id'))
                        ->addFieldToFilter('customer_id', $order->getCustomerId());
                    $rateCollectionCount = $rateCollection->count();
                    if ($rateCollectionCount <= 0) {
                        Mage::getModel('marketplace/torate')
                            ->setData('supplier_id', $product->getData('creator_id'))
                            ->setData('order_id', $orderId)
                            ->setData('product_id', $item->getProductId())
                            ->setData('customer_id', $order->getCustomerId())
                            ->save();
                    }
                }
            }
        }

        return $this;
    }

    public function navLoad($observer)
    {
        $event = $observer->getEvent();
        $items = $event->getItems();

        $items['ORDERS'] = array(
            'label' => 'Orders',
            'url' => 'marketplace/order',
            'parent' => null,
            'action_names' => array(
                'cminds_marketplace_order_index',
                'cminds_marketplace_order_view',
                'cminds_marketplace_shipment_create',
                'cminds_marketplace_invoice_create',
                'cminds_marketplace_shipment_view',
                'cminds_marketplace_order_importshipping'
            ),
            'sort' => 2.5
        );

        $items['ORDER_LIST'] = array(
            'label' => 'Order List',
            'url' => 'marketplace/order',
            'parent' => 'ORDERS',
            'sort' => 0
        );

        $items['IMPORT_SIPMENTS'] = array(
            'label' => 'Import Shipping',
            'url' => 'marketplace/order/importshipping',
            'parent' => 'ORDERS',
            'sort' => 1
        );

        if (Mage::helper('marketplace')->supplierPagesEnabled()) {
            $items['SUPPLIER_PAGE'] = array(
                'label' => 'My Profile Page',
                'url' => 'marketplace/settings/profile',
                'parent' => 'SETTINGS',
                'sort' => -1
            );

            $items['SETTINGS']['action_names'] = array_merge(
                $items['SETTINGS']['action_names'],
                array('cminds_marketplace_settings_profile')
            );

        }
        if (Mage::getStoreConfig('marketplace_configuration/presentation/change_shipping_costs')) {
            $items['SHIPPING_METHODS'] = array(
                'label' => 'Shipping Methods',
                'url' => 'marketplace/settings/shipping',
                'parent' => 'SETTINGS',
                'sort' => 1
            );
            $items['SETTINGS']['action_names'] = array_merge(
                $items['SETTINGS']['action_names'],
                array('cminds_marketplace_settings_shipping')
            );
        }

        $items['REPORTS_ORDERS'] = array(
            'label' => 'Orders',
            'url' => 'marketplace/reports/orders',
            'parent' => 'REPORTS',
            'sort' => -1,

        );

        $items['REPORTS_PRODUCTS'] = array(
            'label' => 'Products',
            'url' => null,
            'parent' => 'REPORTS',
            'sort' => 1,
            'fix_label' => true
        );

        $items['REPORTS_BESTSELLERS'] = array(
            'label' => 'Bestsellers',
            'url' => 'marketplace/reports/bestsellers',
            'parent' => 'REPORTS',
            'sort' => 2,
            'fix_label_children' => true
        );

        $items['REPORTS_ORDERED_ITEMS'] = array(
            'label' => 'Ordered Items',
            'url' => 'supplier/product/ordered',
            'parent' => 'REPORTS',
            'sort' => 3,
            'fix_label_children' => true
        );

        $items['REPORTS_MOST_VIEWED'] = array(
            'label' => 'Most Viewed',
            'url' => 'marketplace/reports/mostViewed',
            'parent' => 'REPORTS',
            'sort' => 4,
            'fix_label_children' => true
        );

        $items['REPORTS_LOW_STACK'] = array(
            'label' => 'Low stock',
            'url' => 'marketplace/reports/lowStock',
            'parent' => 'REPORTS',
            'sort' => 5,
            'fix_label_children' => true
        );


        $items['REPORTS']['action_names'] = array_merge(
            $items['REPORTS']['action_names'],
            array(
                'cminds_marketplace_reports_orders',
                'cminds_supplierfrontendproductuploader_product_ordered',
                'cminds_marketplace_reports_bestsellers',
                'cminds_marketplace_reports_mostViewed',
                'cminds_marketplace_reports_lowStock',
            )
        );

        if (Mage::helper('marketplace')->supplierPagesEnabled()) {
            $customerData = Mage::getSingleton('customer/session')->getCustomer();

            $items['MY_SUPPLIER_PAGE'] = array(
                'label' => 'My Supplier Page',
                'url' => Mage::helper('marketplace')->getSupplierRawPageUrl($customerData->getId()),
                'parent' => null,
                'sort' => 4.5
            );
        }

        $observer->getEvent()->setItems($items);
    }

    public function chooseTemplate($t)
    {
        $template = $t->getBlock()->getTemplate();
        $showIn = Mage::getStoreConfig('marketplace_configuration/presentation/show_sold_by_in');
        /**
         * marketplace/model/config/soldby.php
         */
        $array = explode(",", $showIn);

        if ($template == 'checkout/cart/item/default.phtml' && in_array('checkout_cart', $array)) {
            $t->getBlock()->setTemplate("marketplace/checkout/cart/item/default.phtml");
        } elseif ($template === 'checkout/onepage/review/item.phtml' && in_array('checkout', $array)) {
            $t->getBlock()->setTemplate("marketplace/checkout/onepage/review/item.phtml");
        } elseif ($template === 'checkout/cart/minicart/default.phtml' && in_array('checkout_minicart', $array)) {
            $t->getBlock()->setTemplate("marketplace/checkout/cart/minicart/default.phtml");
        } elseif ($template === 'sales/order/items/renderer/default.phtml' && in_array('order_view', $array)) {
            $t->getBlock()->setTemplate("marketplace/sales/order/items/renderer/default.phtml");
        } elseif ($template === 'email/order/items/shipment/default.phtml' && in_array('emails', $array)) {
            $t->getBlock()->setTemplate("marketplace/email/order/items/shipment/default.phtml");
        } elseif ($template === 'email/order/items/order/default.phtml' && in_array('emails', $array)) {
            $t->getBlock()->setTemplate("marketplace/email/order/items/order/default.phtml");
        } elseif ($template === 'email/order/items/invoice/default.phtml' && in_array('emails', $array)) {
            $t->getBlock()->setTemplate("marketplace/email/order/items/invoice/default.phtml");
        } elseif ($template === 'email/order/items/creditmemo/default.phtml' && in_array('emails', $array)) {
            $t->getBlock()->setTemplate("marketplace/email/creditmemo/items/invoice/default.phtml");
        }
    }

    public function onSaveShippingMethod($observer)
    {
        $request = $observer->getControllerAction()->getRequest();
        $postData = $request->getPost();

        if ((isset($postData['shipping_method'])
                && $postData['shipping_method'] == 'marketplace_shipping_marketplace_shipping')
            ||
            (isset($postData['estimate_method'])
                && $postData['estimate_method'] == 'marketplace_estimated_time_marketplace_estimated_time')
        ) {
            if (isset($postData['estimatetime'])) {
                Mage::getSingleton('checkout/session')->setEstimateData($postData['estimatetime']);
            }

            if (isset($postData['estimatetimeName'])) {
                Mage::getSingleton('checkout/session')->setEstimateMethodName($postData['estimatetimeName']);
            }

            $quote = Mage::getModel('checkout/session')->getQuote();
            $shippingAddress = $quote->getShippingAddress();

            $shippingAddress
                ->setCollectShippingRates(true)
                ->collectShippingRates()
                ->save();
        }
    }

    public function onCartPageLoad()
    {
        /**
        * @var  Cminds_Marketplace_Helper_Data $dataHelper
         */
        $dataHelper = Mage::helper("marketplace");

        if (!$dataHelper->canShowCartNotice()) {
            return $this;
        }

        $order = Mage::getModel('checkout/cart')->getQuote();
        $items = $order->getAllItems();
        $vendors = array();

        foreach ($items as $item) {
            $product = Mage::getModel('catalog/product')->load($item->getProductId());
            $vendors[] = $product->getData('creator_id');
        }


        if (count(array_unique($vendors)) > 1) {
            Mage::getSingleton('checkout/session')->addNotice(
                $dataHelper->__("This order contained products from multiple Vendors. It will be separated into multiple orders after you place this order.")
            );
        }
    }
}