<?php

class Cminds_Marketplace_OrderController extends Cminds_Marketplace_Controller_Action {
    public function preDispatch()
    {
        parent::preDispatch();
        $hasAccess = $this->_getHelper()->hasAccess();

        if (! $hasAccess) {
            $this->getResponse()->setRedirect(
                $this->_getHelper('supplierfrontendproductuploader')
                    ->getSupplierLoginPage()
            );
        }
    }

    public function indexAction()
    {
        $this->_renderBlocks(false, true);
    }

    public function importShippingAction()
    {
        $this->_renderBlocks(false, true);
    }

    public function viewAction()
    {
        /**
         * @var Cminds_Marketplace_Helper_Data $dataHelper
         * */

        $dataHelper = Mage::helper("marketplace");
        $id = $this->getRequest()->getParam('id');
        $supplier_id = $dataHelper->getSupplierId();

        try {
            $this->validate($id, $supplier_id);
            Mage::register('order_id', $id);

            $this->_renderBlocks();
        } catch (Exception $e) {
            Mage::logException($e);

            $this->norouteAction();
            return;
        }
    }

    public function exportCsvAction()
    {
        $orderItemsCollection = $this->_prepareOrderCollection();
        $productCsv           = array();

        /** @var Cminds_Marketplace_Helper_Csv $csvHelper */
        $csvHelper = Mage::helper("marketplace/csv");

        foreach ($orderItemsCollection as $orderItem) {
            $order = $this->getOrder($orderItem->getOrderId());
            $shipping = $order->getShippingAddress();
            $billing  = $order->getBillingAddress();
            $data = array(
                'Order #'       => $order->getIncrementId(),
                'Order Status'  => $order->getStatus(),
                'Order Date'    => $order->getCreatedAt(),
                'Product #'     => $orderItem->getSku(),
                'Quantity'      => $orderItem->getQtyOrdered(),
                'Ship Date'     => $this->getShipmentDate($orderItem->getOrderId()),
                'Ship Method'   => $order->getShippingDescription(),
                'Tracking Code' => join(',', $order->getTrackingNumbers()),
                'Ship to'       => $shipping ? $shipping->getName() : '',
                'Company Name'  => $shipping ? $shipping->getCompany() : '',
                'Address 1'     => $shipping ? $shipping->getStreet(1) : '',
                'Address 2'     => $shipping ? $shipping->getStreet(2) : '',
                'City'          => $shipping ? $shipping->getCity() : '',
                'State'         => $shipping ? $shipping->getRegion() : '',
                'Postal Code'   => $shipping ? $shipping->getPostcode() : '',
                'Country'       => $shipping ? $shipping->getCountryId() : '',
                'Telephone'     => $shipping ? $shipping->getTelephone() : ''
            );

            if ($csvHelper->canShowBillingAddressInOrderExport()) {
                $billingData = array(
                    'Bill to'      => $billing ? $billing->getName() : '',
                    'Billing Company Name' => $billing ? $billing->getCompany() : '',
                    'Billing Address 1'    => $billing ? $billing->getStreet(1) : '',
                    'Billing Address 2'    => $billing ? $billing->getStreet(2) : '',
                    'Billing City'         => $billing ? $billing->getCity() : '',
                    'Billing State'        => $billing ? $billing->getRegion() : '',
                    'Billing Postal Code'  => $billing ? $billing->getPostcode() : '',
                    'Billing Country'      => $billing ? $billing->getCountryId() : '',
                    'Billing Telephone'    => $billing ? $billing->getTelephone() : ''
                );
                $data        = array_merge($data, $billingData);
            }

            if ($csvHelper->canShowPaymentInformation()) {
                $paymentData = array(
                    'Payment Method' => $order->getPayment()
                        ? $order
                            ->getPayment()
                            ->getMethodInstance()
                            ->getCode()
                        : '',
                );
                $data        = array_merge($data, $paymentData);
            }

            if ($csvHelper->canShowGiftMessage()) {
                $giftMessage = array(
                    'Card Message' => $this->getGiftMessage($order->getGiftMessageId())
                );
                $data        = array_merge($data, $giftMessage);
            }

            $productCsv[] = $data;
        }

        $csvHelper->prepareCsvHeaders("order_export_" . date("Y-m-d") . ".csv");

        return $this->getResponse()->setBody($csvHelper->array2Csv($productCsv));
    }

    public function getGiftMessage($messageId)
    {
        $messageText = '';

        if ($messageId) {
            $message = Mage::getModel('giftmessage/message')->load($messageId);

            if ($message->getId()) {
                $messageText = 'From: ';
                if ($message->getSender()) {
                    $messageText .= $message->getSender();
                }
                $messageText .= ' To: ';
                if ($message->getRecipient()) {
                    $messageText .= $message->getRecipient();
                }
                $messageText .= ' Message: ';
                if ($message->getMessage()) {
                    $messageText .= $message->getMessage();
                }
            }
        }

        return $messageText;
    }

    public function getShipmentDate($orderId)
    {
        $shipmentModel = Mage::getModel('sales/order_shipment')
            ->load($orderId, 'order_id');

        $shipmentDate = '';
        if ($shipmentModel->getId()) {
            $shipmentDate = $shipmentModel->getCreatedAt();
        }

        return $shipmentDate;
    }

    protected function _prepareOrderCollection()
    {
        $eavAttribute = new Mage_Eav_Model_Mysql4_Entity_Attribute();
        $supplier_id  = Mage::helper('marketplace')->getSupplierId();
        $code         = $eavAttribute->getIdByCode('catalog_product', 'creator_id');
        $table        = "catalog_product_entity_int";
        $tableName    = Mage::getSingleton("core/resource")->getTableName($table);
        $orderTable   = Mage::getSingleton('core/resource')->getTableName('sales/order');

        $collection = Mage::getModel('sales/order_item')->getCollection();
        $collection->getSelect()
            ->joinInner(
                array('o' => $orderTable),
                'o.entity_id = main_table.order_id',
                array()
            )
            ->joinInner(
                array('e' => $tableName),
                'e.entity_id = main_table.product_id AND e.attribute_id = ' . $code,
                array()
            )
            ->where('main_table.parent_item_id is null')
            ->where('e.value = ?', $supplier_id)
            ->order('o.entity_id DESC')
            ->group('o.entity_id');

        if ($this->getFilter('autoincrement_id')) {
            $collection->getSelect()->where(
                'o.increment_id LIKE ?',
                "%" . $this->getFilter('autoincrement_id') . "%"
            );
        }
        if ($this->getFilter('status')) {
            $collection->getSelect()->where(
                'o.status = ?',
                $this->getFilter('status')
            );
        }

        if ($this->getFilter('from') && strtotime($this->getFilter('from'))) {
            $datetime = new DateTime($this->getFilter('from'));
            $collection->getSelect()->where(
                'main_table.created_at >= ?',
                $datetime->format('Y-m-d') . " 00:00:00"
            );
        }
        if ($this->getFilter('to') && strtotime($this->getFilter('to'))) {
            $datetime = new DateTime($this->getFilter('to'));
            $collection->getSelect()->where(
                'main_table.created_at <= ?',
                $datetime->format('Y-m-d') . " 23:59:59"
            );
        }

        return $collection;
    }

    private function getFilter($key)
    {
        return $this->getRequest()->getPost($key);
    }

    private function getOrder($order_id)
    {
        if (!isset($this->_orders[$order_id])) {
            $this->_orders[$order_id] = Mage::getModel('sales/order')->load($order_id);
        }

        return $this->_orders[$order_id];
    }

    public function importShipmentAction()
    {
        try {
            if (isset($_FILES["import_shipment"])) {
                if ($_FILES["import_shipment"]["error"] > 0) {
                    switch ($_FILES["import_shipment"]["error"]) {
                        case 1:
                            throw new Mage_Exception(
                                "The uploaded file exceeds the upload_max_filesize directive in php.ini"
                            );
                            break;
                        case 2:
                            throw new Mage_Exception(
                                "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form"
                            );
                            break;
                        case 3:
                            throw new Mage_Exception("The uploaded file was only partially uploaded.");
                            break;
                        case 4:
                            throw new Mage_Exception("No file was uploaded.");
                            break;
                        case 6:
                            throw new Mage_Exception("Missing a temporary folder.");
                            break;
                        case 7:
                            throw new Mage_Exception("Failed to write file to disk.");
                            break;
                        case 8:
                            throw new Mage_Exception("A PHP extension stopped the file upload.");
                            break;
                    }
                } else {
                    $csvObject = new Varien_File_Csv();
                    $s         = $csvObject->getData($_FILES["import_shipment"]['tmp_name']);
                    if (isset($s[0][0])) {
                        if (! is_numeric($s[0][0])) {
                            unset($s[0]);
                        }
                        foreach ($s as $shipment) {
                            $this->_createShipment($shipment);
                        }
                    }
                }
            } else {
                throw new Mage_Exception("No file selected");
            }
            $this->getResponse()->setRedirect(Mage::getUrl('*/*/index/'));

        } catch (Exception $e) {
            Mage::getSingleton('core/session')->addError($e->getMessage());
            $this->getResponse()->setRedirect(Mage::getUrl('*/*/index/'));
        }
    }

    private function _createShipment($line)
    {
        $order = Mage::getModel('sales/order')->loadByIncrementId($line[0]);

        $products = array();
        foreach ($order->getAllItems() as $item) {
            if ($item->getProductId() && Mage::helper('marketplace')->isOwner($item->getProductId())) {
                $products[ $item->getId() ] = $item->getQtyOrdered();
            }
        }
        if ($order->getState() == 'canceled') {
            throw new Exception('You cannot create shipment for canceled order');
        }
        $shipment = $order->prepareShipment($products);

        $shipment->sendEmail(false)
                 ->setEmailSent(false)
                 ->register()
                 ->save();

        foreach ($shipment->getAllItems() as $item) {
            $orderItem = Mage::getModel('sales/order_item')->load($item->getOrderItemId());
            $orderItem->setQtyShipped($item->getQty() + $orderItem->getQtyShipped());
            $orderItem->save();
        }

        $sh = Mage::getModel('sales/order_shipment_track')
                  ->setShipment($shipment)
                  ->setData('title', $line[2])
                  ->setData('number', $line[1])
                  ->setData('carrier_code', 'custom')
                  ->setData('order_id', $order->getId());

        $sh->save();

        $loggedUser = Mage::getSingleton(
            'customer/session',
            array('name' => 'frontend')
        );
        $customer   = $loggedUser->getCustomer();
        $comment = $customer->getFirstname()
            . ' '
            . $customer->getLastname()
            . ' (#' . $customer->getId()
            . ') created shipment for '
            . count($products)
            . ' item(s)';

        $order->addStatusHistoryComment($comment);

        $fullyShipped = true;

        foreach ($order->getAllItems() as $item) {
            if ($item->getQtyToShip() > 0 && ! $item->getIsVirtual()
                 && ! $item->getLockedDoShip()
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
            }
        }
        $order->save();
    }


    public function downloadShipmentCsvAction()
    {
        /** @var Cminds_Marketplace_Helper_Csv $csvHelper */
        $csvHelper = Mage::helper("marketplace/csv");

        $headers   = array();
        $headers[] = 'ID';
        $headers[] = 'Tracking Code';
        $headers[] = 'Title';
        $csvHelper->prepareCsvHeaders('shipment_schema.csv');

        return $this->getResponse()->setBody(implode(',', $headers));
    }

    /**
     * Changes status of the order
     */
    public function changeOrderStatusAction()
    {
        /**
         * @var Cminds_Marketplace_Helper_Data $dataHelper
         * */
        $dataHelper = Mage::helper("marketplace");
        $supplier_id = $dataHelper->getSupplierId();
        $availableStatuses = $dataHelper->getAvailableVendorStatuses();

        $order_id = $this->getRequest()->getParam('order_id', 0);
        $status = $this->getRequest()->getParam('status', null);
        try {
            $order = $this->validate($order_id, $supplier_id, false);

            if (!in_array($status, $availableStatuses)) {
                Mage::throwException(
                    $this->__(
                        "Selected status %s cannot be used. Please check store configuration",
                        $status
                    )
                );
            }

            $order->setStatus($status);

            $vendor = Mage::getModel("customer/customer")->load($supplier_id);
            $history = $order->addStatusHistoryComment(
                $this->__(
                    "Status changed to %s by vendor %s",
                    $status,
                    $vendor->getName()
                ),
                false
            );

            $history->setIsCustomerNotified(false);
            $order->save();

            $this->_redirect(
                "marketplace/order/view",
                array(
                    "id" => $order_id
                )
            );
        } catch (Exception $e) {
            Mage::logException($e);

            $this->norouteAction();
            return;
        }
    }
    
    
    // Start Ubah Status dari customer
    public function ubahOrderStatusAction()
    {
        $order_id = $this->getRequest()->getParam('order_id', 0);
        
        // Mengambil data jumlah order

	    $sql = "SELECT increment_id FROM sales_flat_order WHERE entity_id = $order_id";
	
	    $connection=Mage::getSingleton('core/resource')->getConnection('core_write');
	
	    $orderId = $connection->fetchOne($sql);

        // End
        
        
        $order = Mage::getModel('sales/order')->loadByIncrementId($orderId);
        
        $order->setStatus('complete');
        
        
        
        $history = $order->addStatusHistoryComment($this->__("Status order confirm by Customer"),false);
        
        $this->_redirect("sales/order/view/order_id",array("order_id" => $order_id));
        $order->save();
        return;
 
    }
    // End Custom Ubah Status dari customer
    
    
    /**
     * Validates provided order
     *
     * @param $order_id
     * @param $supplier_id
     * @param bool $allowMultipleVendors
     *
     * @return mixed
     * @throws Mage_Core_Exception
     */
    protected function validate($order_id, $supplier_id, $allowMultipleVendors = true)
    {
        /**
         * @var Cminds_Marketplace_Helper_Order $orderHelper
         * */
        $orderHelper = Mage::helper("marketplace/order");
        $order = $this->getOrder($order_id);

        if (!$order->getId()) {
            Mage::throwException(
                $this->__(
                    "Order with ID %s does not exists",
                    $order_id
                )
            );
        }

        if (!$supplier_id) {
            Mage::throwException(
                $this->__(
                    "Missing supplier ID",
                    $order_id
                )
            );
        }

        if (!$orderHelper->isVendorOrder($order, $supplier_id)) {
            Mage::throwException(
                $this->__(
                    "Selected order #%s cannot be changed by this vendor %s",
                    $order->getIncrementId(),
                    $supplier_id
                )
            );
        }

        if (!$allowMultipleVendors) {
            if (!$orderHelper->isSingleVendor($order, $supplier_id)) {
                Mage::throwException(
                    $this->__(
                        "Order #%s contains items from multiple vendors",
                        $order->getIncrementId(),
                        $supplier_id
                    )
                );
            }
        }

        return $order;
    }
}
