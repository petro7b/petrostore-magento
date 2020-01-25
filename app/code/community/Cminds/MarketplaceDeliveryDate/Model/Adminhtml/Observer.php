<?php
class Cminds_MarketplaceDeliveryDate_Model_Adminhtml_Observer
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
            if (isset($postData['order_lead_time'])) {
                $customer->setData('order_lead_time', $postData['order_lead_time']);
            }

            if (isset($postData['cutoff_time_finish'])) {
                $customer->setData('cutoff_time_finish', $postData['cutoff_time_finish']);
            }

            if (isset($postData['weekdays'])) {
                $customer->setData('allowed_week_days', json_encode($postData['weekdays']));
            }

            if (isset($postData['excluded_days']) &&
                    is_array($postData['excluded_days']) &&
                    count($postData['excluded_days']) > 0) {
                foreach ($postData['excluded_days'] as $date) {
                    if (empty($date)) {
                        continue;
                    }
                    $excludedDate = Mage::getModel('marketplace_delivery_date/excluded');
                    $excludedDate->setSupplierId($customer->getId());
                    $excludedDate->setDate($date);
                    $excludedDate->save();
                }
            }

            $customer->save();
        } catch (Exception $e) {
            Mage::getSingleton('core/session')->addError($e->getMessage());
            Mage::log($e->getMessage());
        }
    }
}
