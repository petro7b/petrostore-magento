<?php

/**
 * Class Cminds_MarketplaceDeliveryDate_Model_Mysql4_Sales_Order_Item_Collection
 */
class Cminds_MarketplaceDeliveryDate_Model_Mysql4_Sales_Order_Item_Collection
    extends Mage_Sales_Model_Resource_Order_Item_Collection
{

    /**
     * Fiter Collection by specific Date.
     *
     * @param $date
     * @return $this
     */
    public function filterBySpecificDay($date)
    {
        $this->addFieldToFilter('delivery_date', array('eq' => $date));

        return $this;
    }

    /**
     *Get Order Items grouped by Customer Email.
     *
     * @return array
     */
    public function getGroupedByCustomers()
    {
        $customerItems = array();
        foreach ($this as $item) {
            $order = $item->getOrder();
            $customerItems[$order->getCustomerEmail()]['items'][] = $item;
            $customerItems[$order->getCustomerEmail()]['name'] = $order->getCustomerName();
        }

        return $customerItems;
    }

}