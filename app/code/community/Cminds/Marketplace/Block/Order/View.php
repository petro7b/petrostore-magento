<?php
class Cminds_Marketplace_Block_Order_View extends Mage_Core_Block_Template {
    protected $statusCollection;
    private $vendorShipping = null;
    private $order = null;
    public function _construct() {
        $this->setTemplate('marketplace/order/view.phtml');
    }
    public function getOrder()
    {
        if (!$this->order) {
            $id = Mage::registry('order_id');
            $this->order = Mage::getModel('sales/order')->load($id);
        }

        return $this->order;
    }
    public function getItems()
    {
        $_order = $this->getOrder();
        $_items = array();

        foreach ($_order->getAllItems() as $item) {
            $product = Mage::getModel('catalog/product')->load($item->getProductId());

            if ($product->getCreatorId() == $this->getLoggedVendorId()) {
                $_items[] = $item;
            }
        }

        return $_items;
    }

    public function getCurrentTab()
    {
        return Mage::app()->getRequest()->getParam('tab', 'products');
    }

    public function getSupplierItems($orderItems, $modelItems) {
        $otherSupplierItems = array();
        $orderItemsIds = array();
        foreach($orderItems as $orderItem) {
            $orderItemsIds[] = $orderItem->getId();
        }

        $items = Mage::getModel($modelItems)->getCollection()->addFieldToFilter('parent_id', array("in" => $orderItemsIds));

        foreach ($items as $item) {
            $productId = $item->getProductId();
            if(!Mage::helper('marketplace')->isOwner($productId)) {
                $otherSupplierItems[] = $item->getParentId();
            }
        }
        return $otherSupplierItems;
    }

    public function canCreateInvoice($orderItems)
    {
        $canCreateInvoice = false;
        foreach ($orderItems as $orderItem) {
            if(Mage::helper('marketplace')->isOwner($orderItem->getProductId()) && $orderItem->getQtyInvoiced() == 0) {
                $canCreateInvoice = true;
            }
        }
        return $canCreateInvoice;
    }

    public function canCreateShipment($orderItems)
    {
        $canCreateShipment = false;
        foreach ($orderItems as $orderItem) {
            if(Mage::helper('marketplace')->isOwner($orderItem->getProductId()) && $orderItem->getQtyShipped() == 0) {
                $canCreateShipment = true;
            }
        }
        return $canCreateShipment;
    }

    public function getSupplierShippingCosts()
    {
        return $this->getVendorShippingData()->getShippingMethodPrice();
    }

    public function getSupplierShippingName()
    {
        return $this->getVendorShippingData()->getShippingMethodName();
    }

    public function canChangeOrderStatus()
    {
        if ($this->getOrder()->getStatus() == "canceled") {
            return false;
        }

        /**
         * @var Cminds_Marketplace_Helper_Data $dataHelper
         * @var Cminds_Marketplace_Helper_Order $orderHelper
         * */
        $dataHelper = Mage::helper("marketplace");
        $orderHelper = Mage::helper("marketplace/order");

        if (!$dataHelper->canChangeOrderStatus()) {
            return false;
        }

        $availableStatuses = $dataHelper->getAvailableVendorStatuses();

        if (count($availableStatuses) === 0) {
            return false;
        }

        if (!$orderHelper->isSingleVendor(
            $this->getOrder(),
            $this->getLoggedVendorId()
        )) {
            return false;
        }

        return true;
    }

    public function getStatusSelectHtml()
    {
        /**
         * @var Cminds_Marketplace_Helper_Data $dataHelper
         * */
        $dataHelper = Mage::helper("marketplace");
        $availableStatuses = $dataHelper->getAvailableVendorStatuses();

        $availableStatusesArray = array();

        foreach ($availableStatuses as $status) {
            $availableStatusesArray[$status] = $this->findOrderStatusLabel($status);
        }

        $select = $this->getLayout()->createBlock('core/html_select')
                       ->setName("status_id")
                       ->setId('status_id')
                       ->setTitle(Mage::helper('marketplace')->__('Status'))
                       ->setClass('validate-select form-control')
                       ->setValue($this->getOrder()->getStatus())
                       ->setOptions($availableStatusesArray);

        return $select->getHtml();
    }

    protected function findOrderStatusLabel($statusCode)
    {
        $statusCollection = $this->getStatusCollection();
        foreach ($statusCollection as $statusEntity) {
            if ($statusEntity['status'] == $statusCode) {
                return $statusEntity['label'];
            }
        }

        return $statusCode;
    }

    protected function getStatusCollection()
    {
        if (!$this->statusCollection) {
            $this->statusCollection = Mage::getModel('sales/order_status')
                ->getResourceCollection()
                ->getData();
        }

        return $this->statusCollection;
    }

    public function getChangeStatusUrl()
    {
        return Mage::getUrl(
            "marketplace/order/changeOrderStatus",
            array(
                "order_id" => $this->getOrder()->getId()
            )
        );
    }
    
    // Ubah status dari customer
    public function getUbahStatusUrl()
    {
        return Mage::getUrl(
            "marketplace/order/ubahOrderStatus",
            array(
                "order_id" => $this->getOrder()->getId()
            )
        );
    }
    // End

    protected function getLoggedVendorId()
    {
        /**
         * @var Cminds_Marketplace_Helper_Data $dataHelper
         * */
        $dataHelper = Mage::helper("marketplace");
        return $dataHelper->getSupplierId();
    }

    protected function canShowPaymentInfo()
    {
        /**
         * @var Cminds_Marketplace_Helper_Data $dataHelper
         * */
        $dataHelper = Mage::helper("marketplace");
        return $dataHelper->canShowPaymentInfo();
    }

    protected function canShowShippingInfo()
    {
        /**
         * @var Cminds_Marketplace_Helper_Data $dataHelper
         * */
        $dataHelper = Mage::helper("marketplace");
        return $dataHelper->canShowShippingInfo();
    }

    protected function getVendorShippingData()
    {
        if (!$this->vendorShipping) {
            $this->vendorShipping = Mage::getModel("marketplace/vendorShipping")
                ->getCollection()
                ->addFieldToFilter("vendor_id", $this->getLoggedVendorId())
                ->addFieldToFilter("order_id", $this->getOrder()->getId())
                ->getFirstItem();
        }

        return $this->vendorShipping;
    }
}