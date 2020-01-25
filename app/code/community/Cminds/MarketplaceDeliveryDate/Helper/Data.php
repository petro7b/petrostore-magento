<?php
class Cminds_MarketplaceDeliveryDate_Helper_Data extends Mage_Core_Helper_Data {

    private $used = array();

    /**
     * Find date.
     *
     * @param $item
     * @param bool $customer_address_id
     * @return mixed
     */
    public function findDate($item, $customer_address_id = false)
    {

        $deliveryDate = $this->getDeliveryDateConfig();
        $deliveryDates = Mage::getSingleton('checkout/session')->getDeliveryDate();

        if ($deliveryDate == 1 and count($deliveryDates) > 0) {
            foreach ($deliveryDates as $index => $date) {
                $arr = explode('_', $index);
                if (isset($arr[1])) {
                    if ($arr[1] == $item->getProductId()) {
                        if ($customer_address_id) {
                            if ($arr[0] != $customer_address_id) {
                                continue;
                            }
                        }

                        if (!in_array($index, $this->used)) {
                            $this->used[] = $index;
                            return $date;
                        }
                    }
                } elseif (isset($arr[0]) && $arr[0] == $item->getId()) {
                    if (!in_array($index, $this->used)) {
                        $this->used[] = $index;
                        return $date;
                    }
                }
            }
        }
    }

    /**
     * Flush data cache.
     */
    public function flushDateCache()
    {
        $this->used = array();
    }

    /**
     * Check delivery date enabled.
     *
     * @return bool
     */
    public function getDeliveryDateConfig()
    {
        $deliveryDate = Mage::getStoreConfig('delivery_date_configuration/general/set_delivery_date');

        return (bool) $deliveryDate;
    }

    /**
     * Check Customer Notifications enabled.
     *
     * @return bool
     */
    public function getNotificationsConfig()
    {
        $config = Mage::getStoreConfig('delivery_date_configuration/general/cron_notifications');

        return (bool)$config;
    }

    /**
     * Check order lead time enabled.
     *
     * @return bool
     */
    public function getOrderLeadTimeConfig()
    {
        $config = Mage::getStoreConfig('delivery_date_configuration/general/order_lead_time');

        return (bool)$config;
    }

    /**
     * Calculate possible delivery date by supplier.
     *
     * @param $supplier
     *
     * @return int
     */
    public function calculatePossibleDeliveryDate($supplier)
    {
        $orderLeadTime = $supplier->getOrderLeadTime();
        $cutoffTimeFinish = $supplier->getCutoffTimeFinish();

        $orderLeadTimeEnabled = Mage::helper('marketplace_delivery_date')->getOrderLeadTimeConfig();
        $days = 0;

        if ($orderLeadTimeEnabled === true) {
            if (!empty($orderLeadTime)) {
                $days = $orderLeadTime;
            }
        }
        if (!empty($cutoffTimeFinish)) {
            if (strtotime($cutoffTimeFinish) < strtotime(date("H:i"))) {
                $days += 1;
            }
        }

        return $days;
    }

    /**
     * Get possible days week for supplier.
     *
     * @param $supplier
     *
     * @return int
     */
    public function getPossibleDaysWeek($supplier)
    {
        $weekDays = $supplier->getAllowedWeekDays();

        if ($weekDays === null) {
            $weekDays = json_encode(array('0', '1', '2', '3', '4' ,'5', '6'));
        }

        return $weekDays;
    }

    /**
     * Check enable for Cminds_Assignsupplierstocustomers module.
     *
     * @return bool
     */
    public function assignsupplierstocustomersEnabled()
    {
        $enabled = Mage::helper('core')->isModuleEnabled('Cminds_Assignsupplierstocustomers');

        return $enabled;
    }

    /**
     * Get logged customer object.
     *
     * @return Mage_Customer_Model_Customer
     */
    public function getCustomer()
    {
        return Mage::getSingleton('customer/session')->getCustomer();
    }
}
