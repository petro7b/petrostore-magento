<?php
class Cminds_Marketplaceamount_Model_Adminhtml_Observer
{

    public function onCustomerSave(Varien_Event_Observer $observer)
    {
        $request = $observer->getRequest();
        $customer = $observer->getCustomer();
        $postData = $request->getPost();

        if (!Mage::helper('supplierfrontendproductuploader')->isSupplier($customer->getId())) {
            return false;
        }

        try {
            if (isset($postData['supplier_min_order_amount'])) {
                $customer->setData('supplier_min_order_amount', $postData['supplier_min_order_amount']);
            }

            if (isset($postData['supplier_min_order_qty'])) {
                $customer->setData('supplier_min_order_qty', $postData['supplier_min_order_qty']);
            }

            if (isset($postData['supplier_min_order_amount_per'])) {
                $customer->setData('supplier_min_order_amount_per', $postData['supplier_min_order_amount_per']);
            }

            $customer->save();
        } catch (Exception $e) {
            Mage::getSingleton('core/session')->addError($e->getMessage());
            Mage::log($e->getMessage());
        }
    }
}
