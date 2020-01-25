<?php

/**
 * Class Cminds_Marketplaceamount_Model_Observer
 */
class Cminds_Marketplaceamount_Model_Observer
{

    protected $suppliers;

    protected $products;

    /**
     * Method is running when checkout is loading.
     *
     * @return $this
     */
    public function onCheckoutLoad()
    {
        $helper = Mage::helper('marketplaceamount');
        if (!$helper->isEnabled()) {
            return $this;
        }

        $quote = Mage::getSingleton('checkout/session')->getQuote();
        $addresses = $quote->getAllAddresses();

        foreach ($addresses as $address) {
            /** Supplier Amount validation */
            $this->validateSupplierAmount($address, $results);
        }

        foreach ($results as $result) {
            if (isset($result['error']) && $result['error']) {
                Mage::getSingleton('core/session')->addError($result['message']);
            }
        }

        Mage::getSingleton('core/session')->setSuppliersErrorsMessages($results);
    }

    /**
     * Method check possible to place order.
     *
     * If it is not possible placing is blocked.
     *
     * @return $this
     */
    public function checkIsPlaceOrderPossible()
    {
        $helper = Mage::helper('marketplaceamount');
        if (!$helper->isEnabled()) {
            return $this;
        }

        $quote = Mage::getSingleton('checkout/session')->getQuote();
        $addresses = $quote->getAllAddresses();
        $results = array();

        foreach ($addresses as $address) {
            /** Supplier Amount validation */
            $this->validateSupplierAmount($address, $results);
        }

        foreach ($results as $result) {
            if (isset($result['error']) && $result['error']) {
                if ($quote->getIsMultiShipping()) {
                    Mage::app()->getResponse()
                        ->setRedirect(Mage::getUrl('checkout/multishipping/billing'))
                        ->sendResponse();
                    throw Mage::exception('Mage_Core', $helper->__('You have not reached some of required value.'));
                } else {
                    throw Mage::exception('Mage_Core', $helper->__('You have not reached some of required value.'));
                }
            }
        }

        return $this;
    }

    /**
     * Method is running when cart is loading.
     *
     * @return $this
     */
    public function onCartLoad()
    {
        $helper = Mage::helper('marketplaceamount');
        $quote = Mage::getSingleton('checkout/session')->getQuote();
        $addresses = $quote->getAllAddresses();
        $results = [];

        if (!$helper->isEnabled()) {
            return $this;
        }

        foreach ($addresses as $address) {
            /** Supplier Amount validation */
            $this->validateSupplierAmount($address, $results);
        }

        foreach ($results as $result) {
            if (isset($result['error']) && $result['error']) {
                Mage::getSingleton('core/session')->addError($result['message']);
            }
        }

        Mage::getSingleton('core/session')->setSuppliersErrorsMessages($results);

        return $this;
    }

    /**
     * Validate total order amount for each supplier.
     *
     * @param Mage_Sales_Model_Quote_Address $address
     * @param array $results
     *
     * @return void
     */
    public function validateSupplierAmount($address, &$results)
    {
        if ($address->getQuote()->getIsVirtual()) {
            return $this;
        }

        $amounts = $this->collectAmountsByCreator($address);
        $qty = $this->collectQtyByCreator($address);

        foreach (array_keys($amounts) as $creatorId) {
            if ($creatorId === 'none') {
                continue;
            }
            if (isset($results[$creatorId])) {
                continue;
            }

            if (!isset($this->suppliers[$creatorId])) {
                $this->suppliers[$creatorId] = Mage::getModel('customer/customer')->load($creatorId);
            }

            $supplier = $this->suppliers[$creatorId];
            $limitValue = $this->getLimitValue($supplier);
            $limitQtyValue = $this->getLimitQtyValue($supplier);

            switch ($supplier->getSupplierMinOrderAmountPer()) {
                case Cminds_Marketplaceamount_Model_Source_MinimumAmount::NONE:
                    continue;
                    break;
                case Cminds_Marketplaceamount_Model_Source_MinimumAmount::ORDER:
                    $status = 0;
                    if ($limitValue > $amounts[$creatorId]) {
                        $status = 1;
                    }

                    if ($limitQtyValue > $qty[$creatorId]) {
                        $status = $status + 2;
                    }

                    if ($status > 0) {
                        $results[$creatorId] = $this->getErrorResult($supplier, $limitValue, $amounts[$creatorId],
                            $limitQtyValue, $qty[$creatorId], $status);
                    }

                    break;
                case Cminds_Marketplaceamount_Model_Source_MinimumAmount::DAY:
                    $dayAmount = $this->getDaySupplierAmount($creatorId);
                    $dayQty = $this->getDaySupplierQty($creatorId);
                    $status = 0;

                    if ($limitValue - $dayAmount > $amounts[$creatorId]) {
                        $status = 1;
                    }

                    if ($limitQtyValue - $dayQty > $qty[$creatorId]) {
                        $status = $status + 2;
                    }

                    if ($status > 0) {
                        $results[$creatorId] = $this->getErrorResult($supplier, $limitValue - $dayAmount,
                            $amounts[$creatorId], $limitQtyValue - $dayQty, $qty[$creatorId], $status);
                    }
                    break;
            }
        }

        return $this;
    }

    /**
     * Collect amount by creator.
     *
     * @param Mage_Sales_Model_Quote_Address $address
     *
     * @return array
     */
    public function collectAmountsByCreator($address)
    {
        $amounts = array();

        foreach ($address->getQuote()->getItemsCollection() as $item) {
            $currentAmount = 0;
            $productId = $item->getProductId();

            if (!isset($this->products[$productId])) {
                $this->products[$productId] =
                    Mage::getModel('catalog/product')->load($productId);
            }

            $creatorId = $this->products[$productId]->getCreatorId();

            if ($creatorId) {
                if (isset($amounts[$creatorId])) {
                    $currentAmount = $amounts[$creatorId];
                }
                $amounts[$creatorId] = $currentAmount + $item->getBaseRowTotal() - $item->getBaseDiscountAmount();
            } else {
                if (isset($amounts['none'])) {
                    $currentAmount = $amounts['none'];
                }
                $amounts['none'] = $currentAmount + $item->getBaseRowTotal() - $item->getBaseDiscountAmount();
            }
        }

        return $amounts;
    }

    /**
     * Collect amount by creator.
     *
     * @param Mage_Sales_Model_Quote_Address $address
     *
     * @return array
     */
    public function collectQtyByCreator($address)
    {
        $qty = array();

        foreach ($address->getQuote()->getItemsCollection() as $item) {
            $currentQty = 0;
            $productId = $item->getProductId();

            if (!isset($this->products[$productId])) {
                $this->products[$productId] =
                    Mage::getModel('catalog/product')->load($productId);
            }

            $creatorId = $this->products[$productId]->getCreatorId();
            if ($creatorId) {
                if (isset($qty[$creatorId])) {
                    $currentQty = $qty[$creatorId];
                }

                $qty[$creatorId] = $currentQty + $item->getQty();
            } else {
                if (isset($qty['none'])) {
                    $currentQty = $qty['none'];
                }

                $qty['none'] = $currentQty + 1;
            }
        }

        return $qty;
    }

    /**
     * Get total supplier amount orders for current day.
     *
     * @param int $creatorId
     *
     * @return float
     */
    public function getDaySupplierAmount($creatorId)
    {
        $daySupplierAmount = Mage::getResourceModel('marketplaceamount/sales_order_collection')
            ->getDaySupplierAmount($creatorId);

        return $daySupplierAmount;
    }

    public function getDaySupplierQty($creatorId)
    {
        $daySupplierQty = Mage::getResourceModel('marketplaceamount/sales_order_collection')
            ->getDaySupplierQty($creatorId);

        return $daySupplierQty;
    }

    /**
     * Prepare error message.
     *
     * @param Mage_Customer_Model_Customer $supplier
     * @param int $limitValue
     * @param int $amount
     * @param int $limitQty
     * @param int $qty
     * @param int $status
     *
     * @return array
     */
    public function getErrorResult($supplier, $limitValue, $amount, $limitQty, $qty, $status)
    {
        $coreHelper = Mage::helper('core');
        $result['error'] = true;
        if ($status === 1) {
            $result['message'] = Mage::helper('marketplaceamount')->__(
                'Minimum Order Amount for %s products should be %s. Currently, you reached %s in your Cart.',
                Mage::helper('supplierfrontendproductuploader')->getSupplierName($supplier->getId()),
                $coreHelper->currency($limitValue, true, false),
                $coreHelper->currency($amount, true, false)
            );
        } else {
            if ($status === 2) {
                $result['message'] = Mage::helper('marketplaceamount')->__(
                    'Minimum Order Qty for %s products should be %s. Currently, you reached %s in your Cart.',
                    Mage::helper('supplierfrontendproductuploader')->getSupplierName($supplier->getId()),
                    round($limitQty),
                    $qty
                );
            } else {
                $result['message'] = Mage::helper('marketplaceamount')->__(
                    'Minimum Order Amount for %s products should be %s And the product Qty should be %s. Currently, you reached %s in the amount and %s in the Qty in your Cart.',
                    Mage::helper('supplierfrontendproductuploader')->getSupplierName($supplier->getId()),
                    $coreHelper->currency($limitValue, true, false),
                    round($limitQty),
                    $coreHelper->currency($amount, true, false),
                    $qty
                );
            }
        }

        return $result;
    }

    /**
     * Get minimum required amount for supplier.
     *
     * @param Mage_Customer_Model_Customer $supplier
     *
     * @return float
     */
    public function getLimitValue($supplier)
    {
        $helper = Mage::helper('marketplaceamount');
        $value = $supplier->getSupplierMinOrderAmount();

        if (!Mage::getSingleton('customer/session')->isLoggedIn()) {
            return $value;
        }

        if (!$helper->assignCustomersEnabled()) {
            return $value;
        }

        $supplierToCustomer = Mage::getResourceModel('cminds_assignsupplierscustomers/customers_collection')
            ->getSupplierToCustomer($supplier->getId(), Mage::getSingleton('customer/session')->getId());

        if (!$supplierToCustomer){
            return $value;
        }

        if (is_null($supplierToCustomer->getMinOrderAmount())){
            return $value;
        }

        $value = $supplierToCustomer->getMinOrderAmount();
        return $value;
    }

    /**
     * Get minimum required Qty for supplier.
     *
     * @param Mage_Customer_Model_Customer $supplier
     *
     * @return float
     */
    public function getLimitQtyValue($supplier)
    {
        $value = $supplier->getSupplierMinOrderQty();
        if (!Mage::getSingleton('customer/session')->isLoggedIn()) {
            return $value;
        }

        $helper = Mage::helper('marketplaceamount');
        if (!$helper->assignCustomersEnabled()) {
            return $value;
        }

        return $value;
    }

    public function navLoad(Varien_Event_Observer $observer)
    {
        $event = $observer->getEvent();
        $items = $event->getItems();
        $helper = Mage::helper('marketplaceamount');

        if ($helper->isEnabled()) {
            $items['ORDER_RESTRICTIONS'] = [
                'label' => $helper->__('Order Restrictions'),
                'url' => 'supplier/amount/view',
                'parent' => 'SETTINGS',
                'sort' => 5
            ];
        }

        $observer->getEvent()->setItems($items);
    }
}