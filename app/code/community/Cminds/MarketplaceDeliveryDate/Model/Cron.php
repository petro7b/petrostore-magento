<?php

/**
 * Class Cminds_MarketplaceDeliveryDate_Model_Cron
 */
class Cminds_MarketplaceDeliveryDate_Model_Cron
{

    /**
     * @var array
     */
    protected $groupedItems = array();


    /**
     * Notify customers with deliveries.
     *
     * @return $this
     */
    public function run()
    {
        if (!$this->canRun()) {
            return $this;
        }

        try {
            $this->sendNotifications();
        } catch (Exception $e) {
            Mage::log($e->getMessage(), null, 'delivery-date.log');
        }

        return $this;
    }

    /**
     * Validate Admin Configuration.
     *
     * @return bool
     */
    public function canRun()
    {
        return Mage::helper('marketplace_delivery_date')->getNotificationsConfig();
    }

    /**
     *Send email to customers which have delivery date scheduled on the same day.
     */
    public function sendNotifications()
    {
        $currentDate = Mage::getModel('core/date')->gmtDate('Y-m-d');

        $helper = Mage::helper('marketplace_delivery_date');
        $store = Mage::app()->getStore();
        $storeId = $store->getId();
        $emailTemplate = Mage::getModel('core/email_template');

        $emailTemplate->setTemplateSubject(
            $helper->__(
                'Delivery Notification'
            )
        );

        $sender  = array(
            'name' => Mage::getStoreConfig('trans_email/ident_support/name', $storeId),
            'email' => Mage::getStoreConfig('trans_email/ident_support/email', $storeId)
        );

        $orderItems = Mage::getResourceModel('marketplace_delivery_date/sales_order_item_collection')
            ->filterBySpecificDay($currentDate);

        foreach ($this->getGroupedItems($orderItems) as $email => $data) {
            $emailTemplate->sendTransactional(
                'delivery_email_order_items_template',
                $sender,
                $email,
                $data['name'],
                array(
                    'items' => $data['items'],
                    'date' => $currentDate
                ),
                $storeId
            );
        }
    }

    /**
     * Get order items grouped by customer email.
     *
     * @param $orderItems Cminds_MarketplaceDeliveryDate_Model_Mysql4_Sales_Order_Item_Collection
     * @return array
     */
    public function getGroupedItems($orderItems)
    {
        if (!$this->groupedItems) {
            $this->groupedItems = $orderItems->getGroupedByCustomers();
        }

        return $this->groupedItems;
    }

}